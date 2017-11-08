<?php

class xmlsitemap {
    
    public $base_url;
    public $nodes = array();
    
    function __construct(){
      
    }
    
    function addNode($title, $lastMod, $changeFreq, $priority){
        array_push($this->nodes, array($title, $lastMod, $changeFreq, $priority));
    }
    
    function buildNode($title, $lastMod, $changeFreq, $priority){
        $disp = "<url> 
                    <loc>".$title."</loc>
                    <lastmod>".$lastMod."</lastmod>
                    <changefreq>".$changeFreq."</changefreq>
                    <priority>".$priority."</priority>
                  </url>";
        return $disp;
    }
    
    function render(){
        $disp = '<?xml version="1.0" encoding="UTF-8" ?>';
        $disp .= "<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd\">";
        foreach($this->nodes as $node){
            $disp .= $this->buildNode($node[0], $node[1], $node[2], $node[3]);
        }
        $disp .= "</urlset>";
        return $disp;
    }
    
}

class rssfeed {
    
    public $base_url;
    public $nodes = array();
    public $title;
    public $description;
    public $link;
    public $last_mod;
    
    function __construct($Title, $Description, $Link, $LastMod){
      $this->title = $Title;
      $this->description = $Description;
      $this->link = $Link;
      $this->last_mod = $LastMod;
    }
    
    function addNode($title, $description, $link, $pubDate){
        array_push($this->nodes, array($title, $description, $link, $pubDate));
    }
    
    function buildNode($title, $description, $link, $pubDate){
        $disp = "<item> 
                    <title>".$title."</title>
                    <description>".$description."</description>    
                    <link>".$link."</link>                        
                    <pubDate>".$pubDate."</pubDate>
                  </item>";
        return $disp;
    }
    
    function render(){
        //header("Content-Type: application/xml; charset=ISO-8859-1");
        $disp = '<?xml version="1.0" encoding="UTF-8" ?>';
        $disp .= '<rss version="2.0"><channel>';
        $disp .= "<title>$this->title</title>";
        $disp .= "<description>$this->description</description>";
        $disp .= "<link>$this->link</link>";
        $disp .= "<lastBuildDate>$this->last_mod</lastBuildDate>";
        $disp .= "<pubDate>$this->last_mod</pubDate>";
        $disp .= "<ttl>15</ttl>";
        foreach($this->nodes as $node){
            $disp .= $this->buildNode($node[0], $node[1], $node[2], $node[3]);
        }
        $disp .= "</channel>";
        $disp .= "</rss>";
        return $disp;
    }
    
}
