<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Bổ sung 'auth/*' và '*' để đảm bảo CORS áp dụng cho tất cả các đường dẫn API
    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie', 'broadcasting/*', '*'],

    'allowed_methods' => ['*'],

    // Cho phép tất cả các domain frontend (bao gồm Vercel) gửi request
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // Đặt là false khi allowed_origins dùng '*' để tránh lỗi xung đột của trình duyệt

];