<?php
require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Support\Connector;
use App\Support\Config;

$dotenv = new \Dotenv\Dotenv(BASE_PATH);
$dotenv->overload();
$api = new Connector();
$data = $api->fire(Config::apiDataUri(), 'GET');
//$data = $api->login();
//$data = $api->refresh();
print $data;
