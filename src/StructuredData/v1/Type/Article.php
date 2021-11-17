<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class Article extends StructuredDataTypeAbstract
{
    /**
     * @return array
     */
    public function parse(): array
    {
        parent::includeProperty('headline');
        parent::includeProperty('publisher');
        parent::includeProperty('datePublished');
        parent::includeProperty('author');

        $this->newData['image'] = (new ImageObject($this->data['image']))->parse();

        return $this->newData;
    }
}
