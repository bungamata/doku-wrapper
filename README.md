# README
The reason I made this library to make it easily integrate Doku into their project without the need to understand
cryptography.

With this library, you only need to send the payload to Doku and this library will handle the cryptography for you.

## How to use
1. To get payment checkout URL, see file `DokuCheckoutV1PaymentUrlTest::testGet()` for how to use it.
2. To validate Doku notification, see file `DokuNotificationTest::testValidateFromRequest()` for how to use it.

## How to test locally
1. Copy file `phpunit.xml.dist` to `phpunit.xml` and replace the of Doku credentials with your own.

## TODO
1. DokuNotification add function to validate notification using plain parameters without Request instance for broader
  support, currently it use Request instance which is only available in Laravel & Symfony framework.
