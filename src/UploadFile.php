<?php
namespace Plinct\Tool;

class UploadFile {

    public function uploadImage($imagesUploaded, $destinationFolder): array {
        $response = [];
        // NUMBER OF IMAGES
        $numberOfImages = count($imagesUploaded['name']);
        // LOOP
        for ($i=0; $i<$numberOfImages; $i++) {
            $name = $imagesUploaded['name'][$i];
            $type = $imagesUploaded['type'][$i];
            $tmp_name = $imagesUploaded['tmp_name'][$i];
            $error = $imagesUploaded['error'][$i];
            $size = $imagesUploaded['size'][$i];
            if ($error === 0 && $size !== 0) {
                $prefix = date("Y-m-d_H:i:s_");
                $extension = substr(strstr($type,"/"),1);
                $filename = pathinfo($name)['filename'];
                $newName = $prefix . md5(StringTool::removeAccentsAndSpaces($filename)) . "." . $extension;
                $destinationFile = $destinationFolder . $newName;
                // IMAGE CLASS

                $image = (new Thumbnail($tmp_name))->uploadImage($destinationFile);

                if (is_object($image)) {
                    var_dump($image);
                    $response['status'] = "ok";
                    $response['data'][] = [
                        "contentUrl" => $image->getHeight(),
                        "height" => $image->getHeight()
                    ];
                }
            }
        }
        return $response;
    }
}