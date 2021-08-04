<?php
namespace Plinct\Tool\Image;

use Exception;
use Plinct\Tool\Curl;
use Plinct\Tool\FileSystem\FileSystem;
use SimpleXMLElement;

abstract class ImageAbstract {
    // paths
    const NO_IMAGE = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";
    protected $src = '';
    protected $source = '';
    private $path = '';
    protected $pathFile = '';
    protected $dirname = '';
    protected $extension = '';
    // measures
    protected $width = 0;
    protected $height = 0;
    protected $type = '';
    protected $ratio = 0;
    protected $fileSize = 0;
    protected $encodingFormat = '';
    // state
    protected $remote = false;
    protected $validate = false;
    // server paths
    protected $docRoot = '';
    protected $requestUri = '';
    protected $serverHost = '';
    protected $serverSchema = '';
    protected $sourceScheme = '';
    protected $sourceHost = '';
    // image transforms
    protected $imageTrueColor;
    protected $imageTemporary;

    public function setExtension() {
        $pathInfo = pathinfo($this->source);
        $this->extension = $pathInfo['extension'];
    }

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

    /**
     * @throws Exception
     */
    protected function setSizes() {
        if (!$this->validate) $this->setValidate();
        if ($this->validate === false) {
           $this->source = self::NO_IMAGE;
           $this->setRemote();
           $this->setSizesForRemote();
        } elseif($this->remote === false) {
            if ($this->extension == "svg") {
                $svg = new SimpleXMLElement(file_get_contents($this->pathFile));
                $attributes = $svg->attributes();
                $width = (array) $attributes['width'];
                $height = (array) $attributes['height'];
                $this->width = $width[0];
                $this->height = $height[0];
                $this->type = "image/svg+xml";
                $this->encodingFormat = "image/svg+xml";
            } else {
                $imageSize = getimagesize($this->pathFile);
                $this->width = $imageSize[0];
                $this->height = $imageSize[1];
                $this->type = $imageSize[2];
                $this->encodingFormat = $imageSize['mime'];
            }
            $this->ratio = $this->width / $this->height;
            $this->fileSize = filesize($this->pathFile);
        }
    }

    /**
     * @throws Exception
     */
    protected function setSizesForRemote() {
        if ($this->remote) {
            $data = (new Curl($this->source))->getImageData();
            $this->validate = $data['validate'];
            $this->fileSize = $data['fileSize'];
            $imageSize = $data['imageSize'];
            $this->width = $imageSize[0];
            $this->height = $imageSize[1];
            if (is_numeric($this->width)) {
                $this->ratio = $this->width / $this->height;
            }
            $this->extension = $imageSize[2];
            $this->encodingFormat = $imageSize['mime'];
        }
    }

    /**
     * @throws Exception
     */
    protected function setValidate() {
        if (!$this->remote) $this->setRemote();
        if (!$this->pathFile) $this->setPathInfo();
        if ($this->remote) {
            $this->setSizesForRemote();
        } elseif (is_file($this->pathFile) && is_readable($this->pathFile)) {
            $this->validate = strstr(mime_content_type($this->pathFile), "/", true) == "image";
        } else {
            $this->src = self::NO_IMAGE;
            $this->validate = false;
        }
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
    }

    protected function setRemote() {
        if (!$this->sourceHost) $this->setParseUrl();
        $this->remote = $this->sourceHost && $this->sourceHost !== $this->serverHost;
    }

    /**
     * @throws Exception
     */
    protected function setSrc() {
        if ($this->remote === null) $this->setRemote();
        if ($this->validate === null) $this->setValidate();
        if ($this->remote) {
            if ($this->validate === false) {
               $this->source = self::NO_IMAGE;
            }
            $this->src = $this->source;
        } else {
            if (!$this->sourceScheme && $this->validate) {
                $this->src = str_replace($this->docRoot, $this->serverSchema . "://" . $this->serverHost, $this->pathFile);
            } else {
                $this->src = $this->source;
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function getValidate(): bool {
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