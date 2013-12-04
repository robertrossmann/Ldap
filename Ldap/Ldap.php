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

use Ldap\Modules\ModuleManager;
use \Evenement\EventEmitterInterface;
use \Evenement\EventEmitterTrait;

/**
 * Class encapsulation for php's ldap functions
 */
class Ldap implements EventEmitterInterface
{
  use EventEmitterTrait;

  protected static $allowed_static_methods = [
    'dn2ufn',
    'err2str',
    'explode_dn',
    'ldap_8859_to_t61',
    't61_to_8859',
  ];

  protected static $allowed_methods = [
    'add',
    'bind',
    'compare',
    'delete',
    'ldap_list',
    'ldap_read',
    'ldap_search',
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


  protected $resource;          // The ldap resource
  protected $rootDSE;           // The rootDSE entry of the server, if loaded by self::rootDSE()
  protected $rootDSEAttributes; // The rootDSE attributes that were requested to be retrieved last time
  protected $e;                 // An instance of EventEmitter to handle events for the module system


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
    $this->loadModules();

    $this->resource = ldap_connect( $server, $port );
    // Use LDAPv3 by default
    $this->set_option( Option::ProtocolVersion, 3 );

    $this->emit( 'new' );
  }

  /**
   * Get the resource for the ldap connection
   *
   * @return  Resource        A resource identifier for the given ldap connection
   */
  public function resource()
  {
    return $this->resource;
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
    $resp = $this->ldap_read( '', 'objectclass=*', $attributes );

    // If the query was not successful, return the response to
    // the other guy to figure out what to do
    if ( ! $resp->ok() ) return $resp;

    $this->rootDSEAttributes  = $attributes;  // Save the list of loaded attributes for later
    $this->rootDSE            = $resp;        // Save the response for later

    return $this->rootDSE;
  }

  public function sort( Response $response, $attribute )
  {
    ldap_sort( $this->resource, $response->result, $attribute );

    return new Response( $this, $response->result );
  }

  public function get_option( $option )
  {
    $return = null;

    ldap_get_option( $this->resource, $option, $return );

    return new Response( $this, $return );
  }

  public function paged_result()
  {
    $args = func_get_args();

    // Prepend the resource to the arguments array
    array_unshift( $args, $this->resource );

    $return = call_user_func_array( 'ldap_control_paged_result', $args );

    return new Response( $this, $return );
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

    // Prepend the resource to the arguments array
    array_unshift( $args, $this->resource );

    // Prefix the method with ldap_ if it's not already prefixed
    ( stripos( $method, 'ldap_' ) !== 0 ) && $method = 'ldap_' . $method;

    $return = call_user_func_array( $method, $args );

    return new Response( $this, $return );
  }

  /**
   * Load and initialise the enabled modules
   *
   * @return      void
   */
  protected function loadModules()
  {
    // Get all enabled modules
    $modules = ModuleManager::getModules();

    foreach ( $modules as $module )
    {
      $module = new $module;
      $module->attachEvents( $this );
    }
  }


  public static function __callStatic( $method, $args )
  {
    // Check if this method can be called on this object
    if ( ! in_array( $method, static::$allowed_static_methods ) )
    {
      $trace = debug_backtrace();
      $method = $trace[0]['class'] . $trace[0]['type'] . $method;
      trigger_error(
        "Call to undefined method $method in " . $trace[0]['file'] .
        " on line " . $trace[0]['line'],
        E_USER_ERROR
      );
    }

    // Prefix the method with ldap_ if it's not already prefixed
    ( stripos( $method, 'ldap_' ) !== 0 ) && $method = 'ldap_' . $method;

    $data = call_user_func_array( $method, $args );
    if ( isset( $data['count'] ) ) unset( $data['count'] ); // No one cares!

    return $data;
  }
}
