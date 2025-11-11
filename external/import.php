<?php

use XHGui\Application;

require __DIR__ . '/../vendor/autoload.php';

$options = getopt('f:');

if (!isset($options['f'])) {
    throw new InvalidArgumentException('You should define a file to be loaded');
}
$file = $options['f'];

if (!is_readable($file)) {
    throw new InvalidArgumentException($file . ' isn\'t readable');
}

$fp = fopen($file, 'r');
if (!$fp) {
    throw new RuntimeException('Can\'t open ' . $file);
}

$app = new Application();
$saver = $app->getSaver();

$contents = file_get_contents($file);
$data = json_decode($contents, true);
if ($data) {
    $saver->save($data);
}

