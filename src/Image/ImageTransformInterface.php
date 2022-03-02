<?php

declare(strict_types=1);

namespace Plinct\Tool\Image;

interface ImageTransformInterface
{
    /**
     * @return mixed
     */
    public function getSrc();

    /**
     * @param $width
     * @param null $height
     * @return ImageTransformInterface
     */
    public function resize($width, $height = null): ImageTransformInterface;

    /**
     * @param string $destinationFile
     * @return mixed
     */
    public function saveToFile(string $destinationFile);

}
