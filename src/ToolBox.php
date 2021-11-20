<?php

declare(strict_types=1);

namespace Plinct\Tool;

use Plinct\Tool\StructuredData\v1\StructuredData;
use Plinct\Tool\Curl\v1\Curl;

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

    /**
     * @return Curl
     */
    public static function Curl(): Curl
    {
        return new Curl();
    }
}