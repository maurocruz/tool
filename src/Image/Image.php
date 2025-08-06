<?php
namespace Plinct\Tool\Image;

use Exception;

class Image extends Thumbnail implements ImageTransformInterface
{
  /**
   * Image constructor.
   * @param string|null $source
   */
  public function __construct(string $source = null)
  {
    $this->setServerRequests();
		$source = str_starts_with($source, '//') ? $this->protocol . $source : $source;
    // DIRECTORY IMAGE
    $posLastSeparator = strrpos($this->requestUri, "/");
    $requestUri = substr($this->requestUri, 0, ($posLastSeparator + 1));
    $this->source = $source ? ((!str_starts_with($source, "/") && !str_starts_with($source, "http") ? $requestUri . $source : $source) ?? $this->src) : null;
    // extension
    if($this->source) $this->setExtension();
  }

	/**
	 * @throws Exception
	 */
	public function createNewImage(string $destination, int $width, $height = null): string
	{
		if (!$this->width) {
			parent::setSizes();
		}
		parent::setNewSizes($width, $height);
		if ($this->validate) {
			parent::setTemporaryImage();
			parent::copyResizedImage();
			parent::saveToFile($destination);
		}
		return $this->protocol."://".$this->serverHost.$destination;
	}

  /**
   * @param $width
   * @param null $height
   * @return ImageTransformInterface
   * @throws Exception
   */
  public function resize($width, $height = null): ImageTransformInterface
  {
    if (!$this->width) parent::setSizes();
    parent::setNewSizes($width, $height);
		if ($this->validate && !parent::thumbIfExists()) {
			parent::setTemporaryImage();
			if ($this->imageTemporary) parent::copyResizedImage();
			if (isset(pathinfo($this->source)['extension'])) {
				parent::saveToFile($this->thumbPath);
			}
		}

    $this->src = $this->validate ? $this->thumbSrc : null;
    return $this;
  }

	/**
	 * @param string $destinationFile
	 * @return void
	 */
  public function saveToFile(string $destinationFile): void
  {
    parent::saveToFile($destinationFile);
  }

  /**
   * @param $width
   * @param null $height
   * @return ?string
   * @throws Exception
   */
  public function thumbnail($width, $height = null): ?string {
    if (!$this->isValidImage()) return null;
    if (!$this->width) parent::setSizes();
    return parent::getThumbnail($width, $height);
  }

  /**
   * @return bool
   */
  public function getRemote(): bool {
    return $this->remote;
  }

  /**
   * WIDTH
   * @return int|null
   * @throws Exception
   */
  public function getWidth(): ?int {
    if (!$this->width) $this->setSizes();
    return $this->width;
  }

  /**
   * HEIGHT
   * @return int|null
   * @throws Exception
   */
  public function getHeight(): ?int {
    if (!$this->height) $this->setSizes();
    return $this->height;
  }

  /**
   * @throws Exception
   */
  public function getFileSize(): ?float {
    if (!$this->fileSize) $this->setSizes();
    return $this->fileSize;
  }

  /**
   * @return string
   */
  public function getSource(): string {
    return $this->source;
  }

  /**
   * @throws Exception
   */
  public function getSrc(): ?string {
    if ($this->src === '') $this->setSrc();
    return $this->src;
  }

  /**
   * @throws Exception
   */
  public function getNewHeight(): int {
    if (!$this->newHeight) parent::setSizes();
    return $this->newHeight;
  }

  /**
   * @throws Exception
   */
  public function getNewWidth(): ?int {
    if (!$this->newWidth) parent::setSizes();
    return $this->newWidth;
  }

  /**
   * @return string
   */
  public function getEncodingFormat(): string {
    return $this->encodingFormat;
  }

	/**
	 * @return string|null
	 */
	public function getExtension(): ?string {
		return $this->extension;
	}

	/**
	 * @return string
	 */
	public function getBasename(): string
	{
		return $this->basename;
	}

	/**
	 * @return string
	 */
	public function getPathFile(): string
	{
		if($this->pathFile === '') {
			$this->setPathInfo();
		}
		return $this->pathFile;
	}

  /**
   * @throws Exception
   */
  public function isValidImage(): bool {
    return $this->getValidate();
  }

	public function isRemote(): bool {
		$this->setRemote();
		return $this->remote;
	}
}
