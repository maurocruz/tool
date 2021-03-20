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

    public function getImageData(): array {
        $handle = curl_init($this->basePath);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($handle);
        $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $type = substr(strstr($contentType,"/"),1);
        $array['validate'] = strstr($contentType,'/', true) == "image";
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
        curl_close($handle);
        return $array;
    }
}
