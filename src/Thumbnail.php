<?php

namespace Plinct\Tool;

use function exif_read_data;

class Thumbnail
{
    const IMAGE_MAX_SIZE = 1080;
    
    const NO_IMAGE = "/fwcSrc/images/noImage.jpg";
    
    protected $src;
    
    protected $pathfile;
    
    protected $imageDirname;

    protected $imageFilename;
    
    protected $imageExtension;
    
    protected $originalWidth;
    
    protected $originalHeight;

    protected $originalRatio;
    
    protected $imageType;
    
    protected $newWidth;
    
    protected $newHeight;

    protected $newRatio;
    
    protected static $image_max_width;
    
    protected $httpRoot;
    
    protected $docroot;

    public function __construct(string $src = null) 
    {
        $this->httpRoot = (filter_input(INPUT_SERVER, "REQUEST_SCHEME") ?? filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_PROTO"))."://".filter_input(INPUT_SERVER, "HTTP_HOST");
        
        $this->docroot = filter_input(INPUT_SERVER, "DOCUMENT_ROOT");
        
        // set vars path and src
        $this->setPathfile($src ?? self::NO_IMAGE);
        
        // get info of image
        $pathInfo = pathinfo($this->pathfile);
        
        $this->imageDirname = $pathInfo['dirname'];
        $this->imageFilename = $pathInfo['filename'];
        $this->imageExtension = $pathInfo['extension'] ?? null;

        self::$image_max_width = $GLOBALS['image_max_width'] ?? self::IMAGE_MAX_SIZE;
    }
    
    private function setPathfile(string $path) 
    {
        // set vars
        if (strpos($path, $this->httpRoot) !== false) { // e.g: http(s)://host/path
            $this->src = $path;
            $this->pathfile = str_replace($this->httpRoot, $this->docroot, $path);
            
        } elseif (substr($path, 0, 2) == "//") { // e.g: '//host/path...
            $this->src = $path;
            $this->pathfile = str_replace("//". filter_input(INPUT_SERVER, "HTTP_HOST"), $this->docroot, $path);
            
        } elseif (substr($path,0, 5) == "/tmp/") { // '/tmp/...' temporary upload files
            $this->src = $this->httpRoot.$path;
            $this->pathfile = $path;
            
        } elseif (substr($path, 0, 1) == "/") { // e.g.: '/path/path/path
            $this->src = $this->httpRoot.$path;
            $this->pathfile = $this->docroot.$path;
            
        } else { // e.g.: path/path
            $uri = filter_input(INPUT_SERVER, "REQUEST_URI");
            
            if (substr($uri, -1) == "/") {
                $dir = $uri;
            } else {
                $exclude = strrchr($uri,"/");
                $len = strlen($exclude);
                $dir = substr($uri, 0, -$len);
            }
            
            $this->src = $this->httpRoot . $dir . "/" . $path;
            $this->pathfile = $this->docroot . $dir . "/" . $path;
        }
        
        // if file exists
        if (!file_exists($this->pathfile)) {
            $this->src = $this->httpRoot.self::NO_IMAGE;
            $this->pathfile = $this->docroot.self::NO_IMAGE;
        }
    }
    
    private function setNewMeasures($width, $height = null) 
    {
        // original sizes
        list($this->originalWidth, $this->originalHeight, $this->imageType) = getimagesize($this->pathfile);

        $this->originalRatio = round($this->originalHeight / $this->originalWidth, 4);
        
        $this->newWidth = $width < 1 ? floor(self::$image_max_width * $width) : ($width == 1 ? $this->originalWidth : $width);
        
        $this->newHeight = isset($height) && $height !== (float) 0 ? floor($this->newWidth * $height) : floor($this->newWidth*($this->originalHeight/$this->originalWidth));

        $this->newRatio = round($this->newHeight / $this->newWidth, 4);
    }


    public function getThumbnailAsAttributesImg($value) 
    {
        // new sizes
        $this->setNewMeasures((float) $value['width'], isset($value['height']) ? (float) $value['height'] : null);  
        
        // alt
        $attributes['alt'] = $value['title'] ?? $value['caption'] ?? "Imagem";
        
        // srcset  
        $attributes = $this->sizesAndSrcset($attributes, $this->newWidth);
        
        $measure23 = floor(2*(self::$image_max_width/3));
        
        if ($this->newWidth > $measure23) {
            $attributes = $this->sizesAndSrcset($attributes, $measure23);
        }
        
        // src
        $finalWidth = $this->newWidth > $this->originalWidth ? $this->originalWidth : $this->newWidth;
        
        $finalHeight = $finalWidth*($this->newHeight/$this->newWidth);
        
        $attributes['src'] = $this->getThumbnail($finalWidth, floor($finalHeight));
        
        return $attributes;
    }

    private function sizesAndSrcset($attributes, $size)
    {  
        $mediaQuery = "(min-width: ".$size."px) ".$size."px";
        
        $attributes['sizes'] = isset($attributes['sizes']) ? $attributes['sizes'].", ".$mediaQuery : $mediaQuery; 
        
        $srcset = $this->getThumbnail($size, floor($size*($this->newHeight/$this->newWidth)))." ".$size."w";
        
        $attributes['srcset'] = isset($attributes['srcset']) ? $attributes['srcset'].", ".$srcset : $srcset;
        
        return $attributes;
    }
    
    public function getThumbnail($newWidth, $newHeight = null)
    {
        if ($newWidth < 1 || $newHeight < 1) {
            $this->setNewMeasures($newWidth, $newHeight);
            
            $newWidth = $this->newWidth;
            $newHeight = $this->newHeight;            
        }    
        
        // thumbnails names
        $thumbnailFile = $this->imageDirname."/thumbs/".urlencode($this->imageFilename)."(".$newWidth."w".$newHeight.").".$this->imageExtension;
        $thumbnailSrc = str_replace($_SERVER['DOCUMENT_ROOT'], "//".$_SERVER['HTTP_HOST'], $thumbnailFile);
        
        // create thumb if not exists
        if (!file_exists($thumbnailFile) && file_exists($this->pathfile)) {
            $this->createThumbnail($newWidth, $newHeight, $thumbnailFile);
        }        
        
        return $thumbnailSrc;
    }
        
    private function createThumbnail($newWidth, $newHeight, $thumbnailFile) 
    {
        // cria uma nova imagem
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // prepara a imagem original
        switch ($this->imageType) {
            case '1': 
                $imageTemporary = imagecreatefromgif($this->pathfile); 
                break;
            
            case '2': 
                $imageTemporary = imagecreatefromjpeg($this->pathfile); 
                break;
            
            case '3': // PNG
                $imageTemporary = imagecreatefrompng($this->pathfile); 
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
        }   
        
        // ajusta orientação 
        $orientation = @exif_read_data($this->pathfile)['Orientation'] ?? 1;
        
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
        if ($this->newRatio == $this->originalRatio) {
            imagecopyresized($newImage, $imageTemporary, 0, 0, 0, 0, $newWidth, $newHeight, $this->originalWidth, $this->originalHeight);
            
        }
        // recorta a imagem
        else {
            // PAISAGEM
            if ($this->newRatio < 1) {
                $widthScale = $this->originalRatio >= $this->newRatio ? $newWidth : ceil($newHeight / $this->originalRatio);
            }
            // RETRATO
            elseif ($this->newRatio > 1) {
                $widthScale = $orientation == 1 ? ceil($newHeight / $this->originalRatio) : ceil($newHeight * $this->originalRatio);
            }
            // QUADRADO
            elseif ($this->newRatio == 1) {
                $widthScale = $orientation == 1 ? ceil($newWidth / $this->originalRatio) : $newWidth;
            }
            
            $imageTemporary = imagescale($imageTemporary, $widthScale);
            $src_x = (imagesx($imageTemporary) - $newWidth) / 2;
            $src_y = (imagesy($imageTemporary) - $newHeight) / 2;
            imagecopymerge($newImage, $imageTemporary, 0, 0, $src_x, $src_y, $newWidth, $newHeight, 100);
        }   
        
        // create dir thumbs 
        $dirname = dirname($thumbnailFile);
        
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777);
            chmod($dirname, 0777);
        }  
        
        // save image
        switch ($this->imageType) {
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
     * If image uploaded is larger than $image_max_widht, create thumbnail
     * 
     * @param string $filename
     */
    public function uploadImage(string $filename) 
    {
        $this->setNewMeasures(self::$image_max_width);
        
        if ($this->originalWidth > self::$image_max_width) {
            $this->createThumbnail($this->newWidth, $this->newHeight, $_SERVER['DOCUMENT_ROOT'] . $filename);
            
        } else {
            move_uploaded_file($this->pathfile, $_SERVER['DOCUMENT_ROOT'] . $filename);            
        }
    }
}
