<?php

namespace Foundry;


trait LazySingleton {

  static $inst;

  public static function Instance(){
      if (!isset(self::$inst)) {
            self::$inst = new self();
            self::$inst->Setup();
        }
        return self::$inst;
  }

  public function Setup(){
    echo "Running Setup from BaseClass";
    echo __LINE__;
  }

  private function __construct() {
  }

}

?>
