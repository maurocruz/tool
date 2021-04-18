<?php
namespace Plinct\Tool\Image;

use Plinct\Tool\Curl;
use Plinct\Tool\FileSystem\FileSystem;

abstract class ImageAbstract {
    // paths
    const NO_IMAGE = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";
    protected $src;
    protected $source;
    private $path;
    protected $pathFile;
    protected $dirname;
    private $basename;
    private $filename;
    protected $extension;
    // measures
    protected $width;
    protected $height;
    protected $type;
    protected $ratio;
    protected $fileSize;
    protected $encodingFormat;
    // state
    protected $remote;
    private $validate;
    // server paths
    private $docRoot;
    private $requestUri;
    protected $serverHost;
    protected $serverSchema;
    private $sourceScheme;
    private $sourceHost;
    // image transforms
    protected $imageTrueColor;
    protected $imageTemporary;

    protected function setServerRequests() {
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->serverHost = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $this->serverSchema = filter_input(INPUT_SERVER, 'HTTPS') && filter_input(INPUT_SERVER, 'HTTPS') != 'off' ? "https" : "http";
    }

    protected function setParseUrl() {
        $parseUrl = parse_url($this->source);
        $this->sourceScheme = $parseUrl['scheme'] ?? false;
        $this->sourceHost = $parseUrl['host'] ?? false;
        $this->path = $parseUrl['path'] ?? false;
    }

    protected function setSizes() {
        if (!$this->validate) $this->setValidate();
        if (file_exists($this->pathFile)) {
            $imageSize = getimagesize($this->pathFile);
            $this->width = $imageSize[0];
            $this->height = $imageSize[1];
            $this->type = $imageSize[2];
            $this->encodingFormat = $imageSize['mime'];
            $this->extension = substr(strstr($imageSize['mime'],'/'),1);
            $this->ratio = $this->width / $this->height;
            $this->fileSize = filesize($this->pathFile);
        } else {
            $this->width = 0;
            $this->height = 0;
        }
    }


    protected function setValidate(): bool {
        if (!$this->remote) $this->setRemote();
        if (!$this->pathFile) $this->setPathInfo();
        $filename = $this->pathFile;
        if ($this->remote) {
            $data = (new Curl($this->source))->getImageData();
            $this->validate = $data['validate'];
            $this->fileSize = $data['fileSize'];
            //$imageSize = $data['imageSize'];
        } elseif (is_file($filename) && is_readable($filename) && strstr(mime_content_type($filename), "/", true) == "image") {
            $this->validate = true;
        }
        return false;
    }

    protected function setPathInfo() {
        if (!$this->remote) $this->setRemote();
        if (!$this->path) $this->setParseUrl();
        if ($this->remote) {
            $this->pathFile = $this->source;
        } elseif(is_uploaded_file($this->path)) {
            $this->pathFile = $this->path;
        } else {
            if (!$this->docRoot) $this->setServerRequests();
            $this->pathFile = substr($this->path, 0, 1) != "/" ? $this->docRoot . $this->requestUri . $this->path : $this->docRoot . $this->path;
        }
        $pathInfo = pathinfo($this->path);
        $this->dirname = $pathInfo['dirname'];
        $this->basename = $pathInfo['basename'];
        $this->filename = $pathInfo['filename'];
    }

    protected function setRemote() {
        $this->remote = $this->sourceHost && $this->serverHost !== $this->sourceHost;
    }

    protected function setSrc() {
        if ($this->remote) {
            $this->src = $this->source;
        } else {
            if (!$this->sourceScheme) {
                $this->src = str_replace($this->docRoot, $this->serverSchema . "://" . $this->serverHost, $this->pathFile);
            } else {
                $this->src = $this->source;
            }
        }
    }

    protected function getValidate() {
        if($this->validate === null) $this->setValidate();
        return $this->validate;
    }

    protected function setTrueColorImage(int $width = null, int $height = null) {
        $this->imageTrueColor = imagecreatetruecolor($width ?? $this->newWidth, $height ?? $this->newHeight);
    }

    protected function setTemporaryImage() {
        if (!$this->imageTrueColor) $this->setTrueColorImage();
        switch ($this->type) {
            case '1':
                $this->imageTemporary = imagecreatefromgif($this->pathFile);
                break;
            case '2':
                $this->imageTemporary = imagecreatefromjpeg($this->pathFile);
                break;
            case '3': // PNG
                $this->imageTemporary = imagecreatefrompng($this->pathFile);
                imagealphablending($this->imageTrueColor, false);
                imagesavealpha($this->imageTrueColor, true);
                break;
        }
    }

    protected function saveToFile(string $destinationFile) {
        FileSystem::makeDirectory(dirname($destinationFile), 0777, true);

        $docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
        $pathfile = strpos($destinationFile, $docroot) !== false ? $destinationFile : $docroot . $destinationFile;

        switch ($this->type) {
            case '1':
                imagegif($this->imageTrueColor, $pathfile);
                break;
            case '2':
                imagejpeg($this->imageTrueColor, $pathfile);
                break;
            case '3':
                imagepng($this->imageTrueColor, $pathfile);
                break;
        }
        imagedestroy($this->imageTemporary);
        imagedestroy($this->imageTrueColor);
    }
}