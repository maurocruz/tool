<?php
namespace Plinct\Tool;

use Exception;
use Plinct\Tool\DateTime\DateTimeInterface;
use Plinct\Tool\Image\Image;
use Plinct\Tool\Logger\Logger;
use Plinct\Tool\StructuredData\v1\StructuredData;
use Plinct\Tool\Curl\v1\Curl;

class ToolBox
{
	/**
	 * @param string|null $datetime
	 * @return DateTimeInterface
	 */
	public static function dateTime(string $datetime = null): DateTimeInterface
	{
		return new \Plinct\Tool\DateTime\DateTime($datetime);
	}

	/**
	 * @param ?string $channel
	 * @param ?string $filename
	 * @return Logger
	 */
	public static function Logger(?string $channel, ?string $filename): Logger
	{
		return new Logger($channel, $filename);
	}

	/**
	 * @param string $sitename
	 * @param string $representativeImage
	 * @param string $url
	 * @return OpenGraph
	 */
	public static function OpenGraph(string $sitename, string $representativeImage, string $url): OpenGraph
	{
		return new OpenGraph($sitename, $representativeImage, $url);
	}

	/**
	 * @param $data
	 * @return StructuredData
	 * @throws Exception
	 */
  public static function StructuredData($data): StructuredData
  {
    return new StructuredData($data);
  }

	/**
	 * @param array $value
	 * @return TypeBuilder
	 */
	public static function typeBuilder(array $value): TypeBuilder
	{
		return new TypeBuilder($value);
	}
	/**
	 * @param string $url
	 * @return Curl
	 */
  public static function Curl(string $url = ''): Curl
  {
    return new Curl($url);
  }

	/**
	 * @param $data
	 * @param string $mode
	 * @return null|array|string
	 * @throws Exception
	 */
  public static function getRepresentativeImageOfPage($data, string $mode = "string")
  {
		$returnImage = null;

    if ($data) {
			// verifica se entre as imagens existe alguma selecionada como representativa
			foreach ($data as $valueImage) {
        if (isset($valueImage['representativeOfPage']) && $valueImage['representativeOfPage'] == '1') {
	        $returnImage = $mode == "string" ? $valueImage['contentUrl'] : $valueImage;
        }
      }
			// se não houver imagem marcada como representativa, escolhe a primeira
      if(!$returnImage) {
				$returnImage = $mode == "string" ? $data[0]['contentUrl'] : $data[0];
      }

			if (is_string($returnImage)) {
				// Verifica se o que foi escolhido é uma imagem válida
				$image = new Image($returnImage);
				if ($image->isValidImage()) {
					return $returnImage;
				}
			} else {
				return $returnImage;
			}
    }

	  return null;
  }

/**
 * @param string|null $source
 * @return Image
 */
  public static function image(string $source = null): Image
  {
    return new Image($source);
  }

/**
 * @param array $array
 * @param string $valueName
 * @param string|null $propertyName
 * @return array|false|mixed
 */
  public static function searchByValue(array $array, string $valueName, string $propertyName = null)
  {
    return ArrayTool::searchByValue($array, $valueName, $propertyName);
  }
}
