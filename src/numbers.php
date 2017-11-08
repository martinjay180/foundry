<?php

class numbers {

    function isEven($num){
        if($num % 2 == 0){
            return true;
        } else {
            return false;
        }
    }
    
    function isOdd($num){
        return !numbers::isEven($num);
    }
    
    function percent($num_amount, $num_total, $symbol = true) {
        $p = number_format(($num_amount / $num_total)*100, 2);
        if($symbol){
            $p .= "%";   
        }
        return $p;
    }
    
    static function nonNull($num, $fallback){
        return is_numeric($num) ? $num : $fallback;
    }
}

?>
