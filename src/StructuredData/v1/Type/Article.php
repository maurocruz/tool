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
        parent::includeProperty('articleSection');
        parent::includeProperty('publisher');
        parent::includeProperty('datePublished');
        parent::includeProperty('dateModified');
        parent::includeProperty('dateCreated');
        parent::includeProperty('author');

        if (isset($this->newData['image'])) $this->newData['image'] = (new ImageObject($this->data['image']))->parse();

        return $this->newData;
    }
}
