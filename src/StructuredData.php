<?php
declare(strict_types=1);
namespace Plinct\Tool;

use Exception;

class StructuredData
{
  /**
   * @var string
   */
  private static string $HOST;

  /**
   *
   */
  public function __construct() {
    self::$HOST = (filter_input(INPUT_SERVER, "REQUEST_SCHEME") ?? filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_PROTO"))."://".filter_input(INPUT_SERVER, "HTTP_HOST");
  }

/**
 * @param array $value
 * @return string
 * @throws Exception
 */
  public function getJson(array $value): string {
    return json_encode(self::getArrayLd($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

/**
 * @param array $valueProperty
 * @param string|null $property
 * @return array|null
 * @throws Exception
 */
  private function getArrayLd(array $valueProperty, string $property = null): ?array
  {
    $newData = null;
    $data = $property ? $valueProperty[$property] : $valueProperty;

    if ($data) {
      if (array_key_exists('@type', $data)) {
        $type = $data['@type'];
        $idName = "id" . lcfirst($type);
        unset($data[$idName]);
        unset($data['identifier']);
        return $this->organizeArray($type, $data);
      } else {
        foreach ($data as $valueItem) {
          $newData[] = is_array($valueItem) ? self::getArrayLd($valueItem) : $valueProperty;
        }
        return $newData;
      }
    }
    return null;
  }

/**
 * @param $type
 * @param $value
 * @return array
 * @throws Exception
 */
  private function organizeArray($type,$value): array
  {
    switch ($type) {
      case "Article":
        $value['dateModified'] = DateTime::formatISO8601($value['dateModified']);
        $value['datePublished'] = DateTime::formatISO8601($value['datePublished']);
        $value['image'] = self::$HOST . ToolBox::getRepresentativeImageOfPage($value['image']);
        unset($value['publishied']);
        unset($value['publisherType']);
        break;
      case "ContactPoint":
        unset($value['whatsapp']);
        unset($value['position']);
        unset($value['obs']);
        break;
      case "Event":
        $value['startDate'] = DateTime::formatISO8601($value['startDate']);
        $value['endDate'] = DateTime::formatISO8601($value['endDate']);
        $value['description'] = strip_tags($value['description']);
        $value['location'] = self::getArrayLd($value,'location');
        $value['image'] = ToolBox::getRepresentativeImageOfPage($value['image']);
        unset($value['create_time']);
        unset($value['address']);
        break;
      case "LocalBusiness":
        $value["@id"] = "https://plinct.com.br/schema/LocaBusiness";
        $value['description'] = strip_tags($value['description']);
        $value['address'] = $value['location']['address'] ?? $value['address'] && self::getArrayLd($value,'address');
        $value['image'] = ToolBox::getRepresentativeImageOfPage($value['image']);
        $value['telephone'] = $value['contactPoint'][0]['telephone'];
        $value['contactPoint'] = self::getArrayLd($value,'contactPoint');
        $value['location'] = self::getArrayLd($value,'location');
        unset($value['dateModified']);
        unset($value['dateCreated']);
        unset($value['organization']);
        break;
      case "ImageObject":
        $value['contentUrl'] = self::$HOST . $value['contentUrl'];
        $value['url'] = $value['contentUrl'];
        unset($value['href']);
        break;
      case "Organization":
        $value['location'] = self::getArrayLd($value,'location');
        $value['contactPoint'] = self::getArrayLd($value,'contactPoint');
        $value['image'] = self::getArrayLd($value,'image');
        unset($value['address']);
        unset($value['create_time']);
        unset($value['update_time']);
        break;
      case "Person":
        $value['contactPoint'] = self::getArrayLd($value,'contactPoint');
        $value['address'] = self::getArrayLd($value,'address');
        $value['image'] = self::getArrayLd($value,'image');
        unset($value['dateModified']);
        unset($value['dateRegistration']);
        break;
      case "Place":
        $value['address'] = self::getArrayLd($value,'address');
        unset($value['elevation']);
        unset($value['dateModified']);
        unset($value['dateCreated']);
        break;
      case "PostalAddress":
        $value['contactType'] =  "general";
        break;
    }
    return $value;
  }
}
