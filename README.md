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

You use the `Ldap\Ldap` class to connect to an ldap server. Simply construct the class with the proper server URL/IP address and optional port ( default is 389 ) and then use any of the below described functions to work with the connection.

### Example code
```
// Include Composer's autoloader
include 'vendor/autoload.php';

// Open the ldap connection
$link = new Ldap\Ldap( 'example.com', 389 );

// Authenticate the connection with the Admin account
$link->bind( 'CN=Admin,DC=example,DC=com', 'MySecretPwd!' );

// List the items that are in the baseDN
$response = $link->ldap_list( 'DC=example,DC=com', 'objectclass=*', ['name', 'objectclass'] );

// Take a look at the structure of the Ldap\Response instance
print_r( $response );
```

### Method naming

There are a few rules that generally apply to the method names and their parameters.

1. A method's name is the function's name, stripped of the leading *ldap_* prefix. Where a syntax error would occur ( e.g. *ldap_list* -> *list* or *ldap_8859_to_t61* -> *8859_to_t61* ) the prefix is kept.
1. The `resource $link_identifier` parameter is omitted in all situations ( the link identifier is stored in the instance of `Ldap\Ldap` class ).
1. Where a `resource $result_identifier` is expected, you pass an instance of `Ldap\Response` class ( e.g. in the `Ldap\Ldap::sort()` method ) that is returned for all ldap method calls.
1. For all other function parameters and its default values, standard php documentation applies.

**There are two exceptions to the above naming rules:**

The pagination control request is even shorter, for your convenience:<br>
`ldap_control_paged_result` -> `Ldap\Ldap::paged_result()`

Since `list` cannot be used as method name, all lookup functions are defined with their prefixes to keep them consistent:<br>
`ldap_search` -> `Ldap\Ldap::ldap_search()`<br>
`ldap_list` -> `Ldap\Ldap::ldap_list()`<br>
`ldap_read` -> `Ldap\Ldap::ldap_read()`<br>

#### Defined methods:

Here's a list of methods you can use.

 - `Ldap\Ldap::resource()` -> get the ldap resource identifier
 - `Ldap\Ldap::add()`
 - `Ldap\Ldap::bind()`
 - `Ldap\Ldap::compare()`
 - `Ldap\Ldap::connect()`
 - `Ldap\Ldap::delete()`
 - `Ldap\Ldap::get_option()`
 - `Ldap\Ldap::ldap_list()`
 - `Ldap\Ldap::ldap_read()`
 - `Ldap\Ldap::ldap_search()`
 - `Ldap\Ldap::mod_add()`
 - `Ldap\Ldap::mod_del()`
 - `Ldap\Ldap::mod_replace()`
 - `Ldap\Ldap::modify()`
 - `Ldap\Ldap::paged_result()`
 - `Ldap\Ldap::rename()`
 - `Ldap\Ldap::sasl_bind()`
 - `Ldap\Ldap::set_option()`
 - `Ldap\Ldap::set_rebind_proc()`
 - `Ldap\Ldap::sort()`
 - `Ldap\Ldap::start_tls()`

### Response structure

Each method call returns **new instance** of the `Ldap\Response` class.

The structure of the response is as follows:

 - `Ldap\Response::result` - Whatever the ldap function returned, either a boolean, a resource or anything else
 - `Ldap\Response::data` - If a function returned a resource, the actual ldap data will be already extracted here
 - `Ldap\Response::code` - The ldap response code of the operation performed
 - `Ldap\Response::message` - The ldap response message that corresponds to the response code
 - `Ldap\Response::referrals` - If the server responds with referrals, you will find them here
 - `Ldap\Response::cookie` - For paged result responses, a cookie will be here, if returned from server
 - `Ldap\Response::estimated` - The estimated number of objects remaining to return from server when doing paged searches ( not all ldap implementations return this value )
 - `Ldap\Response::matchedDN` - Not much is known here; read php's documentation about [ldap_parse_result()](http://www.php.net/manual/en/function.ldap-parse-result.php)

Not all properties have values in all situations - some of them are only present when doing specific actions, like the *cookie* - it will only be present when pagination is enabled, a lookup operation has been executed and the server returned a cookie.

## License

This software is licensed under the **BSD (3-Clause) License**.
See the [LICENSE](LICENSE) file for more information.
