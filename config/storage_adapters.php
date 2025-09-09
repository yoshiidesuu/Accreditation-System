<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Adapters Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for various storage adapters used
    | throughout the application for file management.
    |
    */

    'local' => [
        'disk' => env('LOCAL_STORAGE_DISK', 'public'),
        'base_path' => env('LOCAL_STORAGE_BASE_PATH', ''),
    ],

    's3' => [
        'disk' => env('S3_STORAGE_DISK', 's3'),
        'base_path' => env('S3_STORAGE_BASE_PATH', ''),
        'bucket' => env('AWS_BUCKET'),
    ],

    'google_drive' => [
        'credentials_path' => storage_path('app/google-drive-credentials.json'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'share_domain' => env('GOOGLE_DRIVE_SHARE_DOMAIN'),
        'service_account_email' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_EMAIL'),
    ],

];