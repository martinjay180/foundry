<?php

namespace Foundry;

class Paging {

    var $mapping;
    var $params;
    var $keys;

    public function __construct($mapping = null){
        $this->mapping = $mapping == null ? [] : $mapping;
        $this->params = $_GET;
        $this->SetKeys();
    }

    public function SetParams($params){
        $this->params = $params;
    }

    public function SetKeys(){
        $this->keys = [
            "pageSize" => array_key_exists("pageSize", $this->mapping) ? $this->mapping["pageSize"] : "pageSize",
            "page" => array_key_exists("page", $this->mapping) ? $this->mapping["page"] : "page",
            "start" => array_key_exists("start", $this->mapping) ? $this->mapping["start"] : "start",
            "end" => array_key_exists("end", $this->mapping) ? $this->mapping["end"] : "end"
        ];
    }

    public function PageSize(){
        $key = $this->keys["pageSize"];
        if(array_key_exists($key, $this->params)){
            return $this->params[$key];
        } else {
            return $this->End() - $this->Start();
        }
    }

    public function Page(){
        $key = $this->keys["page"];
        if(array_key_exists($key, $this->params)){
            return $this->params[$key];
        } else {
            $pageSize = $this->PageSize();
            return ($this->Start() / $pageSize) + 1;
        }
    }

    public function Start(){
        $key = $this->keys["start"];
        if(array_key_exists($key, $this->params)){
            return $this->params[$key];
        } else {
            return ($this->PageSize()*$this->Page()) - $this->PageSize() + 1;
        }
    }

    public function End(){
        $key = $this->keys["end"];
        if(array_key_exists($key, $this->params)){
            return $this->params[$key];
        } else {
            return ($this->PageSize()*$this->Page());
        }
    }
}
?>
