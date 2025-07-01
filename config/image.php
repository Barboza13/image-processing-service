<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', extension_loaded('imagick') ? 'imagick' : 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Configuration Options
    |--------------------------------------------------------------------------
    |
    | These options are passed directly to the driver
    |
    */

    'options' => [
        'decode_options' => [
            'autoOrient' => true,
        ],
        'encode_options' => [
            'jpeg_quality' => 90,
            'png_compression' => 6,
            'webp_quality' => 90,
        ],
    ],
];
