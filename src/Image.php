<?php
namespace Plinct\Tool;

class Image {
    public static $IMAGE;
    protected $source;
    protected $remote;
    protected $validate = false;
    protected $imageSize = [];
    private $ratio;
    private $fileSize = null;
    protected $src = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";

    public function __construct(string $source = null) {
        $this->source = $source ?? $this->src;
        $this->setRemote();
        $this->setSrc();
        self::$IMAGE = $this;
    }

    private function setRemote() {
        $parseUrl = parse_url($this->source);
        $this->remote = array_key_exists('host', $parseUrl) ? filter_input(INPUT_SERVER, 'HTTP_HOST') !== $parseUrl['host'] : false;
    }

    protected function setValidate() {
        $filename = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . $this->source;
        if ($this->remote) {
            $data = (new Curl($this->source))->getImageData();
            $this->validate = $data['validate'];
            $this->fileSize = $data['fileSize'];
            $this->imageSize = $data['imageSize'];
        } elseif (is_file($filename) && is_readable($filename) && /*!is_executable($filename) &&*/ strstr(mime_content_type($filename), "/", true) == "image") {
            $this->validate = true;
            $this->fileSize = filesize($filename);
        }
    }

    private function setSizes() {
        if ($this->validate === null) $this->setValidate();
        $filename = $this->remote ? $this->source : filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . $this->source;
        $this->imageSize = getimagesize($filename);
        $this->ratio = round($this->getHeight() / $this->getWidth(), 4);
    }

    private function setSrc($src = null) {
        if ($src) {
            $this->src = $src;
        } elseif ($this->remote) {
            $this->src = $this->source;
        } else {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https" : "http";
            $this->src = $protocol . "://" . $_SERVER['HTTP_HOST'] . $this->source;
        }
    }
    public function is_remote(): bool {
        return $this->remote;
    }
    public function is_valide(): bool {
        return $this->validate;
    }
    public function getImageName(): string {
        return basename($this->source,".".$this->getExtension());
    }
    public function getImageSize(): array {
        return $this->imageSize;
    }
    public function getWidth() {
        if (!$this->imageSize) $this->setSizes();
        return $this->imageSize[0];
    }
    public function getHeight() {
        if (!$this->imageSize) $this->setSizes();
        return $this->imageSize[1];
    }
    public function getType() {
        if (!$this->imageSize) $this->setSizes();
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