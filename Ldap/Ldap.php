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


	protected $resource;		// The ldap resource
	protected $rootDSE;			// The rootDSE entry of the server, if loaded by self::rootDSE()


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

	/**
	 * Read the rootDSE entry and optionally include extra information
	 *
	 * @param		string|array		A single entry or an array of rootDSE entries to be present
	 * 									in addition to the default set ( ['*', '+'] )
	 *
	 * @return		array|Response		An array with all rootDSE entries or an instance of
	 * 									the Response class containing the error information
	 */
	public function rootDSE( $optional = null )
	{
		$optional = (array)$optional;
		$optional = array_map( 'strtolower', $optional );

		// Do not load data from server if we already have it loaded
		if ( ! empty( $this->rootDSE ) )
		{
			$present = array_keys( $this->rootDSE );
			$missing = array_diff( $optional, $present );

			// Nothing more to be loaded - return the rootDSE!
			if ( empty( $missing ) ) return $this->rootDSE;

			// Load attributes that have been requested for this call,
			// but also load any previously loaded optional attributes
			$optional = array_unique( array_merge( $present, $optional ) );
		}

		// Read the rootDSE entry from the server
		$resp = $this->ldap_read( '', 'objectclass=*', array_merge( ['*', '+'], $optional ) );

		// If the query was not successful, return the response to
		// the other guy to figure out what to do
		if ( $resp->code !== 0 ) return $resp;

		$this->rootDSE = $resp->data[0];

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
		if ( stripos( $method, 'ldap_' ) !== 0 ) $method = 'ldap_' . $method;

		$return = call_user_func_array( $method, $args );

		return new Response( $this, $return );
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
		if ( stripos( $method, 'ldap_' ) !== 0 ) $method = 'ldap_' . $method;

		$data = call_user_func_array( $method, $args );
		if ( isset( $data['count'] ) ) unset( $data['count'] );	// No one cares!

		return $data;
	}
}
