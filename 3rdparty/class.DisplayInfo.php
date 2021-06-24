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

    public function getElementRiskPollen($color){

        switch ($color) {

            case '#00BD01':
                return 'Risque Bas';
                break;
            case '#EFE800':
                return 'Risque modéré';
                break;
            case '#E79C00':
                return 'Risque élevé';
                break;
            default :
                return 'Risque très élevé';
        }
    }

    public function getElementRiskAqi($color){

        switch ($color) {

            case '#00AEEC':
                return __("Bon", __FILE__);
                break;
            case '#00BD01':
                return __("Correct", __FILE__);
                break;
            case '#EFE800':
                return  __("Dégradé", __FILE__);
                break;
            case '#E79C00':
                return __("Mauvais", __FILE__);
                break;
            case '#B00000':
                return __("Très mauvais", __FILE__);
                break;
            case '#B00000':
                return __("Extrême", __FILE__);
                break;
        }
    }

    /**
     * Basé sur l'indice UV officiel
     */
    public function getUVRapport($level){
        switch ($level) {
        case $level > 0  && $level < 3:
            $alert = 'Faible';
            break;
        case $level >= 3  && $level < 6:
            $alert = 'Modéré';
            break;
        case $level >= 6  && $level < 8:
            $alert = 'Élevé';
            break;
        case $level >= 8  && $level < 11:
            $alert = 'Très élevé';
            break;
        case $level >= 11 :
            $alert = 'Extrême';
            break;
        }
        return $alert;
    }

    /**
     * Basé sur les info Météo Marine Française
     */
    public function getVisibilityRapport($level){
        switch ($level) {
        case $level > 0  && $level < 700:
                $alert = 'Très Mauvaise';
                break;
        case $level >= 700  && $level < 3210:
            $alert = 'Mauvaise';
            break;
        case $level >= 3210  && $level < 8000:
            $alert = 'Moyenne';
            break;
        case $level >= 8000 :
            $alert = 'Bonne';
            break;
        }
        return $alert;
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

 

}
