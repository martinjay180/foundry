<?php

class sitemap {

    public $base_url;
    public $nodes = array();

    function __construct($baseUrl){
        $this->base_url = $baseUrl;
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
        $disp = "</urlset>";
        return $disp;
    }

}
