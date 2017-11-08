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

class String {
  static function Format($pattern, $data, $options = false){
    $delimiter = array("{", "}");
    preg_match_all("/".$delimiter[0]."(.*)".$delimiter[1]."/U", $pattern, $matches, PREG_SET_ORDER);
    if (sizeof($matches) > 0) {
        foreach ($matches as $key=>$match) {
          $match_key = $match[1];
          $match_value = $match_key == "" ? $data[$key] : $data[$match_key];
          $limit = $match_key == "" ? 1 : -1;
          $pattern = preg_replace("/" .$delimiter[0] . $match_key . "\\".$delimiter[1]."/", $match_value, $pattern, $limit);
        }
    } else {
        $pattern = $data[$pattern];
    }
    return $pattern;
  }
}

class Strings {

    function RemoveCharacters($characters, $text){
        foreach ($characters as $char) {
            $pos = 0;
            while ($pos = strpos($text, $char, $pos)) {
                $positions[$char][] = $pos;
                $pos += strlen($char);
            }
        }
        return str_replace($characters, '', $text);
    }

    function Format($data, $pattern) {
        preg_match_all("/{{(.*)}}/U", $pattern, $matches, PREG_SET_ORDER);
        general::pretty($matches);
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

    /**
    * @param $value
    * @return mixed
    */
    function JsonEscape($value) { # list from www.json.org: (\b backspace, \f formfeed)
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

}

?>
