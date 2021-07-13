<?php

class DisplayInfo
{

    public function formatValueForDisplay($value, $style = 'normal')
    {
        if ($style === 'normal') {
            switch ($value) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    return $value;
                case $value > 0  && $value <= 10:
                    return number_format((float)($value), 2, '.', '');
                case $value > 10  && $value <= 100:
                    return number_format((float)($value), 1, '.', '');
                case $value > 100:
                    return number_format((float)($value), 0, '.', '');
            }
        } else {
            switch ($value) {
                case $value > 10:
                    return number_format((float)($value), 0, '.', '');
                default:
                    return number_format((float)($value), 1, '.', '');
            }
        }
    }

    public function getAqiName($aqi)
    {
        switch ($aqi) {
            case  1:
                return __("Bon", __FILE__);
            case 2:
                return __("Correct", __FILE__);
            case 3:
                return __("Dégradé", __FILE__);
            case 4:
                return __("Mauvais", __FILE__);
            case 5:
                return __("Très mauvais", __FILE__);
            case 6:
                return __("Extrême", __FILE__);
        }
    }

    public function getElementRiskPollen($color, $nocolor = false){
        
        switch ($color) {

            case '#00BD01':
                return __("Risque bas", __FILE__);
                break;
            case '#EFE800':
                return  __("Risque modéré", __FILE__);
                break;
            case '#E79C00':
                return __("Risque haut", __FILE__);
                break;
            default :
                return  __("Risque très haut", __FILE__);
        }
    }

    public function getElementRiskAqi($color){

        switch ($color) {

            case '#00AEEC':
                return __("Bon", __FILE__);
            case '#00BD01':
                return __("Correct", __FILE__);
            case '#EFE800':
                return  __("Dégradé", __FILE__);
            case '#E79C00':
                return __("Mauvais", __FILE__);
            case '#B00000':
                return __("Très mauvais", __FILE__);
            case '#662D91':
                return __("Extrême", __FILE__);
        }
    }

    /**
     * Basé sur l'indice UV officiel
     */
    public function getUVRapport($level){
        if ( $level == 0){
            return __('Nul',__FILE__);
        } 
        else if ( $level > 0  && $level < 3){
            return __('Faible',__FILE__);
        } 
        else if ($level >= 3  && $level < 6){
            return __('Modéré',__FILE__);
        }
        else if ( $level >= 6  && $level < 8){
            return __('Élevé',__FILE__);
        }
        else if ( $level >= 8  && $level < 11){
            return __('Très élevé',__FILE__);
        }
        else if (  $level >= 11){
            return __('Extrême',__FILE__);
        }
    }

    /**
     * Basé sur les info Météo Marine Française
     */
    public function getVisibilityRapport($level){
        switch ($level) {
        case $level > 0  && $level < 700:
            return __('Très Mauvaise',__FILE__);
        case $level >= 700  && $level < 3210:
            return __('Mauvaise',__FILE__);
        case $level >= 3210  && $level < 8000:
            return __('Moyenne',__FILE__);
        case $level >= 8000 :
            return __('Bonne',__FILE__);
        }
    }


    public function getPollenRisk(string $level)
    {
        switch ($level) {
            case  'High':
                return __("Risque haut", __FILE__);
            case 'Moderate':
                return __("Risque modéré", __FILE__);
            case 'Low':
                return __("Risque bas", __FILE__);
            case 'Very High':
                return __("Risque très haut", __FILE__);
        }
    }

    public function getListPollen($category)
    {
        switch ($category) {
            case 'tree_pollen':
                return __('Aulne', __FILE__) . ' - ' . __('Bouleau', __FILE__) . ' - ' . __('Cyprès', __FILE__) . ' - ' . __('Chêne', __FILE__)
                    . ' - ' . __('Platane', __FILE__) . ' - ' . __('Noisetier', __FILE__) . ' - ' . __('Orme', __FILE__) . ' - ' . __('Pin', __FILE__);
                break;
            case 'grass_pollen':
                return __('Herbes', __FILE__) . ' - ' . __('Poacées', __FILE__) . ' - ' . __('Graminées', __FILE__);
                break;
            case 'weed_pollen':
                return __('Chenopod', __FILE__) . ' - ' . __('Armoise', __FILE__) . ' - ' . __('Ortie', __FILE__) . ' - ' . __('Ambroisie', __FILE__);
                break;
            case 'autres':
                return __("Autres pollens d'origine inconnue", __FILE__);
        }
    }

    public function parseDate(){
        $datetime = new DateTime;
        $time =   $datetime-> format('H:i');
        $date = $datetime-> format('Y-m-d');
        return __('Mise à jour le ',__FILE__) . $date. __(' à ',__FILE__). $time;
    }

    public function getNameDay($numDay)
    {
        switch ($numDay) {
            case 1:
                return __('Lundi', __FILE__);
            case 2:
                return __('Mardi', __FILE__);
            case 3:
                return __('Mercredi', __FILE__);
            case 4:
                return __('Jeudi', __FILE__);
            case 5:
                return  __('Vendredi', __FILE__);
            case 6:
                return  __('Samedi', __FILE__);
            case 7:
                return __('Dimanche', __FILE__);
        }
    }
 

    public function getMessage($value, $pollen ){

        // if($this)
        // {
        //     $message ='';
        // }
        return 'TOTO';


    }

}
