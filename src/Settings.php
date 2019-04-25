<?php

namespace Foundry;

use Symfony\Component\Yaml\Yaml;
use Foundry\LazySingleton;

class Settings {
    use LazySingleton;

    var $data;

    public function Setup($src = "settings.yaml")
    {
        $yaml = Yaml::parse(file_get_contents($src));
        $this->data = $yaml;
    }

    public static function Get($key, $default = ""){
        $inst = Settings::Instance();
        if(array_key_exists($key, $inst->data)){
            return $inst->data[$key];
        } else {
            return $default;
        }

    }
}
