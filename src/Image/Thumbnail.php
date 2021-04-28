<?php
namespace Plinct\Tool\Image;

class Thumbnail extends ThumbnailAbstract {

    protected function getThumbnail($width, $height = null) {
        // SET NEW SIZES
        parent::setNewSizes($width,$height);
        // FILE THUMBS IF EXISTS
        if (parent::ThumbIfExists()) return $this->thumbSrc;
        // IF REMOTE FILE
        $this->setSrc();
        if($this->remote) return $this->src;
        // SAVE THUMBNAIL
        $this->saveThumbnail();
        return $this->thumbSrc;
    }

    private function saveThumbnail() {
        // CREATE THUMBNAIL
        $this->createThumbnail();
        // SAVE THUMBNAIL
        parent::saveImage();
    }

    private function createThumbnail() {
        // CREATE TEMPORARY IMAGE
        parent::setTemporaryImage();
        // COPY AND RESIZED
        parent::copyResizedImage();
    }

    public function getNewRatio() {
        return $this->newRatio;
    }

    public function getThumbSrc() {
        return $this->thumbSrc;
    }


}