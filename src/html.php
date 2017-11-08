<?php

class htmlAttr {
    public $name;
    public $value;
    
    public function htmlAttr($name, $value){
        $this->name = $name;
        $this->value = $value;
    }
}

class htmlElement {
    public $tag;
    public $id;
    public $name;
    public $class;
    public $disp;
    public $onclick;
    public $attrArr;
    public $content;
    public $propArr;
    
    function __construct($tag, $id = null, $name = null, $class = null, $onclick = null) {
        $this->tag = $tag;
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->onclick = $onclick;
        $this->content = array();
        $this->attrArr = array();
        $this->propArr = array();
        $this->getDefaultAttr();
    }
    
    public function getDefaultAttr(){
        array_push($this->attrArr, new htmlAttr("id", $this->id));
        array_push($this->attrArr, new htmlAttr("name", $this->name));
        array_push($this->attrArr, new htmlAttr("class", $this->class));
        array_push($this->attrArr, new htmlAttr("onclick", $this->onclick));
    }
    
    public function buildElement(){
        $disp = "<".$this->tag." ";
        foreach($this->attrArr as $attr){
            if($attr->value != null){
                $disp .= $attr->name."=\"".$attr->value."\" ";
            }
        }
        $disp .= join(" ", $this->propArr);
        $disp .= " >";
        foreach($this->content as $content){
            $disp .= $content;
        }
        $disp .= "</".$this->tag.">";
        $this->disp = $disp;
    } 
    
    public function addProp($prop){
        array_push($this->propArr, $prop);
    }
    
    public function addAttr($attr){
        array_push($this->attrArr, $attr);
    }
    
    public function addContent($content){
        array_push($this->content, $content);
    }
    
    public function display(){
        $this->buildElement();
        return $this->disp;
    }
    
   function a($title, $href = null, $id = null, $class = null, $onclick = null, $name = null, $itemprop = null){
        $a = new htmlElement("a", $id, $name, $class, $onclick);
        $a->addAttr(new htmlAttr("href", $href));
        $a->addAttr(new htmlAttr("itemprop", $itemprop));
        $a->addContent($title);
        return $a->display();
    }
    
    function h($title, $size, $class = null){
        $h = new htmlElement("h".$size, null, null, $class);
        $h->addContent($title);
        return $h->display();
    }    
    
    function p($content, $class, $id){
        $p = new htmlElement("p", $id, null, $class);
        $p->addContent($content);
        return $p->display();
    }
    
    function img($src, $class, $height = null, $width = null, $alt = null, $title = null){
        $img = new htmlElement("img", null, null, $class);
        $img->addAttr(new htmlAttr("height", $height));
        $img->addAttr(new htmlAttr("src", $src));
        $img->addAttr(new htmlAttr("width", $width));
        $img->addAttr(new htmlAttr("alt", $alt));
        $img->addAttr(new htmlAttr("title", $title));
        return $img->display();
    }
    
    function div($content, $id = null, $class = null, $onclick = null, $name = null, $itemscope = null, $itemtype = null){
        $div = new htmlElement("div", $id, $name, $class, $onclick);
        $div->addAttr(new htmlAttr("itemscope", $itemscope));
        $div->addAttr(new htmlAttr("itemtype", $itemtype));
        $div->addContent($content);
        return $div->display();
    }
    
    //input 
    function textBox($id, $class = null, $value = null){
        $t = new htmlElement("input", $id, null, $class);
        $t->addAttr(new htmlAttr("name", $id));
        $t->addAttr(new htmlAttr("value", $value));
        $t->addAttr(new htmlAttr("type", "text"));
        return $t->display();
    }
    
    function fileUpload($id, $class, $value){
        $t = new htmlElement("input", $id, null, $class);
        $t->addAttr(new htmlAttr("name", $id));
        $t->addAttr(new htmlAttr("value", $value));
        $t->addAttr(new htmlAttr("type", "file"));
        return $t->display();
    }
    
    function button($value, $id, $class, $onclick){
        $b = new htmlElement("input", $id, null, $class, $onclick);
        $b->addAttr(new htmlAttr("type", "button"));
        $b->addAttr(new htmlAttr("value", $value));
        return $b->display();
    }
    
    function textArea($content, $id, $class){
        $t = new htmlElement("textArea", $id, null, $class);
        $t->addAttr(new htmlAttr("name", $id));
        $t->addContent($content);
        return $t->display();
    }
    
    function password($id, $class = null, $value = null){
        $t = new htmlElement("input", $id, $id, $class);
        $t->addAttr(new htmlAttr("value", $value));
        $t->addAttr(new htmlAttr("type", "password"));
        return $t->display();
    } 
    
    function script($src, $type = "text/javascript"){
        $j = new htmlElement("script");
        $j->addAttr(new htmlAttr("src", $src));
        return $j->display();
    }
    
    function style($src, $type = "text/javascript"){
        $j = new htmlElement("link");
        $j->addAttr(new htmlAttr("src", $src));
        $j->addAttr(new htmlAttr("rel", "stylesheet"));
        $j->addAttr(new htmlAttr("type", "text/css"));
        return $j->display();
    }
    
    function tr($content, $id, $class){
        $tr = new htmlElement("tr");
        $tr->addAttr(new htmlAttr("id", $id));
        $tr->addAttr(new htmlAttr("class", $class));
        $tr->addContent($content);
        return $tr->display();
    }
    
    function th($content, $id, $class, $colspan){
        $th = new htmlElement("th");
        $th->addAttr(new htmlAttr("id", $id));
        $th->addAttr(new htmlAttr("class", $class));
        $th->addAttr(new htmlAttr("colspan", $colspan));
        $th->addContent($content);
        return $th->display();
    }
    
    function td($content, $id, $class, $colspan){
        $td = new htmlElement("td");
        $td->addAttr(new htmlAttr("id", $id));
        $td->addAttr(new htmlAttr("class", $class));
        $td->addAttr(new htmlAttr("colspan", $colspan));
        $td->addContent($content);
        return $td->display();
    }
    
    function form($content, $id, $action, $method){
        $form = new htmlElement("form");
        $form->addAttr(new htmlAttr("action", $action));
        $form->addAttr(new htmlAttr("method", $method));
        $form->addAttr(new htmlAttr("id", $id));
        $form->addAttr(new htmlAttr("enctype", "multipart/form-data"));
        $form->addContent($content);
        return $form->display();
    }
    
    function submit($disp, $class = null){
        return "<input type=\"submit\" value=\"$disp\" class=\"$class\">";
    }
    
    function select(&$data, $disp_col, $id_col, $id = null, $class = null, $selected_id = null, $selected_name = null, $initial_option = null, $additional_attr = null, $multiple = false){
        $s = new htmlElement("select");
        $s->addAttr(new htmlAttr("id", $id));
        $s->addAttr(new htmlAttr("class", $class));
        if($initial_option){
            if(!is_array($initial_option)){
                $initial_option = array($initial_option, $null);
            }
            $v = new htmlElement("option");
            $v->addAttr(new htmlAttr("value", $initial_option[1]));
            $v->addContent($initial_option[0]);
            $s->addContent($v->display());
        }
        if($multiple){
            $s->addProp("multiple");
            $s->addAttr(new htmlAttr("name", $id."[]"));
        } else {
            $s->addAttr(new htmlAttr("name", $id));
        }
        foreach($data as $datum){
            $id = $datum[$id_col];
            $v = new htmlElement("option");
            $v->addAttr(new htmlAttr("value", $id));
            if($additional_attr != null){
                //general::pretty($additional_attr);
                foreach($additional_attr as $attr_group){
                    $attr_arr = explode(",", $attr_group);
                    //general::pretty($attr_arr);
                    $v->addAttr(new htmlAttr($attr_arr[0],$datum[$attr_arr[1]]));
                }
            }
            //if($id == $selected_id){
            if(in_array($id, explode(",",$selected_id))){
                $v->addAttr(new htmlAttr("selected", "selected"));
            }
//            if(is_string($disp_col)){
//                $v->addContent($datum[$disp_col]);
//            } else {
//                $v->addContent($disp_col->render($datum));                   
//            }
            $v->addContent(String::Format($datum, $disp_col));
            $s->addContent($v->display());
        }
        return $s->display();
    }

}

?>
