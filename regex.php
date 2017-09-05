<?php

class regex {
    
    static function getHtmlProp($tag, $prop){
        preg_match('/'.$prop.'=[\"\'](.*?)[\"\']/', $tag, $matches);
        return $matches[1]; 
    }
    
}
