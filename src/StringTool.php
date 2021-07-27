<?php

namespace Plinct\Tool;

class StringTool 
{
    /**
     * REMOVE ACENTOS E ESPAÇOS EM TEXTO
     * @param string $string
     * @return string
     */
    public static function removeAccentsAndSpaces(string $string): string
    {
        $de = array('Á','Í','Ó','Ú','É','Ä','Ï','Ö','Ü','Ë','À','Ì','Ò','Ù','È','Ã','Õ','Â','Î','Ô','Û','Ê','á','í','ó','ú','é','ä','ï','ö','ü','ë','à','ì','ò','ù','è','ã','õ','â','î','ô','û','ê','Ç','ç',' ');
        
        $para = array('A','I','O','U','E','A','I','O','U','E','A','I','O','U','E','A','O','A','I','O','U','E','a','i','o','u','e','a','i','o','u','e','a','i','o','u','e','a','o','a','i','o','u','e','C','c','');
        
        return preg_replace("/[^.\/a-zA-Z0-9_-]/", "", str_replace($de,$para,$string));
    }

    public static function translateWordsSeparatedByDelimiter(string $delimiter, string $string = null): ?string
    {
        if($string) {
            $array = explode($delimiter, $string);
            foreach ($array as $value) {
                $translate = _(trim($value));
                $newArray[] = $translate;
            }
            return implode($delimiter . " ", $newArray);
        }
        return null;
    }

    public static function removeDuplicateQueryStrings($name = null): string {
        $url = strstr($_SERVER['REQUEST_URI'],"?",true);
        $queryString = $_SERVER['QUERY_STRING'];
        $queryStringArray = explode("&", $queryString);
        foreach ($queryStringArray as $value) {
            $queryArray = explode("=",$value);
            $newArray[$queryArray[0]] = $queryArray[1];
        }
        foreach ($newArray as $key=>$value2) {
            if ($name != $key) {
                $response[] = "$key=$value2";
            }
        }
        return $url.'?'.implode("&",$response);
    }
}
