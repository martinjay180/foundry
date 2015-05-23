<?php

class ItemServiceActions {

    const ItemById = 1;
    const ItemCollection = 2;
    const ItemsByType = 3;
    const ItemsByFieldInfo = 4;
    const NoAction = 5;
    const Applications = 6;
    const Templates = 7;
    const TemplateFields = 8;
    const UpdateItem = 9;
}

class ItemBase {
    
    public $conn;
    public $redis;
    public $applicationId;
    public $headers;

    function __construct($conn) {
        $this->conn = $conn;
        $params = $_REQUEST["params"];
        $this->paramArr = explode("/", $params);
        $this->headers = apache_request_headers();
        $this->applicationId = $this->headers["Authorization"];
    }
}

class ItemService extends ItemBase {
    
    public $conn;
    public $action;
    public $paramArr = array();
        
    function process(){
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Authorization, Content-Type");
        $this->determineAction();
        switch($this->action){
            case ItemServiceActions::ItemById:
                $resp = $this->getItemById();
                break;
            case ItemServiceActions::ItemsByType:
                $resp = $this->getItemsByType();
                break;
            case ItemServiceActions::ItemCollection:
                $resp = $this->getItemCollection();
                break;
            case ItemServiceActions::ItemsByFieldInfo:
                $resp = $this->getItemsByFieldInfo();
                break;
            case ItemServiceActions::Applications:
                $resp = $this->getApplications();
                break;
            case ItemServiceActions::Templates:
                $resp = $this->getTemplates();
                break;
            case ItemServiceActions::TemplateFields:
                $resp = $this->getTemplateFields();
                break;
            case ItemServiceActions::UpdateItem:
                $resp = $this->UpdateItem();
                break;
            default:
                $resp = $this->noAction();
                break;
        }
        return $resp;
    }
    
    function determineAction(){
        if($this->applicationId != ""){
        switch($this->paramArr[0]){
            case "ItemById":
                $this->action = ItemServiceActions::ItemById;
                break;
            case "ItemsByType":
                $this->action = ItemServiceActions::ItemsByType;
                break;
            case "ItemCollection":
                $this->action = ItemServiceActions::ItemCollection;
                break;
            case "ItemsByFieldInfo":
                $this->action = ItemServiceActions::ItemsByFieldInfo;
                break;
            case "Applications":
                $this->action = ItemServiceActions::Applications;
                break;
            case "Templates":
                $this->action = ItemServiceActions::Templates;
                break;
            case "TemplateFields":
                $this->action = ItemServiceActions::TemplateFields;
                break;
            case "UpdateItem":
                $this->action = ItemServiceActions::UpdateItem;
                break;
            default:
                $this->action = ItemServiceActions::NoAction;
        }
        } else {
            $this->action = ItemServiceActions::NoAction;
        }
    }
    
    function noAction(){
        $resp = array();
        $resp["timestamp"] = time();
        $resp["request"] = implode("/", $this->paramArr);
        $resp["response"] = "invalid request";
        //$resp["debug"] = $this;
        return json_encode($resp, JSON_PRETTY_PRINT);
    }
    
    function getItemById(){
        $id = $this->paramArr[1];
        //return json_encode($this, JSON_PRETTY_PRINT);
        //return json_encode($this->getSimpleItemDictionary($id), JSON_PRETTY_PRINT);
        $query = "SELECT json FROM items WHERE id = $id";
        $sql = new sqlQuery($this->conn, $query);
        return json_encode(unserialize($sql->rows[0]["json"]), JSON_PRETTY_PRINT);
        //return $sql->rows[0]["json"];
    }
    
    function UpdateItem(){
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata, true);
        $item_id = $this->paramArr[1];
        $query = "UPDATE item_data SET value = CASE id ";
        $keys = array_keys($data);
        foreach($keys as $key){
            $query .= "WHEN $key THEN '$data[$key]' ";   
        }
        $query .= "END WHERE id IN (".implode(",", $keys).")";
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        return json_encode($sql, JSON_PRETTY_PRINT);
    }
    
    function getItemsByType(){
        //return json_encode($this, JSON_PRETTY_PRINT);
        $type_id = $this->paramArr[1];
        $include_inactive = $this->paramArr[2];
        $query = "SELECT json FROM items WHERE template_id = $type_id AND application = '$this->applicationId' ORDER BY sort_order";
        $sql = new sqlQuery($this->conn, $query);
        //return json_encode($sql, JSON_PRETTY_PRINT);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, unserialize($row["json"]));
        }
        return json_encode($items, JSON_PRETTY_PRINT);
    }
    
    function getItemCollection(){  
        $ids = $this->paramArr[1];
        $query = "SELECT json FROM items WHERE id IN (".$ids.") ORDER BY FIELD(id, ".$ids.")";
        $sql = new sqlQuery($this->conn, $query);
        //json_encode($sql, JSON_PRETTY_PRINT);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, unserialize($row["json"]));
        }
        array_push($items, $sql);
//        $wrapped_items = array_map(
//            function ($el) {
//                return "{\{$el}}";
//            },
//            $items
//        );
        //general::pretty($items);
        //return implode(",", $items);
        return json_encode($items, JSON_PRETTY_PRINT);
    }
    
    function getItemsByFieldInfo(){
        //return json_encode($this, JSON_PRETTY_PRINT);
        $filters = explode(",",$this->paramArr[1]);
        $where = implode(" AND ", $filters);
        $query = "SELECT DISTINCT(item_id), json FROM getitems WHERE application = '$this->applicationId' AND ".$where;
        $sql = new sqlQuery($this->conn, $query);
        //return json_encode($sql, JSON_PRETTY_PRINT);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, unserialize($row["json"]));
        }
        return json_encode($items, JSON_PRETTY_PRINT);
    }
    
    function getApplications(){
        $query = "SELECT * FROM application WHERE active = 1";
        $sql = new sqlQuery($this->conn, $query);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, $row);
        }
        return json_encode($items, JSON_PRETTY_PRINT);
    }
    
    function getTemplates(){
        if($this->paramArr[1] != 0){
            $query = "SELECT * FROM item_templates WHERE id = ".$this->paramArr[1];
        } else {
            $query = "SELECT * FROM item_templates";
        }
        $sql = new sqlQuery($this->conn, $query);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, $row);
        }
        return json_encode($items, JSON_PRETTY_PRINT);
    }
    
    function getTemplateFields(){
        $query = "SELECT * FROM getitems WHERE item_id = ".$this->paramArr[1];   
        $sql = new sqlQuery($this->conn, $query);
        $items = array();
        foreach($sql->rows as $row){
            array_push($items, $row);
        }
        return json_encode($items, JSON_PRETTY_PRINT);
    }
   
}

class Item {
    
}

class ItemManager {

    public $conn;
    public $query_count;
    public $cache_count;
    public $router;
    public $outputs = array();
    public $redis;

    function __construct($conn) {
        $this->query_count = 0;
        $this->cache_count = 0;
        $this->conn = $conn;
//        Predis\Autoloader::register();
//        $redis = new Predis\Client();
//        $this->redis = $redis;
        //general::pretty($this->redis);
        //if ($this->checkCredentials()) {
            $this->goodCredentials();
        //} else {
        //    $this->badCredentials();
        //}
    }
    
    function checkCredentials() {
        return true;
    }
    
    function getApplicationWhere(){
        $id = $this->getCurrentApplication();
        if($id == "7a40a126-7d83-44ed-8c82-9a8fad534b75"){
            $where = "TRUE";
        } else {
            $where = "application = '".$id."'";
        }
        return $where;
    }
    
    function saveJson($id){
        $json = $this->getSimpleItemDictionary($id);
        //$json = htmlspecialchars(json_encode($json), ENT_QUOTES, 'UTF-8');
        $json = serialize($json);
        $query = "UPDATE items SET json = '$json' WHERE id = $id";
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        general::pretty($sql);
    }
    
    function updateJson(){
        $sql = $this->query('SELECT id FROM items active = 1');
        foreach($sql->rows as $row){
            $id = $row["id"];
            print_r($id.",");
            $this->saveJson($id);
        }
    }
    
    function getApplicationForm(){
        $query = "SELECT * FROM Application";
        $sql = $this->query($query);
        $select = htmlElement::select($sql->rows, "name", "uuid", "applicationId", $class, $_SESSION["ApplicationUUID"], $selected_name, $initial_option, $multiple);
        return "<form id='application' action='setApplicationId' method='post'>" . $select ."</form>";
    }
    
    function getCurrentApplication(){
        return (!$_SESSION["ApplicationUUID"]) ? $_REQUEST["Application"] : $_SESSION["ApplicationUUID"];
    }
    
    function getMasterApplication(){
        return "7a40a126-7d83-44ed-8c82-9a8fad534b75";
    }
    
    function isMasterApplication(){
        if($this->getCurrentApplication() == $this->getMasterApplication()){
            return true;
        } else {
            return false;
        }
    }
    
    function badCredentials(){
        $this->addOutput("No valid credentials");
    }
    
    function goodCredentials(){
        $this->routeItem();
            if ($_REQUEST["debug"] === "true") {
                $this->debug();
            }
    }

    function addOutput($value, $key = null) {
        if (string::IsNullOrEmptyString($key)) {
            $key = general::getUUID();
        }
        $this->outputs = general::array_push_assoc($this->outputs, $key, $value);
    }

    function debug() {
        general::pretty($this->router->debug());
        //$this->debugOutputs();
    }

    function debugOutputs() {
        general::pretty($this->outputs);
    }

    function renderOutputs() {
        foreach ($this->outputs as $output) {
            $disp .= $output;
        }
        return $disp;
    }

    function routeItem() {
        $this->router = new ItemRouter();
        $item = $this->router->item;
        switch ($this->router->translateURLAction()) {
            case ItemActions::ListAll:
                switch ($this->router->item) {
                    case "Templates":
                        $this->addOutput($this->listTemplates());
                        break;
                    case "Items":
                        $this->addOutput($this->listItems());
                        break;
                    case "Service":
                        //$this->addOutput($this->listItems(ItemOutputFormats::JSON));
                        echo json_encode($this->listItems(ItemOutputFormats::JSON), JSON_PRETTY_PRINT);
                        die();
                        break;
                }
                break;
            case ItemActions::Details:
                //header("Access-Control-Allow-Origin: *");
                //echo json_encode($_SERVER, JSON_PRETTY_PRINT);
                echo json_encode($this->getSimpleItemDictionary($this->router->data), JSON_PRETTY_PRINT);
                die();
                break;
            case ItemActions::SetApplicationId:
                //general::pretty($_REQUEST);
                $_SESSION["ApplicationUUID"] = $_REQUEST["applicationId"];
                //general::pretty($_SESSION);
                die();
                break;
            case ItemActions::Authorize:
                $key = $_REQUEST["key"];
                $ts  = time();
                $site = "";
                break;
            case ItemActions::Edit:
                if ($item === "Templates") {
                    $this->addOutput($this->editTemplate());
                } else {
                    $this->addOutput($this->editItem());
                }
                break;
            case ItemActions::Update:
                if ($this->router->item === "Templates") {
                    $this->updateTemplate();
                } else {
                    $this->updateItem();
                }
                break;
            case ItemActions::CreateNew:
                if ($this->router->item === "Templates") {
                    $this->addOutput($this->newTemplate());
                } else {
                    $this->addOutput($this->newItem());
                }
                break;
            case ItemActions::Insert:
                if ($item === 'Templates') {
                    $this->insertTemplate();
                } else {
                    $response = $this->insertItem($this->router->data, $_REQUEST);
                    if ($item === 'Service') {
                        echo $response;
                        die();
                    } else {
                        $this->afterItemInsert();
                    }
                }
                break;
            case ItemActions::Sort:
                if ($item === 'Templates') {
                    $this->addOutput($this->sortItems());
                }
                break;
            case ItemActions::UpdateSort:
                $this->updateSort();
                $this->afterUpdateSort();
                break;
            case ItemActions::UpdateJson:
                general::pretty($this);
                die();
                $this->updateJson();
                break;
            default:
                $this->addOutput($this->extendedAction($this->router));
        }
    }

    function extendedAction() {
        return '';
    }

    function query($query) {
        $this->query_count++;
        //general::pretty($query);
        return new sqlQuery($this->conn, $query);
    }

    function getItemById($id) {
        $query = 'SELECT * FROM getitems WHERE item_id = ' . $id;
        return $this->query($query)->rows;
    }

    function getItemDictionary($id) {
        $data = $this->getItemById($id);
        $dict = array();
        $dict = general::array_push_assoc($dict, 'Id', $id);
        foreach ($data as $datum) {
            general::array_push_assoc($dict, $datum['field_name'], $datum);
        }
        return $dict;
    }

    function getSimpleItemDictionary($id) {
        //$hash = 'Items:SimpleDictionary';
        //$cache = $this->redis->hget($hash, $id);
        //if (!$cache) {
            $data = $this->getItemById($id);
            $dict = array();
            $dict = general::array_push_assoc($dict, 'Id', $id);
            //$dict = general::array_push_assoc($dict, 'uuid', $data[0]['uuid']);
            $dict = general::array_push_assoc($dict, 'Name', $data[0]['item_name']);
            $dict = general::array_push_assoc($dict, 'Application', $data[0]['application']);
            $dict = general::array_push_assoc($dict, 'ItemTemplate', $data[0]['template_id']);
            $dict = general::array_push_assoc($dict, 'Description', $data[0]['item_description']);
            $dict = general::array_push_assoc($dict, 'CreatedDate', $data[0]['item_created_date']);
            foreach ($data as $datum) {
                $dict = general::array_push_assoc($dict, $datum['field_name'], ($datum['data_value']));
            }
            //$this->redis->hset($hash, $id, json_encode($dict));
        //} else {
            //$this->cache_count++;
            //$dict = json_decode($cache, true);
        //}
        return $dict;
    }

    function doesItemDataExist($item_id, $field_id) {
        $query = 'SELECT * FROM item_data WHERE item_id = ' . $item_id . ' AND item_field_id = ' . $field_id;
        $item = $this->query($query);
        //general::pretty($item);
        if ($item->numRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function getItemDataValue($item_id, $field_id) {
        $query = 'SELECT * FROM item_data WHERE item_id = ' . $item_id . ' AND item_field_id = ' . $field_id;
        $item = $this->query($query);
        return $item->rows[0]['value'];
    }

    function buildTable($sql, $table, $columns) {
        $table->class = 'table table-striped';
        $table->headers = $columns;
        foreach ($sql->rows as $row) {
            $col_vals = array();
            $options = '<i class="fa fa-pencil fa-fw"></i>';
            $options .= '<i class="fa fa-trash-o"></i>';
            array_push($col_vals, $options);
            foreach ($columns as $col) {
                array_push($col_vals, $row[$col]);
            }
            $table->buildRow($col_vals);
        }
        return $table;
    }

    function buildItemList() {
        $t = new table();
        $t->title = 'Templates';
        $sql = $this->query('SELECT * FROM item_templates');
        //foreach($sql->rows as $template){
        //    $t->addRow($template['id']);
        //}
        $t = $this->buildTable($sql, $t, $this->template_columns);
        return $t->render();
    }
    
    function listItemOptions($item){
        $options = array();
        array_push($options, "<a href=\"/Items/Edit/" . $item['id'] . "\"><i class=\"fa fa-pencil fa-fw\"></i></a>");
        array_push($options, "<a href=\"/Items/Edit/" . $item['id'] . "\"><i class=\"fa fa-trash-o\"></i></a>");
        return $options;
    }

    function listItems($outputType = ItemOutputFormats::HTML) {
        if ($this->router->params[3] === 'Last') {
            $sql = $this->query('SELECT * FROM items WHERE template_id = ' . $this->router->data . ' ORDER BY date_created DESC LIMIT ' . $this->router->params[4]);
        } else {
            $sql = $this->query('SELECT * FROM items WHERE template_id = ' . $this->router->data);
        }
        //general::pretty($sql);
        switch ($outputType) {
            case ItemOutputFormats::HTML:
                $t = new table();
                $title = "<div class='col-md-12'>" .$this->router->item . "<span class='pull-right'><a href='/Items/New/" . $this->router->data . "'><i class='fa fa-plus fa-fw'></i></a><a class='filterToggleBtn' href='javascript:void(0)'><i class='fa fa-filter'></i></a></span></div>";
                $filters = $this->getFilters($this->router->data);
                $t->class = 'table table-striped';
                $t->headers = array('Options', 'Id', 'Name', 'Description', 'Created Date', 'UUID');
                $columns = array('id', 'name', 'description', 'date_created', 'uuid');
                foreach ($sql->rows as $row) {
                    $col_vals = array();
                    $options = join("", $this->listItemOptions($row));
                    array_push($col_vals, $options);
                    foreach ($columns as $col) {
                        $raw = $row[$col];
                        switch ($col) {
                            case 'date_created':
                                $disp = ts::getFullDateTime($raw);
                                break;
                            default:
                                $disp = $raw;
                                break;
                        }
                        array_push($col_vals, $disp);
                    }
                    $t->buildRow($col_vals);
                }
                return $title.$filters.$t->render();
                break;
            case ItemOutputFormats::JSON:
                $items = array();
                foreach ($sql->rows as $row) {
                    $dict = $this->getSimpleItemDictionary($row['id']);
                    array_push($items, $dict);
                }
                return general::pretty($sql);
                return $items;
                break;
        }
    }

    function getFilters($template_id) {
        $query = "SELECT * FROM gettemplatefields WHERE template_id = $template_id";
        $fields = $this->query($query);
        foreach ($fields->rows as $field) {
            $disp .= htmlElement::div($field['field_name']);
        }
        return htmlElement::div($disp, "filters", "col-md-6");
    }

    function listTemplates() {
        $t = new table();
        $t->title = "Templates" . "<span class='pull-right'><a href='/Templates/New'><i class='fa fa-plus fa-fw'></i></a></span>";
        $sql = $this->query('SELECT * FROM item_templates');
        $t->class = 'table table-striped';
        $t->headers = array('Options', 'Id', 'Name', 'Description', 'UUID');
        $columns = array('id', 'name', 'description', 'uuid');
        foreach ($sql->rows as $row) {
            $template_id = $row['id'];
            $col_vals = array();
            $options = "<a href=\"/Items/List/" . $template_id . "\"><i class=\"fa fa-list fa-fw\"></i></a>";
            $options .= "<a href='/Items/New/$template_id'><i class='fa fa-plus fa-fw'></i></a>";
            $options .= "<a href='/Templates/Edit/$template_id'><i class='fa fa-pencil fa-fw'></i></a>";
            $options .= "<a href='/Templates/Sort/$template_id'><i class='fa fa-sort fa-fw'></i></a>";
            $options .= '<i class="fa fa-trash-o"></i>';
            array_push($col_vals, $options);
            foreach ($columns as $col) {
                array_push($col_vals, $row[$col]);
            }
            $t->buildRow($col_vals);
        }
        return $t->render();
    }

    function newTemplate() {
        $label = "<label for='name'>Name</label>";
        $input = "<input type='text' class='form-control' id='template_name' name='name' placeholder='Enter Name'>";
        $groups = htmlElement::div($label . $input, null, 'form-group');

        $label = "<label for='name'>Description</label>";
        $input = htmlElement::textarea($value, 'description', 'form-control');
        $groups .= htmlElement::div($label . $input, null, 'form-group');

        $buttons = "<button type='submit' class='btn btn-default'>Save</button>";
        $buttons .= "<a href='/Templates/List/' class='btn btn-default'>Cancel</a>";
        return "<form action='/Templates/Insert' method='post'>" . $groups . $buttons . '</form>';
    }

    function insertTemplate() {
        general::pretty($_REQUEST);
        $name = $_REQUEST['name'];
        $description = $_REQUEST['description'];
        $query = "INSERT INTO item_templates (name, description, uuid) VALUES ('$name','$description', '" . general::getUUID() . "')";
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
        header('Location: /Templates/List');
    }

    function editTemplate() {
        $sql = $this->query('select * from gettemplatefields WHERE template_id = ' . $this->router->data);
        //general::pretty($sql->rows);
        $helper = $sql->rows[0];
        $name = $helper['template_name'];
        $disp = bootstrap::pageHeader($name, 'Data Fields');
        $types = $this->query('select * from item_field_types');
        $templates = $this->query('select * from item_templates');
        foreach ($sql->rows as $row) {
            $field_id = $row['field_id'];
            $field_data = $row['field_data'];
            $select = htmlElement::select($types->rows, 'name', 'id', 'field_type', $class, $row['field_type_id'], $selected_name, $initial_option, $additional_attr);
            if ($row['field_type_id'] === 4) {
                $fk_selected = $field_data;
            } else {
                $fk_selected = null;
            }
            $fk_select = htmlElement::select($templates->rows, 'name', 'id', 'field_fk_id', $class, $fk_selected, $selected_name, '-', $additional_attr);
            $items .= "<li id='$field_id' class=\"ui-state-default\">Field Name:<input type='text' id='field_name' value='" . $row['field_name'] . "'> Type: $select <input type='text' id='field_data' class='' value='$field_data'> FK: $fk_select</li>";
        }
        $disp = "<ul id='templateFieldList' class='sortable' style='width:100%;'>$items</ul>";
////            $label = "<label for='field_id_" . $row['field_id'] . "'>" . $row['field_name'] . "</label>";
////            $input = $this->getTemplateFieldDisplay($row);
////            $div = htmlElement::div($label . $input, null, "form-group");
////            $groups .= htmlElement::div($div, null, "ui-state-default");
//        }
//        $disp .= htmlElement::div($groups, null, "sortable");
        $disp .= "<input id='data' type='text' name='data'>";
        $new = "<div><a id='templateAddField' href='javascript:void(0)'>Add New Field</a></div>";
        $buttons = "<a href='#' onclick='submitTemplateEdit()' class='btn btn-default'>Save</a>";
        $buttons .= "<a href='/Templates/List/" . $helper['template_id'] . "' class='btn btn-default'>Cancel</a>";
//
        $disp .= $new . $buttons;
//
        return "<form id='editTemplate' action='/Templates/Update/" . $this->router->data . "' method='post'>" . $disp . "</form>";
    }

    function editItem() {
        $is_master = $this->isMasterApplication();
        $sql = $this->query("select * from getitems WHERE item_id = " . $this->router->data);
        //general::pretty($sql->rows);
        $helper = $sql->rows[0];
        $name = $helper["item_name"];
        $desc = $helper["item_description"];
        $template_name = $helper["template_name"];
        $application = $helper["application"];
        
        if(true /*$is_master*/){
            $app_query = "SELECT * FROM Application";
            $app_sql = $this->query($app_query);
            $select = htmlElement::select($app_sql->rows, "name", "uuid", "applicationId", "chosen", $application, $selected_name, $initial_option, $multiple);
            $label = "<label for='name'>Application</label>";
            $application_div = htmlElement::div($label . $select, null, "form-group");
        }
        $label = "<label for='name'>Name</label>";
        $input = "<input type='text' class='form-control' name='item_name' value='$name' placeholder=''>";
        $name_div = htmlElement::div($label . $input, null, "form-group");
        $label = "<label for='name'>Description</label>";
        $input = "<textarea type='text' class='form-control' name='item_description' value='$desc' placeholder=''>$desc</textarea>";
        $desc_div = htmlElement::div($label . $input, null, "form-group");
        $groups .= $application_div . $name_div . $desc_div;
        //t->title = "Edit Item - ".$template_name." Template";
        //$t->addRow("Name", htmlElement::textbox("name","form-control",$name));
        foreach ($sql->rows as $row) {
            $label = "<label for='field_id_" . $row['field_id'] . "'>" . $row['field_name'] . "</label>";
            $input = $this->getItemFieldDisplay($row);
            $div = htmlElement::div($label . $input, null, "form-group");
            $groups .= $div;
        }
        $buttons = "<button type='submit' class='btn btn-default'>Save & Close</button>";
        $buttons .= "<button type='button' onclick='itemEditSave()' class='btn btn-default'>Save</button>";
        $buttons .= "<a href='/Items/List/" . $helper['template_id'] . "' class='btn btn-default'>Cancel</a>";
        $buttonDiv = htmlElement::div($buttons, null, "bottomStaticDiv");
        return "<form id='editTemplate' action='/Items/Update/" . $this->router->data . "' method='post'>" . $groups . $buttonDiv . "</form>";
    }

    function getItemFieldDisplay(&$item) {

        $field_id = $item['field_id'];
        $value = $item['data_value'];
        $name_attr = "field_id_" . $field_id;
        switch ($item["field_type_id"]) {
            case "1":
                if ($value === "on") {
                    $checked = "checked='checked'";
                } else {
                    $checked = null;
                }
                $disp = "<input type='checkbox' " . $checked . " class='' name='field_id_$field_id'>";
                break;
            case "2":
                $disp = htmlElement::textBox($name_attr, "form-control", $value);
                //$disp = "<input type='text' class='form-control' id='field_id_$field_id' value='$value' placeholder=''>";
                break;
            case "3":
                $disp = htmlElement::textarea($value, $name_attr, "form-control redactor");
                break;
            case "4":
                //general::pretty($item);
                $query = "SELECT * FROM items WHERE template_id = " . $item["field_data"];
                //general::pretty($query);
                $sql = $this->query($query);
                //general::pretty($sql->rows);
                $selected_id = $item["data_value"];
                $disp = htmlElement::select($sql->rows, "{{name}} - {{description}}", "id", $name_attr, "chosen", $selected_id, $selected_name, "-", $additional_attr);
                break;
            case "5":
                //general::pretty($item);
                $query = "SELECT * FROM items WHERE template_id = " . $item["field_data"];
                //general::pretty($query);
                $sql = $this->query($query);
                //general::pretty($sql->rows);
                $selected_id = $item["data_value"];
                $disp = htmlElement::select($sql->rows, "{{name}} : {{description}}", "id", $name_attr, "chosen", $selected_id, $selected_name, "-", $additional_attr, true);
                break;
            case "6":
                //$disp = "<input type='file' name='".$name_attr."'>";
                $disp = "<input type='button' class='btn btn-primary' value='Media'>";
                break;
            case "7":
                $disp = htmlElement::textBox($name_attr, "datepicker", $value);
                break;
            case "8":
                //general::pretty($item);
                if (array_key_exists("item_id", $item)) {
                    $json = json_decode($item["field_data"]);
                    $filters = $json->filters;
                    $query = "SELECT * FROM items WHERE template_id = " . $item["field_data"];
                    $filter = $filters[0];
                    $item_query = "select * from getitems WHERE field_id = " . $filter->field_id . " AND data_value = " . $item["item_id"];
                    $item_field = $this->query($item_query);
                    $disp = htmlElement::select($item_field->rows, "item_name", "item_id", $name_attr, $class, $item["data_value"], $selected_name, $initial_option);
                } else {
                    $disp = false;
                }
                break;
            case "9":
                $disp = htmlElement::textarea($value, $name_attr, "form-control");
                break;
            default:
                return $item["field_type_id"];
        }
        return $disp;
    }

    function getTemplateFieldDisplay($item) {
        
    }

    function queryBuilder($template_id, $fieldArr = null, $order_by = null, $limit = null) {
        $q = "SELECT DISTINCT(item_id) FROM getitems WHERE template_id = " . $template_id . " ";
        $keys = array_keys($fieldArr);
        foreach ($keys as $key) {
            $q .= "AND item_id IN (SELECT item_id from getitems WHERE field_id = " . $key . " AND data_value = '" . $fieldArr[$key] . "') ";
        }
        if ($order_by) {
            $q .= "ORDER BY " . $order_by . " ";
        }
        if ($limit) {
            $q .= "LIMIT " . $limit;
        }
        return $q;
    }

    function updateItem() {
        if($this->isMasterApplication()){
            $application = $_REQUEST["applicationId"];
        } else {
            $application = $_SESSION["ApplicationUUID"];
        }
        $query = "UPDATE items SET application = '".$_REQUEST["applicationId"]."', name = '" . $_REQUEST["item_name"] . "', description = '" . $_REQUEST["item_description"] . "', date_last_update = " . time() . " WHERE id = " . $this->router->data;
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        $item = $this->getItemById($this->router->data);

        $sql = $this->query("select * from gettemplatefields WHERE template_id = " . $item[0]["template_id"]);
        $helper = $sql->rows[0];
        $item_id = $this->router->data;
        foreach ($sql->rows as $field) {
            $field_id = $field["field_id"];
            $type = $field["field_type_id"];
            $key = "field_id_" . $field_id;
            //general::pretty($key);
            if (array_key_exists('field_id_' . $field_id, $_REQUEST) || $type === 1) {
                switch($type){
                    case 1:
                        $value = array_key_exists('field_id_' . $field_id, $_REQUEST) ? "on" : "off";
                        break;
                    case 5:
                        $value = implode(",", $_REQUEST[$key]);
                        break;
                    default:
                        $value = sqlQuery::escape($_REQUEST["field_id_" . $field_id]);
                        break;
                }
                 general::pretty($value);
//                if ($type === 1) {
//                    $value = array_key_exists('field_id_' . $field_id, $_REQUEST) ? "on" : "off";
//                } else if($type === 5){
//                    $value = implode(",", $_REQUEST[$key]);
//                    general::pretty("test");
//                    general::pretty($value);
//                } else {
//                    $value = sqlQuery::escape($_REQUEST["field_id_" . $field_id]);
//                }
                if ($this->doesItemDataExist($item_id, $field_id)) {
                    $query = "UPDATE item_data SET value = '" . $value . "' WHERE item_field_id = $field_id AND item_id = $item_id";
                    $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
                    general::pretty($sql);
                } else {
                    $query = "INSERT INTO item_data (item_id, item_field_id, value, active) VALUES ('$item_id','$field_id','$value','1')";
                    $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
                }
            }
        }
        $this->saveJson($this->router->data);
        //header('Location: /Items/List/' . $helper['template_id']);
    }

    function updateTemplate() {
        $json = $_REQUEST["data"];
        $data = json_decode($json, true);
        //general::pretty($data);
        $template_id = $this->router->data;
        $sort_order = 1;
        foreach ($data as $field) {
            $type = $field["type"];
            if ($type === 4) {
                $data = $field["fk"];
            } else {
                $data = $field["data"];
            }
            if (String::IsNullOrEmptyString($field["id"])) {

                $query = "INSERT INTO item_template_fields (template_id, field_type_id, name, data, sort_order) VALUES ('" . $template_id . "','" . $field["type"] . "','" . $field["name"] . "','" . $data . "','" . $sort_order . "')";
                $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
                //echo $query;
            } else {
                $query = "UPDATE item_template_fields SET field_type_id = '" . $field["type"] . "', name = '" . $field["name"] . "', data = '" . $data . "', sort_order = '" . $sort_order . "' WHERE id = '" . $field["id"] . "'";
                $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
            }
            $sort_order++;
        }
        header('Location: /Templates/List');
    }

    function newItem() {
        $sql = $this->query("select * from gettemplatefields WHERE template_id = " . $this->router->data);
        //return general::pretty($sql->rows);
        $helper = $sql->rows[0];
        $name = $helper["item_name"];
        $template_name = $helper["template_name"];

        $label = "<label for='name'>Name</label>";
        //$input = "<input type='text' class='form-control' name= ''id='field_id_$field_id' value='$name' placeholder=''>";
        $input = htmlElement::textBox("name", "form-control", $value);
        $name_div = htmlElement::div($label . $input, null, "form-group");
        $label = "<label for='name'>Description</label>";
        $input = "<textarea type='text' class='form-control' name='description' value='$desc' placeholder=''>$desc</textarea>";
        $desc_div = htmlElement::div($label . $input, null, "form-group");
        $groups .= $name_div . $desc_div;
        //t->title = "Edit Item - ".$template_name." Template";
        //$t->addRow("Name", htmlElement::textbox("name","form-control",$name));
        foreach ($sql->rows as $row) {
            $label = "<label for='field_id_" . $row['field_id'] . "'>" . $row['field_name'] . "</label>";
            $input = $this->getItemFieldDisplay($row);
            if ($input !== false) {
                $div = htmlElement::div($label . $input, null, "form-group");
                $groups .= $div;
            }
        }
        $buttons = "<button type='submit' class='btn btn-default'>Save</button>";
        $buttons .= "<a href='/Items/Update/" . $helper['template_id'] . "' class='btn btn-default'>Cancel</a>";
        return "<form id='editTemplate' action='/Items/Insert/" . $this->router->data . "' method='post'>" . $groups . $buttons . "</form>";
    }

    function insertItem($template_id, $data) {
        if($this->isMasterApplication()){
            $application = $_REQUEST["applicationId"];
        } else {
            $application = $_SESSION["ApplicationUUID"];
        }
        $query = "INSERT INTO items (application, template_id, uuid, name, description, date_created, active) VALUES ('".$application."', '" . $template_id . "', '" . general::getUUID() . "','" . $data['name'] . "','" . $data['description'] . "','" . time() . "','1')";
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
        $item_id = $sql->response;
        $fields = $this->query("SELECT * FROM gettemplatefields WHERE template_id = " . $template_id);
        foreach ($fields->rows as $field) {
            $field_id = $field['field_id'];
            if (array_key_exists('field_id_' . $field_id, $data)) {
                //$value = $_REQUEST["field_id_" . $field_id];
                $value = sqlQuery::escape($data["field_id_" . $field_id]);
                $query = "INSERT INTO item_data (item_id, item_field_id, value, active) VALUES ('$item_id','$field_id','$value','1')";
                $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
                //general::pretty($sql);
            }
        }
        //return "test";
    }

    function afterItemInsert() {
        header('Location: /Items/List/' . $this->router->data);
    }

    function sortItems() {
        $query = "SELECT * FROM items WHERE template_id = " . $this->router->data . " ORDER BY sort_order";
        $sql = $this->query($query);
        foreach ($sql->rows as $item) {
            $items .= "<li id='" . $item["id"] . "' class='ui-state-default'>" . $item["name"] . " - " . $item["description"] . "</li>";
        }
        $disp = "<ul id='templateItemList' class='sortable' style='width:100%;'>$items</ul>";
        $disp .= "<input id='data' style='display:none;' type='text' name='data'>";
        $buttons = "<a href='#' onclick='submitTemplateSort()' class='btn btn-default'>Save</a>";
        $buttons .= "<a href='/Templates/List/" . $helper['template_id'] . "' class='btn btn-default'>Cancel</a>";
        $disp .= $buttons;
        return "<form id='sortTemplateItems' action='/Templates/UpdateSort/" . $this->router->data . "' method='post'>" . $disp . "</form>";
    }

    function updateSort() {
        general::pretty($_REQUEST);
        $list = $_REQUEST["data"];
        $i = 0;
        foreach (explode(",", $list) as $id) {
            $query = "UPDATE items SET sort_order = " . $i . " WHERE id = " . $id;
            $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
            $i++;
        }
    }

    function afterUpdateSort() {
        header('Location: /Templates/List/');
    }

}

class ItemRouter {

    public $params;
    public $item;
    public $action;
    public $data;
    public $raw;

    function __construct() {
        $this->raw = $_REQUEST["params"];
        $parts = explode("/", $this->raw);
        $this->item = $parts[0];
        $this->action = $parts[1];
        $this->data = $parts[2];
        $this->params = $parts;
    }

    function debug() {
        $t = new table();
        $t->title = "ItemRouter Debug Info";
        $t->class = "table";
        $t->addRow("Item", $this->item);
        $t->addRow("Action", $this->action);
        $t->addRow("Data", $this->data);
        for ($i = 3; $i < sizeof($this->params); $i++) {
            $t->addRow($i, $this->params[$i]);
        }
        return $t->render();
    }

    function translateURLAction(&$action = null) {
        if (String::IsNullOrEmptyString($action)) {
            $val = $this->action;
        } else {
            $val = $action;
        }
        switch ($val) {
            case "Details":
                return ItemActions::Details;
                break;
            case "List":
                return ItemActions::ListAll;
                break;
            case "Edit":
                return ItemActions::Edit;
                break;
            case "Update":
                return ItemActions::Update;
                break;
            case "New":
                return ItemActions::CreateNew;
                break;
            case "Insert":
                return ItemActions::Insert;
                break;
            case "Sort":
                return ItemActions::Sort;
                break;
            case "UpdateSort":
                return ItemActions::UpdateSort;
                break;
            case "Authorize":
                return ItemActions::Authorize;
                break;
            case "setApplicationId":
                return ItemActions::SetApplicationId;
                break;
            case "UpdateJson":
                return ItemActions::UpdateJson;
                break;
            default:
                return ItemActions::NoAction;
        }
    }

}

class ItemActions {

    const ListAll = 1;
    const Edit = 2;
    const Details = 3;
    const CreateNew = 4;
    const Insert = 5;
    const Update = 6;
    const NoAction = 7;
    const Sort = 8;
    const UpdateSort = 9;
    const Authorize = 10;
    const SetApplicationId = 11;
    const UpdateJson = 12;

}

class ItemOutputFormats {

    const HTML = 1;
    const JSON = 2;
    const XML = 3;

}
