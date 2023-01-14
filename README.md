<img src="doc/logo.png" width="48"> ApnsPHP: Apple Push Notification System Library
==========================

<p align="center">
	<img src="https://poser.pugx.org/m2mobi/apns-php/downloads">
	<img src="https://poser.pugx.org/m2mobi/apns-php/d/monthly">
	<img src="https://poser.pugx.org/m2mobi/apns-php/d/daily">
	<img src="https://poser.pugx.org/m2mobi/apns-php/license">
</p>

A **full set** of *open source* PHP classes to interact with the **Apple Push Notification service** for the iPhone, iPad and the iPod Touch.

- [Sample PHP Push code](sample_push.php)
- [How to generate a Push Notification certificate and download the Entrust Root Authority certificate](doc/CertificateCreation.md)

Packagist
-------

https://packagist.org/packages/m2mobi/apns-php

Architecture
-------

- **Message class**, to build a notification payload.
- **Push class**, to push one or more messages to Apple Push Notification service.

Details
---------

All client-server activities are based on the "on error, retry" pattern with customizable timeouts, retry times and retry intervals.

Requirements
-------------

PHP 7.4.0 or later with OpenSSL.

```
./configure --with-openssl[=PATH]
```
