<?php
namespace Plinct\Tool;

/**
 * Class Curl
 * @package Plinct\Tool
 */
class Curl {
    /**
     * @var string
     */
    private $basePath;

    /**
     * Curl constructor.
     * @param $basePath
     */
    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * @param string $relativeUrl
     * @return string
     */
    private function getUrl(string $relativeUrl): string {
        return $this->basePath . (strpos($this->basePath, '/', -1) === false ? "/" : null) . $relativeUrl;
    }

    /**
     * @param string $relativeUrl
     * @param array|null $params
     * @return bool|string
     */
    public function get(string $relativeUrl, array $params = null) {
        $urlWithQueries = $relativeUrl . "?" . http_build_query($params);
        return $this->request("get", $urlWithQueries);
    }

    /**
     * @param string $relativeUrl
     * @param array $params
     * @param string|null $token
     * @return bool|string
     */
    public function post(string $relativeUrl, array $params, string $token = null) {
        return $this->request("post", $relativeUrl, $params, $token);
    }

    /**
     * @param string $relativeUrl
     * @param array $params
     * @param string $token
     * @return bool|string
     */
    public function put(string $relativeUrl, array $params, string $token) {
        return $this->request("put", $relativeUrl, $params, $token);
    }

    /**
     * @param string $relativeUrl
     * @param array $params
     * @param string $token
     * @return bool|string
     */
    public function delete(string $relativeUrl, array $params, string $token) {
        return $this->request("delete", $relativeUrl, $params, $token);
    }

    /**
     * @param string $type
     * @param string $relativeUrl
     * @param array|null $params
     * @param string|null $token
     * @return bool|string
     */
    private function request(string $type, string $relativeUrl, array $params = null, string $token = null) {
        $method = strtoupper($type);
        // CURL INIT
        $handle = curl_init($this->getUrl($relativeUrl));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        // HEADERS
        $headers[] = "Content-Type: application/json";
        if ($token) $headers[] = "Authorization: Bearer $token";
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        // METHOD
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        // PARAMS
        if ($params) curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_SLASHES));
        // disable for production
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        }
        // EXECUTE
        $exec = curl_exec($handle);
        if ($exec === false) {
            $response = curl_error($handle);
        } else {
            $response = $exec == '' ? true : $exec;
        }
        curl_close($handle);
        return $response;
    }

    /**
     * @return array
     */
    public function getImageData() {
        $response = false;
        // HANDLE
        $handle = curl_init($this->basePath);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($handle);
        // CHECK IF RESPONSE IS 200
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($responseCode == 200) {
            $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
            $type = substr(strstr($contentType, "/"), 1);
            $array['validate'] = strstr($contentType, '/', true) == "image";
            $array['fileSize'] = curl_getinfo($handle, CURLINFO_SIZE_DOWNLOAD);
            // get image sizes
            $temporary_image = imagecreatefromstring($data);
            $array['imageSize'] = [
                imagesx($temporary_image),
                imagesy($temporary_image),
                $type,
                'mime' => $contentType
            ];
            imagedestroy($temporary_image);
            $response = $array;
        }
        curl_close($handle);
        return $response;
    }

    public static function remote_file_exists($filename): bool {
        // Initialize cURL
        $ch = curl_init($filename);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // Check the response code
        if($responseCode == 200){
            return true;
        }else{
            return false;
        }
    }
}
