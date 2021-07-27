<?php

class DisplayInfo
{

    public function formatValueForDisplay($value, $style = 'normal', $decimal = null)
    {
        if(!empty($decimal)){
            return number_format((float)($value), $decimal, '.', '');
        }
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
                return [__("Bon", __FILE__), 1];
            case '#00BD01':
                return [__("Correct", __FILE__), 2];
            case '#EFE800':
                return  [__("Dégradé", __FILE__), 3];
            case '#E79C00':
                return [__("Mauvais", __FILE__), 4];
            case '#B00000':
                return [__("Très mauvais", __FILE__), 5];
            case '#662D91':
                return [__("Extrême", __FILE__), 6];
        }
    }

    /**
     * Basé sur l'indice UV officiel
     */
    public function getUVLevel($value)
    {
        if ($value >= 0  && $value < 3) {
            return [__('Faible', __FILE__), 1];
        } else if ($value >= 3  && $value < 6) {
            return [__('Modéré', __FILE__), 2];
        } else if ($value >= 6  && $value < 8) {
            return [__('Élevé', __FILE__), 3];
        } else if ($value >= 8  && $value < 11) {
            return [__('Très élevé', __FILE__), 4];
        } else if ($value >= 11) {
            return [__('Extrême', __FILE__), 5];
        }
    }

    /**
     * Basé sur les info Météo Marine Française
     */
    public function getVisibilityLevel($level)
    {
        switch ($level) {
            case $level >= 0  && $level < 700:
                return [__('Très Mauvaise', __FILE__), 4];
            case $level >= 700  && $level < 3210:
                return [__('Mauvaise', __FILE__), 3];
            case $level >= 3210  && $level < 8000:
                return [__('Moyenne', __FILE__), 2];
            case $level >= 8000:
                return [__('Bonne', __FILE__), 1];
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
            default:
                return __("Autres pollens d'origine inconnue", __FILE__);
        }
    }

    public function parseDate($date = null)
    {
        if (empty($date)) {
            $datetime = new DateTime;
            $time =   $datetime->format('H:i');
            $date = $datetime->format('d-m-Y');
            return __('Mise à jour le ', __FILE__) . $date . __(' à ', __FILE__) . $time;
        } else {
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            return __('Mise à jour le ', __FILE__) . $dt->format('d') . '-' . $dt->format('m') . '-' . $dt->format('Y') . __(' à ', __FILE__) . $dt->format('H') . 'h' . $dt->format('i');
        }
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
     * Retourne tous les messages pollution 
     */
    public function getAllMessagesPollution($oldData, $newDataPollution, $newDataOc, $paramAlertAqi, $city = '')
    {
        $message = [];
        $messageInMore = [];
        $finalMessage = [];
        $importance = [];
        // PM2.5
        $newPm25 = $newDataPollution->components->pm2_5;
        $oldPm25 = $oldData['pm25'];
        if ($paramAlertAqi['pm25_alert_level'] <= $newPm25 || $newPm25 < $oldPm25) {
            $mess = $this->makeMessageAqi($newPm25, $oldPm25, 'pm25', 'PM2.5');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['pm25'] =  $mess[2];
        }
        // PM10
        $newPm10 = $newDataPollution->components->pm10;
        $oldPm10 = $oldData['pm10'];
        if ($paramAlertAqi['pm10_alert_level'] <= $newPm10 || $newPm10 < $oldPm10) {
            $mess = $this->makeMessageAqi($newPm10, $oldPm10, 'pm10', 'PM10');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['pm10'] =  $mess[2];
        }
        // O³
        $newO3 = $newDataPollution->components->o3;
        $oldO3 = $oldData['o3'];
        if ($paramAlertAqi['o3_alert_level'] <= $newO3  || $newO3 < $oldO3) {
            $mess = $this->makeMessageAqi($newO3, $oldO3, 'o3', 'O³');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['o3'] =  $mess[2];
        }
        // NO²
        $newNo2 = $newDataPollution->components->no2;
        $oldNo2 = $oldData['no2'];
        if ($paramAlertAqi['no2_alert_level'] <= $newNo2 || $newNo2 < $oldNo2) {
            $mess = $this->makeMessageAqi($newNo2, $oldNo2, 'no2', 'NO²');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['no2'] =  $mess[2];
        }
        // SO²
        $newSo2 = $newDataPollution->components->so2;
        $oldSo2 = $oldData['so2'];
        if ($paramAlertAqi['so2_alert_level'] <= $newSo2 || $newSo2 < $oldSo2) {
            $mess = $this->makeMessageAqi($newSo2, $oldSo2, 'so2', 'SO²');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['so2'] =  $mess[2];
        }
        // CO
        $newCo = $newDataPollution->components->co;
        $oldCo = $oldData['co'];
        if ($paramAlertAqi['co_alert_level'] <= $newCo || $newCo < $oldCo) {
            $mess = $this->makeMessageAqi($newCo, $oldCo, 'co', 'CO');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['co'] =  $mess[2];
        }
        // NO
        $newNo = $newDataPollution->components->no;
        $oldNo = $oldData['no'];
        if ($paramAlertAqi['no_alert_level'] <= $newNo || $newNo < $oldNo) {
            $mess = $this->makeMessageAqi($newNo, $oldNo, 'no', 'NO');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['no'] =  $mess[2];
        }
        // NH³  
        $newNh3 = $newDataPollution->components->nh3;
        $oldNh3 = $oldData['nh3'];
        if ($paramAlertAqi['nh3_alert_level'] <= $newNh3 || $newNh3 < $oldNh3) {
            $mess = $this->makeMessageAqi($newNh3, $oldNh3, 'nh3', 'NH³');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['nh3'] =  $mess[2];
        }
        // UV
        $newUv = $newDataOc->uvi;
        $oldUv = $oldData['uv'];
        if ($paramAlertAqi['uv_alert_level'] <= $newUv || $newUv < $oldUv) {
            $mess = $this->makeMessageAqi($newUv, $oldUv, 'uv', 'UV');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
            $importance['uv'] =  $mess[2];
        }
        // Visibility
        $newVisibility = $newDataOc->visibility;
        $oldVisibility = $oldData['visibility'];
        if ($paramAlertAqi['visibility_alert_level'] >= $newVisibility || $oldVisibility < $newVisibility) {
            $mess = $this->makeMessageAqi($newVisibility, $oldVisibility, 'visibility', 'Visibilité');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInMore[] = $mess[1];
            }
        }

        // AQI
        $newAqi = $newDataPollution->main->aqi;
        $oldAqi = $oldData['aqi'];
        if ($paramAlertAqi['aqi_alert_level'] <= $newAqi && $newAqi != $oldAqi || $message != []) {
            if ($newAqi > $oldAqi) {
                $message[] = __('- Dégradation de l\'AQI à l\'indice ', __FILE__) . $newAqi;
            } else if ($newAqi < $oldAqi) {
                $message[] = __('- Amélioration de l\'AQI à l\'indice ', __FILE__) . $newAqi;
            } else {
                $message[] = __('- AQI stable à l\'indice ', __FILE__) . $newAqi;
            }
            $importance['aqi'] = $newAqi;
        }

        $finalMessage = ($paramAlertAqi['alert_details'] == 1) ? array_merge($message, $messageInMore) : $message;
        $stringMess = implode(' - ', $finalMessage);

        if ($paramAlertAqi['alert_notification'] == 1) {
            message::add('Message Pollution', $stringMess);
        }

        $htmlMessage = $this->formatAqiForTelegram($finalMessage, $city);
        $smsMessage = $this->formatAqiForSms($finalMessage);
        $markdownMessage = $this->formatAqiMarkdown($finalMessage);
        log::add('airquality', 'debug', 'Makdown Message' . json_encode( $markdownMessage));
        return [$stringMess, $htmlMessage, $smsMessage, $markdownMessage];
    }


    /**
     * Fabrique un message d'alerte de pollution
     */
    private function makeMessageAqi($newData, $oldData, $type, $typeName)
    {
        $message = '';
        $messageInMore = '';

        switch ($type) {
            case 'visibility':
                $increase =  $this->getSynonyme('dégradation');
                $decrease =  $this->getSynonyme('amélioration');
                break;
            case 'uv':
                $increase = 'baisse';
                $decrease = $this->getSynonyme('hausse');
                break;
            default:
                $decrease = $this->getSynonyme('dégradation');
                $increase = $this->getSynonyme('amélioration');
                break;
        }

        [$newCategory, $importance] = $this->getLevelAQI($newData, $type);
        [$oldCategory] = $this->getLevelAQI($oldData, $type);

        // Cas 1 : hausse de l'AQI
        if ($newData > $oldData) {
            if ($newCategory != $oldCategory) {
                $message = __("- <b>" . $typeName . "</b> " . $decrease .  " " . $this->getSynonyme('niveau'), __FILE__) . " " . $newCategory;
                $message .= $this->getSynonyme(' à cause d\'') . $this->makeEndMessage($newData, $type);
            } else if ($oldCategory != 'extrême' && $oldCategory != 'très mauvaise') {
                $messageInMore = __("- <b>" . $typeName . "</b>" . " " . $this->getSynonyme('petite') . " " . strtolower($decrease) . ", " . $this->getSynonyme('reste') . " " . $this->getSynonyme('niveau') . " " . $newCategory, __FILE__);
                $messageInMore .= ' avec ' . $this->makeEndMessage($newData, $type);
            } else {
                $messageInMore = __("- <b>" . $typeName . "</b> " . $this->getSynonyme('petite') . " " . $decrease . ", " . $this->getSynonyme('reste') . " " . $this->getSynonyme('niveau') . " maximum " . $newCategory, __FILE__);
                $messageInMore .= ' avec ' . $this->makeEndMessage($newData, $type);
            }
            // Cas 2 : Baisse de l'AQI
        } else if ($newData < $oldData) {
            if ($newCategory != $oldCategory) {
                $message = __("- <b>" . $typeName . "</b> " . $increase . " " . $this->getSynonyme('niveau'), __FILE__) . " " . $newCategory;
                $message .=  $this->getSynonyme(' avec ') . $this->makeEndMessage($newData, $type);
            } else if ($newCategory != 'bon' && $newCategory != 'bonne' && $newCategory != 'faible') {

                $messageInMore = __("- <b>" . $typeName . "</b> " . $this->getSynonyme('petite') . " " . $increase . ", " . $this->getSynonyme('reste') . " " . $this->getSynonyme('niveau') . " " . $newCategory, __FILE__);
                $messageInMore .= ' avec ' . $this->makeEndMessage($newData, $type);
            } else {
                $messageInMore = __("- <b>" . $typeName . "</b> " . $this->getSynonyme('petite') . " " . $increase . ", " . $this->getSynonyme('reste') . " au meilleur niveau", __FILE__);
                $messageInMore .= ' avec ' . $this->makeEndMessage($newData, $type);
            }
            // Cas 3 : niveau stable
        } else {
            $messageInMore = __("<b> - " . $typeName . "</b> " . $this->getSynonyme('stable') . " " . $this->getSynonyme('niveau') . " " .  $newCategory, __FILE__);
            $messageInMore .= $this->getSynonyme(' avec ') . $this->makeEndMessage($newData, $type);
        }
        return [$message, $messageInMore, $importance];
    }

    /**
     * Make end of message AQI
     * 
     */
    private function makeEndMessage($value, $type)
    {
        switch ($type) {
            case 'uv':
                return 'un indice de ' . $value;
            case 'visibility':
                return 'une distance de ' . $value . ' m.';
            default:
                return 'une ' . $this->getSynonyme('concentration') . ' de ' . $value . ' μg/m3';
        }
    }

    private function getSynonyme($name)
    {
        $synonymes = [
            'concentration' => ['quantité', 'mesure', 'concentration', 'mesure', 'quantité'],
            'amélioration' => ['amélioration', 'embellie', 'amélioration'],
            'dégradation' => ['dégradation', 'altération', 'détérioration'],
            'hausse' => ['hausse', 'augmentation', 'élévation', 'hausse'],
            'stable' => ['stable', 'constant', 'stabilisé', 'stable'],
            'niveau' => ['au niveau', 'au palier', 'à l\'échelon', 'au niveau'],
            'reste' => ['reste', 'se stabilise', 'stable', 'stable'],
            ' à cause d\'' => [' avec ', ' avec ', ' en raison d\''],
            ' avec ' => [' avec ', ' avec ', ' avec ', ' grâce à '],
            'baisse' => ['baisse', 'diminution'],
            'petite' => ['petite', 'légère', 'légère', 'petite']
        ];
        if (isset($synonymes[$name])) {
            $tab = $synonymes[$name];
            $rand_keys = array_rand($tab, 1);
            return $tab[$rand_keys];
        } else {
            return $name;
        }
    }


    /**
     * Création messages : analyse si bascule de tranche 
     */
    public function getAllMessagesPollen($oldData, $dataPollen, $paramAlertPollen, $city = '')
    {
        $message = [];
        $messageInMore = [];
        // Poaceae
        $newPoaceae = isset($dataPollen[0]->Species->Grass->{"Grass / Poaceae"}) ? $dataPollen[0]->Species->Grass->{"Grass / Poaceae"} : -1;
        // $newPoaceae = $dataPollen[0]->Species->Grass->{"Grass / Poaceae"};
        $oldPoaceae = $oldData['poaceae'];
        if ($paramAlertPollen['poaceae_alert_level'] <= $newPoaceae) {
            $mess = $this->makeMessagePollen($newPoaceae, $oldPoaceae, 'poaceae', 'Graminées');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        //Elm
        $newElm = isset($dataPollen[0]->Species->Tree->Elm) ? $dataPollen[0]->Species->Tree->Elm : -1;
        // $newElm = $dataPollen[0]->Species->Tree->Elm;
        $oldElm = $oldData['elm'];
        if ($paramAlertPollen['elm_alert_level'] <= $newElm) {
            $mess = $this->makeMessagePollen($newElm, $oldElm, 'elm', 'Orme');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        //Alder
        $newAlder = isset($dataPollen[0]->Species->Tree->Alder) ? $dataPollen[0]->Species->Tree->Alder : -1;
        // $newAlder = $dataPollen[0]->Species->Tree->Alder;
        $oldAlder = $oldData['alder'];
        if ($paramAlertPollen['alder_alert_level'] <= $newAlder) {
            $mess = $this->makeMessagePollen($newAlder, $oldAlder, 'alder', 'Aulne');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }
        // Birch
        $newBirch = isset($dataPollen[0]->Species->Tree->Birch) ? $dataPollen[0]->Species->Tree->Birch : -1;
        // $newBirch = $dataPollen[0]->Species->Tree->Birch;
        $oldBirch = $oldData['birch'];
        if ($paramAlertPollen['birch_alert_level'] <= $newBirch) {
            $mess = $this->makeMessagePollen($newBirch, $oldBirch, 'birch', 'Bouleau');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }
        // Cypress
        $newCypress = isset($dataPollen[0]->Species->Tree->Cypress) ? $dataPollen[0]->Species->Tree->Cypress : -1;
        // $newCypress = $dataPollen[0]->Species->Tree->Cypress;
        $oldCypress = $oldData['cypress'];
        if ($paramAlertPollen['cypress_alert_level'] <= $newCypress) {
            $mess = $this->makeMessagePollen($newCypress, $oldCypress, 'cypress', 'Cyprès');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Hazel    
        $newHazel = isset($dataPollen[0]->Species->Tree->Hazel) ? $dataPollen[0]->Species->Tree->Hazel : -1;
        // $newHazel = $dataPollen[0]->Species->Tree->Hazel;
        $oldHazel = $oldData['hazel'];
        if ($paramAlertPollen['hazel_alert_level'] <= $newHazel) {
            $mess = $this->makeMessagePollen($newHazel, $oldHazel, 'hazel', 'Noisetier');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Oak 
        $newOak = isset($dataPollen[0]->Species->Tree->Oak) ? $dataPollen[0]->Species->Tree->Oak : -1;
        // $newOak = $dataPollen[0]->Species->Tree->Oak;
        $oldOak = $oldData['oak'];
        if ($paramAlertPollen['oak_alert_level'] <= $newOak) {
            $mess = $this->makeMessagePollen($newOak, $oldOak, 'oak', 'Chêne');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Pine 
        $newPine = isset($dataPollen[0]->Species->Tree->Pine) ? $dataPollen[0]->Species->Tree->Pine : -1;
        // $newPine = $dataPollen[0]->Species->Tree->Pine;
        $oldPine = $oldData['pine'];
        if ($paramAlertPollen['pine_alert_level'] <= $newPine) {
            $mess = $this->makeMessagePollen($newPine, $oldPine, 'pine', 'Pin');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Plane
        $newPlane = isset($dataPollen[0]->Species->Tree->Plane) ? $dataPollen[0]->Species->Tree->Plane : -1;
        // $newPlane = $dataPollen[0]->Species->Tree->Plane;
        $oldPlane = $oldData['plane'];
        if ($paramAlertPollen['plane_alert_level'] <= $newPlane) {
            $mess = $this->makeMessagePollen($newPlane, $oldPlane, 'plane', 'Platane');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Poplar
        $newPoplar = isset($dataPollen[0]->Species->Tree->Poplar) ? $dataPollen[0]->Species->Tree->Poplar : -1;
        // $newPoplar = $dataPollen[0]->Species->Tree->{"Poplar / Cottonwood"};
        $oldPoplar = $oldData['poplar'];
        if ($paramAlertPollen['poplar_alert_level'] <= $newPoplar) {
            $mess = $this->makeMessagePollen($newPoplar, $oldPoplar, 'poplar', 'Peuplier');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Chenopod
        $newChenopod = isset($dataPollen[0]->Species->Weed->Chenopod) ? $dataPollen[0]->Species->Weed->Chenopod : -1;
        // $newChenopod = $dataPollen[0]->Species->Weed->Chenopod;
        $oldChenopod = $oldData['chenopod'];
        if ($paramAlertPollen['chenopod_alert_level'] <= $newChenopod) {
            $mess = $this->makeMessagePollen($newChenopod, $oldChenopod, 'chenopod', 'Chénopodes');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }
        // Mugwort
        $newMugwort = isset($dataPollen[0]->Species->Weed->Mugwort) ? $dataPollen[0]->Species->Weed->Mugwort : -1;
        // $newMugwort = $dataPollen[0]->Species->Weed->Mugwort;
        $oldMugwort = $oldData['mugwort'];
        if ($paramAlertPollen['mugwort_alert_level'] <= $newMugwort) {
            $mess = $this->makeMessagePollen($newMugwort, $oldMugwort, 'mugwort', 'Armoises');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Nettle
        $newNettle = isset($dataPollen[0]->Species->Weed->Nettle) ? $dataPollen[0]->Species->Weed->Nettle : -1;
        // $newNettle = $dataPollen[0]->Species->Weed->Nettle;
        $oldNettle = $oldData['nettle'];
        if ($paramAlertPollen['nettle_alert_level'] <= $newNettle) {
            $mess = $this->makeMessagePollen($newNettle, $oldNettle, 'nettle', 'Ortie');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }
        // Ragweed 
        $newRagweed = isset($dataPollen[0]->Species->Weed->Ragweed) ? $dataPollen[0]->Species->Weed->Ragweed : -1;
        // $newRagweed = $dataPollen[0]->Species->Weed->Ragweed;
        $oldRagweed = $oldData['ragweed'];
        if ($paramAlertPollen['ragweed_alert_level'] <= $newRagweed) {
            $mess = $this->makeMessagePollen($newRagweed, $oldRagweed, 'ragweed', 'Ambroisie');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        // Others
        $newOthers = isset($dataPollen[0]->Species->Others) ? $dataPollen[0]->Species->Others : -1;
        // $newOthers = $dataPollen[0]->Species->Others;
        $oldOthers = $oldData['others'];
        if ($paramAlertPollen['others_alert_level'] <= $newOthers) {
            $mess = $this->makeMessagePollen($newOthers, $oldOthers, 'others', 'Autres pollens');
            if (!empty($mess[0])) {
                $message[] = $mess[0];
            } else if (!empty($mess[1])) {
                $messageInmore[] = $mess[1];
            }
        }

        if ($paramAlertPollen['alert_details'] == 1) {
            $message = array_merge($message, $messageInmore);
        }

        $stringMess = implode(' - ', $message);
        if ($paramAlertPollen['alert_notification'] == 1) {
            message::add('Message Pollen', $stringMess);
        }

        $telegramMessage = $this->formatPollensForTelegram($message, $city = '');
        $markdownMessage = $this->formatPollenMarkDown($message);
        $smsMessage = $this->formatPollensForSms($message);
        return [$stringMess, $telegramMessage, $smsMessage, $markdownMessage];
    }


    private function makeMessagePollen($newData, $oldData, $type, $typeName)
    {
        $message = '';
        $messageMore = '';
        if ($newData > $oldData) {
            $newCategory = $this->getLevelPollen($newData, $type);
            $oldCategory = $this->getLevelPollen($oldData, $type);
            if ($newCategory !== $oldCategory) {
                $message = __('- <b>' . $typeName . "</b>  en " . $this->getSynonyme('hausse') . " au niveau ", __FILE__) . $newCategory .
                    ' avec ' . $newData . ' part/m³ ';
            } else if ($oldCategory != 'risque très haut') {
                $messageMore = __(' - <b>' . $typeName . "</b> en légère " . $this->getSynonyme('hausse') . ", reste " . $this->getSynonyme('niveau') . " " . $newCategory, __FILE__) . ' avec ' . $newData . ' part/m³ ';
            }
        } else if ($newData < $oldData) {
            $newCategory = $this->getLevelPollen($newData, $type);
            $oldCategory = $this->getLevelPollen($oldData, $type);
            if ($newCategory !== $oldCategory) {
                $message = "<b>" . $typeName . "</b> en " . $this->getSynonyme('baisse') . " au niveau " . $newCategory . " avec " . $newData . " part/m³ ";
            } else if ($oldCategory != 'risque bas') {
                $messageMore = __("- <b>" . $typeName . "</b> légère " . $this->getSynonyme('baisse') . " mais reste " . $this->getSynonyme('niveau') . " " . $newCategory, __FILE__) . " avec " . $newData . " part/m³ ";
            }
        } else {
            // pour le dev uniquement 
            $newCategory = $this->getLevelPollen($newData, $type);
            $messageMore = __(' - <b>' . $typeName . "</b> : " . $this->getSynonyme('stable') . " " . $this->getSynonyme('niveau') . " " . $newCategory, __FILE__) . ' avec ' . $newData . ' part/m³ ';
        }
        return [$message,  $messageMore];
    }

    /**
     * Retourne le niveau d'alert 
     */
    public function getLevelAQI($value, $type)
    {
        $allranges = SetupAqi::$aqiRange;
        $ranges = $allranges[$type];
        $indexLevel = 0;
        foreach ($ranges as $color => $range) {
            $indexLevel++;
            if ($range[0] <= $value && $range[1] > $value) {
                switch ($type) {
                    case 'pm25':
                    case 'pm10':
                    case 'co':
                    case 'no':
                    case 'no2':
                    case 'o3':
                    case 'so2':
                    case 'nh3':
                        [$aqiLevel] = $this->getElementRiskAqi($color);
                        return [strtolower($aqiLevel), $indexLevel];
                    case 'uv':
                        [$uvLevel] = $this->getUVLevel($value);
                        return [str_replace('Élevé', 'élevé', strtolower($uvLevel)), $indexLevel];
                    case 'visibility':
                        [$visibilityLevel] = $this->getVisibilityLevel($value);
                        return  [strtolower($visibilityLevel), $indexLevel];
                }
            }
        }
    }

    public function getLevelPollen($value, $type)
    {
        $allranges = SetupAqi::$pollenRange;
        $ranges = $allranges[$type];
        foreach ($ranges as $color => $range) {
            if ($range[0] <= $value && $range[1] > $value) {
                return strtolower($this->getElementRiskPollen($color));
            }
        }
    }

    private function formatAqiForSms($messages)
    {
        if (!empty($messages)) {
            $arrayMessage[] = "-- Alerte AQI -- \n";
            foreach ($messages as $message) {
                $message = str_replace('³', '3', $message);
                $message = str_replace('²', '2', $message);
                $arrayMessage[] = strip_tags($message) . " \n";
            }
            return implode(' ', $arrayMessage);
        }
    }

    private function getGoodIcon()
    {
        $goodsIcons = ['&#128166;', '&#127808;', '&#127752;'];
        $rand_keys = array_rand($goodsIcons, 1);
        return $goodsIcons[$rand_keys];
    }

    private function getBadIcon()
    {
        $badsIcons = ['&#128169;', '&#128549;', '&#128557;'];
        $rand_keys = array_rand($badsIcons, 1);
        return $badsIcons[$rand_keys];
    }

    private function getIconsWithStatus()
    {
        $iconsGood =  $this->getGoodIcon();
        $iconsBad =  $this->getBadIcon();
        return  [
            '&#127795;' => 'correct', '&#128549;' => 'élevé', $iconsBad => 'mauvais', '&#128545;' => 'très', '&#128520;' => 'extrême', '&#128551;' => 'modéré',
            '&#128550;' => 'moyenne', '&#128529;' => 'dégradé', '&#127749;' => 'nul', '&#127752;' => 'faible', $iconsGood => 'bon'
        ];
    }

    private function formatAqiForTelegram($messages, $city = '')
    {
        if (!empty($messages)) {
            $arrayMessage[] = "&#127757; <b>Alerte AQI - ". $city . "</b> " . " \n ";
            foreach ($messages as $message) {
                $icon = '';
                foreach ($this->getIconsWithStatus() as $key => $value) {
                    $match = (str_replace($value, '', $message) != $message);
                    if ($match) {
                        $icon = $key;
                    }
                }
                $arrayMessage[] = "<em>" . $message . "</em> " .  $icon . " " . "   \n ";
            }
            return implode(' ', $arrayMessage);
        } else {
            return '';
        }
    }

    private function formatPollensForTelegram($messages, $city = '')
    {
        $arrayMessage[] = "&#127804; <b>Alerte Pollens - ". $city . "</b> " ." \n" . " ";
        $findLetters = [
            '&#127808;' => 'bas', '&#128545;' => 'haut', '&#128520;' => 'très', '&#127803;' => 'modéré'
        ];
        foreach ($messages as $message) {
            $icon = '';
            foreach ($findLetters as $key => $value) {
                $match = (str_replace($value, '', $message) != $message);
                if ($match) {
                    $icon = $key;
                }
            }
            $arrayMessage[] = "<em>" . $message . "</em> " . $icon . " \n";
        }
        return implode(' ', $arrayMessage);
    }

    private function formatPollensForSms($messages)
    {
        $arrayMessage[] = "-- Alerte Pollens -- \n";
        foreach ($messages as $message) {
            $arrayMessage[] = strip_tags($message) . " \n";
        }
        return implode(' ', $arrayMessage);
    }

    /**
     *  Format for discord
     */
    private function formatPollenMarkDown($messages)
    {
        $arrayMessage[] = ":blossom: **Alerte Pollens** :herb:" . " ";
        $findLetters = [
            ':four_leaf_clover:' => 'bas', ':maple_leaf:' => 'haut', ':rage:' => 'très', ':sunflower:' => 'modéré'
        ];
        foreach ($messages as $message) {
            $icon = '';
            $message = str_replace('<b>', '**', $message);
            $message = str_replace('</b>', '**', $message);
            $message = strip_tags($message);

            foreach ($findLetters as $key => $value) {
                $match = (str_replace($value, '', $message) != $message);
                if ($match) {
                    $icon = $key;
                }
            }
            $arrayMessage[] = $message . "  " . $icon;
        }
        // log::add('airquality', 'debug', 'Markdown Pollen : '. (implode(' ', $arrayMessage)));
        return implode(' ', $arrayMessage);
    }

    private function formatAqiMarkdown($messages)
    {
        if (!empty($messages)) {
            $arrayMessage[] = ":earth_africa:  **Alerte AQI** ";
            foreach ($messages as $message) {
                $icon = '';
                $message = str_replace('<b>', '**', $message);
                $message = str_replace('</b>', '**', $message);
                $message = strip_tags($message);
                foreach ($this->getIconsMarkdownWithStatus() as $key => $value) {
                    $match = (str_replace($value, '', $message) != $message);
                    if ($match) {
                        $icon = $key;
                    }
                }
                $arrayMessage[] =  $message . " " . " " .  $icon;
            }
            // log::add('airquality', 'debug', 'Markdown Pollution : '. (implode(' ', $arrayMessage)));
            return implode(' ', $arrayMessage);
        } else {
            return '';
        }
    }

    private function getIconsMarkdownWithStatus()
    {
        return  [
            ':expressionless:' => 'correct', ':warning:' => 'élevé', ':persevere:' => 'mauvais', ':scream:' => 'très', ':imp:' => 'extrême', ':relaxed:' => 'modéré',
            ':hushed:' => 'moyenne', ':disappointed:' => 'dégradé', ':zzz:' => 'nul', ':ok_hand:' => 'faible', ':sweat_drops:' => 'bon'
        ];
    }


    


}
