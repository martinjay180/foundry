<?php

class general {
	
	function pretty($arr){
		echo '<code><pre>';
		print_r($arr);
		echo '</pre></code>';
	}

    function array_push_assoc($array, $key, $value){
        $array[$key] = $value;
        return $array;
    }
    
    function getUUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }
    
    function getPublicObjectVars($obj) {
        return get_object_vars($obj);
    }
    
    function ipsum($num, $withTag = null){
        $a = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur elementum suscipit diam, ut sagittis nisl pellentesque ac. Ut eu est fringilla, iaculis dui eu, consequat lectus. Mauris neque lectus, dapibus sed arcu vitae, accumsan ultrices urna. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam gravida pharetra turpis eget suscipit. Integer eget mauris diam. Fusce sed enim eros. Mauris tincidunt condimentum nibh nec sodales. Etiam at mattis mauris, eu fringilla enim. In lacinia posuere odio vitae iaculis. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.";
        $b = "Aenean vehicula ornare augue, non ultricies ipsum aliquam eu. Quisque hendrerit egestas nulla, id imperdiet lorem tincidunt eu. Mauris et ante rhoncus, congue diam sit amet, bibendum diam. Suspendisse vel risus vel felis adipiscing elementum. Phasellus hendrerit euismod tortor, at gravida metus porta a. Cras pretium rutrum dolor nec pretium. Nullam dictum, quam vel interdum dictum, lacus tellus eleifend nibh, sit amet consequat quam erat vel quam. Suspendisse ullamcorper porta mauris, eget faucibus massa ultricies eu. Pellentesque rhoncus neque magna, sit amet dapibus ante elementum a. Donec volutpat fermentum lorem non tincidunt. Nulla porta vulputate nunc in interdum. Etiam molestie purus quam, sit amet euismod diam tempor non. Pellentesque nec porttitor enim.";
        $c = "Vestibulum ac convallis lacus, id ullamcorper ante. Nulla blandit nisl non nulla luctus blandit. Aliquam faucibus iaculis tempor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Vivamus augue arcu, posuere a metus gravida, blandit vestibulum eros. Etiam eu odio laoreet, tempus lorem ullamcorper, sodales quam. Curabitur consequat rhoncus sagittis. Nullam dignissim odio tortor, id porta turpis elementum quis. Aliquam vestibulum libero in molestie facilisis. In sit amet mi nec enim interdum faucibus. Ut id tellus vel felis ultrices consequat id ut lorem. Mauris adipiscing aliquam diam, eget pulvinar eros commodo a. Sed eget neque tortor. Praesent pharetra egestas nulla, quis interdum magna blandit nec. Phasellus adipiscing, augue id auctor mattis, ligula dui sagittis ligula, sed tincidunt nulla urna a turpis.";
        $d = "Nam commodo ipsum sed ligula feugiat, eu congue nisl sodales. Praesent libero urna, ultrices at pharetra id, rhoncus eget eros. Aliquam mollis velit vitae arcu malesuada, a cursus diam viverra. Praesent gravida cursus lacus at euismod. Sed at ultricies nunc. Maecenas id mollis risus. Aliquam malesuada lectus quis accumsan accumsan. Proin pretium turpis dolor, quis sollicitudin elit accumsan sed. Phasellus vitae metus eros.";
        $e = "Curabitur eleifend fringilla nunc, eu sodales nisl fringilla eu. Duis volutpat tincidunt velit, at semper turpis feugiat elementum. Mauris tincidunt turpis nibh, ut luctus risus semper at. Donec nec dignissim est. Ut pretium lorem rhoncus mi feugiat consectetur. Aliquam eu egestas nisi, eu tempus felis. Fusce rutrum vel enim ut malesuada. Vestibulum ipsum metus, lacinia in eleifend nec, placerat nec ante. Sed sed nulla at quam sollicitudin iaculis. Nullam sed metus non nisl dignissim scelerisque id a ante.";
        $paras = array($a, $b, $c, $d, $e);
        for($i = 0; $i < $num; $i++){
            $index = $i % 5;
            $text = $paras[$index];
            if(string::IsNullOrEmptyString($withTag)){
                $disp .= $text;
            } else {
                $disp .= "<$withTag>$text</$withTag>";  
            }
        }
        return $disp;
    }
    
    function gaEvent($category, $action, $label, $value = 0){
        return "ga('send', 'event', '$category', '$action', '$label', $value);";
    }

}
?>
