<?php

return [
    'base_url' => env('NUWIRA_SMSGW_BASE_URL', 'http://apisms.nuwira.net/'),
    
    'client_id' => env('NUWIRA_SMSGW_CLIENT_ID', 'client_id'),
    'client_secret' => env('NUWIRA_SMSGW_CLIENT_SECRET', 'client_secret'),
    
    'pretend' => env('NUWIRA_SMSGW_PRETEND', false),
];