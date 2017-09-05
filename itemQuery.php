<?php

class ItemQueryComparitors {
    const Equals = 0;
    const StartsWith = 1;
    const Contains = 2;
    const EndsWith = 3;
};

class ItemQueryWhere {
  private $key;
  private $val;
  private $comp;

  function __construct($key, $val, $comp){
    $this->key = $key;
    $this->val = $val;
    $this->comp = $comp;
  }

  function Build(){
    switch($this->comp){
      case ItemQueryComparitors::Equals:
        $q = "$this->key = '$this->val'";
        break;
      case ItemQueryComparitors::Contains:
        $q = "$this->key LIKE '%$this->val%'";
        break;
      case ItemQueryComparitors::StartsWith:
        $q = "$this->key LIKE '$this->val%'";
        break;
      case ItemQueryComparitors::EndsWith:
        $q = "$this->key LIKE '%$this->val'";
        break;
    }
    return $q;
  }
};

class ItemQuery {

  public $conn;
  private $query;
  private $debug;
  private $application;
  public $whereArr = array();
  public $limitArr = array();
  public $orderArr = array();

  function __construct($conn, $application = null) {
    $this->application = $application;
    $this->conn = $conn;
    $this->debug = $debug;
  }

  // Shortcuts
  function Active($active = true){
    return $this->Where("active", $active ? 1 : 0);
  }
  function ByType($typeId){
    return $this->Where("template_id", $typeId);
  }

  function ByApplication($applicationId){
    return $this->Where("application", $applicationId);
  }

  // Query Operations
  function Where($key, $val, $comp = ItemQueryComparitors::Equals){
    array_push($this->whereArr, new ItemQueryWhere($key, $val, $comp));
    return $this;
  }

  function OrderBy($key, $asc = true){
    array_push($this->orderArr, array($key, $asc));
    return $this;
  }

  // Take Operations
  function Limit($num, $offset = 0){
    $this->limitArr = [$num, $offset];
  }

  function First(){
    $this->Limit(1, 0);
    $this->Build();
    return $this->Run()[0];
  }

  function Take($limit, $offset = 0){
    $this->Limit($limit, $offset);
    $this->Build();
    return $this->Run();
  }

  function All(){
    $this->Build();
    return $this->Run();
  }

  //Build Operations
  function Build(){
    if($this->applicaiton != null){
      $this->ByApplication($this->applicaiton);
    }
    $query = "SELECT * FROM items";
    if(sizeof($this->whereArr)){
      $query .= " WHERE ";
        $query .= join(' AND ', array_map(function($w) {
          return $w->Build();
        }, $this->whereArr));
    }
    if(sizeof($this->orderArr)){
      $query .= " ORDER BY ";
      $query .= join(', ', array_map(function($o) {
        return $o[0]." ".($o[1] ? "ASC" : "DESC");
      }, $this->orderArr));
    }
    if(!empty($this->limitArr)){
      $query .= " LIMIT ".$this->limitArr[0]." OFFSET ".$this->limitArr[1]." ";
    }

      $this->query = $query;
      if($this->debug){
        general::pretty($this->query);
      }
    }

    function Run(){
      $sql = new sqlQuery($this->conn, $this->query);
      if($this->debug){
        general::pretty($sql);
      }
      return $sql->rows;
    }

  };

  ?>
