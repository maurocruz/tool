<?php

declare(strict_types=1);

namespace Plinct\Tool\Image;

use Exception;
use Plinct\Tool\Curl;
use Plinct\Tool\FileSystem\FileSystem;
use SimpleXMLElement;

abstract class ImageAbstract
{
  /**
   *
   */
  const NO_IMAGE = "https://plinct.com.br/App/static/cms/images/noImage.jpg";
  /**
   * @var string
   */
  protected string $src = '';
  /**
   * @var ?string
   */
  protected ?string $source = null;
  /**
   * @var ?string
   */
  private ?string $path = null;
  /**
   * @var string
   */
  protected string $pathFile = '';
  /**
   * @var string
   */
  protected string $dirname = '';
  /**
   * @var ?string
   */
  protected ?string $extension = null;
  /**
   * @var int
   */
  protected int $width = 0;
  /**
   * @var int
   */
  protected int $height = 0;
  /**
   * @var int|string
   */
  protected $type;
  /**
   * @var float
   */
  protected float $ratio = 0;
  /**
   * @var int|float
   */
  protected float $fileSize = 0;
  /**
   * @var string
   */
  protected string $encodingFormat = '';
  /**
   * @var bool
   */
  protected bool $remote = false;
  /**
   * @var ?bool
   */
  protected ?bool $validate = null;
  /**
   * @var string
   */
  protected string $docRoot = '';
  /**
   * @var string
   */
  protected string $requestUri = '';
  /**
   * @var string
   */
  protected string $serverHost = '';
	/**
	 * @var string
	 */
	protected string $protocol;
  /**
   * @var ?string
   */
  protected ?string $sourceScheme = null;
  /**
   * @var ?string
   */
  protected ?string $sourceHost = null;
	/**
	 * @var resource
	 */
  protected $imageTrueColor;
  /**
   * @var resource
   */
  protected $imageTemporary;
	/**
	 * @var false|int
	 */
	private $exifOrientation = false;

	/**
   *
   */
  public function setExtension()
  {
    $pathInfo = pathinfo($this->source);
    $this->extension = $pathInfo['extension'] ?? null;
  }

  /**
   *
   */
  protected function setServerRequests()
  {
    $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
    $this->requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $this->serverHost = filter_input(INPUT_SERVER, 'HTTP_HOST');
		if (isset($_SERVER['HTTPS'])
			&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
			&& $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
		{
			$this->protocol = 'https';
		} else {
			$this->protocol = 'http';
		}
  }

  /**
   *
   */
  protected function setParseUrl()
  {
    $parseUrl = parse_url($this->source);
    $this->sourceScheme = $parseUrl['scheme'] ?? null;
    $this->sourceHost = $parseUrl['host'] ?? null;
    $this->path = $parseUrl['path'] ?? null;
  }

  /**
   * @throws Exception
   */
  protected function setSizes()
  {
    if (!$this->validate) $this->setValidate();

    if ($this->validate === false) {
     $this->source = self::NO_IMAGE;
     $this->setRemote();
     $this->setSizesForRemote();
    }
    elseif($this->remote === false) {
      if ($this->extension == "svg") {
        $svg = new SimpleXMLElement(file_get_contents($this->pathFile));
        $attributes = $svg->attributes();
        $width = (array) $attributes['width'];
        $height = (array) $attributes['height'];
        $this->width = (int) $width[0];
        $this->height = (int) $height[0];
        $this->type = "image/svg+xml";
        $this->encodingFormat = "image/svg+xml";
      }
      else {
        $imageSize = getimagesize($this->pathFile);
				if ($this->extension !== 'png' && $this->extension !== "gif") {
					$exif = exif_read_data($this->pathFile);
					$this->exifOrientation = $exif['Orientation'] ?? false;
				}

				switch ($this->exifOrientation) {
					case 6 :
						$this->width = (int)$imageSize[1];
						$this->height = (int)$imageSize[0];
						break;
					default:
						$this->width = (int)$imageSize[0];
						$this->height = (int)$imageSize[1];
				}

        $this->type = $imageSize[2];
        $this->encodingFormat = $imageSize['mime'];
      }

      $this->ratio = $this->width / $this->height;
      $this->fileSize = filesize($this->pathFile);
    }

  }

  /**
   * @throws Exception
   */
  protected function setSizesForRemote()
  {
    if ($this->remote) {
      $data = (new Curl($this->source))->getImageData();

      $this->validate = $data['validate'];
      if ($data['validate']) {
        $this->fileSize = $data['fileSize'];
        $imageSize = $data['imageSize'];

        $this->width = (int)$imageSize[0];
        $this->height = (int)$imageSize[1];

        if (is_numeric($imageSize[0])) {
          $this->ratio = $this->width / $this->height;
        }

        $this->extension = $imageSize[2];
        $this->encodingFormat = $imageSize['mime'];
      }
    }
  }

  /**
   * @throws Exception
   */
  protected function setValidate()
  {
		if($this->source) {
			if (!$this->remote) $this->setRemote();
			if (!$this->pathFile) $this->setPathInfo();

			if ($this->remote) {
				$this->setSizesForRemote();

			} elseif (is_file($this->pathFile) && is_readable($this->pathFile)) {
				$this->validate = strstr(mime_content_type($this->pathFile), "/", true) == "image";

			} else {
				$this->validate = false;
			}
		} else {
			$this->validate = false;
		}
  }

  /**
   *
   */
  protected function setPathInfo()
  {
    if (!$this->remote) $this->setRemote();
    if (!$this->path) $this->setParseUrl();

    if ($this->remote) {
      $this->pathFile = $this->source;

    } elseif(is_uploaded_file($this->path)) {
      $this->pathFile = $this->path;

    } elseif (is_string($this->path)) {
      if (!$this->docRoot) $this->setServerRequests();
      $this->pathFile = substr($this->path, 0, 1) != "/" ? $this->docRoot . $this->requestUri . $this->path : $this->docRoot . $this->path;
    }

    if (is_string($this->path)) {
      $pathInfo = pathinfo($this->path);
      $this->dirname = $pathInfo['dirname'];
    }
  }

  /**
   *
   */
  protected function setRemote()
  {
      if (!$this->sourceHost) $this->setParseUrl();

      $this->remote = $this->sourceHost && $this->sourceHost !== $this->serverHost;
  }

  /**
   * @throws Exception
   */
  protected function setSrc()
  {
      if ($this->remote == null) $this->setRemote();
      if ($this->validate == null) $this->setValidate();

      if ($this->remote) {
          if ($this->validate === false) {
             $this->source = self::NO_IMAGE;
          }
          $this->src = $this->source;

      } else {
          if (!$this->sourceScheme && $this->validate) {
              $this->src = str_replace($this->docRoot, $this->protocol . "://" . $this->serverHost, $this->pathFile);
          } else {
              $this->src = $this->source;
          }
      }
  }

  /**
   * @throws Exception
   */
  protected function getValidate(): bool
  {
      if($this->validate === null) $this->setValidate();
      return $this->validate;
  }

  /**
   * @param int|null $width
   * @param int|null $height
   */
  protected function setTrueColorImage(int $width = null, int $height = null)
  {
    $this->imageTrueColor = imagecreatetruecolor($width ?? $this->newWidth, $height ?? $this->newHeight);
  }

  /**
   *
   */
  protected function setTemporaryImage()
  {
    if (!$this->imageTrueColor) $this->setTrueColorImage();

    switch ($this->type) {
      case '1':
        $this->imageTemporary = imagecreatefromgif($this->pathFile);
        break;
      case '2':
	      $this->imageTemporary = imagecreatefromjpeg($this->pathFile);
	      break;
	    case '3': // PNG
        $this->imageTemporary = imagecreatefrompng($this->pathFile);
        imagealphablending($this->imageTrueColor, false);
        imagesavealpha($this->imageTrueColor, true);
        break;
    }

		switch ($this->exifOrientation) {
			case 3:
				$this->imageTemporary = imagerotate($this->imageTemporary, -180, 000000);
				break;
			case 6:
				$this->imageTemporary = imagerotate($this->imageTemporary, -90, 000000);
				break;
    }
  }

  /**
   * @param string $destinationFile
   */
  protected function saveToFile(string $destinationFile)
  {
    FileSystem::makeDirectory(dirname($destinationFile), 0777, true);

    $docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
    $pathfile = strpos($destinationFile, $docroot) !== false ? $destinationFile : $docroot . $destinationFile;

    switch ($this->type) {
      case '1':
        imagegif($this->imageTrueColor, $pathfile);
        break;
      case '2':
				imagewebp($this->imageTrueColor, $pathfile);
        break;
      case '3':
        imagepng($this->imageTrueColor, $pathfile);
        break;
    }

    imagedestroy($this->imageTrueColor);
  }
}
