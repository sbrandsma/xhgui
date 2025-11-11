<?php
return [
    // Database configuration (MongoDB)
    'db' => [
        'host' => 'mongodb://mongo:27017/xhprof',
        // If you want authentication:
        // 'host' => 'mongodb://user:pass@mongo:27017/xhprof',
    ],

    // Optional: specify the folder where XHGui stores temporary cache
    'cache' => [
        'enabled' => true,
        'path'    => __DIR__ . '/../cache', // container path to cache folder
    ],

    // Optional: number of samples to keep in MongoDB per request URL
    'profiler' => [
        'samples' => 1000,
    ],

    // Optional: specify request duration threshold for profiling (in microseconds)
    'profiler_enable' => true,
];


