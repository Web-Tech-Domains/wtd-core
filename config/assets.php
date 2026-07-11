<?php

declare(strict_types=1);

return [
    'dev_server' => env('VITE_DEV_SERVER', 'http://127.0.0.1:5173'),
    'hot_file' => 'public/hot',
    'build_path' => 'public/build',
    'manifest' => 'public/build/.vite/manifest.json',
];
