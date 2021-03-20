<?php
namespace Plinct\Tool;

use Plinct\Cms\App;

class Image {
    public static $IMAGE;
    protected $source;
    protected $remote;
    protected $validate = false;
    protected $imageSize;
    private $ratio;
    private $fileSize;
    protected $src = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";

    public function __construct($source) {
        $this->source = $source;
        $this->setRemote();
        $this->setValidate();
        if ($this->validate) {
            $this->setSrc();
            $this->setSizes();
        }
        self::$IMAGE = $this;
    }

    protected function setRemote($remote = null) {
        if ($remote) {
            $this->remote = $remote;
        } else {
            $parseUrl = parse_url($this->source);
            $this->remote = array_key_exists('host', $parseUrl) ? filter_input(INPUT_SERVER, 'HTTP_HOST') !== $parseUrl['host'] : false;
        }
    }

    protected function setValidate($validate = null) {
        $filename = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . $this->source;
        if ($validate) {
            $this->validate = $validate;
        } elseif ($this->remote) {
            $data = (new Curl($this->source))->getImageData();
            $this->validate = $data['validate'];
            $this->fileSize = $data['fileSize'];
            $this->imageSize = $data['imageSize'];
        } elseif(is_file($filename) && is_readable($filename) && !is_executable($filename) && strstr(mime_content_type($filename), "/", true) == "image") {
            $this->validate = true;
            $this->fileSize = filesize($filename);
        }
    }

    protected function setSizes($imageSize = null) {
        if ($imageSize) {
            $this->imageSize = $imageSize;
        } elseif (!$this->imageSize) {
            $filename = $this->remote ? $this->source : filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . $this->source;
            $this->imageSize = getimagesize($filename);
        }
        $this->ratio = round($this->getHeight() / $this->getWidth(), 4);
    }

    protected function setSrc($src = null) {
        if ($src) {
            $this->src = $src;
        } elseif ($this->remote) {
            $this->src = $this->source;
        } else {
            $this->src = App::$HOST . $this->source;
        }
    }
    public function is_remote(): bool {
        return $this->remote;
    }
    public function is_valide(): bool {
        return $this->validate;
    }
    public function getImageSize(): array {
        return $this->imageSize;
    }
    public function getWidth() {
        return $this->imageSize[0];
    }
    public function getHeight() {
        return $this->imageSize[1];
    }
    public function getType() {
        return $this->imageSize[2];
    }
    public function getMimeType() {
        return $this->imageSize['mime'];
    }
    public function getFileSize() {
        return $this->fileSize;
    }
    public function getRatio() {
        return $this->ratio;
    }
    public function getSource() {
        return $this->source;
    }
    public function getSrc(): string {
        return $this->src;
    }
}