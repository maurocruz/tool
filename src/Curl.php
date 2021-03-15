<?php
namespace Plinct\Tool;

class Curl {
    private $basepath;

    public function __construct($basepath) {
        $this->basepath = $basepath;
    }

    public function get(string $relativeUrl, array $params = null) {
        $handle = curl_init($this->basepath . $relativeUrl . "?" . http_build_query($params));
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $exec = curl_exec($handle);
        curl_close($handle);
        return $exec;
    }
}
