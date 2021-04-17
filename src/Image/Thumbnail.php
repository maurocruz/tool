<?php
namespace Plinct\Tool\Image;

class Thumbnail extends ThumbnailAbstract {


    protected function getThumbnail($width, $height = null) {
        // SET NEW SIZES
        parent::setNewSizes($width,$height);
        // FILE THUMBS IF EXISTS
        if (parent::ThumbIfExists()) return $this->thumbSrc;
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
        // CREATE IMAGE TRUE COLOR
        parent::setTrueColorImage();
        // CREATE TEMPORARY IMAGE
        parent::setTemporaryImage();
        // COPY AND RESIZED
        parent::copyResizedImage();

    }

    public function getNewRatio() {
        return $this->newRatio;
    }

}