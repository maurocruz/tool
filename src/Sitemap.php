<?php

namespace Plinct\Tool;

use DOMDocument;
use DateTime;
use Exception;

class Sitemap
{
    private $filename;

    private $version = "1.0";

    private $encoding = "UTF-8";

    private $xml;

    private $xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9";

    private $urlset;

    public function __construct($filename)
    {
        $this->filename = $filename;

        $this->xml = new DOMDocument($this->version, $this->encoding);

        $this->urlset = $this->xml->createElement("urlset");

        $this->urlset->setAttribute("xmlns", $this->xmlns);
    }

    public function createSitemapNews($data)
    {
        $this->urlset->setAttribute("xmlns:news", "http://www.google.com/schemas/sitemap-news/0.9");

        foreach ($data as $value) {
            $url = $this->xml->createElement("url");

            // loc
            $loc = $this->xml->createElement("loc", $value['loc']);
            $url->appendChild($loc);

            // news:news
            $newsNews = $this->xml->createElement("news:news");
                // news:publication
                $newsPublication = $this->xml->createElement("news:publication");
                    // news:name
                    $newsName = $this->xml->createElement("news:name", rawurlencode($value['name']));
                    $newsPublication->appendChild($newsName);

                    // news:language
                    $newsLanguage = $this->xml->createElement("news:language", $value['language']);
                    $newsPublication->appendChild($newsLanguage);

                $newsNews->appendChild($newsPublication);

                // news:publication_date
                $newsPublication_date = $this->xml->createElement("news:publication_date", self::formatDate($value['publication_date']));
                $newsNews->appendChild($newsPublication_date);

                // news:title
                $newsTitle = $this->xml->createElement("news:title", rawurlencode($value['title']));
                $newsNews->appendChild($newsTitle);

            $url->appendChild($newsNews);

            $this->urlset->appendChild($url);
        }

        $this->saveXml();
    }

    public function createSitemap($data)
    {
        foreach ($data as $value) {
            $url = $this->xml->createElement("url");

            // loc
            $loc = $this->xml->createElement("loc", $value['loc']);
            $url->appendChild($loc);

            // lastmod
            if (isset($value['lastmod'])) {
                $lastmod = $this->xml->createElement("lastmod", self::formatDate($value['lastmod']));
                $url->appendChild($lastmod);
            }

            $this->urlset->appendChild($url);
        }

        $this->saveXml();
    }

    private function saveXml()
    {
        $this->xml->appendChild($this->urlset);

        $this->xml->save($this->filename);
    }

    private static function formatDate($date)
    {
        try {
            $dateModified = new DateTime($date);
            $formattedDate = date('c', $dateModified->getTimestamp());
        } catch (Exception $e) {
            $formattedDate = date('c');
        }

        return $formattedDate;
    }
}