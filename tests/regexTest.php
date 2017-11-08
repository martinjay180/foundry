<?php

use PHPUnit\Framework\TestCase;

final class regexTest extends TestCase
{

  var $tag_a = "<a class='bold'>Hello</a>";
  var $invalid_tag_a = "not a tag";

  public function testgetHtmlProp(){
    $this->assertEquals('bold',regex::getHtmlProp($this->tag_a, "class"));
  }

  public function testgetHtmlPropFallback(){
    $this->assertEquals('fallback', regex::getHtmlProp($this->tag_a, "id", "fallback"));
  }

  public function testgetHtmlPropNoMatch(){
    $this->assertNull(regex::getHtmlProp($this->tag_a, "id"));
  }

  public function testgetHtmlPropInvalidTag(){
    $this->assertNull(regex::getHtmlProp($this->invalid_tag_a, "id"));
  }
}
?>
