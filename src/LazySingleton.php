<?php

namespace Foundry;


class LazySingleton {

  public static function Instance(){
      static $inst = null;
      if ($inst === null) {
          $inst = new static();
          $inst->Setup();
      }
      return $inst;
  }

  public function Setup(){
    echo "Running Setup from BaseClass";
  }

  private function __construct() {
  }

}

?>
