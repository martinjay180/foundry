<?php

// Generated by 'vendor/bin/php-generate-autoload' 'src/autoload.php'

\spl_autoload_register(function ($class) {
  static $map = array (
  'BaseQuery' => 'itemQuery.php',
  'Crypt' => 'encrypt.php',
  'ItemBase' => 'items.php',
  'ItemService' => 'items.php',
  'ItemServiceActions' => 'items.php',
  'JWT' => 'jwt.php',
  'Parsedown' => 'Parsedown.php',
  'QueryComparitors' => 'itemQuery.php',
  'QueryOperations' => 'itemQuery.php',
  'QueryWhere' => 'itemQuery.php',
  'SecondsIn' => 'datetime.php',
  'Strings' => 'String.php',
  'bootstrap' => 'bootstrap.php',
  'buynan' => 'buynan.php',
  'cache' => 'cache.php',
  'carousel_item' => 'bootstrap.php',
  'entities' => 'entities.php',
  'entityColFormats' => 'entities.php',
  'entityColSettings' => 'entities.php',
  'entityColTypes' => 'entities.php',
  'entityNavigation' => 'entities.php',
  'entityOperations' => 'entities.php',
  'entityRouter' => 'entities.php',
  'entityRouterModes' => 'entities.php',
  'entityTableSettings' => 'entities.php',
  'filesystem' => 'filesystem.php',
  'general' => 'general.php',
  'htmlAttr' => 'html.php',
  'htmlElement' => 'html.php',
  'media_item' => 'bootstrap.php',
  'navNode' => 'navigation.php',
  'navigation' => 'navigation.php',
  'numbers' => 'numbers.php',
  'regex' => 'regex.php',
  'rssfeed' => 'xmlsitemap.php',
  'sitemap' => 'sitemap.php',
  'sqlConnection' => 'sql.php',
  'sqlQuery' => 'sql.php',
  'sqlQueryBuilder' => 'sql.php',
  'sqlQueryTypes' => 'sql.php',
  'sqlQueryWhereComparators' => 'sql.php',
  'stringBuilder' => 'String.php',
  'table' => 'table.php',
  'ts' => 'datetime.php',
  'xmlsitemap' => 'xmlsitemap.php',
);

  if (isset($map[$class])) {
    require_once __DIR__ . '/' . $map[$class];
  }
}, true, false);

