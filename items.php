<?php

class ItemServiceActions {

    
    const ItemCollection = 2;
    const ItemsByFieldInfo = 4;
    const NoAction = 5;
    const Applications = 6;
    const Templates = 7;
    const TemplateFields = 8;
    const UpdateItem = 9;
    const InsertItem = 10;
    const UpdateJson = 11;
    const Publish = 12;
    const Archive = 13;
    const UploadMedia = 14;
    const UpdateMissingFields = 16;
    const PublishAll = 17;
}

class ItemBase {

    public $conn;
    public $redis;
    public $applicationId;
    public $headers;
    public $mem;
    public $params;

    function __construct($conn) {
        $this->conn = $conn;
        $this->mem = new Memcached();
        $this->params = $_REQUEST["params"];
        $this->paramArr = explode("/", $this->params);
        if (function_exists("apache_request_headers")) {
            $this->headers = apache_request_headers();
            $this->applicationId = $this->headers["Authorization"];
        }
        if($this->applicationId){
            $this->mem->addServer($this->applicationId, 11211);
        } else {
            $this->mem->addServer("127.0.0.1", 11211);
        }
    }
    
    function prepareJson($data){
        return json_encode($this->unpackJson($data));
    }
    
    function unpackJson($data){
        return unserialize(base64_decode($data));
    }

    function getSimpleItemDictionary($id) {
        $query = "select i.name as name, i.application as application, i.template_id as template_id, i.description as description, i.value as value, i.date_value as date_value, i.date_created as date_created, itf.name as col, itf.field_type_id as field_type, id.value as data from items i ";
        $query .= "left join item_data id  on i.id = id.item_id ";
        $query .= "left join item_templates it on i.template_id = it.id ";
        $query .= "left join item_template_fields itf on id.item_field_id = itf.id ";
        $query .= "where i.id = " . $id;
        $sql = new sqlQuery($this->conn, $query);
        //return $query;
        $data = $sql->rows;
        $dict = array();
        $dict = general::array_push_assoc($dict, 'Id', intval($id));
        $dict = general::array_push_assoc($dict, 'Name', $data[0]['name']);
        //$dict = general::array_push_assoc($dict, 'Description', sqlQuery::escape($data[0]['description']));        
        $dict = general::array_push_assoc($dict, 'Description', $data[0]['description']);
        $dict = general::array_push_assoc($dict, 'Value', $data[0]['value']);
        $dict = general::array_push_assoc($dict, 'DateValue', $data[0]['date_value']);
        $dict = general::array_push_assoc($dict, 'Application', $data[0]['application']);
        $dict = general::array_push_assoc($dict, 'ItemTemplate', $data[0]['template_id']);
        $dict = general::array_push_assoc($dict, 'Description', $data[0]['description']);
        $dict = general::array_push_assoc($dict, 'CreatedDate', $data[0]['date_created']);
        foreach ($data as $datum) {
            switch($datum["field_type"]){
                case 1:
                    $val = boolval($datum['data']);
                    break;
                default:
                    $val = $datum['data'];
                    break;
            }
            $dict = general::array_push_assoc($dict, str_replace(' ', '_', $datum['col']), $val);
        }
        return $dict;
    }
    
    function getItemJsonById($id){
        //error_log("GETITEMJSONBYID ::: " .$id);
        $cache = $this->mem->get("ITEMBYID:".$id);
        if($cache){
            error_log("GETTING ITEM FROM CACHE :::" . $id);
            return $cache;
        } else {
        $query = "SELECT json FROM items WHERE id = $id";
        $sql = new sqlQuery($this->conn, $query);
        //return $sql;
        $json = $this->unpackJson($sql->rows[0]["json"]);
        $this->mem->set("ITEMBYID:".$id, $json);
        return $json;
        }
    }

    function saveJson($id) {
        $json = $this->getSimpleItemDictionary($id);
        $this->mem->set("ITEMBYID:".$id, $this->unpackJson($json));
        //return $json;
//        $json = htmlspecialchars(json_encode($json), ENT_QUOTES, 'UTF-8');
        $json = base64_encode(serialize($json));
        $query = "UPDATE items SET json = '$json' WHERE id = " . $id;
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        return $sql;
    }

    function updateJson($output = false) {
        if ($this->applicationId == null) {
            $this->applicationId = $argv[1];
        }
        $sql = new sqlQuery($this->conn, "SELECT id FROM items WHERE active = 1 AND application = '" . $this->applicationId . "' ORDER BY id");
        $output = array();
        foreach ($sql->rows as $row) {
            array_push($output, $this->saveJson($row['id']));
        }
        return json_encode($output);
    }

}

class ItemService extends ItemBase {

    public $conn;
    public $action;
    public $paramArr = array();
    public $routes = array();
    public $headers = array();
    
    function addRoute($route, $function){
        $this->routes[$route] = $function;
    }
    
    function addHeader($header){
        array_push($this->headers, $header);
    }
    
    function outputHeaders(){
        foreach($this->headers as $header){
            header($header);
        }
    }
    
    function mapRoute(){
        $keys = array_keys($this->routes);
        $params = $_REQUEST["params"];
        $matchFound = false;
        foreach($keys as $pattern){
            $matches = preg_grep('#^'.$pattern.'$#', array($params));
            if(sizeof($matches) > 0){
                $route = $this->routes[$pattern];
                $this->outputHeaders();
                $matchFound = true;
                call_user_func($this->routes[$pattern], $this);
                break;
            }
        }
        if(!$matchFound){
            call_user_func($this->routes["notFound"], $this);
        }
    }

}