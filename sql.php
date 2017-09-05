<?php

//include_once 'general.php';

class sqlQueryTypes {
    
    const sqlQueryTypeSELECT = 0;
    const sqlQueryTypeUPDATE = 1;
    const sqlQueryTypeINSERT = 2;
    const sqlQueryTypeDELETE = 3;
    
};

class sqlQueryWhereComparators {
    const EQUALS = 0;
}

class sqlConnection {
    
    var $server;
    var $database;
    var $username;
    var $password;
    
    function __construct($server, $database, $username, $password){
        $this->server = $server;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }
}

class sqlQuery {
    
    var $query;
    var $rows = array();
    var $sqlConnection;
    var $numRows;
    var $response;
    var $error;
    var $error_list;
    var $error_code;
    //var $debug = true;
    //var $debug = false;

    function __construct($sqlConnection, $query, $type = sqlQueryTypes::sqlQueryTypeSELECT){
        $this->sqlConnection = $sqlConnection;
        $this->query = $query;
        $this->type = $type;
        switch($type){
            case sqlQueryTypes::sqlQueryTypeSELECT:
                $this->select();
                break;
            case sqlQueryTypes::sqlQueryTypeUPDATE:
                //$this->query = $this->escape($this->query);
                $this->update();
                break;
            case sqlQueryTypes::sqlQueryTypeINSERT:
                //$this->query = $this->escape($this->query);
                $this->insert();
                break;
            case sqlQueryTypes::sqlQueryTypeDELETE:
                $this->delete();
                break;
        }
        if($GLOBALS["debug"]){
            //error_log("SQL class instantiated ::: " . $query);
        }
    }

    function select(){
        $mysqli = new mysqli($this->sqlConnection->server, $this->sqlConnection->username, $this->sqlConnection->password, $this->sqlConnection->database);
        $mysqli->set_charset('utf8');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
//        echo($this->query);
        if ($result = $mysqli->query($this->query)) {
            $this->response = true;
            $i = 0;
            while($row = $result->fetch_assoc()){
                $this->rows[] = $row;
                $i++;
            }
            $this->numRows = $i;
        }
        $mysqli->close();
    }
    
    function update(){
        $mysqli = new mysqli($this->sqlConnection->server, $this->sqlConnection->username, $this->sqlConnection->password, $this->sqlConnection->database);
        $mysqli->set_charset('utf8');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        if ($result = $mysqli->query($this->query)) {
            $this->response = true;
        }
        $this->error_list = $mysqli->error_list;
        //$result->close();
        $mysqli->close();
        
    }
    
    function insert(){
        $mysqli = new mysqli($this->sqlConnection->server, $this->sqlConnection->username, $this->sqlConnection->password, $this->sqlConnection->database);
        $mysqli->set_charset('utf8');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        if ($result = $mysqli->query($this->query)) {
            $this->response = $mysqli->insert_id;
        }
        $this->error = $mysqli->error;
        $this->error_code = $mysqli->errno;
        $this->error_list = $mysqli->error_list;
        //$result->free();
        $mysqli->close();
    }
    
    function delete(){
        $mysqli = new mysqli($this->sqlConnection->server, $this->sqlConnection->username, $this->sqlConnection->password, $this->sqlConnection->database);
        $mysqli->set_charset('utf8');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        if ($result = $mysqli->query($this->query)) {
            $this->response = true;
        }
        $mysqli->close();
        //print_r("Inserted");
    }
    
    function escape($str){
        $search=array("\\","\0","\n","\r","\x1a","'",'"');
        $replace=array("\\\\","\\0","\\n","\\r","\Z","\'",'\"');
        return str_replace($search,$replace,$str);
    }
    
    function buildValueArray($data, $except){
        $arr = array();
        foreach ($data as $col => $val) {
            if($col != $except){
                array_push($arr, "$col = '$val'");
            }
        }
        return $arr;
    }
    
    function buildValueString($arr){
        return implode(",", $arr);
    }
    
    function buildValues($data, $except){
        $arr = sqlQuery::buildValueArray($data, $except);
        $str = sqlQuery::buildValueString($arr);
        return $str;
    }
    
};

class sqlQueryBuilder {
    
    var $columns = array();
    var $query_type;
    var $table;
    var $where = array();
    var $order = array();
    public $limit_start = 0;
    public $limit_length = 0;
    
    var $query;
    
    function __construct($table, $query_type = sqlQueryTypes::sqlQueryTypeSELECT){
        $this->query_type = $query_type;
        $this->table = $table;
    }
    
    function where($col, $cond, $comp = sqlQueryWhereComparators::EQUALS){
        switch($comp){
            case sqlQueryWhereComparators::EQUALS:
                $where_item = "$col = $cond";
        }
        array_push($this->where, $where_item);
    }
    
    function column($col){
        array_push($this->columns, $col);
    }
    
    function order($col){
        array_push(($this->order), $col);
    }
    
    function render(){
        $query = "SELECT ";
        //COLUMNS
        if(count($this->columns) > 0){
            $cols = implode(", ", $this->columns);
        } else {
            $cols = "*";
        }
        $query .= $cols." ";
        //TABLE
        $query .= "FROM $this->table ";
        //WHERE
        if(count($this->where) > 0){
            $query .= "WHERE ";
            $query .= implode(" AND ", $this->where);
        }
        //LIMIT
        if($this->limit_length > 0){
            if($this->limit_start > 0){
                $limit_start = "$this->limit_start,";
            } else {
                $limit_start = "";
            }
            $limit .= " LIMIT $limit_start $this->limit_length ";
            $query .= $limit;
        }
        return $query;
    }
    
    
    
}

?>
