<?php
namespace Plinct\Tool;

use Exception;
use NumberFormatter;
use Plinct\Tool\DateTime\DateTimeInterface;
use Plinct\Tool\Image\Image;
use Plinct\Tool\Logger\Logger;
use Plinct\Tool\StructuredData\v1\StructuredData;
use Plinct\Tool\Curl\v1\Curl;

class ToolBox
{
	/**
	 * @param string $input
	 * @return string
	 */
	public static function camelCaseToSentence(string $input): string {
		// Insere espaço antes de letras maiúsculas, mas não separa siglas (duas ou mais maiúsculas seguidas)
		$spaced = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $input);
		// Tudo minúsculo, depois capitaliza a primeira letra
		return ucfirst(strtolower($spaced));
	}

	/**
	 * @param array $attributes
	 * @return string
	 */
	public function convertAttributesToString(array $attributes): string
	{
		$string = '';
		foreach ($attributes as $key => $value) {
			$string .= $key . '="' . $value . '" ';
		}
		return $string;
	}
	/**
	 * @return array
	 */
	public static function currencies(): array
	{
		$currencies = [];
		$curl = new Curl('https://restcountries.com/v3.1/all?fields=currencies');
		$data = json_decode($curl->returnWithJson()->ready(), true);
		foreach ($data as $item) {
			$currency = $item['currencies'];
			$key = array_key_first($currency);
			if ($key !== null) {
				$currencies[$key] = array_key_first($currency) .', '. $currency[$key]['name'].', '.$currency[$key]['symbol'];
			}
		}
		sort($currencies);
		return $currencies;
	}
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
	 * @param string $locale
	 * @return NumberFormatter
	 */
	public static function NumberFormatterCurrency(string $locale = 'pt_BR'): NumberFormatter
	{
		return new NumberFormatter($locale, NumberFormatter::CURRENCY);
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
	 * @param array|int|null $value
	 * @return TypeBuilder
	 */
	public static function typeBuilder(array|null|int $value): TypeBuilder
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
  public static function getRepresentativeImageOfPage($data, string $mode = "string"): array|string|null
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
  public static function searchByValue(array $array, string $valueName, string $propertyName = null): mixed
  {
    return ArrayTool::searchByValue($array, $valueName, $propertyName);
  }

	/**
	 * @param string|null $telephoneNumber
	 * @return array|string|null
	 */
	public function telephoneFormatter(?string $telephoneNumber): array|string|null
	{
		if ($telephoneNumber == null) return null;
		// Remove tudo que não é número
		$telephoneNumber = preg_replace('/\D+/', '', $telephoneNumber);
		// 9 dígitos (celular): 2 DDD + 9 + 4
		if (preg_match('/^(\d{2})(\d{5})(\d{4})$/', $telephoneNumber)) {
			return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '$1 $2-$3', $telephoneNumber);
		}
		// 8 dígitos (fixo): 2 DDD + 4 + 4
		if (preg_match('/^(\d{2})(\d{4})(\d{4})$/', $telephoneNumber)) {
			return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '$1 $2-$3', $telephoneNumber);
		}
		// Caso seja inválido
		return _('Invalid nember');
	}
}
