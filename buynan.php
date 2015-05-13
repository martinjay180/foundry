<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of buynan
 *
 * @author martin.jay
 */
class buynan {

    public $curl;
    public $application;
    public $instance;

    function __construct($application, $instance) {
        $this->curl = curl_init();
        $this->application = $application;
        $this->instance = $instance;
    }

    function info($msg, $desc, $data = null) {
        $this->log("157", $msg, $desc, $data);
    }

    function log($level, $msg, $desc, $data) {
        curl_setopt_array($this->curl, array(
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_URL => 'http://104.236.115.99/Service/Insert/15',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT_MS => 1,
            //CURLOPT_CONNECTTIMEOUT_MS => 100,
            CURLOPT_POSTFIELDS => array(
                field_id_42 => $this->instance,
                field_id_40 => print_r($data, true),
                description => $desc, 
                name => $msg,
                field_id_41 => $level,
                field_id_39 => $this->application
            )
        ));
        $resp = curl_exec($this->curl);
        //curl_close($this->curl);
    }
}
