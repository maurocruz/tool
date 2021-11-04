<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class Event extends StructuredDataTypeAbstract
{
    /**
     * @return array
     */
    public function parse(): array
    {
        $startDate = substr($this->data['startDate'],11) == "00:00:00" ? substr($this->data['startDate'],0,10) : $this->data['startDate'];
        $endDate = substr($this->data['endDate'],11) == "00:00:00" ? substr($this->data['endDate'],0,10) : $this->data['endDate'];

        $this->newData['startDate'] = $startDate;
        $this->newData['endDate'] = $endDate;

        if (isset($this->data['location'])) $this->newData['location'] = (new Place($this->data['location']))->parse();

        if (isset($this->data['image'])) $this->newData['image'] = (new ImageObject($this->data['image']))->parse();

        return $this->newData;
    }
}