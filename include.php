<?php

include 'sql.php';
//include 'entities.php';
include 'html.php';
include 'table.php';
include 'general.php';
//include 'navigation.php';
include 'string.php';
include 'datetime.php';
include 'filesystem.php';
include 'numbers.php';
include 'bootstrap.php';
include 'xmlsitemap.php';
include 'cache.php';
//include 'predis-1.0/autoload.php';
include 'items.php';
include 'buynan.php';
include 'encrypt.php';
include 'underscore.php';
include 'itemQuery.php';

if( !function_exists('apache_request_headers') ) {

function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}

}

?>
