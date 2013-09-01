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
 * An encapsulation of a ldap response information
 *
 * This class holds information about the result of an ldap operation.
 * It contains the status code of the operation, the associated status message,
 * for lookup operations it also contains the raw resource link identifier and
 * the extracted data from the resource, and in some situations it may contain
 * other information described below.
 *
 * @property-read		mixed			$result		The expected result of an ldap operation ( boolean for compare
 * 													operations, resource for lookup operations etc. )
 * @property-read		array			$data		Only available when a resource is available - The data extracted from a resource
 * @property-read		int				$code		A status code of the ldap operation executed
 * @property-read		string			$message	A status message associated with the status code
 * @property-read		binary			$cookie		For paged result responses, a cookie will be here, if returned from server
 * $property-read		array			$referrals	When checking for referrals,
 *
 * @property-read		int				$estimated	The estimated number of objects remaining to return from server
 * 													when doing paged searches ( not all ldap implementations return this value )
 */
class Response
{
	protected $result;				// The raw result as returned from server
	protected $data;				// The actual ldap data extracted from result, in case a resource was returned
	protected $code;				// Status code of the operation
	protected $message;				// Textual representation of the status code
	protected $referrals;
	protected $matchedDN;
	protected $cookie;				// A pagination cookie if returned from server
	protected $estimated;			// An estimated number of objects yet to be returned from server for paged searches

	public function __construct( Ldap $link, $result = null )
	{
		$this->result = $result;

		if ( is_resource( $result ) )
		{
			$this->data = ldap_get_entries( $link->resource(), $result );
			ldap_parse_result( $link->resource(), $result, $this->code, $this->matchedDN, $this->message, $this->referrals );
			$this->message = ldap_error( $link->resource() );

			( count( $this->referrals ) == 0 ) && $this->referrals = null;	// Remove the array if there's nothing inside

			// Try to extract pagination cookie and estimated number of objects to be returned
			// Since there's no way to tell if pagination has been enabled or not, I am suppressing php errors
			@ldap_control_paged_result_response( $link->resource(), $result, $this->cookie, $this->estimated );
		}
		else
		{
			$this->code		= ldap_errno( $link->resource() );
			$this->message	= ldap_error( $link->resource() );
		}
	}


	/**
	 * Read-only property access mapper
	 *
	 * @internal
	 */
	public function __get( $property )
	{
		return $this->$property;
	}

	public function __destroy()
	{
		if ( is_resource( $this->result ) ) ldap_free_result( $this->result );
	}
}
