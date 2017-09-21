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
    const In = 4;
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
      case QueryComparitors::In:
        //if array then join else assume it is already joined
        $val = is_array($this->val) ? join(",", $this->val) : $this->val;
        $q = "$this->key IN ($val)";
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
  public $defaultsArr = array();

  function __construct($conn, $table) {
    $this->table = $table;
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

  function ById($id){
    return $this->Where("id", $id);
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
  
  function Columns(){
      return "*";
  }

  //Build Operations
  function Build($operation = QueryOperations::Select){
    switch($operation){
      case QueryOperations::Select:
        $query = "SELECT ".$this->Columns()." FROM ".$this->table;
        break;
      case QueryOperations::Update:
        $query = "UPDATE ".$this->table;
        break;
      case QueryOperations::Insert:
        //merge the set array with the defaultsArr
        $this->setArr = array_merge($this->defaultsArr, $this->setArr);
        $query = "INSERT INTO ".$this->table;
        $query .= " (".join(', ', array_map(function($o) {
          return $o;
        }, array_keys($this->setArr)));
        $query .=") VALUES (";
        $query .= join(', ', array_map(function($o) {
          return "'".$o."'";
        }, $this->setArr));
        $query .= ")";
        $this->query = $query;
        return $query;
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
      $this->setArr[$key] = $value;
      return $this;
    }

    function Update(){
      $this->Build(QueryOperations::Update);
      return $this->Run(QueryOperations::Update)->response;
    }

    //INSERT Operations
    function Insert(){
      $this->Defaults($this->defaultsArr);
      general::pretty($this->defaultsArr);
      $this->Build(QueryOperations::Insert);
      return $this->Run(QueryOperations::Insert)->response;
    }

    function Defaults(){
      return 0;
    }

  };

  ?>
