<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

use DateTime;
use DateTimeInterface;
use Exception;

class Event extends StructuredDataTypeAbstract
{
	/**
	 * @return array
	 * @throws Exception
	 */
  public function parse(): array
  {
		// DATES
	  // start date format
	  if (substr($this->data['startDate'],11) === "00:00:00") {
		  $startDate = substr($this->data['startDate'],0,10);
	  } else {
		  $sd = new DateTime($this->data['startDate']);
			$startDate = $sd->format(DateTimeInterface::ATOM);
	  }
		// end date format
		if (substr($this->data['startDate'],11) === "00:00:00" ) {
			$endDate = substr($this->data['endDate'],0,10);
		} else {
			$ed = new DateTime($this->data['endDate']);
			$endDate = $ed->format(DateTimeInterface::ATOM);
		}

	  $this->newData['startDate'] = $startDate;
    $this->newData['endDate'] = $endDate;

		// LOCATION
    if (isset($this->data['location'])) $this->newData['location'] = (new Place($this->data['location']))->parse();
		// IMAGE
	  if (isset($this->data['image']) && !empty($this->data['image'])) {
			foreach ($this->data['image'] as $value) {
				$this->newData['image'][] = $value['contentUrl'];
			}
	  }
		// RETURN
    return $this->newData;
  }
}
