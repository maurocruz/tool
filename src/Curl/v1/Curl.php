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
     * @var string|null
     */
    private ?string $exec = null;

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
        if (!$this->exec) $this->exec();

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
        curl_close(self::$HANDLE);

        curl_setopt(self::$HANDLE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$HANDLE, CURLOPT_SSL_VERIFYHOST, false);

        $this->exec();
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
    private function exec()
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

        if (!$this->exec) $this->exec();

        if (curl_error(self::$HANDLE) !== '' && $this->exec === false) {
            $return  = curl_error(self::$HANDLE);
        } else {
            $return = $this->exec;
        }
        curl_close(self::$HANDLE);

        return $return;
    }
}
