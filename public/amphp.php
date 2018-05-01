<?php
require dirname(__DIR__) . '/bootstrap/autoload.php';
use App\Support\Connector;
use Amp\Artax\Request;
use Amp\Artax\DefaultClient;
use App\Support\Config;
use Amp\Loop;

Loop::run(function () {
    Loop::repeat(2000, function () {
        try {
            $client = new DefaultClient;
            $request = (new Request('https://localhost.dev/api/authorize/login', 'POST'))->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
                'Cache-Control' => 'no-cache',
                'Authorization' => 'Bearer ' . getenv('API_AUTH_TOKEN')
            ])
                ->withBody('{"email":"admin@admin.com","password":"123456"}');
            $promise  = $client->request($request);
            $response = yield $promise;
            $body = yield $response->getBody();
            print $body . PHP_EOL;
        } catch (Amp\Artax\HttpException $error) {
            echo $error;
        }
    });
});
