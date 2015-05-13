<?php

class cache {
    
    public $path;
    
    function __construct($path) {
        $this->path = $path;
    }
    
    function setValue($key, $value){
        $data = "define(\"".$key."\", \"".addslashes($value)."\");";
        $this->appendFile($data);
    }
    
    function appendFile($data){
        $content = file_get_contents($this->path);
        file_put_contents($this->path, $content."\n".$data."\n");
    }
    
    function clearCache(){
        file_put_contents($this->path, "<?php\n");
    }
}

?>
