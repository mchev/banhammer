<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Specify the name of the table created during migration for the ban system.
    | This table will store information about banned users.
    |
    */

    'table' => 'bans',

    /*
    |--------------------------------------------------------------------------
    | Where to Redirect Banned Users
    |--------------------------------------------------------------------------
    |
    | Define the URL to which users will be redirected when attempting to log in
    | after being banned. If not defined, the banned user will be redirected to
    | the previous page they tried to access.
    |
    */

    'fallback_url' => null, // Examples: null (default), "/oops", "/login"

    /*
    |--------------------------------------------------------------------------
    | 403 Message
    |--------------------------------------------------------------------------
    |
    | The message that will be displayed if no fallback URL is defined for banned users.
    |
    */

    'messages' => [
        'user' => 'Your account has been banned.',
        'ip' => 'Access from your IP address is restricted.',
        'country' => 'Access from your country is restricted.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Block by Country
    |--------------------------------------------------------------------------
    |
    | Determine whether to block users based on their country. This setting uses
    | the value of BANHAMMER_BLOCK_BY_COUNTRY from the environment. Enabling this
    | feature may result in up to 45 HTTP requests per minute with the free version
    | of https://ip-api.com/.
    |
    */

    'block_by_country' => env('BANHAMMER_BLOCK_BY_COUNTRY', false),

    /*
    |--------------------------------------------------------------------------
    | List of Blocked Countries
    |--------------------------------------------------------------------------
    |
    | Specify the countries where users will be blocked if 'block_by_country' is true.
    | Add country codes to the array to restrict access from those countries.
    |
    */

    'blocked_countries' => [], // Examples: ['US', 'CA', 'GB']

];
