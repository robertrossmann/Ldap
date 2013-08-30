# Ldap :: Object encapsulation of php's ldap functions

This library provides a class encapsulation of php's ldap functions. This might be very useful for mocking during unit testing or if you simply prefer the beauty of OOP.

## Features

 - Class Ldap\Ldap provides function encapsulation of all important php ldap_* functions
 - Class Ldap\Option provides you with a known ldap options as class constants
 - Class Ldap\Response provides a nice way to handle server responses
 - Class Ldap\ResponseCode defines most of the known server response codes for you to use in your implementations

## Installation

### Requirements

 - PHP 5.4.0 and newer with LDAP support ( [setup instructions](http://www.php.net/manual/en/ldap.installation.php) )
 - OpenSSL module for SSL / TLS connections ( [setup instructions](http://cz1.php.net/manual/en/openssl.installation.php) )

#### Via Composer

 `composer require alaneor/ldap:dev-master`<br>
( visit [Packagist](https://packagist.org/packages/alaneor/ldap) for list of all available versions )

## Documentation

Standard php functions' documentation applies. More info and tutorials will be provided shortly. If you are interested, check the code - it's quite simple.

## License

This software is licensed under the **BSD (3-Clause) License**.
See the [LICENSE](LICENSE) file for more information.
