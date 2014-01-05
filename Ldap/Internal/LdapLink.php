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


namespace Ldap\Internal;

/**
 * Object encapsulation of the ldap link resource object
 *
 * Only methods that use the link resource can be used here - other methods will be rejected.
 */
class LdapLink
{
  protected static $methods = [
    'add',
    'bind',
    'compare',
    'delete',
    'errno',
    'error',
    'get_entries',
    'ldap_list',
    'ldap_read',
    'ldap_search',
    'mod_add',
    'mod_del',
    'mod_replace',
    'modify',
    'paged_result',
    'rename',
    'sasl_bind',
    'set_option',
    'set_rebind_proc',
    'sort',
    'start_tls',
    'unbind',
  ];


  protected $resource;  // The actual ldap link resource


  /**
   * Create a new instance of the LinkResource
   *
   * @param     $server     IP address or hostname to connect to
   * @param     $port       Port to be used for connection
   */
  public function __construct( $server, $port = 389 )
  {
    $this->resource = ldap_connect( $server, $port );
  }

  public function get_option( $option, &$return )
  {
    ldap_get_option( $this->resource, $option, $return );
  }

  public function paged_result_response( $result, &$cookie = null, &$estimated = null )
  {
    ldap_control_paged_result_response( $this->resource, $result, $cookie, $estimated );
  }

  public function parse_reference( $entry, &$referrals )
  {
    ldap_parse_reference( $this->resource, $entry, $referrals );
  }

  public function parse_result( $result, &$errcode, &$matcheddn = null, &$errmsg = null, &$referrals = null )
  {
    ldap_parse_result( $this->resource, $result, $errcode, $matcheddn, $errmsg, $referrals );
  }

  public function __call( $method, $args )
  {
    // Check if this method can be called on this object
    if ( ! in_array( $method, static::$methods ) )
    {
      $trace = debug_backtrace();
      $method = $trace[0]['class'] . $trace[0]['type'] . $method;
      trigger_error(
        "Call to undefined method $method in " . $trace[0]['file'] .
        " on line " . $trace[0]['line'],
        E_USER_ERROR
      );
    }

    array_unshift( $args, $this->resource );  // Prepend the resource to the arguments array
    if ( stripos( $method, 'ldap_' ) !== 0 ) $method = 'ldap_' . $method; // Prefix the method name if necessary

    return call_user_func_array( $method, $args );
  }
}
