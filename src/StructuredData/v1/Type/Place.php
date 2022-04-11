<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class Place extends StructuredDataTypeAbstract
{
  /**
   * @return array
   */
  public function parse(): array
  {
	  // GEO COORDINATES
    if ($this->data['longitude'] && $this->data['latitude']) {
			$this->newData['geo'] = [
				'@type'=>'GeoCoordinates',
				'latitude'=>(float)$this->data['latitude'],
				'longitude'=>(float)$this->data['longitude']
			];
    }
		// ADDRESS
    $this->newData['address'] = $this->data['address'] ? (new PostalAddress($this->data['address']))->parse() : null;
		// NEW DATA
    return $this->newData;
  }
}