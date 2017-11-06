<?php

require_once '../vendor/autoload.php';
//Predis\Autoloader::register();

use \Firebase\JWT\JWT;

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
    public $selectConn;
    public $redis;
    public $applicationId;
    public $jwt;
    public $headers;
    public $mem;
    public $params;

    function __construct($conn) {
        if (is_array($conn)) {
            $this->conn = $conn[0];
            $this->selectConn = $conn[1];
        } else {
            $this->conn = $conn;
            $this->selectConn = $conn;
        }
        //$this->redis = new Predis\Client();
        $this->mem = new Memcached();
        $this->params = $_REQUEST["params"];
        $this->paramArr = explode("/", $this->params);
        if (function_exists("apache_request_headers")) {
            $this->headers = apache_request_headers();
            $this->applicationId = $this->headers["Authorization"];
            $this->jwt = $this->headers["jwt"];
        }
        $this->mem->addServer("127.0.0.1", 11211);
    }

    function getItemById($id) {
        //check the cache first
        //$item = $this->redis->get("item:$id");
        if (!$item) {
            error_log("ITEM NOT IN CACHE ::: LOOKING UP FROM DB ::: " . $id);
            $query = "SELECT json FROM items WHERE id = $id";
            $sql = new sqlQuery($this->conn, $query);
            $json = $sql->rows[0]["json"];
            //now put it in the cache for next time
            $item = $this->prepareJson($json);
            //$this->redis->set("item:$id", $item);
        }
        return json_decode($item);
    }


    function getExpandedItemById($id) {
        //$item = $this->redis->get("expandeditem:$id");
        //if (!$item) {
            $query = "
            select
            fk.json as fk_json, i.json as item_json, id.item_field_id, itf.name
            from item_data id
            left join item_template_fields itf on id.item_field_id = itf.id
            left join items fk on itf.field_type_id = 4 and id.value = fk.id
            left join items i on id.item_id = i.id
            where id.item_id = $id and itf.field_type_id = 4
            UNION
            select
            '' as fk_json, i.json as item_json, '' as item_field_id, '' as name
            from items i where i.id = $id";
            $sql = new sqlQuery($this->conn, $query);
            $item = $this->unpackJson($sql->rows[0]["item_json"]);
            foreach ($sql->rows as $row) {
                $fk_json = $row["fk_json"];
                if($fk_json){
                    $fk_item = $this->unpackJson($fk_json);
                    $field_name = str_replace(' ', '_', $row['name']);
                    $item[$field_name] = $fk_item;
                }
            }
            //$json = json_encode($item);
            //$this->redis->set("expandeditem:$id", $json);
         //   return $item;
        //}
        //return json_decode($item);
            return $item;
    }

    function prepareJson($data) {
        return json_encode($this->unpackJson($data));
    }

    static function unpackJson($data) {
        return unserialize(base64_decode($data));
    }

    function getUser($id) {
        $query = "SELECT * from users WHERE id = $id";
        $sql = new sqlQuery($this->conn, $query);
        return $sql->rows[0];
    }

    function getSimpleItemDictionary($id) {
        $query = "select i.name as name, i.application as application, i.template_id as template_id, i.description as description, i.value as value, i.date_value as date_value, i.date_created as date_created, itf.name as col, itf.field_type_id as field_type, id.value as data from items i ";
        $query .= "left join item_data id  on i.id = id.item_id ";
        $query .= "left join item_templates it on i.template_id = it.id ";
        $query .= "left join item_template_fields itf on id.item_field_id = itf.id ";
        $query .= "where i.active = 1 AND i.id = " . $id;
        $sql = new sqlQuery($this->conn, $query);
        $dict = array();
        $data = $sql->rows;

        if (true || $data["inherits_user"] == 1) {
            $dict = general::array_push_assoc($dict, 'User', $this->getUser($data[0]["value"]));
        }

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
            switch ($datum["field_type"]) {
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

    function getItemJsonById($id) {
        $cache = false;
        if ($cache) {
            error_log("GETTING ITEM FROM CACHE :::" . $id);
            return $cache;
        } else {
            $query = "SELECT json FROM items WHERE id = $id";
            $sql = new sqlQuery($this->conn, $query);
            $json = $this->unpackJson($sql->rows[0]["json"]);
            $this->mem->set("ITEMBYID:" . $id, $json);
            return $json;
        }
    }

    function saveJson($id) {
        $dict = $this->getSimpleItemDictionary($id);
        $json = base64_encode(serialize($dict));
        $query = "UPDATE items SET json = '$json' WHERE id = " . $id;
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        return $dict;
    }

    function updateJson($output = false) {
//        if ($this->applicationId == null) {
//            $this->applicationId = $argv[1];
//        }
        $sql = new sqlQuery($this->conn, "SELECT id FROM items WHERE active = 1 AND application = '" . $this->applicationId . "' ORDER BY id");
        $output = array();
        foreach ($sql->rows as $row) {
            array_push($output, $this->saveJson($row['id']));
        }
        return json_encode($sql);
    }

}

class ItemService extends ItemBase {

    public $conn;
    public $action;
    public $paramArr = array();
    public $routes = array();
    public $headers = array();
    public $start_ts;
    public $resp = array();
    public $validUser;
    public $jwt;
    public $key;
    public $errors = array();
    public $data;
    public $providers = array();

    function __construct($conn, $key) {
        parent::__construct($conn);
        $this->resp;
        $this->key = $key;
        $this->validUser = false;
        //$this->addToResp("server", $_SERVER);
        $this->addToResp("request", $_REQUEST);
        if (function_exists("apache_request_headers")) {
            $this->headers = apache_request_headers();
            $this->addToResp("headers", $this->headers);
            if (array_key_exists("jwt", $this->headers)) {
                //$this->validateJWT();
            }
        }
    }

    function decodeJWT() {
        try {
            $this->jwt = JWT::decode($this->headers["jwt"], $this->key, array('HS256'));
        } catch (Exception $e) {
            $this->addErrorResp($e->getMessage());
            return false;
        }
        return true;
    }

    function validateJWT() {
        $this->validUser = false;
        if ($this->decodeJWT()) {
            if ($this->jwt->ra == $_SERVER['REMOTE_ADDR']) {
                $this->validUser = true;
            }
        }
    }

    function addRoute($route, $function) {
        $this->routes = array_merge($this->routes, [$route => $function]);
    }

    function addProvider($key, $value){
      if(!$this->providers){
          $this->providers = array();
      }
      $this->providers[$key] = $value;
    }

    function addHeader($header) {
        array_push($this->headers, $header);
    }

    function outputHeaders() {
        foreach ($this->headers as $header) {
            header($header);
        }
    }

    function response($resp) {
        echo $resp;
    }

    function route() {
        header('Content-Type: application/json');
        $this->outputHeaders();
        $this->mapRoute();
        $this->addToResp("errors", $this->errors);
        $this->addToResp("data", $this->data);
        error_log(json_encode($this->data));
        //$this->addToResp("service", $this);
        //echo json_encode($this->data);
        echo json_encode($this->resp, JSON_PRETTY_PRINT);
    }

    function addToResp($k, $v) {
        $this->resp = array_merge($this->resp, [$k => $v]);
    }

    function addErrorResp($error) {
        array_push($this->errors, $error);
    }

    function addDataResp($data) {
        $this->data = $data;
    }

    function mapRoute() {
        $keys = array_keys($this->routes);
        $params = $_REQUEST["params"];
        $matchFound = false;
        foreach ($keys as $pattern) {
            $matches = preg_grep('#^' . $pattern . '$#', array($params));
            if (sizeof($matches) > 0) {
                $route = $this->routes[$pattern];
                $matchFound = true;
                call_user_func($this->routes[$pattern], $this);
                break;
            }
        }
        if (!$matchFound) {
            call_user_func($this->routes["notFound"], $this);
        }
    }

}
