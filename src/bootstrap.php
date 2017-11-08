<?php

//include_once "html.php";

class bootstrap {

    function row($content){
        return htmlElement::div($content, null, "row");
    }
    
    function col($content, $class, $id = null){
        return htmlElement::div($content, $id, $class);
    }
    
    function pageHeader($title, $small = null){
        if(!string::IsNullOrEmptyString($small)){
            $small = " <small>$small</small>";
        }
        return htmlElement::div(htmlElement::h($title.$small, 1), null, "page-header");
    }
    
    function breadcrumb($arr){
    	$disp = "<ol class=\"breadcrumb\">";
    	$size = sizeof($arr);
    	for($i = 0; $i <= $size; $i++){
    		$item = $arr[$i];
    		if($i == $size - 1){
    			$disp .= "<li class=\"active\">".$item[0]."</li>";
    		} else {
    			$disp .= "<li><a href=\"".$item[1]."\">".$item[0]."</a></li>";
    		}
    	}
		$disp .= "</ol>";
		return $disp;
    }
    
    function carousel($items){
//        $disp = '<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">';
//        $disp .= '<ol class="carousel-indicators">';
//        $disp .= '<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>';
//        $disp .= '<li data-target="#carousel-example-generic" data-slide-to="1"></li>';
//        $disp .= '</ol>';
//        $disp .= '<div class="carousel-inner">';
//        $i = 0;
//        foreach($items as $item){
//            $class = "item ".$item->item_class;
//            if($i == 0){
//                $class .= " active";
//            } 
//            $disp .= "<div class=\"".$class."\">";
//            $disp .= "<img src=\"".$item->img_src."\" alt=\"\">";
//            $disp .= '<div class="carousel-caption">';
//            $disp .= htmlElement::h($item->title,"3");
//            $disp .= htmlElement::p($item->caption);
//            $disp .= '</div>';
//            $disp .= '</div>';
//            $i++;
//        }
//        $disp .= '</div>';
//        $disp .= '<a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">';
//        $disp .= '<span class="glyphicon glyphicon-chevron-left"></span>';
//        $disp .= '</a>';
//        $disp .= '<a class="right carousel-control" href="#carousel-example-generic" data-slide="next">';
//        $disp .= '<span class="glyphicon glyphicon-chevron-right"></span>';
//        $disp .= '</a>';
//        $disp .= '</div>';
        foreach($items as $item){
            $disp .= htmlElement::div($item);
        }
        return htmlElement::div($disp, null, "col-md-12 slick");
    }
    
    function mediaList($items){
        foreach($items as $item){
            $img = htmlElement::img($src, $class, $height, $width, $alt, $title);
            $title = htmlElement::h($title, "4", "media-heading");
            $description = htmlElement::p($content, null, null);
            $body = htmlElement::div($title.$description, null, "media-body");
            $media = htmlElement::div($body, null, "media");
            $disp .= $media;
        }
        return $disp;
    }

}

class media_item {
    public $img_src;
    public $title;
    public $description;
}

class carousel_item {

    public $img_src;
    public $title;
    public $caption;
    public $link;
    public $item_class;
    
    public function carousel_item($img_src, $title, $caption, $link, $item_class){
        $this->img_src = $img_src;
        $this->title = $title;
        $this->caption = $caption;
        $this->link = $link;
        $this->item_class = $item_class;
    }
}
