<?php

class regex {

    static function getHtmlProp($tag, $prop, $default = null){
        preg_match('/'.$prop.'=[\"\'](.*?)[\"\']/', $tag, $matches);
        return $matches[1] == null ? $default : $matches[1];
    }

}
