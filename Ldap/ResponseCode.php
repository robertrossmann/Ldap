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
 * A construct definining most of the ldap response codes
 */
class ResponseCode extends Enumeration\Enumeration
{
  const Success                       = 0;
  const OperationsError               = 1;
  const ProtocolError                 = 2;
  const TimelimitExceeded             = 3;
  const SizelimitExceeded             = 4;
  const CompareFalse                  = 5;
  const CompareTrue                   = 6;
  const AuthMethodNotSupported        = 7;
  const StrongAuthRequired            = 8;
  const Referral                      = 10;
  const AdminlimitExceeded            = 11;
  const UnavailableCriticalExtension  = 12;
  const ConfidentialityRequired       = 13;
  const SaslBindInProgress            = 14;
  const NoSuchAttribute               = 16;
  const UndefinedType                 = 17;
  const InappropriateMatching         = 18;
  const ConstraintViolation           = 19;
  const TypeOrValueExists             = 20;
  const InvalidSyntax                 = 21;
  const NoSuchObject                  = 32;
  const AliasProblem                  = 33;
  const InvalidDnSyntax               = 34;
  const InappropriateAuth             = 48;
  const InvalidCredentials            = 49;
  const ErrorTooManyContextIds        = 49;
  const InsufficientAccess            = 50;
  const Busy                          = 51;
  const Unavailable                   = 52;
  const UnwillingToPerform            = 53;
  const LoopDetect                    = 54;
  const NamingViolation               = 64;
  const ObjectClassViolation          = 65;
  const NotAllowedOnNonleaf           = 66;
  const NotAllowedOnRdn               = 67;
  const AlreadyExists                 = 68;
  const NoObjectClassMods             = 69;
  const ResultsTooLarge               = 70;
  const AffectsMultipleDsas           = 71;
  const Other                         = 80;
  // Active Directory-specific responses
  const UserNotFound                  = 525;
  const NotPermittedToLogonAtThisTime = 530;
  const RestrictedToSpecificMachines  = 531;
  const PasswordExpired               = 532;
  const AccountDisabled               = 533;
  const AccountExpired                = 701;
  const UserMustResetPassword         = 773;
}
