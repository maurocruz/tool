<?php
namespace Plinct\Tool\Image;

interface ImageTransformInterface {

    public function resize($width, $height = null): ImageTransformInterface;

    public function saveToFile(string $destinationFile);
}