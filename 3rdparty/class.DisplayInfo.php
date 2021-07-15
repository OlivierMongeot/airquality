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

    public function getElementRiskPollen($color)
    {

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
            default:
                return  __("Risque très haut", __FILE__);
        }
    }

    public function getElementRiskAqi($color)
    {

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
    public function getUVRapport($level)
    {
        if ($level == 0) {
            return __('Nul', __FILE__);
        } else if ($level > 0  && $level < 3) {
            return __('Faible', __FILE__);
        } else if ($level >= 3  && $level < 6) {
            return __('Modéré', __FILE__);
        } else if ($level >= 6  && $level < 8) {
            return __('Élevé', __FILE__);
        } else if ($level >= 8  && $level < 11) {
            return __('Très élevé', __FILE__);
        } else if ($level >= 11) {
            return __('Extrême', __FILE__);
        }
    }

    /**
     * Basé sur les info Météo Marine Française
     */
    public function getVisibilityRapport($level)
    {
        switch ($level) {
            case $level >= 0  && $level < 700:
                return __('Très Mauvaise', __FILE__);
            case $level >= 700  && $level < 3210:
                return __('Mauvaise', __FILE__);
            case $level >= 3210  && $level < 8000:
                return __('Moyenne', __FILE__);
            case $level >= 8000:
                return __('Bonne', __FILE__);
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

    public function parseDate()
    {
        $datetime = new DateTime;
        $time =   $datetime->format('H:i');
        $date = $datetime->format('Y-m-d');
        return __('Mise à jour le ', __FILE__) . $date . __(' à ', __FILE__) . $time;
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

    /**
     * Création message : analyse live si bascule tranche + prevision pic si > norme
     */
    public function getMessagePollen($oldData, $newData, $paramAlertPollen)
    {
        $message = '';
        $newPoaceae = $newData[0]->Species->Grass->{"Grass / Poaceae"};
        $oldPoaceae = $oldData['poaceae'];
        message::addInfo('newPollen', $newPoaceae);
        message::addInfo('oldPollen', $oldPoaceae);
        message::addInfo('paramPollen', json_encode($paramAlertPollen));
        if ($paramAlertPollen['poaceae_alert_level'] <= $newPoaceae){
            if ($newPoaceae > $oldPoaceae){
                $mess = $this->makeMessagePollen($newPoaceae, $oldPoaceae, 'poaceae', 'Graminées');
                if (!empty($mess)) {
                    $message .= $mess;
                }
            }

        }
        return  $message;
    }


    public function getMessagePollution($oldData, $newDataPollution, $newDataOc, $paramAlertAqi)
    {

        $message = '';
        // AQI
        $newAqi = $newDataPollution->main->aqi;
        $oldAqi = $oldData['aqi'];
        if ($paramAlertAqi['aqi_alert_level'] <= $newAqi) {
            if ($newAqi > $oldAqi) {
                $message.= __('Dégradation de l\'AQI à ', __FILE__) . $newAqi.'. ';
            } else if ($newAqi < $oldAqi) {
                $message.= __('Amélioration de l\'AQI à ', __FILE__) . $newAqi.'. ';
            }
        }

        // PM2.5
        $newPm25 = $newDataPollution->components->pm2_5;
        $oldPm25 = $oldData['pm25'];
        if ($paramAlertAqi['pm25_alert_level'] <= $newPm25) {
            $mess = $this->makeMessage($newPm25, $oldPm25, 'pm25', 'PM2.5');
            if ($mess != '') {
            $message .= $mess;
            } 
        }
        // PM10
        $newPm10 = $newDataPollution->components->pm10;
        $oldPm10 = $oldData['pm10'];
        if ($paramAlertAqi['pm10_alert_level'] <= $newPm10) {
            $mess = $this->makeMessage($newPm10, $oldPm10, 'pm10', 'PM10');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // O³
        $newO3 = $newDataPollution->components->o3;
        $oldO3 = $oldData['o3'];
        if ($paramAlertAqi['o3_alert_level'] <= $newO3) {
            $mess = $this->makeMessage($newO3, $oldO3, 'o3', 'O³');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // NO²
        $newNo2 = $newDataPollution->components->no2;
        $oldNo2 = $oldData['no2'];
        if ($paramAlertAqi['no2_alert_level'] <= $newNo2) {
            $mess = $this->makeMessage($newNo2, $oldNo2, 'no2', 'NO²');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // SO²
        $newSo2 = $newDataPollution->components->so2;
        $oldSo2 = $oldData['so2'];
        if ($paramAlertAqi['so2_alert_level'] <= $newSo2) {
            $mess = $this->makeMessage($newSo2, $oldSo2, 'so2', 'SO²');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // CO
        $newCo = $newDataPollution->components->co;
        $oldCo = $oldData['co'];
        if ($paramAlertAqi['co_alert_level'] <= $newCo) {
            $mess = $this->makeMessage($newCo, $oldCo, 'co', 'CO');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // NO
        $newNo = $newDataPollution->components->no;
        $oldNo = $oldData['no'];
        if ($paramAlertAqi['no_alert_level'] <= $newNo) {
            $mess = $this->makeMessage($newNo, $oldNo, 'no', 'NO');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // NH³  
        $newNh3 = $newDataPollution->components->nh3;
        $oldNh3 = $oldData['nh3'];
        if ($paramAlertAqi['nh3_alert_level'] <= $newNh3) {
            $mess = $this->makeMessage($newNh3, $oldNh3, 'nh3', 'NH³');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // UV
        $newUv = $newDataOc->uvi;
        $oldUv = $oldData['uv'];
        if ($paramAlertAqi['uv_alert_level'] <= $newUv) {
            $mess = $this->makeMessage($newUv, $oldUv, 'uv', 'UV');
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // Visibility
        $newVisibility = $newDataOc->visibility;
        $oldVisibility = $oldData['visibility'];
        if ($paramAlertAqi['visibility_alert_level'] <= $newVisibility) {
            $mess = $this->makeMessage($newVisibility, $oldVisibility, 'visibility', 'Visibilité', $paramAlertAqi);
            if ($mess != '') {
                $message .= $mess;
            }
        }
        // message::add('Message',  $message);
        return  $message;
    }


    private function makeMessage($newData, $oldData, $type, $typeName)
    {
        $message = '';
        switch ($type) {
            case 'visibility':
            case 'uv':
                $increase = 'Dégradation';
                $decrease = 'Amélioration';
                break;
            default:
                $decrease = 'Dégradation';
                $increase = 'Amélioration';
                break;
        }

        if ($newData > $oldData) {
            // Check if change category => compare category
            $newCategory = $this->getLevel($newData, $type);
            $oldCategory = $this->getLevel($oldData, $type);
            if ($newCategory !== $oldCategory) {
                // message::add('check ' . $type,  $decrease.' de la ' . $typeName . ' à : ' . $newCategory);
                $message = __( $decrease.' des  ' .$typeName . ' au niveau ', __FILE__) . $newCategory.'. ';
            }
        } else if ($newData < $oldData) {
            $newCategory = $this->getLevel($newData, $type);
            $oldCategory = $this->getLevel($oldData, $type);
            if ($newCategory !== $oldCategory) {
                // message::add('check ' . $type,  $increase.' de la ' . $typeName . ' à : ' . $newCategory);
                $message = __($increase.' des '.$typeName. ' au niveau  ', __FILE__) . $newCategory.'. ';
            }
        } else {
            // pour le dev uniquement 
            // $newCategory = $this->getLevel($newData, $type);
            // $message = __($typeName . ' stable au niveau ' . $newCategory, __FILE__).'. ';
            // message::add('check ' . $type, __($typeName . ' stable au niveau ' . $newCategory, __FILE__));
        }
        return $message;
    }


    
    private function makeMessagePollen($newData, $oldData, $type, $typeName)
    {
        $message = '';
        $increase = 'Dégradation';
        $decrease = 'Amélioration';
           
        if ($newData > $oldData) {
            // Check if change category => compare category
            $newCategory = $this->getLevelPollen($newData, $type);
            $oldCategory = $this->getLevelPollen($oldData, $type);
            if ($newCategory !== $oldCategory) {
                // message::add('check ' . $type,  $decrease.' de la ' . $typeName . ' à : ' . $newCategory);
                $message = __( $decrease.' des  ' .$typeName . ' au niveau ', __FILE__) . $newCategory.'. ';
            }
        } else if ($newData < $oldData) {
            $newCategory = $this->getLevelPollen($newData, $type);
            $oldCategory = $this->getLevelPollen($oldData, $type);
            if ($newCategory !== $oldCategory) {
                // message::add('check ' . $type,  $increase.' de la ' . $typeName . ' à : ' . $newCategory);
                $message = __($increase.' des '.$typeName. ' au niveau  ', __FILE__) . $newCategory.'. ';
            }
        } else {
            // pour le dev uniquement 
            $newCategory = $this->getLevelPollen($newData, $type);
            $message = __($typeName . ' stable au niveau ' . $newCategory, __FILE__).'. ';
            message::add('check ' . $type, __($typeName . ' stable au niveau ' . $newCategory, __FILE__));
        }
        return $message;
    }

    public function getLevel($value, $type)
    {
        $allranges = SetupAqi::$aqiRange;
        $ranges = $allranges[$type];
        foreach ($ranges as $color => $range) {
            if ($range[0] <= $value && $range[1] >= $value) {
                switch ($type) {
                    case 'pm25':
                    case 'pm10':
                    case 'co':
                    case 'no':
                    case 'no2':
                    case 'o3':
                    case 'so2':
                    case 'nh3':
                        return  $this->getElementRiskAqi($color);
                    case 'uv':
                        return  $this->getUVRapport($value);
                    case 'visibility':
                        return  $this->getVisibilityRapport($value);
                }
            }
        }
    }

    public function getLevelPollen($value, $type)
    {
        $allranges = SetupAqi::$pollenRange;
        $ranges = $allranges[$type];
        foreach ($ranges as $color => $range) {
            if ($range[0] <= $value && $range[1] >= $value) {
                        return  $this->getElementRiskPollen($color);
            }
        }
    }

}
