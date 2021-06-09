<?php
namespace plinct\tool\test;

use PHPUnit\Framework\TestCase;
use Plinct\Tool\Image\Image;

class ImageTest extends TestCase {

    /**
     * @Test
     */
    public function testIfCreateThumbnail() {
        $source = "https://pirenopolis.tur.br/portal/public/images/artigos/2021/2021-05-14_11:43:11_5e8d70103d5f7f826327f49f884cf435.jpeg";

        $image = new Image($source);

       $this->assertStringContainsString("thumbs",$image->thumbnail("200") );
    }
}