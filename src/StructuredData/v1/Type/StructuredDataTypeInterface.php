<?php

namespace Plinct\Tool\StructuredData\v1\Type;

interface StructuredDataTypeInterface
{
    /**
     * @return array
     */
    public function parse(): array;
}