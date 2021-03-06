<?php
namespace Plinct\Tool\Image;

class Image extends Thumbnail implements ImageTransformInterface {

    /**
     * Image constructor.
     * @param string|null $source
     */
    public function __construct(string $source = null) {
        $this->setServerRequests();
        // DIRECTORY IMAGE
        $posLastSeparator = strrpos($this->requestUri, "/");
        $requestUri = substr($this->requestUri, 0, ($posLastSeparator + 1));
        $this->source = (substr($source,0,1) != "/" && substr($source,0,4) != "http" ? $requestUri . $source : $source) ?? $this->src;
    }

    /**
     * @param $width
     * @param null $height
     * @return ImageTransformInterface
     */
    public function resize($width, $height = null): ImageTransformInterface {
        if (!$this->width) parent::setSizes();
        parent::setNewSizes($width, $height);
        parent::setTemporaryImage();
        imagecopyresized($this->imageTrueColor, $this->imageTemporary, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
        return $this;
    }

    public function saveToFile(string $destinationFile) {
        parent::saveToFile($destinationFile);
    }

    /**
     * @param $width
     * @param null $height
     * @return mixed
     */
    public function thumbnail($width, $height = null) {
        if (!$this->width) parent::setSizes();
        return parent::getThumbnail($width, $height);
    }

    /**
     * @return bool
     */
    public function getRemote(): bool {
        if ($this->remote === null) $this->setRemote();
        return $this->remote;
    }

    /**
     * WIDTH
     * @return mixed
     */
    public function getWidth() {
        if (!$this->width) $this->setSizes();
        return $this->width;
    }

    /**
     * HEIGHT
     * @return mixed
     */
    public function getHeight() {
        if (!$this->height) $this->setSizes();
        return $this->height;
    }
    public function getFileSize(): ?int {
        if (!$this->fileSize) $this->setSizes();
        return $this->fileSize;
    }
    public function getSource(): string {
        return $this->source;
    }
    public function getSrc(): string {
        if (!$this->src) $this->setSrc();
        return $this->src;
    }
    public function getNewHeight(): int {
        if (!$this->newHeight) parent::setSizes();
        return $this->newHeight;
    }
    public function getNewWidth(): int {
        if (!$this->newWidth) parent::setSizes();
        return $this->newWidth;
    }

    public function getEncodingFormat(){
        return $this->encodingFormat;
    }
}