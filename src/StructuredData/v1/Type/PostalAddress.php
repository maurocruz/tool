<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class PostalAddress extends StructuredDataTypeAbstract
{
    /**
     * @return array
     */
    public function parse(): array
    {
        parent::includeProperty('streetAddress');
        parent::includeProperty('addressLocation');
        parent::includeProperty('addressRegion');
        parent::includeProperty('addressCountry');

        return $this->newData;
    }
}