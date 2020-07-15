<?php

namespace Plinct\Tool;

class DateTime
{
    public $year;
    public $month;
    public $day;
    public $hour;
    public $minute;
    public $second;
    public $weekday;
    public $literalMonth;
    public $literalMonthAbrev;
    public $literalWeekDay;
    public $literalWeekDayAbrev;
    
    public function __construct($date, $object = NULL) 
    {
        $datetime = new \DateTime($date, $object);
        $this->year = $datetime->format("Y");
        $this->month = $datetime->format("n");
        $this->day = $datetime->format("d");
        $this->hour = $datetime->format("h");
        $this->minute = $datetime->format("i");
        $this->second = $datetime->format("s");
        $this->weekday = $datetime->format("N");
        $this->literalMonth = self::translateMonth($this->month);
        $this->literalMonthAbrev = substr(self::translateMonth($this->month),0,3);
        $this->literalWeekDay = self::translateWeekday($this->weekday);
        $this->literalWeekDayAbrev = substr(self::translateWeekday($this->weekday),0,3);
    }
    
    // formata Dia dd/mm weekday
    public static function formatDateWithWeekday($data)
    {
        $date = new \DateTime($data);
        return $date->format('d').'/'.$date->format('n').' '.self::translateWeekday($date->format('N'));
    } 
    
    // RETORNA UM PERÍODO DE datainicio A datafim
    static function formatDate($start = null, $end = null, $mode = 'NUMERAL') 
    {
        if ($start === false) {
            return "Data inválida!";
        }
        
        $inicio = new \DateTime($start);
        $fim = $end != null ? new \DateTime($end) : null;
        
        // calculando a diferença
        $interval = $fim ? $inicio->diff($fim) : null;
        $connective = $interval && $interval->format('%a') > 1 ? 'a' : 'e';
        
        // se houver fim e forem no mesmo mês
        if($end && $fim!=$inicio && date_format($inicio, 'n') == date_format($fim, 'n')):            
            return $mode == 'TEXTUAL' ? date_format($inicio, 'd').' '.$connective.' '.date_format($fim, 'd').' de '.self::translateMonth(date_format($fim, 'n')).' de '.date_format($fim, 'Y') : date_format($inicio, 'd').'  '.$connective.'  '. date_format($fim, 'd/m/Y');
          
        // se houver fim e NÃO forem no mesmo mês
        elseif($end && $fim!=$inicio && date_format($inicio, 'n') != date_format($fim, 'n')):
            return $mode == 'TEXTUAL' ? date_format($inicio,'d').' de '.self::translateMonth(date_format($inicio, 'n')).'  '.$connective.'  '.date_format($fim, 'd').' de '.self::translateMonth(date_format($fim, 'n')).' de '.date_format($fim, 'Y') : date_format($inicio,"d/m/Y").'  '.$connective.'  '.date_format($fim, "d/m/Y");
        
        // se não houver fim
        else:
            return $mode == 'TEXTUAL' ? date_format($inicio,'d').' de '.self::translateMonth(date_format($inicio, 'n')).' de '.date_format($inicio, 'Y') : date_format($inicio,"d/m/Y");
        
        endif;
    }
    
    static function formatDateTime($date){
        $data = new \DateTime($date);
        return date_format($data, "d/m/Y \à\s H\:i\:s");
    }  
    
    public function getDataUser($id,$field){
        $db = new UsersModel();
        $where = "id='{$id}'";
        $dados_user = $db->selectUser($where);
        if(empty($dados_user)){
            return "Anônimo";
        }else{
            return $dados_user[0][$field];
        }
    }
        
    // RETORNA HORAS NO FORMATO BRASILEIRO
    static function formatBrazilianTime($time){
        $hour = substr($time, 0, 2);
        $minutes = substr($time, 3, 2);
        $seconds = substr($time, 6, 2);
        if($seconds == '00'){
            return $hour.'h'.$minutes;
        }else{
            return $hour.'h'.$minutes.'min'.$seconds;
        }
    }

    // RETORNA DATA COMO TEXTO
    public static function getTextualDate($date = NULL, $weekday = NULL) {
        $data = $date ? new \DateTime($date) : new \DateTime();
        // weekday
        $week = $weekday ? self::translateWeekday(date_format($data, 'N')).', ' : NULL;
        // dia
        $day = date_format($data, 'd');
        $month = self::translateMonth(date_format($data, 'n')); 
        $year = date_format($data,'Y'); 
        // return
        return $week.$day." de ".$month." de ".$year;
    }
    
    public static function translateWeekday($weekday, $abrev = NULL){
        if($abrev){            
            switch ($weekday) { 
                case "1": return "seg"; 
                case "2": return "ter";  
                case "3": return "qua"; 
                case "4": return "qui"; 
                case "5": return "sex";  
                case "6": return "sáb"; 
                case "7": return "dom";
            }
        }else{
            switch ($weekday) { 
                case "1": return "segunda"; 
                case "2": return "terça";  
                case "3": return "quarta"; 
                case "4": return "quinta"; 
                case "5": return "sexta";  
                case "6": return "sábado"; 
                case "7": return "domingo";
            }
        }
    }
    public static function translateMonth($month){
        switch ($month) { 
            case "1": return "janeiro"; 
            case "2": return "fevereiro";  
            case "3": return "março"; 
            case "4": return "abril"; 
            case "5": return "maio";  
            case "6": return "junho"; 
            case "7": return "julho";
            case "8": return "agosto";
            case "9": return "setembro";
            case "10": return "outubro";
            case "11": return "novembro";
            case "12": return "dezembro";
        }
    }
    public static function formateCompleteDateTimeUTC($date){
        $parse = date_parse_from_format("Y-m-d H:i:s", $date);
        return date("c", mktime($parse['hour'], $parse['minute'], $parse['second'], $parse['month'], $parse['day'], $parse['year']));
    }
    
    public static function formatTime($time) 
    {
        $replace = str_replace(":", "%s", $time);
        $string = sprintf($replace, "h", "min");
        $wsec = substr($string, 0, -5);
        return $wsec;
    }
}

