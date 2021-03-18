<?php
namespace Plinct\Tool;

class Curl {
    private $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    public function get(string $relativeUrl, array $params = null) {
        $url = $this->basePath . $relativeUrl . "?" . http_build_query($params);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $exec = curl_exec($handle);
        curl_close($handle);
        return $exec;
    }
}
