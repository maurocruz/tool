<?php

declare(strict_types=1);

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
		$source = $source ? str_replace(' ','%20',$source) : null;
    $this->setServerRequests();
    // DIRECTORY IMAGE
    $posLastSeparator = strrpos($this->requestUri, "/");
    $requestUri = substr($this->requestUri, 0, ($posLastSeparator + 1));
    $this->source = $source ? ((substr($source,0,1) != "/" && substr($source,0,4) != "http" ? $requestUri . $source : $source) ?? $this->src) : null;
    // extension
    if($this->source) $this->setExtension();
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

    parent::setTemporaryImage();
    if ($this->imageTemporary) parent::copyResizedImage();

		if (isset(pathinfo($this->source)['extension']) && !parent::thumbIfExists()) {
			parent::saveToFile($this->thumbPath);
		}

    $this->src = $this->thumbSrc ?? '';
    return $this;
  }

  public function saveToFile(string $destinationFile) {
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
  public function getSrc(): string {
    if (!$this->src) $this->setSrc();
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
   * @throws Exception
   */
  public function isValidImage(): bool {
    return $this->getValidate();
  }
}
