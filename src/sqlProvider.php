<?php

class sqlProvider {

  var $alias;
  var $cols = array();
  var $conn;
  var $joins = array();
  var $limit;
  var $offset;
  var $query;
  var $queryType;
  var $table;
  var $where = array();

  function __construct($conn, $table){
    $this->conn = $conn;
    $this->table = $table;
  }

  function Select(){
    $this->queryType = QueryOperations::Select;
    return $this;
  }

  function Col($col){
    $this->cols[] = $col;
    return $this;
  }

  function All(&$returnQueryTo){
    return $this->Results($returnQueryTo);
  }

  function Limit($limit){
    $this->limit = $limit;
    return $this;
  }

  function Offset($offset){
    $this->offset = $offset;
    return $this;
  }

  function Results(&$returnQueryTo = null){
    $this->GenerateQuery();
    $returnQueryTo = $this->query;
    $sql = new sqlQuery($this->conn, $this->query);
    return $sql->rows;
  }

  function GenerateQuery(){
    switch($this->queryType){
      case QueryOperations::Select:
        $this->query = $this->GenerateSelectQuery();
        break;
    }
  }

  function GenerateSelectQuery(){
    return Strings::Format("select {cols} from {table}{join}{where}{limit}", array(
      "table"=>$this->GetTableName(),
      "cols"=>$this->GetCols(),
      "limit"=>$this->GetLimit(),
      "where"=>$this->GetWhere(),
      "join"=>$this->GetJoin()
    ));
  }

  function GetTableName(){
    return trim("$this->table $this->alias");
  }

  function GetCols(){
    if($this->cols){
      return join(",",$this->cols);
    } else {
      return "*";
    }
  }

  function GetLimit(){
    $s = $this->limit ? " LIMIT ".$this->limit : "";
    $s = $this->offset ? $s." OFFSET ".$this->offset : $s;
    return $s;
  }

  function GetWhere(){
    if(sizeof($this->where)){
      $query .= " WHERE ";
        $query .= join(' AND ', array_map(function($w) {
          return $w->Build();
        }, $this->where));
    }
    return $query;
  }

  function GetJoin(){
    if(sizeof($this->joins)){
        $query .= join(' ', array_map(function($w) {
          return $w->Build();
        }, $this->joins));
    }
    return $query;
  }

  function Join($table, $alias, $key1, $key2){
    $this->joins[] = new QueryJoin($table, $alias, $key1, $key2);
    return $this;
  }

  function SetAlias($alias){
    $this->alias = $alias;
  }

  function Where($col, $val, $comp = QueryComparitors::Equals){
    $this->where[] = new QueryWhere($col, $val, $comp);
    return $this;
  }
}
