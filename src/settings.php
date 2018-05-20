<?php

class Settings {

  function __construct($data){
    foreach($data as $key => $value){
      $this->$key = $value;
    }
  }
}
?>
