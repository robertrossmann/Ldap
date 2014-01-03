<?php

/**
 * Alaneor/Ldap
 *
 * Licensed under the BSD (3-Clause) license
 * For full copyright and license information, please see the LICENSE file
 *
 * @author      Robert Rossmann <rr.rossmann@me.com>
 * @copyright   2013 Robert Rossmann
 * @link        https://github.com/Alaneor/Ldap
 * @license     http://choosealicense.com/licenses/bsd-3-clause   BSD (3-Clause) License
 */


namespace Ldap;

use \Evenement\EventEmitterInterface;
use \Evenement\EventEmitterTrait;
use \Ldap\Internal\Request;
use \Ldap\Internal\LdapLink;

/**
 * Class encapsulation for php's ldap functions
 */
class Ldap implements EventEmitterInterface
{
  use EventEmitterTrait;

  protected static $allowed_methods = [
    'add',
    'bind',
    'compare',
    'delete',
    'mod_add',
    'mod_del',
    'mod_replace',
    'modify',
    'rename',
    'sasl_bind',
    'set_option',
    'set_rebind_proc',
    'start_tls',
    'unbind',
  ];


  protected $link;              // The ldap link
  protected $rootDSE;           // The rootDSE entry of the server, if loaded by self::rootDSE()
  protected $rootDSEAttributes; // The rootDSE attributes that were requested to be retrieved last time


  /**
   * Connect to an ldap server at specified port
   *
   * By default, the connection will be established using LDAPv3
   * protocol. If you need to use LDAPv2 you can set the protocol version
   * yourself after construction, but before you attempt to bind.
   *
   * ```
   * $ldap = new Ldap\Ldap( 'example.com' );  // Uses LDAPv3 automatically
   * $ldap->set_option( Ldap\Option::ProtocolVersion, 2 ); Downgrade to v2
   * ```
   *
   * @param   string    $server      A server to be connected to
   * @param   int       $port        An optional port to use for connection
   */
  public function __construct( $server, $port = 389 )
  {
    $this->initModules(); // Load and initialise modules

    // Allow dependency injection for testing purposes
    $this->link = func_num_args() > 2 ? func_get_arg( 2 ) : new LdapLink( $server, $port );

    // Use LDAPv3 by default
    $this->set_option( Option::ProtocolVersion, 3 );

    $this->emit( 'new', [$this] );
  }

  /**
   * Get the instance of the encapsulated ldap link resource
   *
   * @return  Ldap\Internal\LdapLink    An instance that encapsulates the ldap link resource
   */
  public function resource()
  {
    return $this->link;
  }

  /**
   * Perform an ldap operation on this connection
   *
   * @param     Request    $req       An instance of the Request class describing the operation to be performed
   *
   * @return    Response              An instance of the Response class holding the data returned from server
   */
  public function execute( Request $req )
  {
    $this->emit( 'request', [$this, $req] );

    $req->prepareForExecution( $this );       // Requests sometimes need to do stuff with link before actual execution
    $args = $req->getActionParameters();      // Arguments to be passed to the action

    $return = call_user_func_array( [$this->link, $req->action()], $args );

    return new Response( $this, $req, $return );
  }

  /**
   * Read the rootDSE data
   *
   * @param   string|array    $attributes     A single entry or an array of rootDSE entries to be loaded
   * @param   bool            $force          If true, any and all previously loaded rootDSE data
   *                                          will be discarded and loaded from the server again
   *
   * @return  Response        An instance of the Response class containing the rootDSE data
   *                          on success, or the reason for failure otherwise
   */
  public function rootDSE( $attributes = ['*', '+'], $force = false )
  {
    $attributes = (array)$attributes;
    $attributes = array_map( 'strtolower', $attributes );

    // If we already have some data from rootDSE loaded, check if there's
    // more required; otherwise just return the current data
    if ( $this->rootDSE instanceof Response && is_array( $this->rootDSE->data ) )
    {
      $missing  = array_diff( $attributes, $this->rootDSEAttributes );

      // Nothing more to be loaded and no force-reload required - return the rootDSE!
      if ( empty( $missing ) && ! $force ) return $this->rootDSE;
    }

    // Read the rootDSE entry from the server
    $req = new \Ldap\Request\ReadRequest;
    $req->base( '' )->attributes( $attributes );
    $resp = $this->execute( $req );

    // If the query was not successful, return the response to
    // the other guy to figure out what to do
    if ( ! $resp->ok() ) return $resp;

    $this->rootDSEAttributes  = $attributes;  // Save the list of loaded attributes for later
    $this->rootDSE            = $resp;        // Save the response for later

    return $this->rootDSE;
  }

  public function sort( Response $response, $attribute )
  {
    $this->link->sort( $this->link, $response->result, $attribute );

    return new Response( $this, $response->request, $response->result );
  }

  public function get_option( $option )
  {
    $return = null;

    $this->link->get_option( $this->link, $option, $return );

    return new Response( $this, null, $return );
  }

  public function __call( $method, $args )
  {
    // Check if this method can be called on this object
    if ( ! in_array( $method, static::$allowed_methods ) )
    {
      $trace = debug_backtrace();
      $method = $trace[0]['class'] . $trace[0]['type'] . $method;
      trigger_error(
        "Call to undefined method $method in " . $trace[0]['file'] .
        " on line " . $trace[0]['line'],
        E_USER_ERROR
      );
    }

    $return = call_user_func_array( [$this->link, $method], $args );

    return new Response( $this, null, $return );
  }

  /**
   * Load and initialise the enabled modules
   *
   * @return      void
   */
  protected function initModules()
  {
    // Get all enabled modules
    $modules = Internal\ModuleManager::getModules();

    foreach ( $modules as $module )
    {
      $module = new $module;
      $module->attachEvents( $this );
    }
  }
}
