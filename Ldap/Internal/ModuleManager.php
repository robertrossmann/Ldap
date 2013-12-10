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
 * ModuleManager keeps track of which modules are currently enabled and provides the list
 * to the library when necessary.
 *
 * In most situations you do not need to interface with this class in any way.
 */
class ModuleManager
{
  protected static $enabledModules = [];


  /**
   * Enable a module
   *
   * Enabled modules will be used only with new instances. Current instances of classes that use the modules
   * will not be notified of a newly enabled module
   *
   * @param     string    $module    The module's fully qualified class name (incl. namespace, if any).
   *                                 The module **must** implement **ModuleInterface**
   *
   * @return    void
   */
  public static function enableModule( $module )
  {
    if ( in_array( 'Ldap\ModuleInterface', class_implements( $module, true ) ) )
    {
      // Is the module already loaded?
      if ( in_array( $module, static::$enabledModules ) ) return;

      static::$enabledModules[] = $module;
    }
    else throw new \Exception( "Module $module does not implement Ldap\ModuleInterface interface" );
  }

  /**
   * Disable a module
   *
   * Disabled modules will not be removed from instances that currenly use them, but new instances
   * will not load them.
   *
   * @param     string    $module    The module's fully qualified class name (incl. namespace, if any)
   *
   * @return    void
   */
  public static function disableModule( $module )
  {
    if ( isset( static::$enabledModules[$module] ) )
    {
      unset( static::$enabledModules[$module] );
      static::$enabledModules = array_values( static::$enabledModules ); // Fix empty keys
    }
  }

  /**
   * Get a list of currently enabled modules, in the order they were enabled
   *
   * @return    array    An array of fully qualified class names
   */
  public static function getModules()
  {
    return static::$enabledModules;
  }
}
