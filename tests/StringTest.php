<?php

use PHPUnit\Framework\TestCase;

final class StringTest extends TestCase
{

  //
  //String::Format
  //
  public function testFormatIndexData(){
    $o = Strings::Format("This is a {1} {0}", array(2, "test"));
    $this->assertEquals('This is a test 2', $o);
  }

  public function testFormatIndexDataNoKey(){
    $o = Strings::Format("Testing {} {}", array("test", 2));
    $this->assertEquals('Testing test 2', $o);
  }

  public function testFormatAssociativeData(){
    $o = Strings::Format("The value of {label} is {val}", array("val" => 2, "label" => "test"));
    $this->assertEquals('The value of test is 2', $o);
  }

  //
  //String::Truncate
  //
  public function testTruncate(){
    $this->assertEquals(
          'truncate...',
          Strings::Truncate("truncate test", 8)
      );
  }

  public function testTruncateNoEllipse(){
    $this->assertEquals(
          'truncate',
          Strings::Truncate("truncate test", 8, false)

      );
  }
}
?>
