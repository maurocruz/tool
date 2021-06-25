<?php
namespace Plinct\Tool;

class ArrayTool {

    public static function searchByValue(array $array, string $valueName, string $propertyName = null) {
        if ($array) {
            // IF HAS VALUE IN ARRAY
            if (array_search($valueName,$array)) {
                return $propertyName ? $array[$propertyName] : $array;
            }
            // IF MANY ARRAYS
            foreach ($array as $value) {
                if (is_array($value) && array_search($valueName, $value)) {
                    return $propertyName ? $value[$propertyName] : $value;
                } elseif ($value == $valueName) {
                    if (in_array($propertyName,$array)) {
                        return $array[$propertyName];
                    } elseif (!$propertyName) {
                        return $array;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Sort array recursivily using key name with parameter
     * @param array $array
     * @param string $name
     * @param string $ordering
     * @return array
     */
    public static function sortByName(array $array, string $name, string $ordering = 'asc'): array {
        usort($array, function($a, $b) use ($name, $ordering) {
            $direction = strtolower(trim($ordering));
            if($direction == 'desc') {
                if ($a[$name] < $b[$name]) return 1;
            } else {
                if ($a[$name] > $b[$name]) return 1;
            }
            return true;
        });
        return $array;
    }
}