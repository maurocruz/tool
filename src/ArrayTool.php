<?php
namespace Plinct\Tool;

class ArrayTool {

    public static function searchByValue(array $array, string $valueName, string $propertyName = null) {
        if ($array) {
            foreach ($array as $value) {
                if (array_search($valueName, $value)) {
                    return $value;
                }
            }
        }
        return null;
    }
}