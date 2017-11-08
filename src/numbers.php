<?php

class numbers {

    function isEven($num){
        if(is_bool($num)){
          return false;
        }
        if($num % 2 === 0){
            return true;
        } else {
            return false;
        }
    }

    function isOdd($num){
        return is_bool($num) ? false : !numbers::isEven($num);
    }

    /**
     *
     * Calculates the percentage of an amount and a total.
     *
     * @param    object  $object The object to convert
     * @return      array
     *
     */
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
