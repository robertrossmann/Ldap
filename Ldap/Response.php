<?php

/**
 * Alaneor/Ldap
 *
 * Licensed under the BSD (3-Clause) license
 * For full copyright and license information, please see the LICENSE file
 *
 * @author      Robert Rossmann <rr.rossmann@me.com>
 * @copyright   2012-2013 Robert Rossmann
 * @link        https://github.com/Alaneor/Ldap
 * @license     http://choosealicense.com/licenses/bsd-3-clause   BSD (3-Clause) License
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
 * @property-read   mixed     $result       The expected result of an ldap operation ( boolean for compare
 *                                          operations, resource for lookup operations etc. )
 * @property-read   array     $data         Only available when a resource is available - The data extracted from a resource
 * @property-read   int       $code         A status code of the ldap operation executed
 * @property-read   string    $message      A status message associated with the status code
 * $property-read   array     $referrals    If the server responds with referrals, you will find them here
 * @property-read   binary    $cookie       For paged result responses, a cookie will be here, if returned from server
 * @property-read   int       $estimated    The estimated number of objects remaining to return from server
 *                                          when doing paged searches ( not all ldap implementations return this value )
 * @property-read   string    $matchedDN    Not much is known here; read php's documentation about ldap_parse_result()
 */
class Response
{
  protected $result;        // The raw result as returned from server
  protected $data;          // The actual ldap data extracted from result, in case a resource was returned
  protected $code;          // Status code of the operation
  protected $message;       // Textual representation of the status code
  protected $referrals;     // List of returned referrals in the resultset ( if any )
  protected $cookie;        // A pagination cookie if returned from server
  protected $estimated;     // An estimated number of objects yet to be returned from server for paged searches
  protected $matchedDN;     // Purpose unknown; available for compatibility reasons


  public function __construct( Ldap $link, $result = null )
  {
    $this->result = $result;

    if ( is_resource( $result ) )
    {
      // Get the status code, matched DN and referrals from the response
      ldap_parse_result( $link->resource(), $result, $this->code, $this->matchedDN, $this->message, $this->referrals );

      // Get the string representation of the status code
      $this->message = ldap_err2str( $this->code );

      // Extract the data from the resource
      $this->data = ldap_get_entries( $link->resource(), $result );
      $this->data = $this->cleanup_result( $this->data );

      // Remove the referrals array if there's nothing inside
      ( count( $this->referrals ) == 0 ) && $this->referrals = null;

      // Try to extract pagination cookie and estimated number of objects to be returned
      // Since there's no way to tell if pagination has been enabled or not, I am suppressing php errors
      @ldap_control_paged_result_response( $link->resource(), $result, $this->cookie, $this->estimated );
    }
    else
    {
      $this->code   = ldap_errno( $link->resource() );
      $this->message  = ldap_error( $link->resource() );
    }

    // Active Directory conceals some additional error codes in the ErrorMessage of the response
    // that we cannot get to with ldap_errno() in authentication failures - let's try to
    // extract them!
    if ( $this->code == 49 )
    {
      $message = null;
      ldap_get_option( $link->resource(), Option::ErrorString, $message );

      if ( stripos( $message, 'AcceptSecurityContext' ) !== false )
      {
        $message = explode( ', ', $message );
        end( $message );
        $message = prev( $message );

        $this->code = explode( ' ', $message )[1];

        // For compatibility reasons with standard ldap, if the error code
        // is 52e let's replace it with 49 ( their meanings are equal, it's just
        // Microsoft doing it its own way again )
        if ( $this->code == '52e' ) $this->code = ResponseCode::InvalidCredentials;
      }
    }
  }

  /**
   * Does this response represent a successful ldap operation or was there an error?
   *
   * @return    bool    true for successful ldap operation, false if there was a failure
   */
  public function ok()
  {
    switch ( $this->code )
    {
      // These response codes do not represent a failed operation; everything else does
      case ResponseCode::Success:
      case ResponseCode::SizelimitExceeded:
      case ResponseCode::CompareFalse:
      case ResponseCode::CompareTrue:

        return true;

      default:

        return false;
    }
  }


  protected function cleanup_result( $result )
  {
    // First, unset the 'count'
    unset( $result['count'] );

    // Let's loop through all returned objects
    foreach ( $result as &$object )
    {
      // Unset the 'count' of returned attributes per object
      unset( $object['count'] );

      // Loop through all attributes
      foreach ( $object as $attribute => &$value )
      {
        // Numeric indexes contain only attribute names - we don't need those
        if ( is_numeric( $attribute ) )
        {
          unset( $object[$attribute] );
          continue;
        }

        if ( is_array( $value ) ) unset( $value['count'] );

        // Some ldap servers ( i.e. AD ) may split too large attributes into smaller
        // attributes with a special notation in the attribute's name ( i.e. "member;0-4999" ).
        // Let's join those together.

        // Search for ';' in attribute's name, returning string BEFORE ';'
        $actualAttribute = strstr( $attribute, ';', true );
        if ( $actualAttribute !== false ) // Yes, this attribute has been split
        {
          $object[$actualAttribute] = array_merge( $object[$actualAttribute], $value );
          unset( $object[$attribute] );
          continue;
        }
      }
    }

    return $result;
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
