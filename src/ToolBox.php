<?php

declare(strict_types=1);

namespace Plinct\Tool;

use Plinct\Tool\StructuredData\v1\StructuredData;

class ToolBox
{
    /**
     * @param $data
     * @return StructuredData
     */
    public static function StructuredData($data): StructuredData
    {
        return new StructuredData($data);
    }
}