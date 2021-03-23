<?php
namespace Plinct\Tool;

use function exif_read_data;

class Thumbnail extends Image {
    const IMAGE_MAX_SIZE = 1080;
    const NO_IMAGE = "https://pirenopolis.tur.br/App/static/cms/images/noImage.jpg";

    private string $pathFile;
    private $imageDirname;
    private $imageFilename;
    private $imageExtension;
    private int $newWidth;
    private int $newHeight;
    private int $newRatio;
    private static $image_max_width;
    private string $httpRoot;
    private $docRoot;

    public function __construct(string $src = null) {
        parent::__construct($src);
        $this->httpRoot = (filter_input(INPUT_SERVER, "REQUEST_SCHEME") ?? filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_PROTO"))."://".filter_input(INPUT_SERVER, "HTTP_HOST");
        $this->docRoot = filter_input(INPUT_SERVER, "DOCUMENT_ROOT");
        // set vars path and src
        $this->setPathFile($src ?? self::NO_IMAGE);
        // get info of image
        $pathInfo = pathinfo($this->pathFile);
        $this->imageDirname = $pathInfo['dirname'];
        $this->imageFilename = $pathInfo['filename'];
        $this->imageExtension = $pathInfo['extension'] ?? null;
        self::$image_max_width = $GLOBALS['image_max_width'] ?? self::IMAGE_MAX_SIZE;
    }

    private function setPathFile(string $path) {
        // set vars
        if (strpos($path, $this->httpRoot) !== false) { // e.g: http(s)://host/path
            $this->pathFile = str_replace($this->httpRoot, $this->docRoot, $path);
        } elseif (substr($path, 0, 2) == "//") { // e.g: '//host/path...
            $this->pathFile = str_replace("//". filter_input(INPUT_SERVER, "HTTP_HOST"), $this->docRoot, $path);
        } elseif (substr($path,0, 5) == "/tmp/") { // '/tmp/...' temporary upload files
            $this->pathFile = $path;
        } elseif (substr($path, 0, 1) == "/") { // e.g.: '/path/path/path
            $this->pathFile = $this->docRoot.$path;
        } else { // e.g.: path/path
            $uri = filter_input(INPUT_SERVER, "REQUEST_URI");
            if (substr($uri, -1) == "/") {
                $dir = $uri;
            } else {
                $exclude = strrchr($uri,"/");
                $len = strlen($exclude);
                $dir = substr($uri, 0, -$len);
            }
            $this->pathFile = $this->docRoot . $dir . "/" . $path;
        }
        // if file exists
        if (!file_exists($this->pathFile)) {
            $this->pathFile = $this->docRoot.self::NO_IMAGE;
        }
    }
    
    private function setNewMeasures($width, $height = null) {
        $this->newWidth = $width < 1 ? floor(self::$image_max_width * $width) : ($width == 1 ? parent::getWidth() : $width);
        $this->newHeight = isset($height) && $height !== (float) 0 ? floor($this->newWidth * $height) : floor($this->newWidth*(parent::getHeight()/parent::getWidth()));
        $this->newRatio = round($this->newHeight / $this->newWidth, 4);
    }

    public function getThumbnailAsAttributesImg($value): array {
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
    
    public function getThumbnail($newWidth, $newHeight = null) {
        if ($newWidth < 1 || $newHeight < 1) {
            $this->setNewMeasures($newWidth, $newHeight);
            $newWidth = $this->newWidth;
            $newHeight = $this->newHeight;            
        }
        // thumbnails names
        $thumbnailFile = $this->imageDirname."/thumbs/".$this->imageFilename."(".$newWidth."w".$newHeight.").".$this->imageExtension;
        $thumbnailSrc = str_replace($_SERVER['DOCUMENT_ROOT'], "//".$_SERVER['HTTP_HOST'], $thumbnailFile);

        // create thumb if not exists
        if (!file_exists($thumbnailFile) && file_exists($this->pathFile)) {
            $this->createThumbnail($newWidth, $newHeight, $thumbnailFile);
        }
        return $thumbnailSrc;
    }
        
    private function createThumbnail($newWidth, $newHeight, $thumbnailFile) 
    {
        $imageTemporary = null;
        $widthScale = null;
        // cria uma nova imagem
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        // prepara a imagem original
        switch (parent::getType()) {
            case '1': 
                $imageTemporary = imagecreatefromgif($this->pathFile);
                break;
            case '2': 
                $imageTemporary = imagecreatefromjpeg($this->pathFile);
                break;
            case '3': // PNG
                $imageTemporary = imagecreatefrompng($this->pathFile);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
        }
        // ajusta orientação 
        $orientation = @exif_read_data($this->pathFile)['Orientation'] ?? 1;
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
            imagecopyresized($newImage, $imageTemporary, 0, 0, 0, 0, $newWidth, $newHeight, parent::getWidth(), parent::getHeight());
        }
        // recorta a imagem
        else {
            // PAISAGEM
            if ($this->newRatio < 1) {
                $widthScale = parent::getRatio() >= $this->newRatio ? $newWidth : ceil($newHeight / parent::getRatio());
            }
            // RETRATO
            elseif ($this->newRatio > 1) {
                $widthScale = $orientation == 1 ? ceil($newHeight / parent::getRatio()) : ceil($newHeight * parent::getRatio());
            }
            // QUADRADO
            elseif ($this->newRatio == 1) {
                $widthScale = $orientation == 1 ? ceil($newWidth / parent::getRatio()) : $newWidth;
            }
            $imageTemporary = imagescale($imageTemporary, $widthScale);
            $src_x = (imagesx($imageTemporary) - $newWidth) / 2;
            $src_y = (imagesy($imageTemporary) - $newHeight) / 2;
            imagecopymerge($newImage, $imageTemporary, 0, 0, $src_x, $src_y, $newWidth, $newHeight, 100);
        }
        // create dir thumbs 
        $dirname = dirname($thumbnailFile);
        if (!is_dir($dirname)) {
            mkdir($dirname);
            chmod($dirname, 0777);
        }
        // save image
        switch (parent::getType()) {
            case '1': 
                imagegif($newImage, $thumbnailFile);
                break;
            case '2':
                imagejpeg($newImage, $thumbnailFile);
                break;
            case '3': 
                imagepng($newImage, $thumbnailFile);
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
            $this->createThumbnail($this->newWidth, $this->newHeight, $_SERVER['DOCUMENT_ROOT'] . $filename);
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
