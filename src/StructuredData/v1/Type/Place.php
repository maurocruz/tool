<?php
namespace Plinct\Tool\StructuredData\v1\Type;

class Place extends StructuredDataTypeAbstract
{
  /**
   * @return array
   */
  public function parse(): array
  {
		$longitude = $this->data['geo']['longitude'];
		$latitude = $this->data['geo']['latitude'];
		$address = $this->data['geo']['address'];
	  // GEO COORDINATES
    if ($longitude && $latitude) {
			$this->newData['geo'] = [
				'@type' => 'GeoCoordinates',
				'latitude' => (float) $latitude,
				'longitude' => (float) $longitude
			];
    }
		// ADDRESS
    $this->newData['address'] = $address ? (new PostalAddress($address))->parse() : null;
		// NEW DATA
    return $this->newData;
  }
}