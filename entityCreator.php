<?php

include("include.php");

//$conn = new sqlConnection("localhost", "mySiteManager", "root", "2011Girdle");
$conn = new sqlConnection("localhost", "MediaLocker", "root", "D.mbass77");

function getTables(){
    $query = "SHOW TABLES FROM ".$GLOBALS["conn"]->database;
    $sql = new sqlQuery($GLOBALS["conn"], $query);
    return $sql->rows;
}

function getColumns($table){
   $query = "SHOW FULL COLUMNS FROM $table";
   $sql = new sqlQuery($GLOBALS["conn"], $query);
   return $sql->rows; 
}

function getFK($table, $col){
     $query = "select referenced_table_name, referenced_column_name from information_schema.key_column_usage where referenced_table_name is not null AND table_name = '".$table."' AND column_name = '".$col."'";
     $sql = new sqlQuery($GLOBALS["conn"], $query);
     $table_name = $sql->rows[0]["referenced_table_name"];
     $col_name = $sql->rows[0]["referenced_table_name"];
     if(!string::IsNullOrEmptyString($table_name) && !string::IsNullOrEmptyString($col_name)){
        //$disp = ", \"fkTable\" => \"".$sql->rows[0]["referenced_table_name"]."\", \"fkColumn\" => \"".$sql->rows[0]["referenced_column_name"]."\"";
         $disp = $sql->rows[0]["referenced_table_name"];
     } else {
         $disp = null;
     }
     return $disp;
}

echo "<?php\n\n";
echo "\$entities = array();\n\n";

$tables = getTables();

foreach($tables as $row){
    $table = $row["Tables_in_".strtolower($GLOBALS["conn"]->database)];
    $table_id_col;
    $table_disp_col;
    $cols = getColumns($table);
    echo "class ".$table." extends entities {\n\n";
    foreach($cols as $col){
        $field = $col["Field"];
        $key = $col["Key"];
        if($key == "PRI"){
            $table_id_col = $field;
        }
        $comment = $col["Comment"];
        if($comment == "disp"){
            $table_disp_col = $field;
        } 
        echo "\tpublic \$".$field.";\n";
    }
    if($table_disp_col == null){
        $table_disp_col = $table_id_col;
    }
    
    echo "\n\tfunction getColumnArray(){";
    foreach($cols as $col){
        $field = $col["Field"];
        echo "\n\t\tarray_push(\$this->cols,\"".$field."\");";
    }
    echo "\n\t}\n";
    
    echo "\n\tfunction getSettings(){";
    echo "\n\t\t\$this->conn = \$GLOBALS[\"conn\"];";
    echo "\n\t\t\$this->table_name = \"$table\";";
    echo "\n\t\t\$this->disp_name = \"$table\";";
    echo "\n\t\t\$this->id_col = \"$table_id_col\";";
    echo "\n\t\t\$this->active_col = \"active\";";
    echo "\n\t\t\$this->disp_col = \"$table_disp_col\";";
    echo "\n\t\t\$this->sort_col = \"sort_order\";";
    echo "\n\t}\n";
    echo "};\n\n";
    
    echo "\$$table = new $table();\n\n";
    
    foreach($cols as $col){
        $field = $col["Field"];
        $key = $col["Key"];
        if($key == "MUL"){
            $fk_setting = getFK($table, $field);
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_ShowInList, true);\n"; 
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_Type, entityColTypes::ForeignKey);\n"; 
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_ForeignKeyCol, \"".$fk_setting."\");\n"; 
        } elseif($field == "active"){
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_ShowInList, false);\n"; 
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_Type, entityColTypes::Bool);\n"; 
        } else {
            echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_ShowInList, true);\n"; 
        }
        $disp_name = ucwords(str_replace("_", " ", $field));
        echo "\$".$table."->addColSetting(\"$field\", entityColSettings::entSet_DisplayName, \"$disp_name\");\n";
    }
    
    echo "\n\$".$table."->addTableSetting(entityTableSettings::entSet_SortBy, \$".$table."->sort_col);";
    
    echo "\n\$entities = general::array_push_assoc(\$entities, \"$table\", \$$table);\n\n";
}

echo "?>";

?>

