<?php

include("include.php");

$conn = new sqlConnection("localhost", "myosites", "root", "D.mbass77");
//$conn = new sqlConnection("localhost", "mySiteManager", "root", "2011Girdle");
$query = "SELECT * FROM sites";
$sql = new sqlQuery($conn, $query);

class sites extends entities {
    
    public $name;
    public $id;
    public $url;
    public $active;
    
    function getSettings(){
        $this->conn = $GLOBALS["conn"];
        $this->table_name = "sites";
        $this->disp_name = "Sites";
        $this->id_col = "id";
        $this->active_col = "active";
        $this->disp_col = "name";
    }
    
};

class pages extends entities {
    
    public $id;
    public $site_id;
    public $name;
    public $active;
    
    function getSettings(){
        $this->conn = $GLOBALS["conn"];
        $this->table_name = "pages";
        $this->disp_name = "Pages";
        $this->id_col = "id";
        $this->active_col = "active";
        $this->disp_col = "name";
    }
}

$sites = new sites();
$sites->addColSetting("id", entityColSettings::entSet_ShowInList, true);
$sites->addColSetting("name", entityColSettings::entSet_ShowInList, true);
$sites->addColSetting("name", entityColSettings::entSet_DisplayName, "Site Name");
$sites->addColSetting("url", entityColSettings::entSet_ShowInList, true);
$sites->addColSetting("url", entityColSettings::entSet_Type, entityColTypes::URL);
//$sites->addColSetting("active", entityColSettings::entSet_ShowInList, false);
$sites->addColSetting("active", entityColSettings::entSet_Type, entityColTypes::Bool);

$pages = new pages();
$pages->addColSetting("id", entityColSettings::entSet_ShowInList, t);
$pages->addColSetting("site_id", entityColSettings::entSet_ShowInList, true);
$pages->addColSetting("site_id", entityColSettings::entSet_Type, entityColTypes::ForeignKey);
$pages->addColSetting("site_id", entityColSettings::entSet_ForeignKeyCol, "sites");
$pages->addColSetting("site_id", entityColSettings::entSet_DisplayName, "Site");
$pages->addColSetting("name", entityColSettings::entSet_ShowInList, true);
$pages->addColSetting("active", entityColSettings::entSet_Type, entityColTypes::Bool);

$entities = array();
$entities = general::array_push_assoc($entities, "sites", $sites);
$entities = general::array_push_assoc($entities, "pages", $pages);

$nav = new navigation();
$entNav = new entityNavigation($entities, entityRouterModes::entRouterMode_Default);
$nav->addNode($entNav->render());

//$sites->getAll();
//$curr_site = $sites->getById(3)->first();
//$mySites = $sites->getAll();
//$p = $pages->getById(28);

$entRouter = new entityRouter($entities);
//$p = $entRouter->entity;
//$p->getAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
        
        <link rel="stylesheet" type="text/css" title="Red" href="css/960.css" />
        <link rel="stylesheet" type="text/css" title="Red" href="css/foundry.css" />
    </head>
    <body>
        <div class="container_12">
            <div id="nav" class="grid_12 foundryNav">
                <?php echo $nav->display();?>
            </div>
            <div id="main" class="grid_12">
                <?php     
                    //echo $mySites->listItems();
                    //echo $p->listItems();
                    //echo $p->editItemForm();
                echo $entRouter->render();
                ?>
            </div>
        </div>
    </body>
</html>
