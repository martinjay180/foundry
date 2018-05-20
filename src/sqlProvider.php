<?php

class sqlProvider
{
    public $alias;
    public $cols = array();
    public $conn;
    public $joins = array();
    public $limit;
    public $offset;
    public $query;
    public $queryType;
    public $table;
    public $where = array();

    public function __construct($conn, $table)
    {
        $this->conn = $conn;
        $this->table = $table;
    }

    public function CleanUp()
    {
        $this->cols = array();
        $this->joins = array();
        $this->where = array();
        $this->alias = null;
        $this->limit = null;
        $this->offset = null;
        $this->tempTable = null;
    }

    public function Select()
    {
        $this->CleanUp();
        $this->queryType = QueryOperations::Select;
        return $this;
    }

    public function Update($object = false)
    {
        $this->CleanUp();
        $this->queryType = QueryOperations::Update;
        return $this;
    }

    public function Col($col)
    {
        $this->cols[] = $col;
        return $this;
    }

    public function Set($key, $val)
    {
        $this->cols[$key] = sqlQuery::escape($val);
        return $this;
    }

    public function All(&$returnQueryTo)
    {
        return $this->Results($returnQueryTo);
    }

    public function Limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function Offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function Results(&$returnQueryTo = null)
    {
        $this->GenerateQuery();
        $returnQueryTo = $this->query;
        $sql = new sqlQuery($this->conn, $this->query);
        return $sql->rows;
    }

    public function Save(&$returnQueryTo = null)
    {
        $this->GenerateQuery();
        $returnQueryTo = $this->query;
        error_log($this->query);
        $sql = new sqlQuery($this->conn, $this->query, sqlQueryTypes::sqlQueryTypeUPDATE);
        return $sql;
    }

    public function GenerateQuery()
    {
        switch ($this->queryType) {
      case QueryOperations::Select:
        $this->query = $this->GenerateSelectQuery();
        break;
      case QueryOperations::Update:
        $this->query = $this->GenerateUpdateQuery();
        break;
    }
        //the query has been generated, let's clean up.
        $this->CleanUp();
    }

    public function GenerateSelectQuery()
    {
        return Strings::Format("select {cols} from {table}{join}{where}{limit}", array(
      "table"=>$this->GetTableName(),
      "cols"=>$this->GetCols(),
      "limit"=>$this->GetLimit(),
      "where"=>$this->GetWhere(),
      "join"=>$this->GetJoin()
    ));
    }

    public function GenerateUpdateQuery()
    {
        return Strings::Format("update {table} {set} {where}", array(
      "table"=>$this->GetTableName(),
      "set"=>$this->GetSet(),
      "where"=>$this->GetWhere()
    ));
    }

    public function GetTableName()
    {
        $tablename = $this->tempTable ? $this->tempTable : $this->table;
        return trim("$tablename $this->alias");
    }

    public function GetCols()
    {
        if ($this->cols) {
            return join(",", $this->cols);
        } else {
            return "*";
        }
    }

    public function GetSet()
    {
        $set = sizeof($this->cols > 0) ? "set " : "";
        return $set.implode(', ', array_map(function ($value, $key) {
            return $key." = '".$value."'";
        }, array_values($this->cols), array_keys($this->cols)));
    }

    public function GetLimit()
    {
        $s = $this->limit ? " LIMIT ".$this->limit : "";
        $s = $this->offset ? $s." OFFSET ".$this->offset : $s;
        return $s;
    }

    public function GetWhere()
    {
        if (sizeof($this->where)) {
            $query .= " WHERE ";
            $query .= join(' AND ', array_map(function ($w) {
                return $w->Build();
            }, $this->where));
        }
        return $query;
    }

    public function GetJoin()
    {
        if (sizeof($this->joins)) {
            $query .= join(' ', array_map(function ($w) {
                return $w->Build();
            }, $this->joins));
        }
        return $query;
    }

    public function Join($table, $alias, $key1, $key2)
    {
        $this->joins[] = new QueryJoin($table, $alias, $key1, $key2);
        return $this;
    }

    public function SetAlias($alias)
    {
        $this->alias = $alias;
    }

    public function Where($col, $val, $comp = QueryComparitors::Equals)
    {
        $this->where[] = new QueryWhere($col, $val, $comp);
        return $this;
    }
}
