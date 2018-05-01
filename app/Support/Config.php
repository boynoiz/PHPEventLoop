<?php

namespace App\Support;

use Carbon\Carbon;

class Config
{
    /**
     * @return array|false|string
     */
    public static function apiAuthUri()
    {
        return getenv('API_AUTH_URI');
    }

    /**
     * @return array|false|string
     */
    public static function apiCheckAuthUri()
    {
        return getenv('API_CHECK_AUTH');
    }

    /**
     * @return array|false|string
     */
    public static function apiDataUri()
    {
        return getenv('API_DATA_URI');
    }

    /**
     * @return array|false|string
     */
    public static function refreshTokenUri()
    {
        return getenv('API_REFRESH_TOKEN');
    }

        /**
     * @return string
     */
    public static function authLogin() : string
    {
        $body = [
            'email' => getenv('API_AUTH_EMAIL'),
            'password' => getenv('API_AUTH_PASSWORD')
        ];
        return json_encode($body);
    }

    /**
     * @return array
     */
    public static function apiOptionals() : array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Bearer ' . getenv('API_AUTH_TOKEN')
        ];
    }

    /**
     * @param $token
     * @return bool
     */
    public function writeNewToken($token): bool
    {
        $envFile = $this->envFilePath();
        $timestamp = Carbon::now()->format('d-m-Y-H-i');
        $saveToken = file_put_contents($envFile, preg_replace($this->tokenReplacementPattern(), 'API_AUTH_TOKEN=' . $token, file_get_contents($envFile)));
        $saveTimestamp = file_put_contents($envFile, preg_replace($this->timeReplacementPattern(), 'API_TOKEN_UPDATED_AT=' . $timestamp, file_get_contents($envFile)));
        if (!$saveToken || !$saveTimestamp) {
            return false;
        }
        $dotenv = new \Dotenv\Dotenv(BASE_PATH);
        $dotenv->overload();
        return true;
    }

    /**
     * @return string
     */
    protected function envFilePath(): string
    {
        $envFile = BASE_PATH . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envFile)) {
            echo 'No .env file found, copy env.sample.php to .env and add your details.', PHP_EOL;
            exit(1);
        }
        return $envFile;
    }

    /**
     * @return string
     */
    protected function tokenReplacementPattern(): string
    {
        $escaped = preg_quote('=' . getenv('API_AUTH_TOKEN'), '/');
        return "/^API_AUTH_TOKEN{$escaped}/m";
    }
    /**
     * @return string
     */
    protected function timeReplacementPattern(): string
    {
        $escaped = preg_quote('=' . getenv('API_TOKEN_UPDATED_AT'), '/');
        return "/^API_TOKEN_UPDATED_AT{$escaped}/m";
    }
}