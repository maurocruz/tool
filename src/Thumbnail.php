<?php
namespace Plinct\Tool;

use function exif_read_data;

class Thumbnail extends Image {
    const IMAGE_MAX_SIZE = 1080;
    const NO_IMAGE = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";
    private $pathFile;
    private $newWidth;
    private $newHeight;
    private $newRatio;
    private static $image_max_width;

    public function __construct(string $src = null) {
        parent::__construct($src);
        self::$image_max_width = $GLOBALS['image_max_width'] ?? self::IMAGE_MAX_SIZE;
    }

    private function pathFile(): string {
        return $_SERVER['DOCUMENT_ROOT'] . $this->getSource();
    }

    private function thumbFile(): string {
        $thumbName = $this->getImageName() . "(" . $this->newWidth ."w".$this->newHeight.").".$this->getExtension();
        return dirname($this->getSource()) . DIRECTORY_SEPARATOR . "thumbs" . DIRECTORY_SEPARATOR . $thumbName;
    }

    private function thumbSrc(): string {
        $parseUrl = parse_url($this->getSrc());
        return $parseUrl['scheme'] . "://" . $parseUrl['host'] . $this->thumbFile();
    }
    
    private function setNewMeasures($width, $height = null) {
        $this->newWidth = $width < 1 ? floor(self::$image_max_width * $width) : ($width == 1 ? parent::getWidth() : $width);
        $this->newHeight = isset($height) && $height !== (float) 0 ? floor($this->newWidth * $height) : floor($this->newWidth*(parent::getHeight()/parent::getWidth()));
        $this->newRatio = round($this->newHeight / $this->newWidth, 4);
    }

    public function getThumbnailAsAttributesImg($value): array {
        parent::setValidate();
        if ($this->remote) {
            return ["src" => $this->src];
        } elseif ($this->validate) {
            // new sizes
            $this->setNewMeasures((float) $value['width'], isset($value['height']) ? (float) $value['height'] : null);
            // srcset
            // alt
            $attributes['alt'] = $value['title'] ?? $value['caption'] ?? "Imagem";

            $attributes = $this->sizesAndSrcset($attributes, $this->newWidth);

            $measure23 = floor(2 * (self::$image_max_width / 3));
            if ($this->newWidth > $measure23) {
                $attributes = $this->sizesAndSrcset($attributes, $measure23);
            }
            // src
            $finalWidth = $this->newWidth > parent::getWidth() ? parent::getWidth() : $this->newWidth;
            $finalHeight = $finalWidth * ($this->newHeight / $this->newWidth);

            $attributes['src'] = $this->getThumbnail($finalWidth, floor($finalHeight));

            return $attributes;
        }
        return [ "src" => self::NO_IMAGE ];
    }

    private function sizesAndSrcset($attributes, $size) {
        $mediaQuery = "(min-width: ".$size."px) ".$size."px";
        $attributes['sizes'] = isset($attributes['sizes']) ? $attributes['sizes'].", ".$mediaQuery : $mediaQuery;
        $srcset = $this->getThumbnail($size, floor($size*($this->newHeight/$this->newWidth)))." ".$size."w";
        $attributes['srcset'] = isset($attributes['srcset']) ? $attributes['srcset'].", ".$srcset : $srcset;
        return $attributes;
    }
    
    public function getThumbnail($newWidth, $newHeight = null): string {
        if ($newWidth < 1 || $newHeight < 1) {
            $this->setNewMeasures($newWidth, $newHeight);
        }
        // create thumb if not exists
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $this->thumbFile()) && file_exists($this->pathFile())) {
            $this->createThumbnail();
        }
        return $this->thumbSrc();
    }
        
    private function createThumbnail() {
        $imageTemporary = null;
        $widthScale = null;
        // cria uma nova imagem
        $newImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
        // prepara a imagem original
        switch (parent::getType()) {
            case '1': 
                $imageTemporary = imagecreatefromgif($this->pathFile());
                break;
            case '2': 
                $imageTemporary = imagecreatefromjpeg($this->pathFile());
                break;
            case '3': // PNG
                $imageTemporary = imagecreatefrompng($this->pathFile());
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
        }
        // ajusta orientação 
        $orientation = @exif_read_data($this->pathFile())['Orientation'] ?? 1;
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
        $dirname = dirname($_SERVER['DOCUMENT_ROOT'] . $this->thumbFile());
        if (!is_dir($dirname)) {
            mkdir($dirname);
            chmod($dirname, 0777);
        }
        // save image
        switch (parent::getType()) {
            case '1': 
                imagegif($newImage, $_SERVER['DOCUMENT_ROOT'] . $this->thumbFile());
                break;
            case '2':
                imagejpeg($newImage, $_SERVER['DOCUMENT_ROOT'] . $this->thumbFile());
                break;
            case '3': 
                imagepng($newImage, $_SERVER['DOCUMENT_ROOT'] . $this->thumbFile());
                break;
        }
        imagedestroy($newImage);
        imagedestroy($imageTemporary);
    }
    
    /**
     * Upload image. 
     * If image uploaded is larger than $image_max_width, create thumbnail
     * 
     * @param string $filename
     */
    public function uploadImage(string $filename) 
    {
        $this->setNewMeasures(self::$image_max_width);
        if (parent::getWidth() > self::$image_max_width) {
            $this->createThumbnail();
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'] . $filename;
            if(is_dir(dirname($path)) === false) {
                $path = $this->makeDir($path);
            }
            move_uploaded_file($this->pathFile, $path);
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
