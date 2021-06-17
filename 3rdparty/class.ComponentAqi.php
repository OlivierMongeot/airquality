<?php

class ComponentAqi
{
    private $itemByCell ;
    private $countElements;
    private $slides = [];
    private $countCell ;
    private $id;
 
    public function __construct($slides, $id, $itemByCell = 1)
    {
        $this->id = $id;
        $this->countElements = count($slides);
        $this->slides = $slides;
        $this->itemByCell = $itemByCell;
        $this->countCell = ceil($this->countElements/$this->itemByCell);
      
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
                $starCell =  $this->getStartCell($k);
                $html[] = $starCell . $item[0] . $endCell ;
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

        if( $this->itemByCell == 3){
            $res = array_chunk($array,3);
            //  log::add('airquality', 'debug', json_encode( $res));
            foreach ($res as $cellule) {
                $newTab[] = implode('', $cellule);
            }
            // log::add('airquality', 'debug', json_encode( $newTab));
            foreach ($newTab as $k => $item){
                $starCell =  $this->getStartCell($k);
                $html[] = $starCell . implode('', $item) . $endCell ;
            }
        }
        // log::add('airquality', 'debug', json_encode(implode( '', $html)));
        return implode( '', $html); 

    }

    private function getStartCell($k){
        if($k == 0){
            $active = 'active'; 
            $interval ='15000';
        } else {
            $active = ''; 
            $interval ='5000';
        }
        return '<div class="item '.$active.'" data-interval="'.$interval.'"><div class="aqi-'.$this->id.'-row">';
    }

}

