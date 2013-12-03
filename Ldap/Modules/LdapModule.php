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

abstract class LdapModule
{
  /**
   * Register this module with the library so it can be used
   *
   * @return    void
   */
  public static function enable()
  {
    ModuleManager::enableModule( get_called_class() );
  }

  /**
   * Disable the module so it will not be used anymore
   *
   * @return    void
   */
  public static function disable()
  {
    ModuleManager::disableModule( get_called_class() );
  }


  /**
   * Attach events to the given event emitter
   *
   * @param     EventEmitter    $emitter    The Ldap instance to which this module will listen
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
