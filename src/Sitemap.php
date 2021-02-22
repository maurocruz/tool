<?php
namespace Plinct\Tool;

use DOMDocument;

class Sitemap
{
    private $filename;
    private $version = "1.0";
    private $encoding = "UTF-8";
    private $xml;
    private static $xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9";
    private static $xmlnsImage = "http://www.google.com/schemas/sitemap-image/1.1";
    private static $xmlnsNews = "http://www.google.com/schemas/sitemap-news/0.9";
    private $urlset;
    private $url;
    private $extension;

    public function __construct($filename) {
        $this->filename = $filename;
        $this->xml = new DOMDocument($this->version, $this->encoding);
        $this->urlset = $this->xml->createElement("urlset");
        $this->urlset->setAttribute("xmlns", self::$xmlns);
    }

    public function saveSitemap($data, $extension = "simple") {
        $this->extension = $extension;
        $this->setNamespace();

        foreach ($data as $value) {
            if (isset($value['image'])) $this->setNamespace("image");
            $this->appendUrl($value);
        }
        $this->saveXml();
    }

    private function setNamespace($extension = null) {
        if ($extension == "image")  $this->urlset->setAttribute("xmlns:image", self::$xmlnsImage);
        if ($this->extension == "news")   $this->urlset->setAttribute("xmlns:$this->extension", self::$xmlnsNews);
    }

    private function appendUrl($value) {
        $this->url = $this->xml->createElement("url");
            $this->appendTag("loc",$value['loc']);
            if (isset($value['lastmod']))       $this->appendTag("lastmod",$value['lastmod']);
            if (isset($value['image']))         $this->appendImage($value['image']);
            if ($this->extension == "news")     $this->appendNews($value['news']);
        $this->urlset->appendChild($this->url);
    }

    private function appendTag($tag, $value) {
        $this->url->appendChild($this->xml->createElement($tag,$value));
    }

    private function appendImage($value) {
        foreach ($value as $item) {
            $imageElement = $this->xml->createElement("image:image");
            $imageElement->appendChild($this->xml->createElement("image:loc",$item['contentUrl']));
            if(isset($item['caption'])) $imageElement->appendChild($this->xml->createElement("image:caption",strip_tags($item['caption'])));
            $imageElement->appendChild($this->xml->createElement("image:license",$item['license']));
            $this->url->appendChild($imageElement);
        }
    }

    private function appendNews($value) {
        $newsNews = $this->xml->createElement("news:news");
            $newsPublication = $this->xml->createElement("news:publication");
                $newsPublication->appendChild($this->xml->createElement("news:name", rawurlencode($value['name'])));
                $newsPublication->appendChild($this->xml->createElement("news:language", $value['language']));
            $newsNews->appendChild($newsPublication);
            $newsNews->appendChild($this->xml->createElement("news:publication_date", $value['publication_date']));
            $newsNews->appendChild($this->xml->createElement("news:title", rawurlencode($value['title'])));
        $this->url->appendChild($newsNews);
    }

    private function saveXml() {
        $this->xml->appendChild($this->urlset);
        $this->xml->save($this->filename);
    }
}