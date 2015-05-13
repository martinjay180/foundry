<?php

include_once 'sql.php';
include_once 'html.php';
include_once 'table.php';
include_once 'general.php';

class entityColTypes{
    const Int = 0;
    const String = 1;
    const Date = 2;
    const Bool = 3;
    const ForeignKey = 4;
    const URL = 5;
    const MultiText = 6;
    const entImage = 7;
}

class entityColSettings{
    const entSet_ShowInList = 0;
    const entSet_ShowInInsert = 1;
    const entSet_ShowInEdit = 2;
    const entSet_ShowInDetail = 3;
    const entSet_Type = 4;
    const entSet_Order = 5;
    const entSet_Group = 6;
    const entSet_Auto = 7;
    const entSet_Default = 8;
    const entSet_Format = 9;
    const entSet_DisplayName = 10;
    const entSet_ForeignKeyCol = 11;
    const entSet_UploadFolder = 12;
    const entSet_PathToListImage = 13;
    const entSet_PathToDetailImage = 14;
    const entSet_AllowFilter = 15;
    const entSet_Data = 16;
    const entSet_PathToFullSizeImage = 17;
}

class entityColFormats{
    const entColFormat_DateAndTime = 0;
    const entColFormat_Date = 1;
    const entColFormat_PlainText = 2;
    const entColFormat_HTMLText = 3;
    const entColFormat_MediaSelector = 4;
}

class entityTableSettings{
    const entSet_ShowEditInList = 0;
    const entSet_ShowDeleteInList = 1;
    const entSet_ShowDetailInList = 2;
    const entSet_EnablePaging = 3;
    const entSet_PagingRecords = 4;
    const entSet_SortBy = 5;
    const entSet_AllowSorting = 6;
}

class entityOperations{
    const entOp_List = 0;
    const entOp_New = 1;
    const entOp_Insert = 2;
    const entOp_Edit = 3;
    const entOp_Detail = 4;
    const entOp_Delete = 5;
    const entOp_Update = 6;
    const entOp_OutsideAction = 7;
    const entOp_AjaxUpdate = 8;
    const entOp_DisplaySort = 9;
    const entOp_UpdateSort = 10;
    const entOp_GetFKSelect = 11;
}

class entityRouterModes{
    const entRouterMode_Default = 0;
    const entRouterMode_SEO = 1;
}

class entityNavigation{
    
    public $entity_arr = array();
    public $router_mode;
    
    function __construct($entities, $mode){
        $this->entity_arr = $entities;
        $this->router_mode = $mode;
    }
    
    function render(){
        $tableList = new navNode("tableList", "#");
        $keys = array_keys($this->entity_arr);
        $base = basename($_SERVER['PHP_SELF']);
        foreach($keys as $key){
            $ent  = $this->entity_arr[$key];
            $list = $base.entities::generateLinkStatic($ent->table_name, 0, entityOperations::entOp_List, null);
            $new = $base.entities::generateLinkStatic($ent->table_name, 0, entityOperations::entOp_New, null);
            $node = new navNode($ent->disp_name, $list);
//            $node->addChild(new navNode("list", $list));
//            $node->addChild(new navNode("new", $new));
            $tableList->addChild($node);
        }
        return $tableList;
    }
    
    function getLink($entity, $operation, $data){
//        return basename($_SERVER['PHP_SELF'])."?entity=$entity&operation=$operation&data=$data";
        return basename($_SERVER['PHP_SELF']).  entities::generateLinkStatic($entity, $this->router_mode, $operation, $data);
    }
}

class entityRouter{
    
    public $entity;
    public $entity_arr = array();
    public $operation;
    public $data;
    public $router_mode;
    public $filtered;
    public $default_ent;
    public $action;
    public $suppress;
    
    function __construct($entities, $mode, $default_ent){
        $this->entity_arr = $entities;
        $this->router_mode = $mode;
        
        $req_entity = $_REQUEST['entity'];
        $req_operation = $_REQUEST['operation'];
        $this->filtered = $_REQUEST['filtered'];
        $req_page = $_REQUEST['page'];
        $action = $_REQUEST['action'];
        $this->suppress = $_REQUEST['suppress'];
        
        if($_POST){
            $req_data = $_POST;
        } else {
            $req_data = $_REQUEST['data'];
        }
        if(!string::IsNullOrEmptyString($action)){
            $this->action = $action;
            $this->data = $req_data;
            $this->operation = entityOperations::entOp_OutsideAction;
        } else {
            if(string::IsNullOrEmptyString($req_page)){
                $req_page = 0;
            }
            if(string::IsNullOrEmptyString($req_entity)){
                $req_entity = $default_ent;
            }
            $this->entity = $this->entity_arr[$req_entity];
            $this->entity->router_mode = $mode;
            $this->entity->data = $req_data;
            $this->entity->page = $req_page;
            $this->entity->suppress = $this->suppress;
            $this->operation = $this->convertOperationUrlVariable($req_operation);
            $this->entity->curr_operation = $this->operation;
            $this->data = $req_data;
        }
    }
    

    
    function getEntityRouterForm($content, $action, $button_disp){
        $action = basename($_SERVER['PHP_SELF'])."?action=$action";
        $disp = htmlElement::submit($button_disp);
        $form = htmlElement::form($content.$disp, "foundryEntityForm", $action, "POST");
        return htmlElement::div($form, null, "grid_12");
    }
	
	function renderAction($action, $data = null){
		$this->operation = entityOperations::entOp_OutsideAction;
		$this->action = $action;
		$this->data = $data;
		return $this->render();
	}
    
    function render(){
        switch($this->operation){
            case entityOperations::entOp_OutsideAction:
                $disp = call_user_func($this->action, null);
                break;
            case entityOperations::entOp_Delete:
                $disp = $this->entity->delete();
                break;
            case entityOperations::entOp_Detail:
                $disp = $this->entity->getById($this->data)->getDetails();
                break;
            case entityOperations::entOp_List:
                if($this->filtered != "yes"){
                    $disp = $this->entity->getAll(true)->listItems();
                } else {
                    $disp = $this->entity->getAllWithFilters()->listItems(true);
                }
                break;
            case entityOperations::entOp_Edit:
                $disp = $this->entity->getById($this->data)->editItemForm();
                break;
            case entityOperations::entOp_Update:
                $disp = $this->entity->update();
                break;
            case entityOperations::entOp_New:
                $disp = $this->entity->newItemForm();
                break;
            case entityOperations::entOp_Insert:
                $disp = $this->entity->insertItem();
                break;
            case entityOperations::entOp_AjaxUpdate:
                $disp = $this->entity->update();
                echo $disp;
                die();
                break;
            case entityOperations::entOp_DisplaySort:
                $disp = $this->entity->getAll()->displaySort();
                break;
            case entityOperations::entOp_UpdateSort:
                $disp = $this->entity->updateSort();
                break;
            default:
                $disp = $this->entity->getAll()->listItems();
                break;
        }
        if($this->suppress == "yes"){
            echo $disp;
            die();
        } else{
            return $disp;
        }
    }
    
    function convertOperationEnum($input){
        switch($input){
            case entityOperations::entOp_List:
                $output = "List";
                break;
            case entityOperations::entOp_Detail:
                $output = "Details";
                break;
            case entityOperations::entOp_Edit:
                $output = "Edit";
                break;
            case entityOperations::entOp_Delete:
                $output = "Delete";
                break;
            case entityOperations::entOp_New:
                $output = "New";
                break;
            case entityOperations::entOp_Update:
                $output = "Update";
                break;
            case entityOperations::entOp_Insert:
                $output = "Insert";
                break;
            case entityOperations::entOp_DisplaySort:
                $output = "DisplaySort";
                break;
            case entityOperations::entOp_UpdateSort:
                $output = "UpdateSort";
                break;
            default:
                $output = "List";
                break;
        }
        return $output;
    }
    
    function convertOperationUrlVariable($input){
        switch($input){
            case "Details":
                $output = entityOperations::entOp_Detail;
                break;
            case "Edit":
                $output = entityOperations::entOp_Edit;
                break;
            case "List":
                $output = entityOperations::entOp_List;
                break;
            case "Delete":
                $output = entityOperations::entOp_Delete;
                break;
            case "Update":
                $output = entityOperations::entOp_Update;
                break;
            case "New":
                $output = entityOperations::entOp_New;
                break;
            case "Insert":
                $output = entityOperations::entOp_Insert;
                break;
            case "AjaxUpdate":
                $output = entityOperations::entOp_AjaxUpdate;
                break;
            case "DisplaySort":
                $output = entityOperations::entOp_DisplaySort;
                break;
            case "UpdateSort":
                $output = entityOperations::entOp_UpdateSort;
                break;
            default:
                $output = entityOperations::entOp_List;
                break;
        }
        return $output;
    }
}

class entities {
    
    public $conn;
    public $route_mode;
    
    public $table_name;
    public $disp_name;
    public $id_col;
    public $active_col;
    public $disp_col;
    public $sort_col;
    
    public $sql;
    public $items = array();
    
    public $iterator;
    public $curr;
    
    public $col_settings = array();
    public $cols = array();
    public $table_settings = array();
    public $curr_operation;
    
    public $data;
    public $number_of_filtered_columns;
    public $all_count;
    
    public $page;
    
    public $suppress;
    public $query;
    
    function __construct(){
        $this->iterator = 0;
        $this->number_of_filtered_columns = 0;
        $this->getSettings();
        $this->getColumnArray();
    }
    
    public static function newEnt() {
        $obj = new static();
        return $obj;
    }
    
    function valueOf($col_name){
        return $this->curr->$col_name;
    }
    
    function getSettings(){
        //echo "Gettings Settings";
    }
    
    function getColumnArray(){
        
    }
    
    //
    // SORTING
    //
    
    function displaySort(){
        while($this->next()){
            $name = $this->disp_col;
            $items .= htmlElement::div($this->curr->$name, $this->valueOf($this->id_col), "ui-state ui-state-default");
        }
        $disp = htmlElement::div($items, null, "sortable");
        $disp .= htmlElement::textBox("sortable_data", "hidden");
        $disp .= htmlElement::submit("Edit","btn btn-default");
        $action = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_UpdateSort);
        $form = htmlElement::form($disp, "foundryEntityEditForm", $action, "POST");
        return $form;
    }
    
    function updateSort(){
        
        $order = $this->data["sortable_data"];
        $order_arr = explode(",", $order);
        $query = "UPDATE $this->table_name SET $this->sort_col = CASE $this->id_col ";
        $i = 0;
        foreach($order_arr as $item){
            $query .= sprintf("WHEN %d THEN %d ", $item, $i);
            $i++;
        }
        $query .= "END WHERE id IN ($order)";
        $sql = new sqlQuery($GLOBALS["conn"], $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        $link = $this->generateLink(entityOperations::entOp_List, null);
        header('Location: '.basename($_SERVER['PHP_SELF']).$link);
    }
  
    
    //
    //QUERY PROCESSING
    //
    
    function executeQuery($query, $type = sqlQueryTypes::sqlQueryTypeSELECT){
        //echo $query;
        $this->query = $query;
        $this->sql = new sqlQuery($this->conn, $query, $type);
        $this->iterator = 0;
        $this->mapColumns();
    }
    
    function getCount($query){
       $sql = new sqlQuery($this->conn, $query);
       return $sql->rows[0]["total"];
    }
    
    function mapColumns(){
        foreach($this->sql->rows as $row){
            $ent = new self();
            $keys = array_keys($row);
            foreach($keys as $key){
                $ent->$key = $row[$key];
            }
            array_push($this->items,$ent);
        }
    }
    
    //
    //ITERATORS
    //
    
    function first(){
        return $this->items[0];
    }
    
    function next(){
        if($this->iterator < sizeof($this->items)){
            $this->curr = $this->items[$this->iterator];
            $this->iterator++;
            return true;
        } else {
            return false;
        }
    }
    
    //
    //SELECT QUERIES
    //
   
    function getAll($only_active = FALSE){
        $qb = new sqlQueryBuilder($this->table_name);
        $qb->column("name");
        $qb->column("type as test");
        $qb->where("active", 1);
        $qb->where("total", 3);
        //$qb->limit_start = 4;
        $qb->limit_length = 10;
        //general::pretty($qb->render());
        if(!$only_active){
            $query = "SELECT * FROM $this->table_name";
            $count_query = "SELECT COUNT(".$this->id_col.") as total FROM $this->table_name";
        } else {
            $query = "SELECT * FROM $this->table_name WHERE $this->active_col = 1";
            $count_query = "SELECT COUNT(".$this->id_col.") as total FROM $this->table_name WHERE $this->active_col = 1";
        }
        $sort = $this->table_settings[entityTableSettings::entSet_SortBy];
        if(!string::IsNullOrEmptyString($sort)){
            $query .= " ORDER BY $sort";
        }
        $this->all_count = $this->getCount($count_query);
        if($this->table_settings[entityTableSettings::entSet_EnablePaging] == true){
            $query .= " LIMIT ".($this->page*25).", 25";
        }
        $this->executeQuery($query);
        return $this;
        
        
    }
    
    function getAllWithFilters(){
        $keys = array_keys($this->col_settings);
        $col_arr = array();
        foreach($keys as $key){
            
            $type = $this->getType($key);
            
                switch($type){
                    case entityColTypes::Date:
                        $val_start = $this->data[$key."_start"];
                        $end_start = $this->data[$key."_end"];
                        if(!string::IsNullOrEmptyString($val_start) && !string::IsNullOrEmptyString($end_start)){
                            $start_date = strtotime($this->data[$key."_start"]);
                            $end_date = strtotime($this->data[$key."_end"]);
                            array_push($col_arr, $key." BETWEEN $start_date AND $end_date");   
                        }
                        break;
                    default:
                        $val = $this->data[$key];
                        if($val != "All" && !string::IsNullOrEmptyString($val)){
                            array_push($col_arr, $key." LIKE '%".$val."%'");
                        }
                        break;
                }
        }
        $query = "SELECT * FROM ".$this->table_name;
        $count_query = "SELECT COUNT(".$this->id_col.") FROM ".$this->table_name;
        if(sizeof($col_arr) > 0){
            $query .= " WHERE " .implode(" AND ", $col_arr);
            $count_query .= " WHERE " .implode(" AND ", $col_arr);
        }
        $sort = $this->table_settings[entityTableSettings::entSet_SortBy];
        if(!string::IsNullOrEmptyString($sort)){
            $query .= " ORDER BY $sort";
        }
        $this->all_count = $this->getCount($count_query);
        $query .= " LIMIT ".($this->page*25).", 25";
        $this->executeQuery($query);
        return $this;
    }
    
    function getById($id){
        $query = "SELECT * FROM $this->table_name WHERE $this->id_col = $id";
        $this->executeQuery($query);
        return $this;
    }
    
    function getAllWhere($where){
        $query = "SELECT * FROM $this->table_name WHERE $where";
        $this->executeQuery($query);
        return $this;
    }
    
    function hasRows(){
        if(sizeof($this->items) > 0){
            return true;
        } else {
            return false;
        }
    }
    
    function numRows(){
        if(sizeof($this->items) > 0){
            return sizeof($this->items);
        } else {
            return 0;
        }
    }
    
    //
    //FOREIGN KEY
    //
    
    function getFKDisplay(){
        $ent = $this->first();
        $dc = $this->disp_col;
        return $ent->$dc;
    }
    
    function getFKSelect($ref_col, $id , $initial_option = null, $attr = null, $class = null){
//        htmlElement::select($data, $disp_col, $id_col, $id, $class, $selected_id, $selected_name, $initial_option);
        return htmlElement::select($this->sql->rows, $this->disp_col, $this->id_col, $ref_col, "chosen ".$class, $id, null, $initial_option, $attr);
        //return "test";
    }
    
    function asSelect($id, $class = null, $selected_id = null, $selected_name = null, $initial_option = null){
        return htmlElement::select($this->sql->rows, $this->disp_col, $this->id_col, $id, $class, $selected_id, $selected_name, $initial_option);
    }
    
    //
    //COL SETTINGS
    //
    
    function addColSetting($colName, $settingName, $settingValue){
        $col_setting_arr = $this->col_settings[$colName];
        if($col_setting_arr == null){
            $col_setting_arr = array();
        }
        $col_setting_arr = general::array_push_assoc($col_setting_arr, $settingName, $settingValue);
        $this->col_settings = general::array_push_assoc($this->col_settings, $colName, $col_setting_arr);
    }
    
    function addTableSetting($settingName, $settingValue){
        $this->table_settings = general::array_push_assoc($this->table_settings, $settingName, $settingValue);
    }
    
    function getValueOfColSetting($colName, $settingName){
        $col_setting_arr = $this->col_settings[$colName];
        if($col_setting_arr == null){
            return null;
        } else {
            return $col_setting_arr[$settingName];
        }
    }
    
    function getColumnDisplayName($colName){
       $col_setting_arr = $this->col_settings[$colName];
        if($col_setting_arr == null){
            return $colName;
        } else {
            $name = $col_setting_arr[entityColSettings::entSet_DisplayName];
            if($name == null){
                $name = $colName;
            }
            return $name;
        } 
    }
    
    function getType($colName){
        $col_setting_arr = $this->col_settings[$colName];
        $type = $col_setting_arr[entityColSettings::entSet_Type];
        return $type;
    }
    
    function getColumnInsertValue($colName, $initial_option = null, $initial_value = null){
        $type = $this->getType($colName);
        if($this->curr_operation == entityOperations::entOp_Edit){
            $row_val = $this->first()->$colName;
        } elseif($this->curr_operation == entityOperations::entOp_List){
            $row_val = $initial_value;
        }else {
            $row_val = null;
        }
        $class = "form-control";
        switch($type){
            case entityColTypes::Bool:
                $data = array( array("disp" => "Yes","val" => "1"),array("disp" => "No","val" => "0"));
                $val = htmlElement::select($data, "disp", "val", $colName, "form-control", $row_val);
                break;
            case entityColTypes::ForeignKey:
                $key_table = $this->getValueOfColSetting($colName, entityColSettings::entSet_ForeignKeyCol);
                $attr = null;
                if($this->getValueOfColSetting($colName, entityColSettings::entSet_Format) == entityColFormats::entColFormat_MediaSelector){
                    $attr = array("data-img-src,path");
                    $class = "imgSelect";
                }
                $val = $this->getColForeignKeySelect($key_table, $colName, $row_val, $initial_option, null, $attr, $class);
                break;
            case entityColTypes::MultiText:
                if($this->getValueOfColSetting($colName, entityColSettings::entSet_Format) == entityColFormats::entColFormat_PlainText){
                    $class = "form-control";
                } else {
                    $class = "redactor";
                }
                $val = htmlElement::textArea($row_val, $colName, $class);
                break;
            case entityColTypes::Date:
                if($initial_option == "All"){
                    $val = htmlElement::textBox($colName."_start", "datepicker form-control", $initial_value[0])." to: ".htmlElement::textBox($colName."_end", "datepicker form-control", $initial_value[1]);
                } else {
                    $val = htmlElement::textBox($colName, "datepicker form-control", ts::getShortDate($row_val));
                }
                break;
            case entityColTypes::entImage:
                $val = htmlElement::fileUpload($colName, "foundryEntityImageUpload", $row_val).htmlElement::textBox($colName, "hidden", null);
                break;
            Default:
                $val = htmlElement::textBox($colName, $class, $row_val);
                break;
        }
        return $val;
    }
    
    function getColumnFormattedValue($colName){
        $raw_value = $this->curr->$colName;
        $type = $this->getType($colName);
        switch($type){
            case entityColTypes::Bool:
                $val = $this->getBoolDisplay($raw_value);
                break;
            case entityColTypes::URL:
                $val = $this->getURLDisplay($raw_value);
                break;
            case entityColTypes::ForeignKey:
                $key_table = $this->getValueOfColSetting($colName, entityColSettings::entSet_ForeignKeyCol);
                $val = $this->getColForeignKeyDisplay($key_table, $raw_value);
                break;
           case entityColTypes::Date:
               if($this->getValueOfColSetting($colName, entityColSettings::entSet_Format) == entityColFormats::entColFormat_DateAndTime){
                   $val = ts::getFullDateTime($raw_value);
               } else {
                    $val = ts::getShortDate($raw_value);
               }
               break;
           case entityColTypes::entImage:
               if($this->curr_operation == entityOperations::entOp_List){
                   $path = $this->getValueOfColSetting($colName, entityColSettings::entSet_PathToListImage);
                   if(string::IsNullOrEmptyString($path)){
                       $path = "media/128/";
                   } 
               }
               if($this->curr_operation == entityOperations::entOp_Detail){
                   $path = "media/512/";
               }
               $full_size_path = $this->getValueOfColSetting($colName, entityColSettings::entSet_PathToFullSizeImage);
               if(string::IsNullOrEmptyString($full_size_path)){
                   $full_size_path = "media/1024/";
               }
               
               $img = htmlElement::img($path.$raw_value, "foundryEntityImageThumbnail");
               
               $link = htmlElement::a($img, $full_size_path.$raw_value, null, "popup");
               //$val = htmlElement::img($path.$raw_value, "foundryEntityImageThumbnail");
               $val = $link;
               //$val = $raw_value;
               break;
           default:
               $val = $raw_value;
               break;
        }
        return $val;
    }
    
    function getColumnValueForUpdateOrInsert($colName, $input_value){
        $type = $this->getType($colName);
        switch($type){
           case entityColTypes::Date:
                if($this->getValueOfColSetting($colName, entityColSettings::entSet_Auto) === true){
                    $val = time();
                } else {
                    $val = strtotime($input_value);
                }
               break;
           case entityColTypes::entImage:
               $uploaded_file = $_FILES[$colName]["tmp_name"];
               $filename = $_FILES[$colName]["name"];
               $ext = filesystem::getFileExtension($filename);
               $new_filename = general::getUUID().".".$ext;
               $base_path = filesystem::getParentDirPath(__DIR__)."/media";
               $full_path = $base_path."/original/".$new_filename;
               if(move_uploaded_file($_FILES[$colName]["tmp_name"],$full_path)){
                   $val = $new_filename;
               } else {
                   //$val = $_FILES[$colName]["error"];
                   $val = $base_path;
               }
               $name = $new_filename;
               exec("convert '$base_path/original/".$name."' -resize 1024x1024 '$base_path/large/".$name."'");
               exec("convert '$base_path/original/".$name."' -resize 512x512 '$base_path/medium/".$name."'");
               exec("convert '$base_path/original/".$name."' -resize 256x256 '$base_path/small/".$name."'");
               exec("convert '$base_path/original/".$name."' -resize 128x128 '$base_path/thumbnail/".$name."'");
               break;
           default:
               $val = sqlQuery::escape($input_value);
               break;
       }
       return $val;
    }
    
    //
    //COLUMN FORMATTING
    //
    
    function getBoolDisplay($val){
        if($val == 1){
            return "YES";
        } else {
            return "NO";
        }
    }
    
    function getURLDisplay($val){
        return htmlElement::a($val, $val);
    }
    
    function getColForeignKeyDisplay($key_table, $id){
        $ent = new $key_table;
        $ent->getById($id);
        return $ent->getFKDisplay();
    }
    
    function getColForeignKeySelect($key_table, $ref_col, $id = null, $initial_option = null, $initial_value = null, $attr = null, $class = null){
        $ent = new $key_table;
        $ent->curr_operation = entityOperations::entOp_GetFKSelect;
        $ent->getAll(false);
        return $ent->getFKSelect($ref_col, $id, $initial_option, $attr, $class);
    }
    
    //
    //CRUD OPERATIONS
    //
	
    function newItemForm($withChecked = false){
        $this->curr_operation = entityOperations::entOp_New;
        $t = new table($this->disp_name);
        $t->class = "table table-bordered";
        $keys = array_keys($this->col_settings);
        foreach($keys as $key){
            if($this->getValueOfColSetting($key, entityColSettings::entSet_ShowInInsert) !== false && $this->id_col != $key && $this->active_col != $key){
                $t->addRow("<strong>".$this->getColumnDisplayName($key)."</strong>", $this->getColumnInsertValue($key));
            }
        }
        $action = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_Insert, null);
        $disp = $t->render();
        if(!$withChecked){
            $disp .= "<p>Insert Another Record: <input type=\"checkbox\" value=\"InsertAgain\" name=\"foundryEntityInsertAgain\" id=\"foundryEntityInsertAgain\"></p>";
        } else {
            $disp .= "<p>Insert Another Record: <input type=\"checkbox\" value=\"InsertAgain\" name=\"foundryEntityInsertAgain\" id=\"foundryEntityInsertAgain\" checked=\"checked\"></p>";    
        }
        $disp .= htmlElement::submit("Add", "foundryEntityNewSubmit");
        $form = htmlElement::form($disp, "foundryEntityNewItemForm", $action, "POST");
        return htmlElement::div($form, null, "grid_12");
    }
    
    function editItemForm(){
        $idCol = $this->id_col;
        $data = $this->first()->$idCol;
        $this->curr_operation = entityOperations::entOp_Edit;
        $t = new table();
        $t->class = "table table-bordered";
        $keys = array_keys($this->col_settings);
        foreach($keys as $key){
            if($this->getValueOfColSetting($key, entityColSettings::entSet_ShowInInsert) !== false && $key != $idCol && $this->getValueOfColSetting($key, entityColSettings::entSet_Auto) !== true){
                $t->addRow($this->getColumnDisplayName($key), $this->getColumnInsertValue($key));
            }
        }
        $disp .= htmlElement::div($t->render(), null, "table-responsive");
        $disp .= htmlElement::textBox($this->id_col, "hidden", $data);
        $disp .= htmlElement::submit("Edit","btn btn-default");
        $data = $this->first()->$idCol;
        $action = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_Update, $data);
        $form = htmlElement::form($disp, "foundryEntityEditForm", $action, "POST");
        $disp = bootstrap::pageHeader($this->disp_name);
        $disp .= htmlElement::div($form, null);
        return $disp;
    }
    
    function delete(){
        $id = $this->data[$this->id_col];
        $query = "DELETE FROM $this->table_name WHERE $this->id_col = $id ";
        //$query = "UPDATE $this->table_name SET $this->active_col = 0 WHERE $this->id_col = $id";
        $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
        if($this->suppress == "yes"){
            //return $query;
           return $sql->response;
       } else {
        return $this->afterDelete($sql->response);
       }
    }
    
    function afterDelete($input){
        $link = $this->generateLink(entityOperations::entOp_List, null);
        header('Location: '.basename($_SERVER['PHP_SELF']).$link);
    }
    
    function update(){
       $id = $this->data[$this->id_col];
       $keys = array_keys($this->data);
       $set_arr = array();
       foreach($keys as $key){
           if($key != $this->id_col){
               if($this->getValueOfColSetting($key, entityColSettings::entSet_Type) == entityColTypes::entImage){
                   if(!String::IsNullOrEmptyString($this->data[$key])){
                       $val = $this->getColumnValueForUpdateOrInsert($key, $this->data[$key]);
                       array_push($set_arr, $key." = '".$val."'");
                   }
               } else {
                $val = $this->getColumnValueForUpdateOrInsert($key, $this->data[$key]);
                array_push($set_arr, $key." = '".$val."'");
               }
           }
       }
       $query = "UPDATE $this->table_name SET ".implode(", ", $set_arr)." WHERE $this->id_col =  $id";
       $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeUPDATE);
       //return $query;
       if($this->curr_operation == entityOperations::entOp_AjaxUpdate){
           return json_encode($this->getById($id)->first());
       } else {
           return $this->afterUpdate($sql->response);
       }
    }
    
    function afterUpdate($input){
       // if($input == 1){
            $link = $this->generateLink(entityOperations::entOp_List, null);
            header('Location: '.basename($_SERVER['PHP_SELF']).$link);
       // } else {
        //    return "There was an error updating";
       // }
    }
    
    function insertItem(){
       $keys = array_keys($this->data);
       $col_arr = array();
       $val_arr = array();
       foreach($keys as $key){
           if($key != $this->id_col && $key != "foundryEntityInsertAgain"){
               $val = $this->getColumnValueForUpdateOrInsert($key, $this->data[$key]);
               array_push($col_arr, $key);
               array_push($val_arr, $val);
           }
       }
       $query = "INSERT INTO $this->table_name (".implode(",", $col_arr).") VALUES ('".implode("','", $val_arr)."')";
       $sql = new sqlQuery($this->conn, $query, sqlQueryTypes::sqlQueryTypeINSERT);
       //return $query;
       //print $query;
       //general::pretty($query);
       if($this->suppress == "yes"){
           return $sql->response;
       } else {
           if($this->data['foundryEntityInsertAgain'] == "InsertAgain"){
                return $this->newItemForm(true);
           } else {
                //return $query;      
                return $this->afterInsert($sql->response);
           }
       }
    }
    
    function saveAsNew(){
        $this->suppress = "yes";
        $arr = get_object_vars($this);
        foreach($arr as $key => $value){
            if(!in_array($key ,$this->cols)){
                unset($arr[$key]);
            }
        }
        $this->data = $arr;
        $this->insertItem();
    }
    
    function afterInsert($input){
        $link = $this->generateLink(entityOperations::entOp_List, null);
        header('Location: '.basename($_SERVER['PHP_SELF']).$link);
    }
    
    function getDetails(){
        $disp = bootstrap::pageHeader($this->disp_name);
        $this->next();
        $this->curr_operation = entityOperations::entOp_Detail;
        $t = new table();
        //$t->headers = array("", "");
        $t->class = "table table-striped table-bordered";
        $keys = array_keys($this->col_settings);
        foreach($keys as $key){
            if($this->getValueOfColSetting($key, entityColSettings::entSet_ShowInDetail) !== false && $this->id_col != $key){
                $t->addRow($this->getColumnDisplayName($key), $this->getColumnFormattedValue($key));
            }
        }
        $disp .= htmlElement::div($t->render(), null);
        return $disp;
    }
    
    function listItems($isFiltered = false){
	$this->curr_operation = entityOperations::entOp_List;
        $t = new table();
        $t->class = "table table-striped table-hover";
        $keys = array_keys($this->col_settings);
        $first = true;
        $header_arr = array();
        $col_arr = array();
        $options_arr = $this->getTableOptions(false);
        $use_options = false;
        if(sizeof($options_arr > 0)){
            $use_options = true;
           array_push($header_arr, "Options"); 
        }
        foreach($keys as $key){
                if($this->getValueOfColSetting($key, entityColSettings::entSet_ShowInList) !== false){
                    array_push($header_arr, $this->getColumnDisplayName($key));
                    array_push($col_arr, $key);
                }
        }
        $t->headers = $header_arr;
        while($this->next()){
            $row_arr = array();
            if($use_options){
                $options_arr = $this->getListTableOptions();
                $opt_disp = implode($options_arr);
                array_push($row_arr, $opt_disp);
            }
            foreach($col_arr as $col){
                array_push($row_arr, $this->getColumnFormattedValue($col));
            }
            $t->buildRow($row_arr);
        }
        $btns = "<div class=\"btn-group pull-right\">";
        $btns .= "<button type=\"button\" class=\"btn btn-default\"><a href=\"".$this->generateLink(entityOperations::entOp_New, null)."\"><span class=\"glyphicon glyphicon-plus\"></span></a></button>";
        if($this->table_settings[entityTableSettings::entSet_AllowSorting] !== false){
            $btns .= "<button type=\"button\" class=\"btn btn-default\" ><a href=\"".$this->generateLink(entityOperations::entOp_DisplaySort, null)."\"><span class=\"glyphicon glyphicon-sort\" ></span></a></button>";
        }
        $btns .= "<button type=\"button\" class=\"btn btn-default\" ><span class=\"glyphicon glyphicon-filter\" data-toggle=\"collapse\" data-target=\"#collapse\"></span></button>";
        $btns .= "</div>";

        $title = htmlElement::div(
                "<h1>".$this->disp_name.$btns."</h1>", 
                null, 
                "page-header"
                );
        $filter_title = htmlElement::h("filters", 4);
        $filter_table = $this->getTableFilters();
        
        if($this->number_of_filtered_columns > 0){
           $filter_submit = htmlElement::submit("Filter", "btn btn-default"); 
           $link = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_List, null);
           $filter_submit .= htmlElement::button("Clear", "foundryEntityFilterClear", "btn btn-default", "window.location='$link'");
        } else {
            $filter_submit = null;
        }
        
        $action = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_List, null)."&filtered=yes";
        $filter_form = htmlElement::form($filter_table.$filter_submit, "foundryEntityFilterForm", $action, "POST");
        $filter_collapse = htmlElement::div($filter_form, "foundryEntityTableFiltersCollapse", "foundryEntityTableFiltersCollapse");
        $filters = htmlElement::div($filter_title.$filter_collapse, "foundryEntityTableOptions", "foundryEntityTableOptions col-md-6");
		
	$options_title = htmlElement::h("options", 4);
	$options_collapse = htmlElement::div($this->getTableOptions(), "foundryEntityTableOptionsCollapse", "foundryEntityTableOptionsCollapse");
        $options = htmlElement::div($options_title.htmlElement::div($options_collapse), "foundryEntityTableOptions", "foundryEntityTableFilters col-md-6");
        
        if($isFiltered){
            $collapse = null;
        } else {
            $collapse = "collapse";
        }
        $top = htmlElement::div($filters.$options,"collapse",$collapse);
        $table = htmlElement::div($t->render(), "foundryEntityTable", "table-responsive");
        $next_link = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_List, null);
        $prev_link = basename($_SERVER['PHP_SELF']).$this->generateLink(entityOperations::entOp_List, null);
        $next_page_num = $this->page + 1;
        $next_link .= "&page=".$next_page_num;
        $next = "<li class=\"\">".htmlElement::a("Next", $next_link, null, "foundryEntityTablePageLink")."</li>";
        $prev_page_num = $this->page - 1;
        if($prev_page_num < 0){
            $prev_page_num = 0;
        }
        $prev_link .= "&page=".$prev_page_num;
        $prev = "<li class=\"\">".htmlElement::a("Prev", $prev_link, null, "foundryEntityTablePageLink")."</li>";
        $page = "<div><ul class=\"pager pull-left\">".$prev.$next."</ul></div>";
        $disp = $title;
        $disp .= $top.$table.$page;
        //return $this->query;
        return $disp;
    }
    
    function getListTableOptions($use_val = true){
        $options_arr = array();
        if($use_val){
            $idCol = $this->id_col;
            $id = $this->curr->$idCol;
        } else {
            $id = null;
        }
        if($this->table_settings[entityTableSettings::entSet_ShowEditInList] !== false){
            //array_push($options_arr, htmlElement::a(htmlElement::img("foundry/img/edit.png", "foundyTableOptionImg"), $this->generateLink(entityOperations::entOp_Edit, $id)));
            array_push($options_arr, htmlElement::a("<i class=\"fa fa-pencil-square-o\"></i>", $this->generateLink(entityOperations::entOp_Edit, $id)));
         }
         if($this->table_settings[entityTableSettings::entSet_ShowDetailInList] !== false){
            array_push($options_arr, htmlElement::a(htmlElement::img("foundry/img/browse.png", "foundyTableOptionImg"), $this->generateLink(entityOperations::entOp_Detail, $id)));
         }
         if($this->table_settings[entityTableSettings::entSet_ShowDeleteInList] !== false){
             array_push($options_arr, htmlElement::a(htmlElement::img("foundry/img/delete.png", "foundyTableOptionImg"), $this->generateLink(entityOperations::entOp_Delete, $id)));
         }
         return $options_arr;
    }
    
    function getTableFilters(){
        $t = new table();
        $t->class = "table";
        $keys = array_keys($this->col_settings);
        foreach($keys as $key){
             if($this->getValueOfColSetting($key, entityColSettings::entSet_AllowFilter) == true){
                $this->number_of_filtered_columns++;
                if($this->getValueOfColSetting($key, entityColSettings::entSet_Type) == entityColTypes::Date ){
                    $arr = array($this->data["time_start"], $this->data["time_end"]);
                    $t->addRow($this->getColumnDisplayName($key), $this->getColumnInsertValue($key, "All", $arr));
                } else {
                    $t->addRow($this->getColumnDisplayName($key), $this->getColumnInsertValue($key, "All", $this->data[$key]));
                }
            }
        }
        $disp = $t->render();
        return $disp;
    }
    
    function getTableOptions(){
        $t = new table();
        $t->addRow(htmlElement::a("Add New", $this->generateLink(entityOperations::entOp_New, null)));
        return $t->render();
    }
    
    function generateLink($Operation, $Data){
        return $this->generateLinkStatic($this->table_name, $this->route_mode, $Operation, $Data);
    }
    
    function generateLinkStatic($Ent_Name, $Mode, $Operation, $Data = null){
        $op = entityRouter::convertOperationEnum($Operation);
        switch($Mode){
            case entityRouterModes::entRouterMode_Default:
                $disp = "?entity=$Ent_Name&operation=".$op;
                if($Data != null){
                    $disp .= "&data=$Data";
                }
                break;
            default:
                $disp = "no route mode";
        }
        return $disp;
    }
}



?>
