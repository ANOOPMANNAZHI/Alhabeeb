<?php
return [
'database' => array(
        'driver' => 'postgres',
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD', '123456'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'plms_live'),
        'port' => env('DB_PORT', '5432'),
        'jdbc_driver' => 'org.postgresql.Driver',
        'jdbc_url' => 'jdbc:postgresql://'.env('DB_HOST', '127.0.0.1').':'.env('DB_PORT', '5432')
        )
];    