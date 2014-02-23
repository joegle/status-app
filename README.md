# status-app
status-app is small LAMP software package that allows one mobile user to use a password to privately and conveniently write a GPS tagged text note and remotely archive it. A [demo of status-app is at joegle.com/status-app/demo/](http://joegle.com/status-app/demo/)

status-app is intended for **rapidly prototyping** authentication patterns and HTML5 RESTful mobile web app interfaces.

## Requirements

Server-side requirements:

* PHP 5.5.0+ for password_hash
* PHP mysqli module
* MySQL 5 database

**Client browser considerations**: 

* The HTML5 interface (implemented in `js/status-form.js`) is optimized for iPhone 5 and not guaranteed to work for other devices but traditional form functionality is reliable. 
* Cookies must be enabled to store login tokens.
* Enabled location services for navigator.geolocation
* Load speed can be improved by concatenating and 'minifying' all linked files (css, js, html).
 
## Installation

* Clone or copy the repository to your server: example.com/status-app/
* Copy and configure the config-sample.php to config.php file in cgi/
* Visit install.php in cgi/
* Configure a password hash at keygen.php in cgi/ to place into the configuration file

# Default Usage
Try the [status-app demo](http://joegle.com/status-app/demo) with password "123"

Messages can be typed into the textarea and sent to the PHP script for submission by pressing the `Submit` button. If a the user is authenticated the textarea will clear. A password can be supplied to start a login session.

## Authentication

The configured password (defined in config.php) must be supplied into the Password input field to submit a note. Submitting the correct password will validate a cookie token stored in the browser for a period of time to enable a "remember me" functionality.

## Security

**Threat model assumptions**: detectable online brute force attacks, secure sever administration practices (privilege escalation mitigation), encrypted data transmission and storage

SSL certificates are required for secure transmission of data over untrusted networks.

status-app does not have any cross-site scripting attack opportunities built in and XSS attacks are not in the scope of concern. XSS vectors can be added loading dynamic content that users modify.

Log out to revoke cookie based sessions by visiting `cgi/status-update.php?action=logout` .

PHP Security links:

* http://www.mediawiki.org/wiki/Manual:Securing_database_passwords
* http://www.php.net/manual/en/faq.passwords.php

# Licensing	

MIT License 2014 Joseph Wright  (@ joegle.com)

Please see the file called LICENSE.

Modeled after https://github.com/panique/php-login-minimal

Uses Bootstrap CSS and jQuery JavaScript libraries respecting their terms of licensing.
