<?php

namespace Plinct\Tool;
/*
 * 
 */
class StringTool 
{    
   /* public static function attributesOrderInArray(array $attributes)
    {
        $return = array();
        foreach ($attributes as $value) {
            $return[$value['name']]= $value['value'];
        }
        return $return;
    }
    
    // ORGANIZA ARRAY POST
    public static function arrayPostForForm()
    {
        $POST = filter_input_array(INPUT_POST);
        unset($POST['submit']);
        unset($POST['submit_x']);
        unset($POST['submit_y']);
        unset($POST['MAX_FILE_SIZE']);
        return $POST;
    }
    
    // include path
    public static function includePath($path)
    {
        if(strpos($path, get_include_path()) === false){
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        }
    }*/
        
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
    
    // return queries strings by url 
   /* static public function queryString($value = null) 
    {
        $queries = filter_input_array(INPUT_GET);        
        if ($queries) {
            foreach ($queries as $key => $valueQueries) {
                $array[] = "$key=$valueQueries";
            }
        }
        return  $value && $queries ? (array_key_exists($value, $queries) ? $queries[$value] : null) : (isset($array) ? implode("&", $array) : null);
    }*/
}

