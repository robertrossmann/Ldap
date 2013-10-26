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
 * A collection of constants with all known ldap options
 *
 * @see     <a href="http://www.php.net/manual/en/function.ldap-get-option.php">PHP - ldap_get_option()</a>
 */
class Option extends Enumeration\Enumeration
{
  const Deref             = 2;
  const Sizelimit         = 3;
  const Timelimit         = 4;
  const Referrals         = 8;
  const Restart           = 9;
  const ProtocolVersion   = 17;
  const ServerControls    = 18;
  const ClientControls    = 19;
  const HostName          = 48;
  const ErrorNumber       = 49;
  const ErrorString       = 50;
  const DiagnosticMessage = 50;
  const MatchedDN         = 51;
  const NetworkTimeout    = 20485;
}
