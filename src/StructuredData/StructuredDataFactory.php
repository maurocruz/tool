<?php

namespace Plinct\Tool\StructuredData;

class StructuredDataFactory {

    public static function getJsonLd(array $value): string
    {
        $structuredData = new \Plinct\Tool\StructuredData();

        return json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function created(string $type, $context = null ): StructuredData {
        return new StructuredData($type, $context);
    }

    public static function getImageRepresentativeOfPage($valueImages, $arrayMode = null ) {
        foreach ($valueImages as $item) {
            if ($item['representativeOfPage']) {
                return $arrayMode ? $item : $item['contentUrl'];
            } else {
                $contentUrl[] = $item['contentUrl'];
            }
        }
        return $arrayMode ? $valueImages[0] : $valueImages[0]['contentUrl'];
    }
}
