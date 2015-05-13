<?php

class ts {
    
    function getShortDate($ts){
        return date("m/d/Y", $ts);
    }
    
    function getFullDateTime($ts){
        date_default_timezone_set("America/New_York");
        return date("m/d/Y g:i:s A", $ts);
    }
    
    function getYearFirstDateTime($ts){
   		return date('Y-m-d', $ts);
    }
    
    function getTSForBeginingOfDay($ts){
        $beginOfDay = strtotime("midnight", time());
        return $beginOfDay;
    }
    
    function getTSForEndOfDay($ts){
        $beginOfDay = strtotime("midnight", time());
        $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;
        return $endOfDay;
    }
    
    function getTodayStartTS(){
        return ts::getTSForBeginingOfDay(time());
    }
    
    function getTodayEndTS(){
        return ts::getTSForEndOfDay(time());
    }
}

class SecondsIn {
    const MIN = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;
}

?>
