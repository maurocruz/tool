<?php
namespace Plinct\Tool;

class Image {
    private $source;
    private $scheme;
    private $host;
    private $path;
    private $pathFile;
    private $remote;
    private $validate = null;
    private $imageSize = [];
    private $ratio;
    private $fileSize = null;
    private $src = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";
    private $docRoot;
    private $requestUri;
    private $httpHost;

    public function __construct(string $source = null) {
        $this->source = $source ?? $this->src;
        // SERVER REQUESTS
        $this->setServerRequests();
        // PARSE URL
        $this->setParseUrl();
        // PATH FILE
        $this->setPathFile();
        // SET SRC
        $this->setSrc();
    }

    private function setServerRequests() {
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->httpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');
    }

    private function setParseUrl() {
        $parseUrl = parse_url($this->source);
        $this->scheme = $parseUrl['scheme'] ?? false;
        $this->host = $parseUrl['host'] ?? false;
        $this->path = $parseUrl['path'] ?? false;
    }

    protected function setPathFile() {
        $this->pathFile = substr($this->path,0,1) != "/"  ?  $this->docRoot . $this->requestUri . $this->path  :  $this->docRoot . $this->path;
    }

    private function setRemote() {
        $this->remote = $this->host ? $this->httpHost !== $this->host : false;
    }

    private function setSrc($src = null) {
        if ($src) {
            $this->src = $src;
        } elseif ($this->remote) {
            $this->src = $this->source;
        } else {
            if (!$this->scheme) {
                $protocol = filter_input(INPUT_SERVER, 'HTTPS') != 'off' ? "https" : "http";
                $this->src = str_replace($this->docRoot, $protocol . "://" . $this->httpHost, $this->pathFile);
            } else {
                $this->src = $this->source;
            }
        }
    }

    protected function setValidate(): bool {
        $filename = $this->getPathFile();
        if ($this->remote) {
            $data = (new Curl($this->source))->getImageData();
            $this->validate = $data['validate'];
            $this->fileSize = $data['fileSize'];
            $this->imageSize = $data['imageSize'];
        } elseif (is_file($filename) && is_readable($filename) && /*!is_executable($filename) &&*/ strstr(mime_content_type($filename), "/", true) == "image") {
            $this->validate = true;
            $this->fileSize = filesize($filename);
            $this->setSizes();
        }
        return false;
    }

    private function setSizes() {
        if ($this->validate === null) $this->setValidate();
        $filename = $this->getPathFile();
        if (file_exists($filename)) {
            $this->imageSize = getimagesize($filename);
            $this->ratio = round($this->imageSize[1] / $this->imageSize[0], 4);
        } else {
            $this->imageSize[0] = 0;
            $this->imageSize[1] = 0;
        }
    }
    protected function getValidate() {
        if($this->validate === null) $this->setValidate();
        return $this->validate;
    }
    protected function getPathFile(): string {
        return $this->pathFile;
    }
    public function getRemote(): bool {
        if ($this->remote === null) $this->setRemote();
        return $this->remote;
    }
    public function getImageName(): string {
        return basename($this->source,".".$this->getExtension());
    }
    public function getImageSize(): array {
        return $this->imageSize;
    }
    public function getWidth() {
        if (empty($this->imageSize)) $this->setSizes();
        return $this->imageSize[0];
    }
    public function getHeight() {
        if (empty($this->imageSize)) $this->setSizes();
        return $this->imageSize[1];
    }
    public function getType() {
        if (empty($this->imageSize)) $this->setSizes();
        return $this->imageSize[2];
    }
    public function getMimeType() {
        if (!$this->imageSize) $this->setSizes();
        return $this->imageSize['mime'];
    }
    public function getFileSize(): ?int {
        if (!$this->fileSize) $this->setSizes();
        return $this->fileSize;
    }
    public function getRatio(): float {
        return $this->ratio;
    }
    public function getSource(): string {
        return $this->source;
    }
    public function getSrc(): string {
        return $this->src;
    }
    public function getExtension(): string {
        return pathinfo($this->source)['extension'];
    }
}