<?php
/**
 * VERSION 1.0
 */
namespace Plinct\Tool\StructuredData\v1;

use Exception;
use Plinct\Tool\StructuredData\v1\Type\Article;
use Plinct\Tool\StructuredData\v1\Type\ImageObject;
use Plinct\Tool\StructuredData\v1\Type\Taxon;

class StructuredData
{
  /**
   * @var array|null
   */
  private array $data;

	/**
  * @param array $data
  * @throws Exception
  */
  public function __construct(array $data)
  {
    if (isset($data['@type'])) {
      $this->data = self::swicth($data);
    } else {
      foreach ($data as $key => $value) {
        $this->data[$key] = self::swicth($value);
      }
    }
  }

	/**
  * @param $data
  * @return array|null
  * @throws Exception
  */
  private static function swicth($data): ?array
  {
    switch ($data['@type']) {
      case 'Article':
        return (new Article($data))->parse();
      case 'ImageObject':
        return (new ImageObject($data))->parse();
      case 'Taxon':
        return (new Taxon($data))->parse();
      default:
          return $data;
    }
  }

  /**
   * @return string
   */
  public function ready(): string
  {
    if (isset($this->data['description'])) $this->data['description'] = strip_tags($this->data['description']);
    return json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }
}
