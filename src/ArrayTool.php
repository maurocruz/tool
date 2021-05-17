<?php
namespace Plinct\Tool;

class ArrayTool {

    public static function searchByValue(array $array, string $valueName, string $propertyName = null) {
        if ($array) {
            foreach ($array as $value) {
                if ($value == $valueName) {
                    return $array;
                } elseif (is_array($value)) {
                    $data = self::searchByValue($value, $valueName);
                    if ($data && $propertyName) {
                        return $data[$propertyName] ?? null;
                    }
                    return $data;
                }
            }
        }
        return null;
    }
}