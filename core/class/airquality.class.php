<?php
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', TRUE);
ini_set('display_errors', TRUE);
ini_set('log_errors', TRUE);
ini_set('error_log', __DIR__ . '/../../../../plugins/airquality/errors.log');

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
// if (!class_exists('ApiAqi')) {
require_once dirname(__FILE__) . '/../../3rdparty/class.ApiAqi.php';
// }
require_once dirname(__FILE__) . '/../../3rdparty/class.CreateHtmlAqi.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.IconesAqi.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.DisplayInfo.php';


class airquality extends eqLogic
{

    public static $_widgetPossibility = ['custom' => true, 'custom::layout' => false];

    public static function cron30()
    {
        foreach (eqLogic::byType(__CLASS__, true) as $airQuality) {
            if ($airQuality->getConfiguration('elements') == 'polution') {
                $airQuality->updatePollution();
            }
        }
    }

    public static function cronHourly()
    {
        foreach (eqLogic::byType(__CLASS__, true) as $airQuality) {
            if ($airQuality->getConfiguration('elements') == 'pollen') {
                $airQuality->updatePollen();
            }
        }
    }

    
    public static function cron()
    {
        foreach (self::byType('airquality') as $airQuality) {
            if ($airQuality->getIsEnable() == 1 && $airQuality->getConfiguration('elements') == 'polution') {
                $autorefresh = '2 7 * * *';
                try {
                    $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                    if ($c->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_forecast');
                            if (is_object($refresh)) {
                                $refresh->execCmd();
                                log::add('airquality', 'debug', 'Refresh Cron Day 7h02' . $airQuality->getHumanName());
                            } else {
                                log::add('airquality', 'debug', 'Impossible de trouver la commande refresh pour ' . $airQuality->getHumanName());
                            }
                        } catch (Exception $e) {
                            log::add('airquality', 'debug', __('Erreur pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    log::add('airquality', 'debug', __('Expression cron non valide pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $autorefresh);
                }
            }
            if ($airQuality->getIsEnable() == 1 && $airQuality->getConfiguration('elements') == 'pollen') {

                $autorefresh = '1 7 * * *';
                try {
                    $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                    if ($c->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_pollen_forecast');
                            if (is_object($refresh)) {
                                $refresh->execCmd();
                            } else {
                                log::add('airquality', 'debug', 'Impossible de trouver la commande refresh pour ' . $airQuality->getHumanName());
                            }
                        } catch (Exception $e) {
                            log::add('airquality', 'debug', __('Erreur pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    log::add('airquality', 'debug', __('Expression cron non valide pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $autorefresh. ' - ' .  $e->getMessage());
                }
            }
        }
    }

    public function preInsert()
    {
        $this->setCategory('heating', 1);
        $this->setIsEnable(1);
        $this->setIsVisible(1);
    }


    public function preUpdate()
    {
        if ($this->getIsEnable()) {
            switch ($this->getConfiguration('searchMode')) {

                case 'city_mode':
                    if ($this->getConfiguration('city') == '' || $this->getConfiguration('country_code') == '') {
                        throw new Exception(__('La ville ou le code pays ne peuvent être vide', __FILE__));
                    }
                    break;
                case 'long_lat_mode':
                    if ($this->getConfiguration('longitude') == '' || $this->getConfiguration('latitude') == '') {
                        throw new Exception(__('La longitude ou la latitude ne peuvent être vide', __FILE__));
                    }
                    break;
                case 'dynamic_mode':
                    if ($this->getConfiguration('geoLongitude') == '' || $this->getConfiguration('geoLatitude') == '') {
                        throw new Exception(__('Probleme de localisation par le navigateur', __FILE__));
                    }
                    break;
            }
        }
    }

    public function postSave()
    {
        if ($this->getIsEnable() && $this->getConfiguration('elements') == 'polution'  ) {
            $cmd = $this->getCmd(null, 'refresh');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
            $cmd = $this->getCmd(null, 'refresh_forecast');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
        }
        if ($this->getIsEnable() && $this->getConfiguration('elements') == 'pollen'  ) {
            $cmd = $this->getCmd(null, 'refresh');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
            // !!  1 appel décompté comme 48 appels (2x 24h de données) de l'API ambee sur un quota de 100 appel gratuits/ jours 
            // $cmd = $this->getCmd(null, 'refresh_pollen_forecast');
            // if (is_object($cmd)) {
            //     $cmd->execCmd();
            // }
        }
    }

  
    public function preSave()
    {
        $this->setDisplay("width", "265px");
        $this->setDisplay("height", "445px");
    }

    public function postUpdate()
    {
        if ($this->getConfiguration('elements') == 'polution') {
            $setup = [
                ['name' => 'aqi', 'title' => 'AQI', 'unit' => '', 'subType' => 'numeric', 'order' => 1],
                ['name' => 'pm10', 'title' => 'PM10', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 2],
                ['name' => 'o3', 'title' => 'O³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 5],
                ['name' => 'no2', 'title' => 'NO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 7],
                ['name' => 'no', 'title' => 'NO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 4],
                ['name' => 'co', 'title' => 'CO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 6],
                ['name' => 'so2', 'title' => 'SO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 8],
                ['name' => 'nh3', 'title' => 'NH³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 9],
                ['name' => 'pm25', 'title' => 'PM2.5', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 3],
                ['name' => 'visibility', 'title' => 'Visibilité', 'unit' => 'm', 'subType' => 'numeric', 'order' => 10],
                ['name' => 'uv', 'title' => 'Indice UV', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 11],
                //Forecast
                ['name' => 'days', 'title' => 'Forecast days', 'unit' => '', 'subType' => 'string', 'order' => 12],
                ['name' => 'no2_min', 'title' => 'NO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 13],
                ['name' => 'no2_max', 'title' => 'NO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 14],
                ['name' => 'so2_min', 'title' => 'SO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 15],
                ['name' => 'so2_max', 'title' => 'SO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 16],
                ['name' => 'no_min', 'title' => 'NO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 17],
                ['name' => 'no_max', 'title' => 'NO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 18],
                ['name' => 'co_min', 'title' => 'CO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 19],
                ['name' => 'co_max', 'title' => 'CO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 20],
                ['name' => 'nh3_min', 'title' => 'NH3 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 21],
                ['name' => 'nh3_max', 'title' => 'NH3 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 22],
                ['name' => 'aqi_min', 'title' => 'AQI Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23],
                ['name' => 'aqi_max', 'title' => 'AQI Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24],
                ['name' => 'o3_min', 'title' => 'O³ Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23],
                ['name' => 'o3_max', 'title' => 'O³ Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24],
                ['name' => 'pm25_min', 'title' => 'PM2.5 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 25],
                ['name' => 'pm25_max', 'title' => 'PM2.5 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 26],
                ['name' => 'pm10_min', 'title' => 'PM10 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 27],
                ['name' => 'pm10_max', 'title' => 'PM10 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 28]
            ];

            $refreshForecast = $this->getCmd(null, 'refresh_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName(__('Rafraichir Forecast', __FILE__));
            }
            $refreshForecast->setEqLogic_id($this->getId());
            $refreshForecast->setLogicalId('refresh_forecast');
            $refreshForecast->setType('action');
            $refreshForecast->setOrder(100);
            $refreshForecast->setSubType('other');
            $refreshForecast->save();

            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setOrder(99);
            $refresh->setSubType('other');
            $refresh->save();
        }

        if ($this->getConfiguration('elements') == 'pollen') {
            $setup = [
                ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 58],
                ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 59],
                ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 54],
                ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType' => 'string', 'order' => 55],
                ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType' => 'string', 'order' => 56],
                ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType' => 'string', 'order' => 57],
                ['name' => 'poaceae', 'title' => 'Graminées', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 19],
                ['name' => 'alder', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 6],
                ['name' => 'birch', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 7],
                ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 8],
                ['name' => 'elm', 'title' => 'Orme', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 9],
                ['name' => 'hazel', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 10],
                ['name' => 'oak', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 11],
                ['name' => 'pine', 'title' => 'Pin', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 12],
                ['name' => 'plane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 13],
                ['name' => 'poplar', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 14],
                ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 15],
                ['name' => 'mugwort', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 16],
                ['name' => 'nettle', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 17],
                ['name' => 'ragweed', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 18],
                ['name' => 'others', 'title' => 'Autres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 22],
                ['name' => 'updatedAt', 'title' => 'Update at', 'unit' => '', 'subType' => 'string', 'order' => 60],
            
                ['name' => 'days', 'title' => 'Forecast days Pollen', 'unit' => '', 'subType' => 'string', 'order' => 23],
                ['name' => 'poaceae_min', 'title' => "Grass-Poaceae Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 24],
                ['name' => 'poaceae_max', 'title' => 'Grass-Poaceae Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 25],
                ['name' => 'alder_min', 'title' => "Alder Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 26],
                ['name' => 'alder_max', 'title' => 'Alder Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 27],
                ['name' => 'birch_min', 'title' => "Birch Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 28],
                ['name' => 'birch_max', 'title' => "Birch Maxi prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 29],
                ['name' => 'cypress_min', 'title' => "Cypress Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 30],
                ['name' => 'cypress_max', 'title' => 'Cypress Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 31],
                ['name' => 'elm_min', 'title' => "Elm Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 32],
                ['name' => 'elm_max', 'title' => 'Elm Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 33],
                ['name' => 'hazel_min', 'title' => "Hazel Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 34],
                ['name' => 'hazel_max', 'title' => 'Hazel Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 35],
                ['name' => 'oak_min', 'title' => "Oak Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 36],
                ['name' => 'oak_max', 'title' => 'Oak Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 37],
                ['name' => 'pine_min', 'title' => "Pine Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 38],
                ['name' => 'pine_max', 'title' => 'Pine Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 39],
                ['name' => 'plane_min', 'title' => "Plane Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 40],
                ['name' => 'plane_max', 'title' => 'Plane Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 41],
                ['name' => 'poplar_min', 'title' => "Poplar Cottonwood Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 42],
                ['name' => 'poplar_max', 'title' => 'Poplar Cottonwood Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 43],
                ['name' => 'chenopod_min', 'title' => "Chenopod Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 44],
                ['name' => 'chenopod_max', 'title' => 'Chenopod Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 45],
                ['name' => 'mugwort_min', 'title' => "Mugwort Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 46],
                ['name' => 'mugwort_max', 'title' => 'Mugwort Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 47],
                ['name' => 'nettle_min', 'title' => "Nettle Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 48],
                ['name' => 'nettle_max', 'title' => 'Nettle Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 49],
                ['name' => 'ragweed_min', 'title' => "Ragweed Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 50],
                ['name' => 'ragweed_max', 'title' => 'Ragweed Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 51],
                ['name' => 'others_min', 'title' => "Others Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 52],
                ['name' => 'others_max', 'title' => 'Others Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 53],
            ];


            $refreshForecast = $this->getCmd(null, 'refresh_pollen_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName(__('Rafraichir Forecast Pollen', __FILE__));
            }
            $refreshForecast->setEqLogic_id($this->getId());
            $refreshForecast->setLogicalId('refresh_pollen_forecast');
            $refreshForecast->setType('action');
            $refreshForecast->setOrder(100);
            $refreshForecast->setSubType('other');
            $refreshForecast->save();

            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setOrder(99);
            $refresh->setSubType('other');
            $refresh->save();
        }

        foreach ($setup as $command) {
            $cmdInfo = $this->getCmd(null, $command['name']);
            if (!is_object($cmdInfo)) {
                $cmdInfo = new airqualityCmd();
                $cmdInfo->setName(__($command['title'], __FILE__));
            }
            $cmdInfo->setEqLogic_id($this->getId());
            $cmdInfo->setLogicalId($command['name']);
            $cmdInfo->setType('info');
            $cmdInfo->setOrder($command['order']);
            $cmdInfo->setTemplate('dashboard', 'tile');
            $cmdInfo->setSubType($command['subType']);
            $cmdInfo->setUnite($command['unit']);
            $cmdInfo->setDisplay('generic_type', 'GENERIC_INFO');
            $cmdInfo->save();
        }
    }

    public function toHtml($_version = 'dashboard')
    {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }

        $this->emptyCacheWidget(); //vide le cache. Pour le développement

        $version = jeedom::versionAlias($_version);
        // Compteur pollen actif
        $activePollen = 0;

        if ($this->getConfiguration('elements') == 'polution') {
            $icone = new IconesAqi;
            $elementTemplate = getTemplate('core', $version, 'element', 'airquality');
      
        } else  if ($this->getConfiguration('elements') == 'pollen') {
            $icone = new IconesPollen;
            $elementTemplate = getTemplate('core', $version, 'elementPollen', 'airquality');
        }

        $display = new DisplayInfo;

        foreach ($this->getCmd('info') as $cmd) {
         
            // Verification si la valeur doit etre afficher todo
            // if ($this->getConfiguration($cmd->getLogicalId(), 0) === 1 || 0 === 0) {

                // Preparation des valeurs à remplacer 
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';

                // Commande/Element  à afficher et remplacer 
                $element = $this->getCmd(null, $nameCmd);

                if (is_object($element)) {

                    // Pour Affichage spécial 
                    if ($nameCmd == 'uv' || $nameCmd == 'visibility') {
                        // $replace[$info] = $display->getAqiName($element->execCmd());
                        $replace[$commandValue] = $element->execCmd();
                        $replace[$commandNameId] = $element->getId();
                        $replace[$commandName] =  $element->getName();
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                        $replace[$nameIcon] = $newIcon;

                    } else  if ($nameCmd == 'tree_pollen' || $nameCmd == 'grass_pollen'  || $nameCmd == 'weed_pollen') {

                        $replace[$commandValue] = $element->execCmd();
                        $replace[$commandNameId] = $element->getId();
                        $replace[$commandName] =  $element->getName();
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                        $replace[$nameIcon] = $newIcon;

                        $listPollen = '#list_' . $nameCmd . '#';
                        $replace[$listPollen] =  $display->getListPollen($nameCmd);
                    } else  if ($nameCmd == 'grass_risk' || $nameCmd == 'tree_risk' || $nameCmd == 'weed_risk') {
                        $replace[$commandValue] = $display->getPollenRisk($element->execCmd());
                    } else  if ($nameCmd == 'updatedAt') {
                        $replace['#updatedAt#'] = $element->execCmd();
                    } else  if (
                        $nameCmd == 'days' || $nameCmd == 'no2_min' || $nameCmd == 'no2_max' || $nameCmd == 'so2_min' || $nameCmd == 'so2_max'
                        || $nameCmd == 'no_min' || $nameCmd == 'no_max' || $nameCmd == 'co_min' || $nameCmd == 'co_max'
                        || $nameCmd == 'nh3_min' || $nameCmd == 'nh3_max' || $nameCmd == 'aqi_min' || $nameCmd == 'aqi_max'
                        || $nameCmd == 'o3_min' || $nameCmd == 'o3_max' || $nameCmd == 'pm10_min' || $nameCmd == 'pm10_max'  || $nameCmd == 'pm25_min' || $nameCmd == 'pm25_max'
                        || $nameCmd == 'poaceae_min' || $nameCmd == 'poaceae_max' || $nameCmd == 'alder_min' || $nameCmd == 'alder_max'  || $nameCmd == 'birch_min'
                        || $nameCmd == 'birch_max' || $nameCmd == 'cypress_min' || $nameCmd == 'cypress_max' || $nameCmd == 'elm_min'  || $nameCmd == 'elm_max'
                        || $nameCmd == 'hazel_min' || $nameCmd == 'hazel_max' || $nameCmd == 'oak_min' || $nameCmd == 'oak_max'  || $nameCmd == 'pine_min'
                        || $nameCmd == 'pine_max' || $nameCmd == 'plane_min' || $nameCmd == 'plane_max' || $nameCmd == 'poplar_min'  || $nameCmd == 'poplar_max'
                        || $nameCmd == 'chenopod_min' || $nameCmd == 'chenopod_max' || $nameCmd == 'mugwort_min' || $nameCmd == 'mugwort_max'  || $nameCmd == 'nettle_min'
                        || $nameCmd == 'nettle_max' || $nameCmd == 'ragweed_min' || $nameCmd == 'ragweed_max' || $nameCmd == 'others_min'  || $nameCmd == 'others_max'
                    ) {
                        // rien 
                    }
                  
                    else {
                     
                        // Incrémentation Compteur de pollens actifs 
                        $activePollen = ($element->execCmd() > 0) ? $activePollen + 1 : $activePollen;

                        if ($element->execCmd() > 0 && $this->getConfiguration('elements') == 'pollen' || $this->getConfiguration('elements') == 'polution' && $this->getConfiguration($nameCmd, 0) === 1 ){
                           
                            $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId(), '30px');
                            $unitreplace['#icone#'] = $newIcon;
                            $unitreplace['#id#'] = $this->getId();
                            $unitreplace['#value#'] = ($this->getConfiguration('elements') == 'polution') ?  $display->formatValueForDisplay($element->execCmd()) : $element->execCmd();
                            $unitreplace['#name#'] = $cmd->getLogicalId();
                            $unitreplace['#display-name#'] = $cmd->getName();
                            $unitreplace['#cmdid#'] = $cmd->getId();
                            $unitreplace['#history#'] = 'history cursor';
                            $unitreplace['#info-modalcmd#'] = 'info-modal' . $element->getId();
                            $unitreplace['#unity#'] = $element->getUnite();
                        
                            $maxCmd = $this->getCmd(null, $nameCmd.'_max');
                            $unitreplace['#max#'] = $maxCmd->execCmd() ;
                            $minCmd = $this->getCmd(null, $nameCmd.'_min');
                            $unitreplace['#min#'] = $minCmd->execCmd() ;
                            $unitreplace['#color#'] =  $icone->getColor();
                            $labels = $this->getCmd(null, 'days');
                            $unitreplace['#labels#'] =  $labels->execCmd();
                    

                            if ($element->getIsHistorized() == 1) {
                                // Historique Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                                $historyStatistique = $element->getStatistique($startHist, date('Y-m-d H:i:s'));
                                $unitreplace['#minHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['min'], 'short');
                                $unitreplace['#maxHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['max'], 'short');
                                $unitreplace['#averageHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['avg'], 'short');
                                // Tendance Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 10 . ' hour'));
                                $tendance = $element->getTendance($startHist, date('Y-m-d H:i:s'));
                                if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                                    $unitreplace['#tendance#'] = 'fas fa-arrow-up';
                                } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                                    $unitreplace['#tendance#'] = 'fas fa-arrow-down';
                                } else {
                                    $unitreplace['#tendance#'] = 'fas fa-minus';
                                }
                                $unitreplace['#display#'] = '';
                            } else {
                                $unitreplace['#display#'] = 'hidden';
                            }
                            $tab[] = template_replace($unitreplace, $elementTemplate);                           
                        }

                        // Affichage central pour AQI 
                        if ($nameCmd == 'aqi') {
                            $replace[$commandValue] = $element->execCmd();
                            $replace[$info] = $display->getAqiName($element->execCmd());
                            $replace[$commandNameId] = $element->getId();
                            $replace[$commandName] =  $element->getName();
                            $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                            $replace[$nameIcon] = $newIcon;
                        }
                    }
                }
            // }
        }
        // End foreach // 


        // Replace Global 
        if ($this->getConfiguration('elements') === 'polution') {
            $replace['#index_name#'] = __('Indice', __FILE__);
        } else {
            $replace['#active_pollen_label#'] = __('Pollens actifs', __FILE__);
            $replace['#activePollen#'] = $activePollen;
        }

        $elementHtml = new CreateHtmlAqi($tab, $this->getId(), 1, $version);
        $replace['#mini_slide#'] =  $elementHtml->getLayer();

        $refresh = $this->getCmd(null, 'refresh');
        $replace['#refresh#'] = is_object($refresh) ? $refresh->getId() : '';


        if ($this->getConfiguration('animation_aqi') === 'disable_anim') {
            $replace['#animation#'] = 'disabled';
            $replace['#classCaroussel#'] = 'data-interval="false"';
        } else {
            $replace['#animation#'] = 'active';
            $replace['#classCaroussel#'] = '';
        }


        if ($this->getConfiguration('elements') == 'polution') {
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality', __CLASS__)));
        } else {
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'pollen', __CLASS__)));
        }
    }



    public static function postConfig_apikey()
    {
        if (config::byKey('apikey', 'airquality') == '') {
            throw new Exception('La clef API ne peut être vide');
        }
        $api = new ApiAqi;
        $checkApi = $api->getAqi(50, 50);
        if (!$checkApi) {
            throw new Exception('La clef API n\'est pas valide ou pas encore active');
        }
    }

    /**
     * Pour recevoir appel Ajax pour reverse géoloc. Utilisé dans la configuration
     */
    public static function fetchReverseGeoLoc($longitude, $latitude)
    {
        $api = new ApiAqi;
        return $api->callApiReverseGeoLoc($longitude, $latitude);
    }

    /**
     * Pour appel Ajax pour geoloc avec ville. Utilisé dans la configuration  
     */
    public static function fetchGeoLoc($city, $country_code, $state_code = null)
    {
        $api = new ApiAqi;
        return $api->callApiGeoLoc($city, $country_code, $state_code = null);
    }
    
    /**
     * todo
     */
    public static function setDynGeoLoc($latitude, $longitude)
    {
        config::save('DynLatitude', $latitude, 'airquality');
        config::save('DynLongitude', $longitude, 'airquality');
        // $resLat = trim(config::byKey('DynLatitude', 'airquality'));
        // $resLong = trim(config::byKey('DynLongitude', 'airquality'));
  
        
        // $api = new ApiAqi;
        // return  $api->callApiReverseGeoLoc($latitude, $longitude);
    }

    /**
     * Redirige l'appel API vers la bonne fonction + check des coordonnées 
     */
    public function getApiData(string $apiName)
    {

        $api = new ApiAqi();
        switch ($this->getConfiguration('searchMode')) {
            case 'city_mode':
                if (
                    $this->getConfiguration('city_longitude') || $this->getConfiguration('city_latitude') ||
                    $this->getConfiguration('city_longitude') != '' || $this->getConfiguration('city_latitude') != ''
                ) {
                    return $api->$apiName($this->getConfiguration('city_latitude'), $this->getConfiguration('city_longitude'));
                } else {
                    throw new Exception('Les coordonnées sont vides, testez la ville dans la configuration ');
                }
            case 'long_lat_mode':
                return $api->$apiName($this->getConfiguration('latitude'), $this->getConfiguration('longitude'));

            case 'dynamic_mode':
                if ($this->getConfiguration('geoLongitude') == '' || $this->getConfiguration('geoLatitude') == '') {
                    throw new Exception('Probleme de localisation');
                }
                return $api->$apiName($this->getConfiguration('geoLatitude'), $this->getConfiguration('geoLongitude'));

            case 'server_mode':
                return $api->$apiName(config::byKey('info::latitude'), config::byKey('info::longitude'));
        }
    }


    /**
     * Lance l'update des données live pollution ou pollen 
     */
    public function updateData()
    {
        if ($this->getConfiguration('elements') == 'polution') {
            $this->updatePollution();
        } else if ($this->getConfiguration('elements') == 'pollen') {
            $this->updatePollen();
        }
    }

    /**
     * Appel api Pollen Live + Update des Commands + reorder by level  
     */
    public function updatePollen()
    {
        $dataAll = $this->getApiData('getAmbee');
        $dataPollen = $dataAll->data;
        $this->checkAndUpdateCmd('poaceae', $dataPollen[0]->Species->Grass->{"Grass / Poaceae"});
        $this->checkAndUpdateCmd('alder', $dataPollen[0]->Species->Tree->Alder);
        $this->checkAndUpdateCmd('birch', $dataPollen[0]->Species->Tree->Birch);
        $this->checkAndUpdateCmd('grass_pollen', $dataPollen[0]->Count->grass_pollen);
        $this->checkAndUpdateCmd('tree_pollen', $dataPollen[0]->Count->tree_pollen);
        $this->checkAndUpdateCmd('weed_pollen', $dataPollen[0]->Count->weed_pollen);
        $this->checkAndUpdateCmd('weed_risk', $dataPollen[0]->Risk->weed_pollen);
        $this->checkAndUpdateCmd('grass_risk', $dataPollen[0]->Risk->grass_pollen);
        $this->checkAndUpdateCmd('tree_risk', $dataPollen[0]->Risk->tree_pollen);
        $this->checkAndUpdateCmd('cypress', $dataPollen[0]->Species->Tree->Cypress);
        $this->checkAndUpdateCmd('elm', $dataPollen[0]->Species->Tree->Elm);
        $this->checkAndUpdateCmd('hazel', $dataPollen[0]->Species->Tree->Hazel);
        $this->checkAndUpdateCmd('oak', $dataPollen[0]->Species->Tree->Oak);
        $this->checkAndUpdateCmd('pine', $dataPollen[0]->Species->Tree->Pine);
        $this->checkAndUpdateCmd('plane', $dataPollen[0]->Species->Tree->Plane);
        $this->checkAndUpdateCmd('poplar', $dataPollen[0]->Species->Tree->{"Poplar / Cottonwood"});
        $this->checkAndUpdateCmd('chenopod', $dataPollen[0]->Species->Weed->Chenopod);
        $this->checkAndUpdateCmd('mugwort', $dataPollen[0]->Species->Weed->Mugwort);
        $this->checkAndUpdateCmd('nettle', $dataPollen[0]->Species->Weed->Nettle);
        $this->checkAndUpdateCmd('ragweed', $dataPollen[0]->Species->Weed->Ragweed);
        $this->checkAndUpdateCmd('others', $dataPollen[0]->Species->Others);
        $this->checkAndUpdateCmd('updatedAt', $dataPollen[0]->updatedAt);
        $this->reorderCmdPollen();
        $this->refreshWidget();
    }

    /**
     * Appel api AqI Live + Update des Commands 
     */
    public function updatePollution()
    {
        $data = $this->getApiData('getAqi');
        $this->checkAndUpdateCmd('aqi', $data->main->aqi);
        $this->checkAndUpdateCmd('no2', $data->components->no2);
        $this->checkAndUpdateCmd('no', $data->components->no);
        $this->checkAndUpdateCmd('co', $data->components->co);
        $this->checkAndUpdateCmd('o3', $data->components->o3);
        $this->checkAndUpdateCmd('so2', $data->components->so2);
        $this->checkAndUpdateCmd('nh3', $data->components->nh3);
        $this->checkAndUpdateCmd('pm25', $data->components->pm2_5);
        $this->checkAndUpdateCmd('pm10', $data->components->pm10);
        $data = $this->getApiData('getOneCallApi');
        $this->checkAndUpdateCmd('uv', $data->uvi);
        $this->checkAndUpdateCmd('visibility', $data->visibility);
        $this->refreshWidget();
    }

    /**
     * Appel api Forecast API + Update des Commands 
     */
    public function updateForecastAQI()
    {
        $forecast =  $this->getApiData('getForecast');
        $this->checkAndUpdateCmd('days', json_encode($forecast['no2']['day']));
        $this->checkAndUpdateCmd('no2_min', json_encode($forecast['no2']['min']));
        $this->checkAndUpdateCmd('no2_max', json_encode($forecast['no2']['max']));
        $this->checkAndUpdateCmd('no_min', json_encode($forecast['no']['min']));
        $this->checkAndUpdateCmd('no_max', json_encode($forecast['no']['max']));
        $this->checkAndUpdateCmd('so2_min', json_encode($forecast['so2']['min']));
        $this->checkAndUpdateCmd('so2_max', json_encode($forecast['so2']['max']));
        $this->checkAndUpdateCmd('co_min', json_encode($forecast['co']['min']));
        $this->checkAndUpdateCmd('co_max', json_encode($forecast['co']['max']));
        $this->checkAndUpdateCmd('nh3_min', json_encode($forecast['nh3']['min']));
        $this->checkAndUpdateCmd('nh3_max', json_encode($forecast['nh3']['max']));
        $this->checkAndUpdateCmd('aqi_min', json_encode($forecast['aqi']['min']));
        $this->checkAndUpdateCmd('aqi_max', json_encode($forecast['aqi']['max']));
        $this->checkAndUpdateCmd('pm10_min', json_encode($forecast['pm10']['min']));
        $this->checkAndUpdateCmd('pm10_max', json_encode($forecast['pm10']['max']));
        $this->checkAndUpdateCmd('o3_min', json_encode($forecast['o3']['min']));
        $this->checkAndUpdateCmd('o3_max', json_encode($forecast['o3']['max']));
        $this->checkAndUpdateCmd('pm25_min', json_encode($forecast['pm2_5']['min']));
        $this->checkAndUpdateCmd('pm25_max', json_encode($forecast['pm2_5']['max']));
        $this->refreshWidget();
    }

    /**
     * Appel api Forecast Pollens + Update des Commands 
     */
    public function updateForecastPollen()
    {
        $forecast =  $this->getApiData('getForecastPollen');
        log::add('airquality', 'debug', json_encode($forecast));
        $this->checkAndUpdateCmd('days', json_encode($forecast['Alder']['day']));
        $this->checkAndUpdateCmd('poaceae_min', json_encode($forecast['Poaceae']['min']));
        $this->checkAndUpdateCmd('poaceae_max', json_encode($forecast['Poaceae']['max']));
        $this->checkAndUpdateCmd('alder_min', json_encode($forecast['Alder']['min']));
        $this->checkAndUpdateCmd('alder_max', json_encode($forecast['Alder']['max']));
        $this->checkAndUpdateCmd('birch_min', json_encode($forecast['Birch']['min']));
        $this->checkAndUpdateCmd('birch_max', json_encode($forecast['Birch']['max']));
        $this->checkAndUpdateCmd('cypress_min', json_encode($forecast['Cypress']['min']));
        $this->checkAndUpdateCmd('cypress_max', json_encode($forecast['Cypress']['max']));
        $this->checkAndUpdateCmd('elm_min', json_encode($forecast['Elm']['min']));
        $this->checkAndUpdateCmd('elm_max', json_encode($forecast['Elm']['max']));
        $this->checkAndUpdateCmd('hazel_min', json_encode($forecast['Hazel']['min']));
        $this->checkAndUpdateCmd('hazel_max', json_encode($forecast['Hazel']['max']));
        $this->checkAndUpdateCmd('oak_min', json_encode($forecast['Oak']['min']));
        $this->checkAndUpdateCmd('oak_max', json_encode($forecast['Oak']['max']));
        $this->checkAndUpdateCmd('pine_min', json_encode($forecast['Pine']['min']));
        $this->checkAndUpdateCmd('pine_max', json_encode($forecast['Pine']['max']));
        $this->checkAndUpdateCmd('plane_min', json_encode($forecast['Plane']['min']));
        $this->checkAndUpdateCmd('plane_max', json_encode($forecast['Plane']['max']));
        $this->checkAndUpdateCmd('poplar_min', json_encode($forecast['Poplar']['min']));
        $this->checkAndUpdateCmd('poplar_max', json_encode($forecast['Poplar']['max']));
        $this->checkAndUpdateCmd('chenopod_min', json_encode($forecast['Chenopod']['min']));
        $this->checkAndUpdateCmd('chenopod_max', json_encode($forecast['Chenopod']['max']));
        $this->checkAndUpdateCmd('mugwort_min', json_encode($forecast['Mugwort']['min']));
        $this->checkAndUpdateCmd('mugwort_max', json_encode($forecast['Mugwort']['max']));
        $this->checkAndUpdateCmd('nettle_min', json_encode($forecast['Nettle']['min']));
        $this->checkAndUpdateCmd('nettle_max', json_encode($forecast['Nettle']['max']));
        $this->checkAndUpdateCmd('ragweed_min', json_encode($forecast['Ragweed']['min']));
        $this->checkAndUpdateCmd('ragweed_max', json_encode($forecast['Ragweed']['max']));
        $this->checkAndUpdateCmd('others_min', json_encode($forecast['Others']['min']));
        $this->checkAndUpdateCmd('others_max', json_encode($forecast['Others']['max']));
        $this->refreshWidget();
    }

    /**
     *  Réarrange par ordre décroissant l'affichage les pollens 
     */
    public function reorderCmdPollen()
    {

        foreach ($this->getCmd('info') as $cmd) {
            $index = $cmd->getLogicalId();
            switch ($index) {
                case 'alder':
                case 'birch':
                case 'cypress':
                case 'elm':
                case 'alder':
                case 'hazel':
                case 'oak':
                case 'pine':
                case 'plane':
                case 'poplar':
                case 'chenopod':
                case 'mugwort':
                case 'nettle':
                case 'ragweed':
                case 'poaceae':
                case 'others':
                    $element = $this->getCmd(null, $index);
                    $value = $element->execCmd();
                    $tabOrder[$index] = $value;
                    break;
                default:
                    break;
            }
        }
        arsort($tabOrder, SORT_REGULAR);
        $k = 0;
        foreach ($tabOrder as $key => $orderedCmd) {
            $cmd = $this->getCmd(null, $key);
            $cmd->setOrder($k);
            $cmd->save();
            $k++;
        }
    }
}


class airqualityCmd extends cmd
{

    public static $_widgetPossibility = array('custom' => false);

    public function execute($_options = array())
    {
        if ($this->getLogicalId() == 'refresh') {
            $this->getEqLogic()->updateData();
        }

        if ($this->getLogicalId() == 'refresh_forecast') {
            $this->getEqLogic()->updateForecastAQI();
        }

        if ($this->getLogicalId() == 'refresh_pollen_forecast') {
            $this->getEqLogic()->updateForecastPollen();
        }
    }
}
