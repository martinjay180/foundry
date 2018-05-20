<?php

use PHPUnit\Framework\TestCase;

final class numbersTest extends TestCase
{

  public function testIsEven(){
    $this->assertTrue(numbers::isEven(2));
    $this->assertFalse(numbers::isEven(3));
    $this->assertFalse(numbers::isEven(true));
    $this->assertFalse(numbers::isEven(false));
  }

  public function testIsOdd(){
    $this->assertTrue(numbers::isOdd(3));
    $this->assertFalse(numbers::isOdd(2));
    $this->assertFalse(numbers::isOdd(true));
    $this->assertFalse(numbers::isOdd(false));
  }

  public function testPercent(){
    $this->assertEquals("25.00", numbers::percent(3,12,false));
    $this->assertEquals("25.00%", numbers::percent(3,12));
  }
}
?>
