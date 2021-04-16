<?php
namespace Plinct\Tool;

use function exif_read_data;

class Thumbnail extends Image {
    const IMAGE_MAX_SIZE = 1080;
    const NO_IMAGE = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";
    private $newWidth;
    private $newHeight;
    private $newRatio;
    private static $image_max_width;
    private $thumbSrc;
    private $thumbPath;

    /**
     * Thumbnail constructor.
     * @param string|null $src
     */
    public function __construct(string $src = null) {
        parent::__construct($src);
        self::$image_max_width = $GLOBALS['image_max_width'] ?? self::IMAGE_MAX_SIZE;
    }

    /**
     * @param mixed $newWidth
     */
    private function setNewWidth($newWidth): void {
        $this->newWidth = $newWidth < 1 ? floor(self::$image_max_width * $newWidth) : ($newWidth == 1 && parent::getWidth() != 0 ? parent::getWidth() : $newWidth);
    }

    /**
     * @param mixed $newHeight
     */
    private function setNewHeight($newHeight): void {
        if ($newHeight) {
            if ($newHeight !== (float) 0 && $newHeight < 1) {
                $this->newHeight = floor($this->newWidth * $newHeight);
            }
        } else {
            $this->newHeight = floor($this->newWidth * (parent::getHeight() / parent::getWidth()));
        }
    }

    private function setThumbPath($pathFile): void {
        $pathinfo = pathinfo($pathFile);
        $this->thumbPath = $_SERVER['DOCUMENT_ROOT'] . sprintf("%s/thumbs/%s(%sw%s).%s", $pathinfo['dirname'], $pathinfo['filename'], $this->newWidth, $this->newHeight, $this->getExtension());
    }

    /**
     * @return mixed
     */
    private function getThumbPath(): string {
        return sprintf("%s/thumbs/%s(%sw%s).%s", parent::getDirname(), parent::getFilename(), $this->newWidth, $this->newHeight, parent::getExtension());
    }

    /**
     * @return string
     */
    private function thumbFile(): string {
        $thumbPath = $this->getFilename() . "(" . $this->newWidth ."w".$this->newHeight.").".$this->getExtension();
        return dirname($this->getPathFile()) . DIRECTORY_SEPARATOR . "thumbs" . DIRECTORY_SEPARATOR . $thumbPath;
    }

    /**
     * THUMBNAIL SRC ATTRIBUTE
     */
    private function setThumbSrc() {
        $parseUrl = parse_url(parent::getSrc());
        $this->thumbSrc = $parseUrl['scheme'] . "://" . $parseUrl['host'] . $this->getThumbPath();
    }
    
    private function setNewMeasures($newWidth, $newHeight = null) {
        $this->setNewWidth($newWidth);
        $this->setNewHeight($newHeight);
        $this->newRatio = round($this->newHeight / $this->newWidth, 4);
    }

    public function getThumbSrc(): string {
        if (!$this->thumbSrc) $this->setThumbSrc();
        return $this->thumbSrc;
    }

    public function getThumbnailAsAttributesImg($value): array {
        // CHECK IF IS VALID IMAGE
        if (parent::getRemote()) {
            return ["src" => parent::getSrc()];
        } elseif (parent::getValidate()) {
            // NEW SIZES
            $this->setNewMeasures((float) $value['width'], isset($value['height']) ? (float) $value['height'] : null);
            // ALT ATTRIBUTE
            $attributes['alt'] = $value['title'] ?? $value['caption'] ?? "Imagem";
            // SRCSET ATTRIBUTES
            $attributes = $this->sizesAndSrcset($attributes, $this->newWidth);
            // if large new width
            $measure23 = floor(2 * (self::$image_max_width / 3));
            if ($this->newWidth > $measure23) {
                $attributes = $this->sizesAndSrcset($attributes, $measure23);
            }
            // SRC ATTRIBUTE
            $finalWidth = $this->newWidth > parent::getWidth() ? parent::getWidth() : $this->newWidth;
            $finalHeight = $finalWidth * ($this->newHeight / $this->newWidth);
            // THUMBNAIL
            $attributes['src'] = $this->getThumbnail($finalWidth, floor($finalHeight));
            // OUTUPUT
            return $attributes;
        }
        // NO IMAGE
        return [ "src" => self::NO_IMAGE ];
    }

    private function sizesAndSrcset($attributes, $size) {
        $mediaQuery = "(min-width: ".$size."px) ".$size."px";
        $attributes['sizes'] = isset($attributes['sizes']) ? $attributes['sizes'].", ".$mediaQuery : $mediaQuery;
        $srcset = $this->getThumbnail($size, $this->newWidth)." ".$size."w";
        $attributes['srcset'] = isset($attributes['srcset']) ? $attributes['srcset'].", ".$srcset : $srcset;
        return $attributes;
    }
    
    public function getThumbnail($newWidth, $newHeight = null): string {
        // SET NEW SIZES
        $this->setNewMeasures($newWidth, $newHeight);
        // create thumb if not exists
        if (!file_exists($this->thumbFile()) && file_exists(parent::getPathFile())) {
            $this->createThumbnail();
        }
        return $this->getThumbSrc();
    }
        
    private function createThumbnail() {
        $imageTemporary = null;
        $widthScale = null;
        // cria uma nova imagem
        $newImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
        // prepara a imagem original
        switch (parent::getType()) {
            case '1': 
                $imageTemporary = imagecreatefromgif($this->getPathFile());
                break;
            case '2': 
                $imageTemporary = imagecreatefromjpeg($this->getPathFile());
                break;
            case '3': // PNG
                $imageTemporary = imagecreatefrompng($this->getPathFile());
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
        }
        // ajusta orientação 
        $orientation = @exif_read_data($this->getPathFile())['Orientation'] ?? 1;
        switch ($orientation) {
            case 8: 
                $imageTemporary = imagerotate($imageTemporary, 90, 0); 
                break;
            case 3: 
                $imageTemporary = imagerotate($imageTemporary, 180, 0); 
                break;
            case 6: 
                $imageTemporary = imagerotate($imageTemporary, -90, 0); 
                break;
        }
        // copia a imagem em novas dimensões
        if ($this->newRatio == parent::getRatio()) {
            imagecopyresized($newImage, $imageTemporary, 0, 0, 0, 0, $this->newWidth, $this->newHeight, parent::getWidth(), parent::getHeight());
        }
        // recorta a imagem
        else {
            // PAISAGEM
            if ($this->newRatio < 1) {
                $widthScale = parent::getRatio() >= $this->newRatio ? $this->newWidth : ceil($this->newHeight / parent::getRatio());
            }
            // RETRATO
            elseif ($this->newRatio > 1) {
                $widthScale = $orientation == 1 ? ceil($this->newHeight / parent::getRatio()) : ceil($this->newHeight * parent::getRatio());
            }
            // QUADRADO
            elseif ($this->newRatio == 1) {
                $widthScale = $orientation == 1 ? ceil($this->newWidth / parent::getRatio()) : $this->newWidth;
            }
            $imageTemporary = imagescale($imageTemporary, $widthScale);
            $src_x = (imagesx($imageTemporary) - $this->newWidth) / 2;
            $src_y = (imagesy($imageTemporary) - $this->newHeight) / 2;
            imagecopymerge($newImage, $imageTemporary, 0, 0, $src_x, $src_y, $this->newWidth, $this->newHeight, 100);
        }
        // create dir thumbs
        if (!$this->thumbPath) $this->setThumbPath($this->getSource());
        $dirname = dirname($this->thumbPath);
        if (!is_dir($dirname)) {
            mkdir($dirname);
            chmod($dirname, 0777);
        }
        // save image
        $response = null;
        switch (parent::getType()) {
            case '1': 
                $response = imagegif($newImage, $this->thumbPath);
                break;
            case '2':
                $response = imagejpeg($newImage, $this->thumbPath);
                break;
            case '3':
                $response = imagepng($newImage, $this->thumbPath);
                break;
        }
        imagedestroy($newImage);
        imagedestroy($imageTemporary);
        return $response;
    }

    /**
     * Upload image.
     * If image uploaded is larger than $image_max_width, create thumbnail
     *
     * @param string $filename
     * @return bool
     */
    public function uploadImage(string $filename) {
        $this->setNewMeasures(self::$image_max_width);
        if (parent::getWidth() > self::$image_max_width) {
            $this->setThumbPath($filename);
            return $this->createThumbnail() ? $this : false;
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'] . $filename;
            if(is_dir(dirname($path)) === false) {
                $path = $this->makeDir($path);
            }
            return move_uploaded_file($this->getPathFile(), $path);
        }
    }

    private function makeDir($path) {
        $dir = null;
        foreach (explode("/",dirname($path)) as $value) {
            $newDir = $dir.$value;
            if (!is_dir($newDir) && $newDir != '') {
                mkdir($newDir);
                chmod($newDir,0777);
            }
            $dir = $newDir.DIRECTORY_SEPARATOR;
        }
          return $path;
    }
}
