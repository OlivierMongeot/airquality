<?php

class CreateHtmlAqi
{
    private $itemByCell;
    private $countElements;
    private $slides = [];
    private $countCell;
    private $id;
    private $version;
  
 
    public function __construct($slides = [], $id, $itemByCell = 1, $version)
    {
        $this->id = $id;
        $this->countElements = count($slides);
        $this->slides = $slides;
        $this->itemByCell = $itemByCell;
        $this->countCell = ceil($this->countElements / $this->itemByCell);
        $this->version = $version;
    }

    public function getLayer()
    {
        $newTab = [];
        $array = $this->slides;
        $html = [];
        if ($this->itemByCell == 1) {
            for ($i = 0; $i < ($this->countCell); $i++) {
                $newTab[] = [$array[$i]];
            }
            foreach ($newTab as $k => $item) {
                $html[] =  $this->getStartCell($k) . $item[0] . $this->getEndCell();
            }
        }

        if (empty($html)) {

            $html[] = '<div disable class="" style="margin-top:20px;color:#00AEEC;display:flex;justify-content:center;align-item:center;flex-direction:column;height:auto;font-size:110%">';
            $html[] = '<div style="display:flex;justify-content:center;height:35px;align-items: center">Détails de polution indisponibles</div><br>';
            $html[] = '<div style="display:flex;justify-content:center;height:35px;align-items: center"><i class="far fa-times-circle fa-2x"></i></div><br>';
            $html[] = '<div style="display:flex;justify-content:center;height:35px;align-items: center">pour cette configuration</div><br>';
            $html[] =  '</div>';
        }
        return implode('', $html);
    }

    private function getEndCell()
    {

        if ($this->version == 'mobile') {
            return  '</div>';
        }
        else {
            return '</div></div>';
        }
    }

    private function getStartCell($k)
    {

        if ($this->version == 'mobile') {
            return '<div id="slide-' . ($k + 1) . '-' . $this->id . '-aqi row aqi-' . $this->id . '-row">';
        } else {

            if ($k == 0) {
                $active = 'active';
                $interval = '15000';
            } else {
                $active = '';
                $interval = '12000';
            }
            return '<div class="item ' . $active . '" data-interval="' . $interval . '"><div class="aqi-' . $this->id . '-row">';
        }
    }
}
