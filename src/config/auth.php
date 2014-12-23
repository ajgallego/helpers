<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Login Attribute
    |--------------------------------------------------------------------------
    | Globally override the login attribute. For example, it allows to use 
    | email (default), username or other field as login attribute.
    */
    'login_attribute' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Login Throttle
    |--------------------------------------------------------------------------
    | Defines how many login failed attempts may be done within
    | the 'throttle_time_period', which is in minutes.
    */
    'throttle_limit' => 5,
    'throttle_suspension_time' => 2, // minutes

    /*
    |--------------------------------------------------------------------------
    | Password reset expiration
    |--------------------------------------------------------------------------
    | By default, a password reset request will expire after 7 hours. With the
    | line below you will be able to customize the duration of the reset
    | requests here.
    */
    'password_reset_expiration' => 7, // hours

    /*
    |--------------------------------------------------------------------------
    | Signup E-mail and confirmation (true or false)
    |--------------------------------------------------------------------------
    |
    | By default a signup e-mail will be send by the system, however if you
    | do not want this to happen, change the line below in false and handle
    | the confirmation using another technique, for example by using the IPN
    | from a payment-processor. Very usefull for websites offering products.
    |
    | signup_email:
    | is for the transport of the email, true or false
    | If you want to use an IPN to trigger the email, then set it to false
    |
    | signup_confirm:
    | is to decide of a member needs to be confirmed before he is able to login
    | so when you set this to true, then a member has to be confirmed before
    | he is able to login, so if you want to use an IPN for confirmation, be
    | sure that the ipn process also changes the confirmed flag in the member
    | table, otherwise they will not be able to login after the payment.
    |
    */
    'signup_email'      => true,
    'signup_confirm'    => true,

    /*
    |--------------------------------------------------------------------------
    | E-Mail queue name
    |--------------------------------------------------------------------------
    | Modify the line below to change which queue driver is used to send
    | e-mails.
    */
    'email_queue_name'      => 'sync',

    /*
    |--------------------------------------------------------------------------
    | Email Views
    |--------------------------------------------------------------------------
    | Views used to email messages for some events. Default views are used
    | but you can create your own views and replace the view names here. 
    | For example, to use "app/views/email/confirmation.blade.php" you have 
    | to write: 
    | 'email_view_account_confirmation' => 'email.confirmation'
    */
    'email_view_reset_password' =>       'laravel-helpers::emails.auth.password_reset',       // with $user and $token.
    'email_view_account_confirmation' => 'laravel-helpers::emails.auth.account_confirmation', // with $user
);
