<?php

class QueryOperations {
  const Select = 0;
  const Update = 1;
  const Insert = 2;
  const Delete = 3;
}

class QueryComparitors {
    const Equals = 0;
    const StartsWith = 1;
    const Contains = 2;
    const EndsWith = 3;
};

class QueryWhere {
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
      case QueryComparitors::Equals:
        $q = "$this->key = '$this->val'";
        break;
      case QueryComparitors::Contains:
        $q = "$this->key LIKE '%$this->val%'";
        break;
      case QueryComparitors::StartsWith:
        $q = "$this->key LIKE '$this->val%'";
        break;
      case QueryComparitors::EndsWith:
        $q = "$this->key LIKE '%$this->val'";
        break;
    }
    return $q;
  }
};

class BaseQuery {

  public $conn;
  private $query;
  private $debug;
  private $table;
  private $application;
  public $whereArr = array();
  public $limitArr = array();
  public $orderArr = array();
  public $setArr = array();

  function __construct($conn, $table, $application = null) {
    $this->table = $table;
    $this->application = $application;
    $this->conn = $conn;
    $this->debug = $debug;
  }

  function Debug($debug){
    $this->debug = $debug;
  }

  // Shortcuts
  function Active($active = true){
    return $this->Where("active", $active ? 1 : 0);
  }
  function ByType($typeId){
    return $this->Where("template_id", $typeId);
  }

  function ById($id){
    return $this->Where("id", $id);
  }

  function ByApplication($applicationId){
    return $this->Where("application", $applicationId);
  }

  // Query Operations
  function Where($key, $val, $comp = QueryComparitors::Equals){
    array_push($this->whereArr, new QueryWhere($key, $val, $comp));
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
    return $this->Run()->rows[0];
  }

  function Take($limit, $offset = 0){
    $this->Limit($limit, $offset);
    $this->Build();
    return $this->Run()->rows;
  }

  function All(){
    $this->Build();
    return $this->Run()->rows;
  }

  //Build Operations
  function Build($operation = QueryOperations::Select){
    if($this->application != null){
      $this->ByApplication($this->application);
    }
    switch($operation){
      case QueryOperations::Select:
        $query = "SELECT * FROM ".$this->table;
        break;
      case QueryOperations::Update:
        $query = "UPDATE ".$this->table;
        break;
    }
    $query .= $this->BuildSet();
    $query .= $this->BuildWhere();
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

    function BuildSet(){
      if(sizeof($this->setArr)){
        $query .= " SET ";
          $query .= join(' , ', array_map(function($s) {
            return $s[0]." = '".$s[1]."'";
          }, $this->setArr));
      }
      return $query;
    }

    function BuildWhere(){
      if(sizeof($this->whereArr)){
        $query .= " WHERE ";
          $query .= join(' AND ', array_map(function($w) {
            return $w->Build();
          }, $this->whereArr));
      }
      return $query;
    }

    function Run($queryType = QueryOperations::Select){
      $sql = new sqlQuery($this->conn, $this->query, $queryType);
      if($this->debug){
        general::pretty($sql);
      }
      $this->Clear();
      return $sql;
    }

    function Clear(){
      $this->whereArr = array();
      $this->setArr = array();
      $this->orderArr = array();
      $this->limitArr = array();
    }

    //UPDATE Operations
    function Set($key, $value){
      array_push($this->setArr, [$key, $value]);
      return $this;
    }

    function Update(){
      $this->Build(QueryOperations::Update);
      return $this->Run(QueryOperations::Update)->response;
    }

  };

  ?>
