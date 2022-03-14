<?php

declare(strict_types=1);

namespace Plinct\Tool\Image;

use Exception;

class Thumbnail extends ThumbnailAbstract
{
    /**
     * @throws Exception
     */
    protected function getThumbnail($width, $height = null): string
    {
        // SET NEW SIZES
        parent::setNewSizes($width,$height);

        // FILE THUMBS IF EXISTS
        if (parent::ThumbIfExists()) return $this->thumbSrc;

        // IF REMOTE FILE
        $this->setSrc();
        if($this->remote || !$this->validate) return $this->src;

        // SAVE THUMBNAIL
        $this->saveThumbnail();

        return $this->thumbSrc;
    }

    /**
     *
     */
    private function saveThumbnail()
    {
        if ($this->type == "image/svg+xml") {
            $this->saveSvgThumbnail();

        } else {
            // CREATE THUMBNAIL
            $this->createThumbnail();
            // SAVE THUMBNAIL
            parent::saveToFile($this->thumbPath);
        }
    }

    /**
     *
     */
    private function createThumbnail()
    {
        // CREATE TEMPORARY IMAGE
        parent::setTemporaryImage();

        // COPY AND RESIZED
        parent::copyResizedImage();
    }

    /**
     * @return float
     */
    public function getNewRatio(): float
    {
        return $this->newRatio;
    }

    /**
     * @return string
     */
    public function getThumbSrc(): string
    {
        return $this->thumbSrc;
    }
}
