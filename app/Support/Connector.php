<?php

namespace App\Support;

use Amp\Loop;
use Amp\Artax\Request;
use Amp\Artax\DefaultClient;
use Amp\Promise;
use Carbon\Carbon;

/**
 * Class Connector
 * @package App\Support
 */
class Connector
{
    protected $newToken = '';

    /**
     * @param $uri
     * @param $method
     *
     * @return mixed
     */
    public function fire($uri, $method)
    {
        Loop::run(function ($getDataWatcher) use ($uri, $method) {
            Loop::repeat(500, function () use ($getDataWatcher, $uri, $method) {
                /**
                 * @var \Amp\Artax\Response $response
                 */
                static $i = 1;
                $response = yield $this->connector($uri, $method);
                $body = yield $response->getBody();

                if (array_key_exists('error', json_decode($body, true))) {
                    $bodyArray = json_decode($body, true);
                    $error = $bodyArray['error'];
                    $message = $bodyArray['message'];
                    echo 'Error : ' . $error . PHP_EOL;
                    echo 'Message : ' . $message . PHP_EOL;
                    echo 'Trying to renew token...' . PHP_EOL;
                    Loop::delay(100, function() use ($getDataWatcher) {
                        echo 'Disable $getDataWatcher loop..'. PHP_EOL;
                        Loop::disable($getDataWatcher);
                    });
                    $tryNewLogin = $this->login();
                    if (!$tryNewLogin) {
                        echo 'Can not save new token!' . PHP_EOL;
                        exit();
                    }
                    Loop::delay(100, function() use ($getDataWatcher) {
                        echo 'Enable $getDataWatcher loop..';
                        Loop::enable($getDataWatcher);
                    });
                    echo 'Login Success!' . PHP_EOL;
                    echo 'Your new token is : ' . $this->newToken . PHP_EOL;
                }
                echo $i++ . ' : ' . Carbon::now() . ' : ' . $body . PHP_EOL;
            });
        });
    }

    /**
     * @return bool
     */
    public function login() : bool
    {
        Loop::run(function($tryLogin) {
            Loop::repeat(500, function () use ($tryLogin) {
                /**
                 * @var \Amp\Artax\Response $response
                 */
                $apiAuthUri = Config::apiAuthUri();
                $response = yield $this->connector($apiAuthUri, 'POST');
                $body = yield $response->getBody();
                $data = json_decode($body, true);
                $newToken = $data['access_token'];
                $config = new Config();
                $saveNewToken = $config->writeNewToken($newToken);
                if (!$saveNewToken) {
                    return false;
                }
                $this->newToken = $newToken;
                Loop::delay(100, function () use ($tryLogin) {
                    Loop::cancel($tryLogin);
                });
            });
        });
        return true;
    }

    /**
     * @param $uri
     * @param $method
     * @param $apiOptions
     * @return \Amp\Promise
     */
    protected function connector($uri, $method): Promise
    {
        $client = new DefaultClient;
        $apiOptions = Config::apiOptionals();
        $request = (new Request($uri, $method))
            ->withHeaders($apiOptions)
            ->withBody(Config::authLogin());

        return $client->request($request);
    }
}
