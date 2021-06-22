<?php

class ComponentAqi
{
    private $itemByCell ;
    private $countElements;
    private $slides = [];
    private $countCell ;
    private $id;
    private $gender;
 
    public function __construct($slides, $id, $itemByCell = 1, $gender)
    {
        $this->id = $id;
        $this->countElements = count($slides);
        $this->slides = $slides;
        $this->itemByCell = $itemByCell;
        $this->countCell = ceil($this->countElements/$this->itemByCell);
        $this->gender = $gender;
    }

    public function getLayer(){

        $newTab = [];
        $endCell ='</div></div>';
        $array = $this->slides;

        if($this->itemByCell == 1) {

            for ( $i = 0 ; $i < ($this->countCell) ; $i++ ) {
                $newTab[] = [ $array[$i]  ] ;
            }
            foreach ($newTab as $k => $item){
                $html[] =  $this->getStartCell($k) . $item[0] . $this->getEndCell();
            }

        }
        //  log::add('airquality', 'debug', json_encode( $newTab));
        if( $this->itemByCell == 2){

            if ( $this->countElements %2 == 0 ){
                // log::add('airquality', 'debug', json_encode('Nombre paire'));
                for ( $i = 0 ; $i < ($this->countCell)*2 ; $i+=2 ) {
                    $newTab[] = [ ($array[$i] ? $array[$i] : '') . ($array[$i+1] ? $array[$i+1]:'')  ] ;
                }
            }
            else {
                for ( $i = 0 ; $i < ($this->countCell)*2-2 ; $i+=2 ) {
                    $newTab[] = [ ($array[$i] ? $array[$i] : '') . ( $array[$i+1] ? $array[$i+1]:'' ) ] ;
                }
                $newTab[] = [$array[array_key_last($array)]];
            }

            foreach ($newTab as $k => $item){
                $starCell =  $this->getStartCell($k);
                $html[] = $starCell . $item[0] . $endCell ;
            }

        }

        // if( $this->itemByCell == 3){
        //     $res = array_chunk($array,3);
        //     //  log::add('airquality', 'debug', json_encode( $res));
        //     foreach ($res as $cellule) {
        //         $newTab[] = implode('', $cellule);
        //     }
        //     // log::add('airquality', 'debug', json_encode( $newTab));
        //     foreach ($newTab as $k => $item){
        //         $starCell =  $this->getStartCell($k);
        //         $html[] = $starCell . implode('', $item) . $this->getEndCell();
        //     }
        // }
        // log::add('airquality', 'debug', json_encode(implode( '', $html)));
        return implode( '', $html); 

    }

    private function getEndCell(){

        if ($this->gender == 'mobile'){
            return ' </div>';
        } else {
            return '</div></div>';
        }
    }

    private function getStartCell($k){
       
        if ($this->gender == 'mobile'){
            return ' <div id="slide-'.($k+1).'-'.$this->id.'-aqi row first-row aqi-'.$this->id.'-row">';
        }
        else {
            if($k == 0){
                $active = 'active'; 
                $interval ='15000';
            } else {
                $active = ''; 
                $interval ='12000';
            } 
            return '<div class="item '.$active.'" data-interval="'.$interval.'"><div class="aqi-'.$this->id.'-row">';
        }
      
    }

}

