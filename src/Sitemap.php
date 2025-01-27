<?php
namespace Plinct\Tool;

use DOMDocument;
use DOMElement;
use DOMException;

class Sitemap
{
  /**
   * @var string
   */
  private string $filename;
  /**
   * @var string
   */
  private string $version = "1.0";
  /**
   * @var string
   */
  private string $encoding = "UTF-8";
  /**
   * @var DOMDocument
   */
  private DOMDocument $xml;
  /**
   * @var string
   */
  private static string $xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9";
  /**
   * @var string
   */
  private static string $xmlnsImage = "http://www.google.com/schemas/sitemap-image/1.1";
  /**
   * @var string
   */
  private static string $xmlnsNews = "http://www.google.com/schemas/sitemap-news/0.9";
  /**
   * @var DOMElement|false
   */
  private $urlset;
  /**
   * @var DOMElement|false
   */
  private $url;
  /**
   * @var string
   */
  private string $extension;
  /**
   * @var string
   */
  private static string $HOST;

	/**
	 * @param $filename
	 * @throws DOMException
	 */
  public function __construct($filename)
  {
    $this->filename = $filename;
    $this->xml = new DOMDocument($this->version, $this->encoding);
    $this->urlset = $this->xml->createElement("urlset");
    $this->urlset->setAttribute("xmlns", self::$xmlns);
    self::$HOST = (filter_input(INPUT_SERVER, 'HTTPS') == 'on' ? "https" : "http") . ":" . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . filter_input(INPUT_SERVER,'HTTP_HOST');
  }

	/**
	 * @param $data
	 * @param string $extension
	 * @return bool
	 * @throws DOMException
	 */
  public function saveSitemap($data, string $extension = "simple"): bool
  {
    $this->extension = $extension;
    $this->setNamespace();
    foreach ($data as $key => $value) {
      if ($key == 'image' && $value != null){
				$this->setNamespace("image");
      }
      $this->appendUrl($value);
    }
    return $this->saveXml();
  }

  /**
   * @param null $extension
   */
  private function setNamespace($extension = null)
  {
    if ($extension == "image")  $this->urlset->setAttribute("xmlns:image", self::$xmlnsImage);
    if ($this->extension == "news")   $this->urlset->setAttribute("xmlns:$this->extension", self::$xmlnsNews);
  }

	/**
	 * @param $value
	 * @throws DOMException
	 */
  private function appendUrl($value)
  {
    $this->url = $this->xml->createElement("url");
    $this->appendTag("loc",$value['loc']);
    if (isset($value['lastmod']))  {
			$this->appendTag("lastmod",$value['lastmod']);
    }
    if (isset($value['image'])) {
			$this->appendImage($value['image']);
    }
    if ($this->extension == "news") {
			$this->appendNews($value['news']);
    }
    $this->urlset->appendChild($this->url);
  }

	/**
	 * @param $tag
	 * @param $value
	 * @throws DOMException
	 */
  private function appendTag($tag, $value)
  {
      $this->url->appendChild($this->xml->createElement($tag,$value));
  }

	/**
	 * @param $value
	 * @throws DOMException
	 */
  private function appendImage($value)
  {
    $imageElement = $this->xml->createElement("image:image");
    $imageElement->appendChild($this->xml->createElement("image:loc",$value));
    $this->url->appendChild($imageElement);
  }

	/**
	 * @param $value
	 * @throws DOMException
	 */
  private function appendNews($value)
  {
    $newsNews = $this->xml->createElement("news:news");
    $newsPublication = $this->xml->createElement("news:publication");
    $newsPublication->appendChild($this->xml->createElement("news:name", rawurlencode($value['name'])));
    $newsPublication->appendChild($this->xml->createElement("news:language", $value['language']));
    $newsNews->appendChild($newsPublication);
    $newsNews->appendChild($this->xml->createElement("news:publication_date", $value['publication_date']));
    $newsNews->appendChild($this->xml->createElement("news:title", rawurlencode($value['title'])));
    $this->url->appendChild($newsNews);
  }

  /**
   *
   */
  private function saveXml()
  {
    $this->xml->appendChild($this->urlset);
	  return $this->xml->save($this->filename);
  }
}
