<?php

// this PHP file is used to convert XHProf JSON files to XHGui format


// command internal php -d display_errors=1 /var/www/xhgui/external/import.php -f /profiles/xhprof_out.json
// command exteral docker exec -it xhgui-xhgui-1 php -d display_errors=1 /var/www/xhgui/external/import.php -f /profiles/xhprof_out.json


// convert_xhprof_to_xhgui.php
$inputFile  = $argv[1] ?? null;
$outputFile = $argv[2] ?? null;

if (!$inputFile || !file_exists($inputFile)) {
    die("Usage: php convertjson.php input.json output.json\n");
}

// Read the raw XHProf data
$xhprofData = json_decode(file_get_contents($inputFile), true);
if (!$xhprofData) {
    die("Invalid JSON in $inputFile\n");
}

// Build the XHGui wrapper
$now = microtime(true);
$sec  = (int)$now;
$usec = (int)(($now - $sec) * 1e6);

$xhguiData = [
    'meta' => [
        'url' => '/',
        'simple_url' => '/',
        'get' => [],
        'env' => [],
        'SERVER' => $_SERVER ?? [],
        'request_ts_micro' => [
            'sec' => $sec,
            'usec' => $usec,
        ],
    ],
    'profile' => $xhprofData,
    
];

// Write as **single-line JSON**, so `import.php` can read it
file_put_contents($outputFile, json_encode($xhguiData) . "\n");

echo "Converted $inputFile -> $outputFile\n";
