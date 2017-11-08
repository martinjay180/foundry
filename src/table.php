<?php

class table extends htmlElement {
    
    public $title;
    public $id;
    public $class;
    public $numCols;
    public $headers;
    public $rows;
    public $rowId;
    
    function __construct($title = null, $id = null, $class = null) {
        $this->title = $title;
        $this->id = $id;
        $this->class = $class;  
        $this->rows = array();
        $this->rowId = 0;
    }
    
    public function render(){
        $this->getNumberOfCols();
        $table = new htmlElement("table");
        $table->addAttr(new htmlAttr("class", $this->class));
        $table->addAttr(new htmlAttr("id", $this->id));
        $thead = new htmlElement("thead");
        if(!string::IsNullOrEmptyString($this->title)){
            $thead->addContent($this->buildTitle());
        }
        $thead->addContent($this->buildHeaders());
        $table->addContent($thead->display());
        $table->addContent($this->buildBody());
        return $table->display();
    }
    
    public function addRow(){
        $cols = func_get_args();
        $this->buildRow($cols);
    }
    
    function buildRow($cols, $rowId = null){
        $this->numCols = max(sizeof($cols), $this->numCols);
        foreach($cols as $col){
             $td = new htmlElement("td", "colId_".$this->colId);
             $td->addContent(($col));
             $content .= $td->display();
             $this->colId++;
             
         }
         if($this->rowNum % 2 == 0){
             $class = "foundryTableRow even";
         } else {
             $class = "foundryTableRow odd";
         }
         if($rowId != null){
            $tr = new htmlElement("tr", "rowId_".$rowId, null, $class);   
         } else {
             $tr = new htmlElement("tr", "rowId_".$this->rowId, null, $class);
         }
         $tr->addContent($content);
         array_push($this->rows, $tr);
         $this->rowId++;
         $this->rowNum++;
    }
    
    function buildTitle(){
        $th = htmlElement::td($this->title, null, null, $this->numCols);
        $tr = htmlElement::tr($th, null, "foundryTableTitle");
        return $tr;
    }
    
    function buildBody(){
        if($start === null){
            $start = 0;
        }
        if($end === null){
            $end = sizeof($this->rows);
        }
        for($i = $start; $i < $end; $i++){
            $tr = $this->rows[$i];
            $disp .= $tr->display();
        }
        $tableBody = new htmlElement("tbody");
        $tableBody->addContent($disp);
        return $tableBody->display();
    }
    
    function buildHeaders(){
        $tr = new htmlElement("tr");
        $tr->addAttr(new htmlAttr("class", "foundryTableHeader"));
        if(is_array($this->headers)){
            foreach ($this->headers as $col){
                $tr->addContent(htmlElement::td($col, null, null, null));
            }
        }
        return $tr->display();
    }
    
    function buildFooter(){
        
    }
    
    function getNumberOfCols(){
//        $numHeaders = sizeof($this->headers);
//        if($numHeaders > 0){
//            $this->numCols = $numHeaders;
//        } else {
//            //$arr = $this->rows[0];
//            print_r($this->rows[0]);
//            $this->numCols = 2;
//        }
    }
    
    function addData($data){
        $numArgs = func_num_args();
        $colNames = array();
        for($i = 1; $i <= $numArgs - 1; $i++){
            array_push($colNames, func_get_arg($i));
        }
        $this->addDataFromColList($data, $colNames);
    }
    
    function addDataFromColList($data, $colNames){
        foreach($data as $datum){
            $row = array();
            foreach($colNames as $col){
                array_push($row, $datum[$col]);
            }
            $this->buildRow($row);
        }
    }
}

?>
