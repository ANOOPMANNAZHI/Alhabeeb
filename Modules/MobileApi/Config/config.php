<?php
return [
    'name' => 'MobileApi',
    'enabled' => env('MOBILE_API_ENABLED', false),
    'signed_url_minutes' => 15,
    'max_upload_kb' => 10240,
    'image_max_px' => 1600,
    'thumb_px' => 300,
];
