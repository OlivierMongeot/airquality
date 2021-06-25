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

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.ApiAqi.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.CreateHtmlAqi.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.IconesAqi.php';
require_once dirname(__FILE__) . '/../../3rdparty/class.DisplayInfo.php';


class airquality extends eqLogic
{

    public static $_widgetPossibility = ['custom' => true, 'custom::layout' => false];

    private static $setupAqi =  [
        ['name' => 'aqi', 'title' => 'AQI', 'unit' => '', 'subType' => 'numeric', 'order' => 1, 'display' => 'both'],
        ['name' => 'pm10', 'title' => 'PM10', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 2, 'display' => 'slide'],
        ['name' => 'o3', 'title' => 'O³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 5, 'display' => 'slide'],
        ['name' => 'no2', 'title' => 'NO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 7, 'display' => 'slide'],
        ['name' => 'no', 'title' => 'NO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 4, 'display' => 'slide'],
        ['name' => 'co', 'title' => 'CO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 6, 'display' => 'slide'],
        ['name' => 'so2', 'title' => 'SO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 8, 'display' => 'slide'],
        ['name' => 'nh3', 'title' => 'NH³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 9, 'display' => 'slide'],
        ['name' => 'pm25', 'title' => 'PM2.5', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 3, 'display' => 'slide'],
        ['name' => 'visibility', 'title' => 'Visibilité', 'unit' => 'm', 'subType' => 'numeric', 'order' => 10, 'display' => 'main'],
        ['name' => 'uv', 'title' => 'Indice UV', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 11, 'display' => 'main'],
        //Forecast
        ['name' => 'days', 'title' => 'Forecast days', 'unit' => '', 'subType' => 'string', 'order' => 12, 'display' => 'chart'],
        ['name' => 'no2_min', 'title' => 'NO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 13, 'display' => 'chart'],
        ['name' => 'no2_max', 'title' => 'NO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 14, 'display' => 'chart'],
        ['name' => 'so2_min', 'title' => 'SO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 15, 'display' => 'chart'],
        ['name' => 'so2_max', 'title' => 'SO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 16, 'display' => 'chart'],
        ['name' => 'no_min', 'title' => 'NO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 17, 'display' => 'chart'],
        ['name' => 'no_max', 'title' => 'NO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 18, 'display' => 'chart'],
        ['name' => 'co_min', 'title' => 'CO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 19, 'display' => 'chart'],
        ['name' => 'co_max', 'title' => 'CO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 20, 'display' => 'chart'],
        ['name' => 'nh3_min', 'title' => 'NH3 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 21, 'display' => 'chart'],
        ['name' => 'nh3_max', 'title' => 'NH3 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 22, 'display' => 'chart'],
        ['name' => 'aqi_min', 'title' => 'AQI Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'aqi_max', 'title' => 'AQI Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'o3_min', 'title' => 'O³ Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'o3_max', 'title' => 'O³ Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'pm25_min', 'title' => 'PM2.5 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 25, 'display' => 'chart'],
        ['name' => 'pm25_max', 'title' => 'PM2.5 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 26, 'display' => 'chart'],
        ['name' => 'pm10_min', 'title' => 'PM10 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 27, 'display' => 'chart'],
        ['name' => 'pm10_max', 'title' => 'PM10 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 28, 'display' => 'chart'],
    ];

    private static $setupPollen = [
        ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 58, 'display' => 'main'],
        ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 59, 'display' => 'main'],
        ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 54, 'display' => 'main'],
        ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType' => 'string', 'order' => 55, 'display' => 'main'],
        ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType' => 'string', 'order' => 56, 'display' => 'main'],
        ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType' => 'string', 'order' => 57, 'display' => 'main'],
        ['name' => 'poaceae', 'title' => 'Graminées', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 19, 'display' => 'slide'],
        ['name' => 'alder', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 6, 'display' => 'slide'],
        ['name' => 'birch', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 7, 'display' => 'slide'],
        ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 8, 'display' => 'slide'],
        ['name' => 'elm', 'title' => 'Orme', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 9, 'display' => 'slide'],
        ['name' => 'hazel', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 10, 'display' => 'slide'],
        ['name' => 'oak', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 11, 'display' => 'slide'],
        ['name' => 'pine', 'title' => 'Pin', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 12, 'display' => 'slide'],
        ['name' => 'plane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 13, 'display' => 'slide'],
        ['name' => 'poplar', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 14, 'display' => 'slide'],
        ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 15, 'display' => 'slide'],
        ['name' => 'mugwort', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 16, 'display' => 'slide'],
        ['name' => 'nettle', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 17, 'display' => 'slide'],
        ['name' => 'ragweed', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 18, 'display' => 'slide'],
        ['name' => 'others', 'title' => 'Autres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 22, 'display' => 'slide'],
        ['name' => 'updatedAt', 'title' => 'Update at', 'unit' => '', 'subType' => 'string', 'order' => 60, 'display' => 'main'],
    
        ['name' => 'days', 'title' => 'Forecast days Pollen', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'poaceae_min', 'title' => "Grass-Poaceae Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'poaceae_max', 'title' => 'Grass-Poaceae Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 25, 'display' => 'chart'],
        ['name' => 'alder_min', 'title' => "Alder Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 26, 'display' => 'chart'],
        ['name' => 'alder_max', 'title' => 'Alder Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 27, 'display' => 'chart'],
        ['name' => 'birch_min', 'title' => "Birch Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 28, 'display' => 'chart'],
        ['name' => 'birch_max', 'title' => "Birch Maxi prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 29, 'display' => 'chart'],
        ['name' => 'cypress_min', 'title' => "Cypress Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 30, 'display' => 'chart'],
        ['name' => 'cypress_max', 'title' => 'Cypress Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 31, 'display' => 'chart'],
        ['name' => 'elm_min', 'title' => "Elm Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 32, 'display' => 'chart'],
        ['name' => 'elm_max', 'title' => 'Elm Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 33, 'display' => 'chart'],
        ['name' => 'hazel_min', 'title' => "Hazel Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 34, 'display' => 'chart'],
        ['name' => 'hazel_max', 'title' => 'Hazel Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 35, 'display' => 'chart'],
        ['name' => 'oak_min', 'title' => "Oak Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 36, 'display' => 'chart'],
        ['name' => 'oak_max', 'title' => 'Oak Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 37, 'display' => 'chart'],
        ['name' => 'pine_min', 'title' => "Pine Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 38, 'display' => 'chart'],
        ['name' => 'pine_max', 'title' => 'Pine Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 39, 'display' => 'chart'],
        ['name' => 'plane_min', 'title' => "Plane Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 40, 'display' => 'chart'],
        ['name' => 'plane_max', 'title' => 'Plane Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 41, 'display' => 'chart'],
        ['name' => 'poplar_min', 'title' => "Poplar Cottonwood Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 42, 'display' => 'chart'],
        ['name' => 'poplar_max', 'title' => 'Poplar Cottonwood Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 43, 'display' => 'chart'],
        ['name' => 'chenopod_min', 'title' => "Chenopod Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 44, 'display' => 'chart'],
        ['name' => 'chenopod_max', 'title' => 'Chenopod Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 45, 'display' => 'chart'],
        ['name' => 'mugwort_min', 'title' => "Mugwort Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 46, 'display' => 'chart'],
        ['name' => 'mugwort_max', 'title' => 'Mugwort Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 47, 'display' => 'chart'],
        ['name' => 'nettle_min', 'title' => "Nettle Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 48, 'display' => 'chart'],
        ['name' => 'nettle_max', 'title' => 'Nettle Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 49, 'display' => 'chart'],
        ['name' => 'ragweed_min', 'title' => "Ragweed Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 50, 'display' => 'chart'],
        ['name' => 'ragweed_max', 'title' => 'Ragweed Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 51, 'display' => 'chart'],
        ['name' => 'others_min', 'title' => "Others Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 52, 'display' => 'chart'],
        ['name' => 'others_max', 'title' => 'Others Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 53, 'display' => 'chart'],
    ];


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
    
                try {
                    $c = new Cron\CronExpression('2 7 * * *', new Cron\FieldFactory);
                    if ($c->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_forecast');
                            if (is_object($refresh)) {
                                $refresh->execCmd();
                            } else {
                                log::add('airquality', 'debug', __('Impossible de trouver la commande refresh pour ',__FILE__) . $airQuality->getHumanName());
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

                try {
                    $c = new Cron\CronExpression('1 7 * * *', new Cron\FieldFactory);
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
        $this->setDisplay("height", "380px");
    }

    public function postUpdate()
    {
        if ($this->getConfiguration('elements') == 'polution') {

            $refreshForecast = $this->getCmd(null, 'refresh_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast');
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
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setOrder(99);
            $refresh->setSubType('other');
            $refresh->save();
            
            $setup = self::$setupAqi;
        }

        if ($this->getConfiguration('elements') == 'pollen') {
           
            $refreshForecast = $this->getCmd(null, 'refresh_pollen_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast Pollen');
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
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setOrder(99);
            $refresh->setSubType('other');
            $refresh->save();

            $setup = self::$setupPollen;
        }

        foreach ($setup as $command) {
            $cmdInfo = $this->getCmd(null, $command['name']);
            if (!is_object($cmdInfo)) {
                $cmdInfo = new airqualityCmd();
                $cmdInfo->setName($command['title']);
            }
            $cmdInfo->setEqLogic_id($this->getId());
            $cmdInfo->setLogicalId($command['name']);
            $cmdInfo->setType('info');
            
            $cmdInfo->setOrder($command['order']);
            $cmdInfo->setTemplate('dashboard', 'tile');
            $cmdInfo->setSubType($command['subType']);
            $cmdInfo->setUnite($command['unit']);
            $cmdInfo->setDisplay('generic_type', 'GENERIC_INFO');
            $cmdInfo->setConfiguration($command['name'], $command['display']);
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
         
                // Preparation des valeurs à remplacer 
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';

                if (is_object($cmd)) {

                    // Pour Affichage Haut
                    if ($nameCmd == 'uv' ) {
                        $replace[$commandValue] = $cmd->execCmd();
                        $replace[$commandNameId] = $cmd->getId();
                        $replace[$commandName] =  __($cmd->getName(),__FILE__);
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $newIcon;
                        $replace['#uv_level#'] = $display->getUVRapport( $cmd->execCmd());
                       
                    } else if ($nameCmd == 'visibility'){
                        $replace[$commandValue] = $cmd->execCmd();
                        $replace[$commandNameId] = $cmd->getId();
                        $replace[$commandName] =  __($cmd->getName(),__FILE__);
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $newIcon;
                        $replace['#visibility_level#'] = $display->getVisibilityRapport( $cmd->execCmd());
                    }
                    else  if ($nameCmd == 'tree_pollen' || $nameCmd == 'grass_pollen'  || $nameCmd == 'weed_pollen') {
                        $replace[$commandValue] = $cmd->execCmd();
                        $replace[$commandNameId] = $cmd->getId();
                        $replace[$commandName] =  __($cmd->getName(), __FILE__);
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $newIcon;
                        $listPollen = '#list_' . $nameCmd . '#';
                        $replace[$listPollen] =  $display->getListPollen($nameCmd);
                   
                    } else  if ($nameCmd == 'grass_risk' || $nameCmd == 'tree_risk' || $nameCmd == 'weed_risk') {
                        $replace[$commandValue] = $display->getPollenRisk($cmd->execCmd());
                   
                    } else  if ($nameCmd == 'updatedAt') {
                        $replace['#updatedAt#'] = $display->parseDate($cmd->execCmd());
                   
                    } else if ($cmd->getConfiguration($nameCmd) == 'slide' || $cmd->getConfiguration($nameCmd) == 'both'  )
                    {   
                        // Incrémentation Compteur de pollens actifs 
                        $activePollen = ($cmd->execCmd() > 0) ? $activePollen + 1 : $activePollen;

                        // Check si les previsons pollen sont > 0 
                        $maxCmd = $this->getCmd(null, $nameCmd.'_max');
                        $max = $maxCmd->execCmd();
                        $max = str_replace(['[',']'],'', $max);
                        $max = array_map( 'self::toInt' , explode(",", $max));
                        $displaySlide = (array_sum($max) > 0) ? true : false;
                        
                        if ($cmd->execCmd() > 0 && $this->getConfiguration('elements') == 'pollen' && $cmd->getIsVisible() == 1 || $this->getConfiguration('elements') == 'polution' && $cmd->getIsVisible() == 1 ||  $displaySlide === true && $this->getConfiguration('elements') == 'pollen'){   
                            $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), '30px');
                            $unitreplace['#icone#'] = $newIcon;
                            $unitreplace['#id#'] = $this->getId();
                            $unitreplace['#value#'] = ($this->getConfiguration('elements') == 'polution') ?  $display->formatValueForDisplay($cmd->execCmd()) : $cmd->execCmd();
                            $unitreplace['#name#'] = $cmd->getLogicalId();
                            $unitreplace['#display-name#'] = __($cmd->getName(),__FILE__);
                            $unitreplace['#cmdid#'] = $cmd->getId();
                            $unitreplace['#history#'] = 'history cursor';
                            $unitreplace['#info-modalcmd#'] = 'info-modal'.$cmd->getLogicalId(). $this->getId();
                            $unitreplace['#unity#'] = $cmd->getUnite();
                        
                            $maxCmd = $this->getCmd(null, $nameCmd.'_max');
                            $unitreplace['#max#'] = $maxCmd->execCmd() ;
                            $minCmd = $this->getCmd(null, $nameCmd.'_min');
                            $unitreplace['#min#'] = $minCmd->execCmd() ;
                            $unitreplace['#color#'] =  $icone->getColor();
                            $labels = $this->getCmd(null, 'days');
                            $unitreplace['#labels#'] =  $labels->execCmd();
                            $unitreplace['#risk#'] =  $display->getElementRiskPollen($icone->getColor());
                            $unitreplace['#level-particule#'] =  $display->getElementRiskAqi($icone->getColor());
                            $unitreplace['#info-tooltips#'] = __("Cliquez pour + d'info",__FILE__);
                            $unitreplace['#mini#'] = __("Mini",__FILE__);
                            $unitreplace['#maxi#'] = __("Maxi",__FILE__);
                            $unitreplace['#tendency#'] = __("Tendance",__FILE__);
                            $unitreplace['#average#'] = __("Moyenne",__FILE__);
                            if ($cmd->getIsHistorized() == 1) {
                                // Historique Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                                $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
                                $unitreplace['#minHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['min'], 'short');
                                $unitreplace['#maxHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['max'], 'short');
                                $unitreplace['#averageHistoryValue#'] =  $display->formatValueForDisplay($historyStatistique['avg'], 'short');
                                // Tendance Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 12 . ' hour'));
                                $tendance = $cmd->getTendance($startHist, date('Y-m-d H:i:s'));
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
                        else {
                            // Cas Pollen à ZERO ou Pollution notVisible ////////////////////////////////////////
                            if ($this->getConfiguration('elements') == 'pollen'){
                            
                                $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), '10px');
                                $pollenZeroReplace['#icone#'] = $newIcon;
                                $pollenZeroReplace['#id#'] = $this->getId();
                                $pollenZeroReplace['#value#'] = ($this->getConfiguration('elements') == 'polution') ?  $display->formatValueForDisplay($cmd->execCmd()) : $cmd->execCmd();
                                $pollenZeroReplace['#name#'] = $cmd->getLogicalId();
                                $pollenZeroReplace['#display-name#'] = __($cmd->getName(),__FILE__);
                                $pollenZeroReplace['#cmdid#'] = $cmd->getId();
                                $pollenZeroReplace['#info-modalcmd#'] = 'info-modal'.$cmd->getLogicalId(). $this->getId();
                                $pollenZeroReplace['#message#'] = __('Aucune Détection',__FILE__);
                                $elementTemplate2 = getTemplate('core', $version, 'elementPollenZero', 'airquality');
                                $tabZero[] = template_replace($pollenZeroReplace, $elementTemplate2); 
                             
                              
                               
                            }

                        }
                      
                      
                    

                        // Affichage central pour AQI 
                        if ($nameCmd == 'aqi') {
                            $replace[$commandValue] = $cmd->execCmd();
                            $replace[$info] = $display->getAqiName($cmd->execCmd());
                            $replace[$commandNameId] = $cmd->getId();
                            $replace[$commandName] =  $cmd->getName();
                            $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                            $replace[$nameIcon] = $newIcon;
                        }
                    }
                }
        }

        $k = 0;
        if ($this->getConfiguration('elements') == 'pollen'){
            // log::add(__CLASS__,'debug', json_encode($tabZero));
               $newArray = array_chunk($tabZero, 3);
               foreach ($newArray as $arr) {
                    $tab[] = implode('',$arr);
                    $k++;
                    }
              }

        // Replace Global 
        if ($this->getConfiguration('elements') === 'polution') {
            $replace['#index_name#'] = __('Indice', __FILE__);
        } else {

          


            $replace['#active_pollen_label#'] = __('Pollens actifs', __FILE__);
            $replace['#activePollen#'] = $activePollen;
        }

        $replace['#info-tooltips#'] = __("Cliquez pour + d'info",__FILE__);

        $elementHtml = new CreateHtmlAqi($tab, $this->getId(), 1, $version, $this->getConfiguration('elements'), $k);
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


    public static function toInt($string){
        return (int)($string);
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
                    throw new Exception(__('Les coordonnées sont vides, testez la ville dans la configuration',__FILE__));
                }
            case 'long_lat_mode':
                return $api->$apiName($this->getConfiguration('latitude'), $this->getConfiguration('longitude'));

            case 'dynamic_mode':
                if ($this->getConfiguration('geoLongitude') == '' || $this->getConfiguration('geoLatitude') == '') {
                    throw new Exception(__('Probleme de localisation dynamique',__FILE__));
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
