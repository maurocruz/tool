<?php
namespace Plinct\Tool\Image;

class Image extends Thumbnail {

    /**
     * Image constructor.
     * @param string|null $source
     */
    public function __construct(string $source = null) {
        $this->source = $source ?? $this->src;
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
        return $this->src;
    }
}