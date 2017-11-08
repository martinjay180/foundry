<?php

class filesystem {
    
    function getFileExtension($filename){
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
    
    function getParentDirPath($path){
        $folders = explode("/", $path);
        array_pop($folders);
        return implode("/", $folders);
    }
}

?>
