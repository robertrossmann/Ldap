<?php

/**
 * Alaneor/Ldap
 *
 * Licensed under the BSD (3-Clause) license
 * For full copyright and license information, please see the LICENSE file
 *
 * @author			Robert Rossmann <rr.rossmann@me.com>
 * @copyright		2012-2013 Robert Rossmann
 * @link			https://github.com/Alaneor/Ldap
 * @license			http://choosealicense.com/licenses/bsd-3-clause		BSD (3-Clause) License
 */


namespace Ldap;

/**
 * Class encapsulation for php's ldap functions
 */
class Ldap
{
	protected static $allowed_methods = [
		'add',
		'bind',
		'compare',
		'connect',
		'count_entries',
		'delete',
		'dn2ufn',
		'err2str',
		'errno',
		'error',
		'explode_dn',
		'get_attributes',
		'get_dn',
		'get_entries',
		'get_option',
		'get_values_len',
		'ldap_8859_to_t61',
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
		't61_to_8859',
	];


	protected $resource;		// The ldap resource


	/**
	 * Connect to an ldap server at specified port
	 *
	 * @param		string		A server to be connected to
	 * @param		int			An optional port to use for connection
	 */
	public function __construct( $server, $port = 389 )
	{
		$this->resource = ldap_connect( $server, $port );
	}

	/**
	 * Get the resource for the ldap connection
	 *
	 * @return      Resource        A resource identifier for the given ldap connection
	 */
	public function resource()
	{
		return $this->resource;
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
			trigger_error(
				"Call to undefined function $method in " . $trace[0]['file'] .
				" on line " . $trace[0]['line'],
				E_USER_ERROR
			);
		}

		// Prepend the resource to the arguments array
		array_unshift( $args, $this->resource );

		// Prefix the method with ldap_ if it's not already prefixed
		if ( stripos( $method, 'ldap_' ) !== 0 ) $method = 'ldap_' . $method;

		$return = call_user_func_array( $method, $args );

		return new Response( $this, $return );
	}

	/**
	 * @internal
	 */
	public function __destruct()
	{
		ldap_unbind( $this->resource );
	}
}
