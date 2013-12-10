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
 * A good starting point for your own modules
 *
 * This class provides all the required functionality a module should implement. All you have to do in your
 * implementations is to create methods that handle various events triggered by the library.
 *
 * #### Example
 * ```
 * class MyModule extends Ldap\Module
 * {
 *   public function onNew()
 *   {
 *     echo "A new Ldap instance has been just created!";
 *   }
 * }
 * ```
 *
 * If you name your methods in the format **on&lt;CamelCaseEventName&gt;** your methods will be set as event handlers
 * for those events automatically. If you need other method names or naming conventions, you should extend/override
 * the **self::attachEvents()** method.
 *
 * @see  Ldap\Module::attachEvents()     Override if needed to attach non-standard methods as event listeners
 */
abstract class Module implements ModuleInterface
{
  /**
   * Register this module with the library so it can be used
   *
   * @return    void
   */
  public static function enable()
  {
    Internal\ModuleManager::enableModule( get_called_class() );
  }

  /**
   * Disable the module so it will not be used in new Ldap instances
   *
   * @return    void
   */
  public static function disable()
  {
    Internal\ModuleManager::disableModule( get_called_class() );
  }


  /**
   * Attach events to the given event emitter
   *
   * @param     \Evenement\EventEmitterInterface    $emitter    The Ldap instance to which this module will listen
   *
   * @return    void
   */
  public function attachEvents( \Evenement\EventEmitterInterface $emitter )
  {
    $events = $this->getSubscriptionMap();

    foreach ( $events as $event => $method )
    {
      $emitter->on( $event, [$this, $method] );
    }
  }

  /**
   * Generate a list of events this module handles and the respective method names that handle the events
   *
   * #### Example:
   * ```
   * ["new" => "onNew"]
   * ```
   *
   * @return    array
   */
  protected function getSubscriptionMap()
  {
    $subscriptions = [];

    $allMethods = get_class_methods( $this );

    foreach ( $allMethods as $methodName )
    {
      if ( strpos( $methodName, 'on' ) === 0 )
      {
        // Strip the 'on' from method name and convert to lowercase, then use it as key
        // ( the key will be used as event name )
        $subscriptions[strtolower( substr( $methodName, 2 ) )] = $methodName;
      }
    }

    return $subscriptions;
  }
}
