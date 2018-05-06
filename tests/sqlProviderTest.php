<?php

use PHPUnit\Framework\TestCase;

final class sqlProviderTest extends TestCase
{

  var $conn = "";

  public function getProvider(){
    return new sqlProvider($this->conn, "test");
  }

  public function testSelectAll(){
    $provider = $this->getProvider();
    $query = "select * from test";
    $results = $provider->Select()->All($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }

  public function testSelectColumns(){
    $provider = $this->getProvider();
    $query = "select a,b,c from test";
    $results = $provider->Select()->Col("a")->Col("b")->Col("c")->All($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }

  public function testSelectLimit(){
    $provider = $this->getProvider();
    $query = "select * from test LIMIT 10 OFFSET 5";
    $results = $provider->Select()->Limit(10)->Offset(5)->Results($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }

  public function testSelectWhere(){
    $provider = $this->getProvider();
    $query = "select * from test WHERE a = '5'";
    $results = $provider->Select()->Where("a",5)->Results($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }

  public function testSelectMultipleWhere(){
    $provider = $this->getProvider();
    $query = "select * from test WHERE a = '5' AND b = 'test'";
    $results = $provider->Select()->Where("a",5)->Where("b","test")->Results($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }

  public function testSelectJoin(){
    $provider = $this->getProvider();
    $provider->setAlias("t");
    $query = "select * from test t JOIN products p ON p.id = t.product_id WHERE t.type IN (3,4,6,5)";
    $results = $provider->Select()->Join("products", "p", "p.id", "t.product_id")->Where("t.type", array(3,4,6,5), QueryComparitors::In)->Results($returnQuery);
    $this->assertEquals($query, $returnQuery);
  }
}
?>
