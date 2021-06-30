<?php

class CreateHtmlAqi
{
    private $itemByCell ;
    private $countElements;
    private $slides = [];
    private $countCell ;
    private $id;
    private $version;
    private $gender;
    private $slidesAtZero;
 
    public function __construct($slides, $id, $itemByCell = 1, $version, $gender, $slidesAtZero)
    {
        $this->id = $id;
        $this->countElements = count($slides);
        $this->slides = $slides;
        $this->itemByCell = $itemByCell;
        $this->countCell = ceil($this->countElements/$this->itemByCell);
        $this->version = $version;
        $this->gender = $gender;
        $this->slidesAtZero = $slidesAtZero;
    }

    public function getLayer(){
       
        // log::add('airquality','debug', json_encode( $this->slides));
        $newTab = [];
        $array = $this->slides;

        if($this->itemByCell == 1) {
            for ( $i = 0 ; $i < ($this->countCell) ; $i++ ) {
                $newTab[] = [$array[$i]] ;
            }
            $total = count($newTab);
            foreach ($newTab as $k => $item){
                $html[] =  $this->getStartCell($k, $total) . $item[0] . $this->getEndCell($k, $total);
            }
        }

        return implode( '', $html); 
    }

    private function getEndCell($k, $total){

        if ($this->version == 'mobile' && $this->gender == 'polution' ){
            return  '</div>';
        } 
        else if ($this->version == 'mobile'  && $this->gender == 'pollen' )
        {
            if ($k >= ($total - $this->slidesAtZero)){
                return '</div></div>';
            } else {
                return '</div>';
            }
        }
        else {
            return '</div></div>';
        }
    }

    private function getStartCell($k, $total){


        if ($this->version == 'mobile' && $this->gender == 'polution' ){
            return ' <div id="slide-'.($k+1).'-'.$this->id.'-aqi row aqi-'.$this->id.'-row">';
        } 
        else
        if ($this->version == 'mobile' && $this->gender == 'pollen' ){
            if ($k >= ($total - $this->slidesAtZero)){

                return ' <div id="slide-'.($k+1).'-'.$this->id.'-aqi row aqi-'.$this->id.'-row" ><div >';
            } else 
            {
                return ' <div id="slide-'.($k+1).'-'.$this->id.'-aqi row first-row aqi-'.$this->id.'-row">';
            }

        } 
        
        else {
            if($k == 0){
                $active = 'active'; 
                $interval ='15000';
            } else {
                $active = ''; 
                $interval ='12000';
            } 

            if ($k >= ($total - $this->slidesAtZero) && $this->gender == 'pollen'){

                return '<div class="item '.$active.'" data-interval="'.$interval.'" ><div class="aqi-'.
                $this->id.'-particule" style="height:200px; display:flex; flex-direction:column;" >';
            } 
            else {
                   return '<div class="item '.$active.'" data-interval="'.$interval.'"><div class="aqi-'.$this->id.'-row">';
            }
        }
    }
}

