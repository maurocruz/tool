<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class ImageObject extends StructuredDataTypeAbstract
{
    /**
     * @return array
     */
    public function parse(): array
    {
        parent::includeProperty('contentUrl');
        parent::includeProperty('url','contentUrl');
        parent::includeProperty('license');
        parent::includeProperty('acquireLicensePage');

        return $this->newData;
    }
}