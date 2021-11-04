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
        parent::includeProperty('latitude');
        parent::includeProperty('longitude');

        $this->newData['address'] = (new PostalAddress($this->data['address']))->parse();

        return $this->newData;
    }
}