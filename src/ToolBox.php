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


    /**
     * @param $data
     * @param string $mode
     * @return null
     */
    public static function getRepresentativeImageOfPage($data, string $mode = "string")
    {
        if ($data) {
            foreach ($data as $valueImage) {
                if (isset($valueImage['representativeOfPage']) && $valueImage['representativeOfPage'] == true) {
                    return $mode == "string" ? $valueImage['contentUrl'] : $valueImage;
                }
            }
            return $mode == "string" ? $data[0]['contentUrl'] : $data[0];
        }
        return null;
    }
}