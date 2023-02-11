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
    |
    */

    'fallback_url' => null,

];
