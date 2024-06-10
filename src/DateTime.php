<?php
namespace Plinct\Tool;

use Exception;

class DateTime {
    public string $year;
    public string $month;
    public string $day;
    public string $hour;
    public string $minute;
    public string $second;
    public string $weekday;
    public ?string $literalMonth;
    public $literalMonthAbrev;
    public ?string $literalWeekDay;
    public $literalWeekDayAbrev;

    /**
     * DateTime constructor.
     * @param $date
     * @param null $object
     */
    public function __construct($date, $object = NULL) {
        try {
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
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $dateTime
     * @return string
     */
    public static function formatISO8601(string $dateTime): string {
        try {
            $dt = new \DateTime($dateTime);
            return $dt->format('Y-m-d\TH:i:s') ."-03:00";
        } catch (Exception $e) {
            return $dateTime;
        }
    }

    /**
     * RETORNA UM PERÍODO DE datainicio A datafim
     * @param null $start
     * @param null $end
     * @param string $mode
     * @return false|string
     */
    static function formatDate($start = null, $end = null, string $mode = 'NUMERAL') {
        if ($start === false) {
            return "Data inválida!";
        }
        try {
            $inicio = new \DateTime($start);
            $fim = $end != null ? new \DateTime($end) : null;
        } catch (Exception $e) {
            return false;
        }
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

    /**
     * @param $date
     * @return false|string
     */
    static function formatDateTime($date) {
        try {
            $data = new \DateTime($date);
        } catch (Exception $e) {
            return false;
        }
        return date_format($data, "d/m/Y \à\s H\:i\:s");
    }

    /**
     * @param $date
     * @return false|string
     */
    public static function formateCompleteDateTimeUTC($date) {
        $parse = date_parse_from_format("Y-m-d H:i:s", $date);
        return date("c", mktime($parse['hour'], $parse['minute'], $parse['second'], $parse['month'], $parse['day'], $parse['year']));
    }

    /**
     * @param $time
     * @return false|string
     */
    public static function formatTime($time)  {
        $replace = str_replace(":", "%s", $time);
        $string = sprintf($replace, "h", "min");
        return substr($string, 0, -5);
    }

    /**
     * RETORNA DATA COMO TEXTO
     * @param null $date
     * @param null $weekday
     * @return string
     */
    public static function getTextualDate($date = NULL, $weekday = NULL): string {
        try {
            $data = $date ? new \DateTime($date) : new \DateTime();
        } catch (Exception $e) {
            return false;
        }
        // weekday
        $week = $weekday ? self::translateWeekday(date_format($data, 'N')).', ' : NULL;
        // dia
        $day = date_format($data, 'd');
        $month = self::translateMonth(date_format($data, 'n')); 
        $year = date_format($data,'Y'); 
        // return
        return $week.$day." de ".$month." de ".$year;
    }

    /**
     * @param $weekday
     * @param null $abrev
     * @return string|null
     */
    public static function translateWeekday($weekday, $abrev = NULL): ?string {
			return ToolBox::dateTime()->translateWeekday($weekday, $abrev);
    }

    /**
     * @param $month
     * @return string|null
     */
    public static function translateMonth($month): ?string {
			return ToolBox::dateTime()->translateMonth($month);
    }

    /**
     * @throws Exception
     */
    public static function timeDiff($timeStart, $timeEnd, $unity = 'hour') {
        $start = strtotime($timeStart);
        $end = strtotime($timeEnd);
        $secs = $end - $start;
        $mins = $secs / 60;
        $hours = $mins / 60;
        return $unity == 'sec' ? $secs : ($unity == 'min' ? $mins : $hours);
    }
}

