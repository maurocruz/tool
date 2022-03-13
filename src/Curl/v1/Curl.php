<?php

declare(strict_types=1);

namespace Plinct\Tool\Curl\v1;

class Curl
{
    /**
     * @var false|resource
     */
    private static $HANDLE;
    /**
     * @var array|null
     */
    private ?array $headers = null;
    /**
     * @var bool|string
     */
    private $exec = false;

    /**
     *
     */
    public function __construct()
    {
        self::$HANDLE = curl_init();
        curl_setopt(self::$HANDLE, CURLOPT_HEADER, false);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): Curl
    {
        curl_setopt(self::$HANDLE, CURLOPT_URL, $url);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        if (!$this->exec) $this->execute();

        return curl_getinfo(self::$HANDLE);
    }

    /**
     * @param string $method
     * @return $this
     */
    public function method(string $method): Curl
    {
        curl_setopt(self::$HANDLE, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        return $this;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function authorizationBear(string $token): Curl
    {
        if($token) curl_setopt(self::$HANDLE, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        return $this;
    }

    /**
     * @return $this
     */
    public function returnWithJson(): Curl
    {
        curl_setopt(self::$HANDLE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$HANDLE, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        return $this;
    }

    /**
     * @return $this
     */
    public function connectWithLocalhost(): Curl
    {
        curl_setopt(self::$HANDLE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$HANDLE, CURLOPT_SSL_VERIFYHOST, false);
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function params(array $params): Curl
    {
        if ($params) {
            curl_setopt(self::$HANDLE, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_SLASHES));
        }
        return $this;
    }

    /**
     *
     */
    private function execute(): void
    {
        $this->exec = curl_exec(self::$HANDLE);
    }

    /**
     * @return string
     */
    public function ready(): string
    {
        if ($this->headers) {
            curl_setopt(self::$HANDLE, CURLOPT_HTTPHEADER, $this->headers);
        }

        // for localhost
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == "::1") $this->connectWithLocalhost();

        if (!$this->exec) $this->execute();

        if (curl_error(self::$HANDLE) !== '' && $this->exec === false) {
            $return  = curl_error(self::$HANDLE);
        } else {
            $return = $this->exec;
        }
        curl_close(self::$HANDLE);

        return $return;
    }
}
