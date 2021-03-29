<?php
namespace Plinct\Tool;

class ArrayTool {

    /**
     * Search the array recursively and return the array
     * @param array $array
     * @param string $valueName
     * @return array|null
     */
    public static function searchByValue(array $array, string $valueName): ?array {
        if ($array) {
            foreach ($array as $value) {
                if ($value == $valueName) {
                    return $array;
                } elseif (is_array($value)) {
                    return self::searchByValue($value, $valueName);
                }
            }
        }
        return null;
    }
}