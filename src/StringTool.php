<?php

namespace Plinct\Tool;

class StringTool 
{            
    /**
     * REMOVE ACENTOS E ESPAÇOS EM TEXTO
     * @param string $string
     * @return string
     */
    public static function removeAccentsAndSpaces($string)
    {
        $de = array('Á','Í','Ó','Ú','É','Ä','Ï','Ö','Ü','Ë','À','Ì','Ò','Ù','È','Ã','Õ','Â','Î','Ô','Û','Ê','á','í','ó','ú','é','ä','ï','ö','ü','ë','à','ì','ò','ù','è','ã','õ','â','î','ô','û','ê','Ç','ç',' ');
        
        $para = array('A','I','O','U','E','A','I','O','U','E','A','I','O','U','E','A','O','A','I','O','U','E','a','i','o','u','e','a','i','o','u','e','a','i','o','u','e','a','o','a','i','o','u','e','C','c','');
        
        return preg_replace("/[^\.\/a-zA-Z0-9_-]/", "", str_replace($de,$para,$string));
    }
}
