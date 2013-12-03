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


namespace Ldap\Modules;

class ModuleManager
{
  protected static $enabledModules = [];


  public static function enableModule( $module )
  {
    static::$enabledModules[] = $module;
  }

  public static function disableModule( $module )
  {
    if ( isset( static::$enabledModules[$module] ) )
    {
      unset( static::$enabledModules[$module] );
      static::$enabledModules = array_values( static::$enabledModules ); // Fix empty keys
    }
  }

  public static function getModules()
  {
    return static::$enabledModules;
  }
}
