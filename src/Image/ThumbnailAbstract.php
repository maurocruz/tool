<?php
namespace Plinct\Tool\Image;

use DOMDocument;
use Exception;
use Plinct\Tool\Curl;

class ThumbnailAbstract extends ImageAbstract {
    const IMAGE_MAX_SIZE = 1080;
    protected $newWidth = null;
    protected $newHeight = null;
    protected $newRatio = null;
    protected $thumbPath = null;
    protected $thumbSrc = null;

    /**
     * @throws Exception
     */
    protected function ThumbIfExists(): bool {
        $this->setThumbPath();
        // REMOTE FILE
        if ($this->remote) {
            return Curl::remote_file_exists($this->thumbPath);
        }
        // LOCAL FILE
        if (file_exists($this->thumbPath)) {
            return true;
        }
        // RETURN
        return false;
    }

    /**
     * @throws Exception
     */
    public function setThumbPath(): void {
        $pathinfo = pathinfo($this->source);
        $thumbFile = "/thumbs/" . $pathinfo['filename'] . sprintf("(%sw%s)", $this->newWidth, $this->newHeight) . "." . $pathinfo['extension'];
        if ($this->remote) {
            $this->thumbPath = $pathinfo['dirname'] . $thumbFile;
            $this->thumbSrc = $this->thumbPath;
        } else {
            if (!$this->pathFile) $this->setPathInfo();
            if (!$this->width) $this->setSizes();
            $this->thumbPath = $this->docRoot . str_replace($this->sourceScheme."://".$this->sourceHost,"",$pathinfo['dirname']) . $thumbFile;
            $this->thumbSrc = $this->serverSchema . "://" . $this->serverHost . $this->dirname . $thumbFile;
        }
    }

    protected function setNewSizes($newWidth, $newHeight) {
        // NEW WIDTH
        $this->newWidth = $newWidth > 1 ? $newWidth : self::IMAGE_MAX_SIZE * $newWidth;
        // NEW HEIGHT
        if (!$newHeight) {
            $this->newHeight =  $this->newWidth / $this->ratio;
        } else {
            $this->newHeight = $newHeight > 1 && is_string($newHeight) ? $newHeight : $this->newWidth * $newHeight;
        }
        // NEW RATIO
        $this->newRatio = (float) $this->newWidth / $this->newHeight;
        // ADJUSTS IF NEW MEASURES > MEASURES
        $this->newHeight = (int) floor($this->newHeight);
        if ($this->newWidth > $this->width || $this->newHeight > $this->height) {
            if($this->ratio > $this->newRatio) {
                $this->newHeight = $this->height;
                $this->newWidth = (int) ($this->newHeight * $this->newRatio );
            } else {
                $this->newWidth = $this->width;
                $this->newHeight = (int) ($this->newWidth / $this->newRatio);
            }
        }
    }

    protected function copyResizedImage() {
        $widthScale = 1;
        if ($this->newRatio == $this->ratio) {
            imagecopyresized($this->imageTrueColor, $this->imageTemporary, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
        } else {
            // PAISAGEM
            if ($this->newRatio > 1) {
                $widthScale = $this->ratio < $this->newRatio ? $this->newWidth : ceil($this->newHeight * $this->ratio);
            }
            // RETRATO
            elseif ($this->newRatio < 1) {
                $widthScale = ceil($this->newHeight * $this->ratio);
            }
            // QUADRADO
            elseif ($this->newRatio == 1) {
                $widthScale = $this->ratio > 1 ? ceil($this->newWidth * $this->ratio) : $this->newWidth;
            }
            $this->imageTemporary = imagescale($this->imageTemporary, $widthScale);
            $src_x = (imagesx($this->imageTemporary) - $this->newWidth) / 2;
            $src_y = (imagesy($this->imageTemporary) - $this->newHeight) / 2;
            imagecopymerge($this->imageTrueColor, $this->imageTemporary, 0, 0, $src_x, $src_y, $this->newWidth, $this->newHeight, 100);
        }
        imagedestroy($this->imageTemporary);
    }

    protected function makeThumbDir () {
        if (!file_exists($this->thumbPath)) {
            $dirname = dirname($this->thumbPath);
            if (!is_dir($dirname)) {
                $oldumask = umask(0);
                mkdir($dirname);
                umask($oldumask);
            }
        }
    }

    protected function saveImage() {
        // MAKE DIR DESTINATIONS
        $this->makeThumbDir();
        switch ($this->type) {
            case '1':
                imagegif($this->imageTrueColor, $this->thumbPath);
                break;
            case '2':
                imagejpeg($this->imageTrueColor, $this->thumbPath);
                break;
            case '3':
                imagepng($this->imageTrueColor, $this->thumbPath);
                break;
        }
        imagedestroy($this->imageTrueColor);
    }

    protected function saveSvgThumbnail() {
        $dom = new DOMDocument();
        $svg = Curl::getUrlContents($this->source);
        $dom->loadXML($svg);
        $svgDom = $dom->documentElement;
        $svgDom->setAttribute('width', $this->newWidth);
        $svgDom->setAttribute('height', $this->newHeight);
        $this->makeThumbDir();
        $dom->save($this->thumbPath);
    }
}