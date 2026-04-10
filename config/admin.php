<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Admin Session Lifetime
     |--------------------------------------------------------------------------
     |
     | The number of seconds that the admin session is allowed to remain idle
     | before an automatic logout is enforced. This provides additional
     | security against abandoned or hijacked administration sessions.
     | Set to 900 seconds (15 minutes) by default.
     |
     */

    'session_lifetime' => env('ADMIN_SESSION_LIFETIME', 900),

];
