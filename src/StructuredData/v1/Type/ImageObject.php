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

        if (isset($this->newData['@type'])) {
            $this->newData['contentUrl'] = str_replace(" ", "%20", $this->newData['url']);
            $this->newData['url'] = str_replace(" ", "%20", $this->newData['url']);
        } else {
            foreach ($this->newData as $key => $value) {
                $this->newData[$key]['contentUrl'] = str_replace(" ","%20",$value['url']);
                $this->newData[$key]['url'] = str_replace(" ","%20",$value['url']);
            }
        }

        return $this->newData;
    }
}