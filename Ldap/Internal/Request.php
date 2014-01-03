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
 * Base class for all Request classes
 */
abstract class Request
{
  protected static $action;


  /**
   * Used internally by the Ldap class
   *
   * @internal
   *
   * @return    array     An array of parameters to be passed to the executing action
   */
  abstract public function getActionParameters();

  /**
   * Used internally by the Ldap class
   *
   * Prepares the Ldap link in any way necessary to fulfill the Request
   *
   * @internal
   * @param     Ldap\Ldap     $link     An instance of Ldap that will be used for the request
   *
   * @return    void
   */
  abstract public function prepareForExecution( \Ldap\Ldap $link );

  /**
   * Used internally by the Ldap class
   *
   * @internal
   *
   * @return    string      The action to be executed when fulfilling the request
   */
  public function action()
  {
    return static::$action;
  }
}
