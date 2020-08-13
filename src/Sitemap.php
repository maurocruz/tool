<?php

namespace Plinct\Tool;

class Sitemap
{
    private $filename;
    private $version = "1.0";
    private $encoding = "UTF-8";
    private $xml;
    private $xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9";

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function createSitemap($data)
    {
        $this->xml = new \DOMDocument($this->version, $this->encoding);

        $urlset = $this->xml->createElement("urlset");

        $urlset->setAttribute("xmlns", $this->xmlns);

        foreach ($data as $key => $value) {
            $url = $this->xml->createElement("url");

            // loc
            $loc = $this->xml->createElement("loc",$value['loc']);
            $url->appendChild($loc);

            // lastmod
            if (isset($value['lastmod'])) {
                $lastmod = $this->xml->createElement("lastmod",$value['lastmod']);
                $url->appendChild($lastmod);
            }

            $urlset->appendChild($url);
        }

        $this->xml->appendChild($urlset);

        $this->save();
    }

    private function save()
    {
        $this->xml->save($this->filename);
    }
}