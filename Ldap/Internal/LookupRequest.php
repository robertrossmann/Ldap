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
 * Base class for all LookupRequest classes
 */
abstract class LookupRequest extends Request
{
  protected $base;
  protected $filter;
  protected $attributes;
  protected $attrsOnly    = false;
  protected $sizeLimit;
  protected $timeLimit;
  protected $deref        = false;
  protected $pageSize     = 1000;
  protected $pagedSearch  = false;
  protected $cookie       = '';


  public function __construct( $base = null, $filter = '(objectclass=*)', $attributes = '*' )
  {
    $this->base       = $base;
    $this->filter     = (string)$filter;
    $this->attributes = (array)$attributes;
  }

  public function prepareForExecution( \Ldap\Ldap $link )
  {
    // Send the paged search control to the server if paged search is requested
    if ( $this->pagedSearch() )
    {
      ldap_control_paged_result( $link->resource(), $this->pageSize(), true, $this->cookie() );
    }
  }

  public function getActionParameters()
  {
    return [
      $this->base,
      $this->filter,
      $this->attributes,
      $this->attrsOnly,
      $this->sizeLimit,
      $this->timeLimit,
      $this->deref,
    ];
  }

  public function base( $base = null )
  {
    if ( $base === null ) return $this->base;

    $this->base = (string)$base;

    return $this;
  }

  public function filter( $filter = null )
  {
    if ( $filter === null ) return $this->filter;

    $this->filter = (string)$filter;

    return $this;
  }

  public function attributes( $attributes = null )
  {
    if ( $attributes === null ) return $this->attributes;

    $this->attributes = (array)$attributes;

    return $this;
  }

  public function attrsOnly( $attrsOnly = null )
  {
    if ( $attrsOnly === null ) return $this->attrsOnly;

    $this->attrsOnly = (bool)$attrsOnly;

    return $this;
  }

  public function sizeLimit( $sizeLimit = null )
  {
    if ( $sizeLimit === null ) return $this->sizeLimit;

    $this->sizeLimit = (int)$sizeLimit;

    return $this;
  }

  public function timeLimit( $timeLimit = null )
  {
    if ( $timeLimit === null ) return $this->timeLimit;

    $this->timeLimit = (int)$timeLimit;

    return $this;
  }

  public function deref( $deref = null )
  {
    if ( $deref === null ) return $this->deref;

    $this->deref = $deref;

    return $this;
  }

  public function pageSize( $pageSize = null )
  {
    if ( $pageSize === null ) return $this->pageSize;

    $this->pageSize = (int)$pageSize;

    return $this;
  }

  public function pagedSearch( $pagedSearch = null )
  {
    if ( $pagedSearch === null ) return $this->pagedSearch;

    $this->pagedSearch = (bool)$pagedSearch;

    return $this;
  }

  public function cookie( $cookie = null )
  {
    if ( $cookie === null ) return $this->cookie;

    $this->cookie = $cookie;

    return $this;
  }

  // Some semantic aliases for the above methods

  /** A setter-only alias for **Request::attributes()** */
  public function get( $attributes )
  {
    return $this->attributes( $attributes );
  }

  /** A setter-only alias for **Request::attributes()** */
  public function andGet( $attributes )
  {
    return $this->attributes( $attributes );
  }

  /** A setter-only alias for **Request::attributes()** */
  public function with( $attributes )
  {
    return $this->attributes( $attributes );
  }

  /** A setter-only alias for **Request::base()** */
  public function from( $base )
  {
    return $this->base( $base );
  }

  /** A setter-only alias for **Request::base()** */
  public function startAt( $base )
  {
    return $this->base( $base );
  }

  /** A setter-only alias for **Request::base()** */
  public function the( $base )
  {
    return $this->base( $base );
  }

  /** A setter-only alias for **Request::base()** */
  public function this( $base )
  {
    return $this->base( $base );
  }

  /** A setter-only alias for **Request::filter()** */
  public function where( $filter )
  {
    return $this->filter( $filter );
  }

  /** A setter-only alias for **Request::sizeLimit()** */
  public function limitTo( $sizeLimit )
  {
    return $this->sizeLimit( $sizeLimit );
  }

  /** Enable paged searching and use **Request::sizeLimit()** as the the number of objects per page */
  public function perPage()
  {
    if ( ! is_int( $this->sizeLimit ) || $this->sizeLimit === 0 )
    {
      throw new \Exception( "Paged search requested but sizeLimit is either not set or zero" );
    }

    $this->pageSize   = $this->sizeLimit;
    $this->sizeLimit  = null;

    return $this->pagedSearch( true );
  }

  /** A setter-only alias for **Request::timeLimit()** */
  public function within( $timeLimit )
  {
    return $this->timeLimit( $timeLimit );
  }

  /** A semantic method to be used in conjunction with **Request::within()** method ( does nothing ) */
  public function secs()
  {
    return $this;
  }
}
