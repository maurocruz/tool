<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

class Taxon extends StructuredDataTypeAbstract
{
    /**
     * @return array
     */
    public function parse(): array
    {
        $this->newData['taxonRank'] = $this->data['taxonRank'];
        $this->newData['alternateName'] = $this->data['vernacularName'];

        if (is_array($this->data['parentTaxon'])) {
            $this->newData['parentTaxon'] = (new Taxon($this->data['parentTaxon']))->parse();
        }

        if(isset($this->data['image'])) (new ImageObject($this->data['image']))->parse();

        return $this->newData;
    }
}