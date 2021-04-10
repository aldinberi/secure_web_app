# Secure web application

> This project was part of a secure development course where the goal was to create as secure as possible login and registration for an online platform using PHP for backend and Bootstrap for frontend.

- [General info](#general-info)
- [Technologies](#technologies)
- [Setup](#setup)
- [Features](#features)
- [Possible improvments](#possible-improvments)

## General info

The task in this project was to create a secure CRUD-based backend for logging in and registering users. For the creation of this backend, PHP was used together with [FlightPHP](https://flightphp.com/), MySQL database, and Bootstrap for frontend. All the usual CRUD APIs have been created and a few extra features have been added that would complement the existing ones. All of the API created are documented using Swagger OpenAPI.

## Technologies

The composer packages used for the creation of this project is:

    - php: "^7.1",
    - mikecao/flight: "^1.3",
    - zircote/swagger-php: "^3.0",
    - tribeos/http-logger: "^0.5.0",
    - firebase/php-jwt: "^5.2",
    - nexmo/client: "^2.0",
    - spomky-labs/otphp: "^10.0",
    - giggsey/libphonenumber-for-php: "^8.12",
    - swiftmailer/swiftmailer: "6.0",
    - ext-mbstring: "^7.2"

## Setup

Be able to set up the project it is enough to have [Composer](https://getcomposer.org/) installed on your machine locally and to execute `cd rest;composer install;composer update` in the terminal inside of the project folder. To start the project it is enough to execute you need to have a PHP server running on your machine. One problem that may occur is that the config file is missing from the repository because of security reasons.

## Features

### CRUD operations

#### Endpoint for registering user

The API for registering a user is located at the route `/register` which uses the POST method. The requirements to register an account are:

- The email is not being used by another user
- The password has not been breached which is checked using the have I been pwned public database
- A valid phone number is provided which is checked using `giggsey/libphonenumber-for-php` for format and it's validated by sending a SMS message using `nexmo/clinet`
- hCapcha is completed

The user is not required to validate his phone number during the registration but will have to do it during the login. The provided password is encrypted using bcrypt and then saved in the database.

#### Endpoint for verifying registered user

The API for verifying a registered user is located at the route `/register/verify` which uses the POST method. This API verifies the user using the Vonage service which as input in the body takes the code sent during registration or login as an SMS message to the user's phone.

#### Endpoint for loging in user

The API for logging in the user is located at the route `/login` which uses the POST method. This API as input in the body will take the email and password. If the credentials are correct a JWT token will be returned and one of the following actions will be executed:

- If the user is not validated they will be sent a code in SMS and prompted to enter the code to validate their account
- If the user has activated the 2-factor authentication using SMS, they will be asked for a code that has been sent as an SMS message on their phone
- If the user has activated the 2-factor authentication using Google Authenticator, they will be asked to enter the code generated in their Google Authenticator app
- If no additional protection has been activated or the 2-factor authentication has been successful the user will be redirected to the home page.

If a user fails to login after 3 attempts they will be presented with a hChapcha which they will need to complete next time they try to log in regardless if they refresh the page.

#### Endpoint for validating user login

The API for authenticating the login of a user is located at the route `/login/verify` which uses the POST method. This API authenticates the user if 2-factor authentication is activated, by calling the right service (Vonage or Google authenticator).

#### Endpoint for updating user

The API for updating the user is located at the route `/users/{id}` which uses the PUT method. One requirement to be able to update is for the request to have in the header a JWT token with a specific signature. The user can update all of their information. The password is again being checked if it has been breached before as well as if the new phone number is valid. Using this API they can active 2-factor authentication (only one). If they active Google Authenticator they will be presented with a QR code which they will need to scan using the Google Authenticator app. They can as well active the remember me option which will no longer require the user to do 2-factor authentication by creating a JWT token and saving it locally in the browser.

#### Endpoint for updating user password

The API for updating the user is located at the route `/users/password/{id}` which uses the PUT method. One requirement to be able to update is for the request to have in the header a JWT token with a specific signature. The password is again being checked if it has been breached before.

#### Endpoint for sending a password recovery email

The API for sending a password recovery mail is located at the route `/users/recovery-email` which uses the POST method. Requirements are that the email is being used in the system and that the account has been validated. An email sent using the `Swiftmailer` and the email service called `SendGrid`. The email contains a link that goes to the password recovery page and contains a JWT token that is valid for one hour.

#### Endpoint for recovering password

The API for sending a password recovery mail is located at the route `/users/password-recover/{id}` which uses the PUT method. One requirement to be able to update the password is for the request to have in the header the JWT token which was part of the URL when the user clicked the recovery link. The password is again being checked if it has been breached before.

#### Additional feature

All the information provided to the APIs is being validated, sanitized, and filtered. PHP PDO is being used as well to prevent SQL injections.

## Possible improvments

### U2F

Another way to implement 2-factor authorization could have been by the usage of the U2F Fido key. Once one is acquired it is possible to implement this feature as it's one of the most secure ones if the key isn't lost.

### Code structure

All of the routes are located in one file which makes the code a little bit messy so restructuring and decoupling would make it more manageable and easier to understand.

### Versioning

Versioning is could be improved since this project was pulled from a private repo which was used to manage this project but I am its sole owner and creator.
