<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Change here the name of the table that will be created during the migration to use the ban system.
    |
    */

    'table' => 'bans',

    /*
    |--------------------------------------------------------------------------
    | Where to redirect banned Models
    |--------------------------------------------------------------------------
    |
    | Url to which the user will be redirected when he/she tries to log in after being banned.
    | If not defined, the banned user will be redirected to the previous page.
    |
    */

    'fallback_url' => null, // null | "/oops"

    /*
    |--------------------------------------------------------------------------
    | 403 Message
    |--------------------------------------------------------------------------
    |
    | The message that will be displayed if no fallback url for banned users has been defined.
    |
    */
    'message' => 'You have been banned.',

];
