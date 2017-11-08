<?php

use PHPUnit\Framework\TestCase;

final class StringTest extends TestCase
{

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
