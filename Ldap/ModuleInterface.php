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

/**
 * This interface describes the methods a module must implement
 *
 * Implement this interface in your classes to use them as modules for this library. It is recommended to only
 * implement this interface if you cannot subclass **Ldap\Module** as that class provides most of the glue-code
 * necessary for a module to work.
 *
 * @see       Module    Ldap\Module
 */
interface ModuleInterface
{
  /**
   * Enable the module
   *
   * Your minimal implementation of this method must call
   * **Ldap\ModuleManager::enableModule()** to have your module
   * registered with the library. Otherwise, you are free to perform any kind of initialisation
   * to get your module up and running.
   *
   * @return    void
   *
   * @see       ModuleManager::enableModule()  to enable your module in ModuleManager
   */
  public static function enable();

  /**
   * Disable the module
   *
   * Your minimal implementation of this method must call
   * **Ldap\ModuleManager::disableModule()** to prevent the library from using your module the next time
   * modules are loaded. Note that existing instances will continue to use your module until they are destroyed.
   *
   * @return    void
   *
   * @see       ModuleManager::disableModule()  to disable your module in ModuleManager
   */
  public static function disable();

/**
 * Subscribe the module to events
 *
 * You receive an EventEmitterInterface instance that your module will be associated with. In this method,
 * you should subscribe to events your module is interested in.
 *
 * @param     Evenement\EventEmitterInterface   $emitter    An instance that emits events your module should respond to
 *
 * @return    void
 */
  public function attachEvents( \Evenement\EventEmitterInterface $emitter );
}
