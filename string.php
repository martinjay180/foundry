<?php

class stringBuilder {

    public $disp;

    public function add($text) {
        $this->disp .= $text;
        return $this;
    }

    public function render() {
        return $this->disp;
    }

}

class string {

    function Format($data, $pattern) {
        preg_match_all("/{{(.*)}}/U", $pattern, $matches, PREG_SET_ORDER);
        //general::pretty($matches);
        if (sizeof($matches) > 0) {
            foreach ($matches as $match) {
                $pattern = preg_replace("/{{" . $match[1] . "\}}/", $data[$match[1]], $pattern);
            }
        } else {
            $pattern = $data[$pattern];
        }
        return $pattern;
    }

    function IsJson($str) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function IsNullOrEmptyString($str) {
        return (!isset($str) || trim($str) === '');
    }

    function Truncate($str, $maxLength, $useEllipse = true) {
        $new_str = substr($str, 0, $maxLength);
        if ($useEllipse && strlen($str) >= $maxLength) {
            $new_str .= "...";
        }
        return $new_str;
    }

    function Prepend($str, $text, $onlyIfNotAlreadyPresent = false) {
        if ($onlyIfNotAlreadyPresent) {
            $i = preg_match("/^$text/", $str);
            if ($i == 0) {
                return $text . $str;
            } else {
                return $str;
            }
        } else {
            return $text . $str;
        }
    }

    function Append($str, $text, $onlyIfNotAlreadyPresent = false) {
        if ($onlyIfNotAlreadyPresent) {
            $i = preg_match("/.*$text$/", $str);
            if ($i == 0) {
                return $str . $text;
            } else {
                return $str;
            }
        } else {
            return $str . $text;
        }
    }

}

?>
