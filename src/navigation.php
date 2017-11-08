<?php

include_once 'html.php';

class navigation {
    
    public $nodes = array();

    function __construct(){
        
    }
    
    function addNode($node){
        array_push($this->nodes, $node);
    }
    
    function display(){
        $ul = new htmlElement("ul", null, null, "nav navbar-nav");
        $ul->addContent("<li><div class='space'></div></li>");
        foreach($this->nodes as $node){
            $ul->addContent($node->getChildren());
        }
        return $ul->display();
    }
}

class navNode {
    
    public $children = array();
    public $a;
    public $href;
    public $title;
    
    function __construct($title, $href) {
        $this->title = $title;
        $this->href = $href;
        $this->a = htmlElement::a($title, $href, null, null, general::gaEvent("Link", "Nav", $title));
    }
    
    function addChild($child){
        array_push($this->children, $child);
    }
    
    function getChildren(){
        if(sizeof($this->children) > 0){
            $li = new htmlElement("li", null, null, "dropdown");
            $a = new htmlElement("a");
            $a->addAttr(new htmlAttr("href", "#"));
            $a->addAttr(new htmlAttr("data-toggle", "dropdown"));
            $a->addAttr(new htmlAttr("class", "data-toggle"));
            $a->addContent($this->title."<span class=\"caret\"></span>");
            $li->addContent($a->display());
            $ul = new htmlElement("ul", null, null, "dropdown-menu");
            foreach($this->children as $child){
                $ul->addContent($child->getChildren());
            }
            $li->addContent($ul->display());
        } else {
           $li = new htmlElement("li", null, null);
           $li->addContent($this->a);
        }
        return $li->display();
    }
}

?>

