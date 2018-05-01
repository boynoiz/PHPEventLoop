<?php
define('BASE_PATH', dirname(__DIR__));
define('VENDOR_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'vendor');
require VENDOR_PATH . DIRECTORY_SEPARATOR . 'autoload.php';

$envFile = BASE_PATH;
if (!file_exists($envFile)) {
    echo 'No .env file found, copy env.sample.php to .env and add your details.', PHP_EOL;
    exit(1);
}
$dotenv = new Dotenv\Dotenv($envFile);
$dotenv->load();