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
if (!class_exists('ApiAqi')) {
    require_once dirname(__FILE__) . '/../../3rdparty/class.ApiAqi.php';
}
if (!class_exists('IconesAqi')) {
    require_once dirname(__FILE__) . '/../../3rdparty/class.IconesAqi.php';
}
if (!class_exists('ComponentAqi')) {
    require_once dirname(__FILE__) . '/../../3rdparty/class.ComponentAqi.php';
}
if (!class_exists('IconesPollen')) {
    require_once dirname(__FILE__) . '/../../3rdparty/class.IconesPollen.php';
}

class airquality extends eqLogic
{

    public static $_widgetPossibility = ['custom' => true, 'custom::layout' => false];


    public static function cron30()
    {
        message::add('debug','cron 30');
        foreach (self::byType('airquality') as $type) {

            if ($type->getIsEnable() == 1) {
                $cmd = $type->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    continue;
                }
                $cmd->execCmd();
            }
        }
    }

    public static function cronHourly()
    {
        message::add('debug','cron Hourly');
        foreach (self::byType('airquality') as $type) {

            if ($type->getIsEnable() == 1) {
                $cmd = $type->getCmd(null, 'refresh_pollen');
                if (!is_object($cmd)) {
                    continue;
                }
                $cmd->execCmd();
            }
        }
    }

    public static function cronDaily()
    {
        foreach (self::byType('airquality') as $type) {
            if ($type->getIsEnable() == 1) {
                $cmd = $type->getCmd(null, 'refresh_forecast');
                if (!is_object($cmd)) {
                    continue;
                }
                $cmd->execCmd();
            }
        }
    }


    // public static function cron() {

	// 	foreach (self::byType('airquality', true) as $airQuality) {
	// 		// $autorefresh = '0 0 1 * * ?';
    //         $autorefresh = '0 * * ? * *'; // Chaque Minute
    //         try {
    //             $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
    //             if ($c->isDue()) {
    //                 log::add('airquality', 'debug','is due');
    //                 try {
    //                     $refresh = $airQuality->getCmd(null, 'refresh');
    //                     if(is_object($refresh)) {
    //                         // $refresh->execCmd();
    //                         message::add('debug','refresh Cron Custom test ');
    //                     } else {
    //                         log::add('airquality', 'debug', __('Impossible de trouver la commande refresh pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $exc->getMessage());
    //                     }
    //                 } catch (Exception $exc) {
    //                     log::add('swisairqualitysmeteo', 'debug', __('Erreur pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $exc->getMessage());
    //                 }
    //             }
    //         } catch (Exception $exc) {
    //             log::add('airquality', 'debug', __('Expression cron non valide pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $autorefresh);
    //         }
	// 	}


        
	// }

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
    
    // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
    public function postSave()
    {
        if ($this->getIsEnable()) {
            $cmd = $this->getCmd(null, 'refresh');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
            $cmd = $this->getCmd(null, 'refresh_forecast');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
        }
    }

    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave()
    {
        if ($this->getConfiguration('displayMode') == 'full_display') {
            $this->setDisplay("width", "250px");
            $this->setDisplay("height", "205px");
        } else if ($this->getConfiguration('displayMode') == 'min_display') {
            $this->setDisplay("width", "270px");
            $this->setDisplay("height", "365px");
        }
    }

    public function postUpdate()
    {
        if ($this->getConfiguration('elements') == 'polution') {
            $setup = [
                ['name' => 'aqi', 'title' => 'AQI', 'unit' => '', 'subType'=>'numeric', 'order' => 1],
                ['name' => 'pm10', 'title' => 'PM10', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 2],
                ['name' => 'o3', 'title' => 'O³', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 3],
                ['name' => 'no2', 'title' => 'NO²', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 6],
                ['name' => 'no', 'title' => 'NO', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 4],
                ['name' => 'co', 'title' => 'CO', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 7],
                ['name' => 'so2', 'title' => 'SO²', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 8],
                ['name' => 'nh3', 'title' => 'NH³', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 9],
                ['name' => 'pm25', 'title' => 'PM2.5', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 5],
                ['name' => 'visibility', 'title' => 'Visibilité', 'unit' => 'm', 'subType'=>'numeric', 'order' => 10],
                ['name' => 'uv', 'title' => 'Indice UV', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 11],
                //Forecast
                ['name' => 'days', 'title' => 'Forecast days', 'unit' => '', 'subType'=>'string', 'order' => 12],
                ['name' => 'no2_min', 'title' => 'NO² Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 13],
                ['name' => 'no2_max', 'title' => 'NO² Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 14],
                ['name' => 'so2_min', 'title' => 'SO² Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 15],
                ['name' => 'so2_max', 'title' => 'SO² Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 16],
                ['name' => 'no_min', 'title' => 'NO Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 17],
                ['name' => 'no_max', 'title' => 'NO Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 18],
                ['name' => 'co_min', 'title' => 'CO Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 19],
                ['name' => 'co_max', 'title' => 'CO Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 20],
                ['name' => 'nh3_min', 'title' => 'NH3 Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 21],
                ['name' => 'nh3_max', 'title' => 'NH3 Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 22],
                ['name' => 'aqi_min', 'title' => 'AQI Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 23],
                ['name' => 'aqi_max', 'title' => 'AQI Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 24],
                ['name' => 'o3_min', 'title' => 'O³ Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 23],
                ['name' => 'o3_max', 'title' => 'O³ Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 24],
                ['name' => 'pm25_min', 'title' => 'PM2.5 Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 25],
                ['name' => 'pm25_max', 'title' => 'PM2.5 Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 26],
                ['name' => 'pm10_min', 'title' => 'PM10 Mini prévision', 'unit' => '', 'subType'=>'string', 'order' => 27],
                ['name' => 'pm10_max', 'title' => 'PM10 Maxi prévision', 'unit' => '', 'subType'=>'string', 'order' => 28]
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
                ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType'=>'numeric', 'order' => 58],
                ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 59],
                ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 54],
                ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType'=>'string' ,'order' => 55],
                ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType'=>'string' ,'order' => 56],
                ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType'=>'string' ,'order' => 57],
                ['name' => 'poaceae', 'title' => 'Graminées', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 19],
                ['name' => 'alder', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 6],
                ['name' => 'birch', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 7],
                ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 8],
                ['name' => 'elm', 'title' => 'Orme', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 9],
                ['name' => 'hazel', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 10],
                ['name' => 'oak', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 11],
                ['name' => 'pine', 'title' => 'Pin', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 12],
                ['name' => 'plane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 13],
                ['name' => 'poplar', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 14],
                ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 15],
                ['name' => 'mugwort', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 16],
                ['name' => 'nettle', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 17],
                ['name' => 'ragweed', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 18],
                ['name' => 'others', 'title' => 'Autres', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 22],
                ['name' => 'updatedAt', 'title' => 'Update at', 'unit' => '', 'subType'=>'string' ,'order' => 60],

                ['name' => 'days', 'title' => 'Forecast days Pollen', 'unit' => '', 'subType'=>'string', 'order' => 23],
                ['name' => 'poaceae_min', 'title' => "Grass-Poaceae Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 24],
                ['name' => 'poaceae_max', 'title' => 'Grass-Poaceae Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 25],

                ['name' => 'alder_min', 'title' => "Alder Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 26],
                ['name' => 'alder_max', 'title' => 'Alder Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 27],

                ['name' => 'birch_min', 'title' => "Birch Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 28],
                ['name' => 'birch_max', 'title' => "Birch Maxi prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 29],

                ['name' => 'cypress_min', 'title' => "Cypress Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 30],
                ['name' => 'cypress_max', 'title' => 'Cypress Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 31],
             
                ['name' => 'elm_min', 'title' => "Elm Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 32],
                ['name' => 'elm_max', 'title' => 'Elm Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 33],

                ['name' => 'hazel_min', 'title' => "Hazel Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 34],
                ['name' => 'hazel_max', 'title' => 'Hazel Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 35],

                ['name' => 'oak_min', 'title' => "Oak Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 36],
                ['name' => 'oak_max', 'title' => 'Oak Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 37],

                ['name' => 'pine_min', 'title' => "Pine Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 38],
                ['name' => 'pine_max', 'title' => 'Pine Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 39],

                ['name' => 'plane_min', 'title' => "Plane Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 40],
                ['name' => 'plane_max', 'title' => 'Plane Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 41],
               
                ['name' => 'poplar_min', 'title' => "Poplar Cottonwood Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 42],
                ['name' => 'poplar_max', 'title' => 'Poplar Cottonwood Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 43],
 
                ['name' => 'chenopod_min', 'title' => "Chenopod Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 44],
                ['name' => 'chenopod_max', 'title' => 'Chenopod Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 45],

                ['name' => 'mugwort_min', 'title' => "Mugwort Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 46],
                ['name' => 'mugwort_max', 'title' => 'Mugwort Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 47],

                ['name' => 'nettle_min', 'title' => "Nettle Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 48],
                ['name' => 'nettle_max', 'title' => 'Nettle Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 49],

                ['name' => 'ragweed_min', 'title' => "Ragweed Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 50],
                ['name' => 'ragweed_max', 'title' => 'Ragweed Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 51],

                ['name' => 'others_min', 'title' => "Others Mini prévision", 'unit' => 'part/m³', 'subType'=>'string', 'order' => 52],
                ['name' => 'others_max', 'title' => 'Others Maxi prévision', 'unit' => 'part/m³', 'subType'=>'string', 'order' => 53],

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


            $refresh = $this->getCmd(null, 'refresh_pollen');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName(__('Rafraichir Pollen', __FILE__));
            }
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh_pollen');
            $refresh->setType('action');
            $refresh->setOrder(99);
            $refresh->setSubType('other');
            $refresh->save();
        }


        foreach ($setup as $command) {

            $info = $this->getCmd(null, $command['name']);

            if (!is_object($info)) {
                $info = new airqualityCmd();
                $info->setName(__($command['title'], __FILE__));
            }
            $info->setEqLogic_id($this->getId());
            $info->setLogicalId($command['name']);
            $info->setType('info');
            $info->setOrder($command['order']);
            $info->setTemplate('dashboard', 'tile');
            $info->setSubType($command['subType']);
            //  To do
            if ($info->getIsHistorized() == 1) {
                // config::save('displayStatsWidget', 1 , 'airquality' );
                $info->setDisplay('showStatsOnmobile', 1);
                $info->setDisplay('showStatsOndashboard', 1);
            }
            $info->setUnite($command['unit']);
            $info->setDisplay('generic_type', 'GENERIC_INFO');
            $info->save();
        }



        // $refresh = $this->getCmd(null, 'refresh');
        // if (!is_object($refresh)) {
        //     $refresh = new airqualityCmd();
        //     $refresh->setName(__('Rafraichir', __FILE__));
        // }
        // $refresh->setEqLogic_id($this->getId());
        // $refresh->setLogicalId('refresh');
        // $refresh->setType('action');
        // $refresh->setOrder(0);
        // $refresh->setTemplate('dashboard', 'tile');
        // $refresh->setSubType('other');
        // $refresh->save();

    
    }

    public function toHtml($_version = 'dashboard')
    {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }

        //vide le cache. Pour le développement
        $this->emptyCacheWidget();

        $version = jeedom::versionAlias($_version);
        $activePollen = 0;
      
        foreach ($this->getCmd('info') as $cmd) {

            // Verification si la valeur doit etre afficher 
            if ($this->getConfiguration($cmd->getLogicalId(), 0) == 1 || 0 == 0 ){
            
                // Preparation des valeurs à remplacer 
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#'.$nameCmd.'_name#';
                $info = '#' . $nameCmd . 'info#';
            
                // Commande/Element  à afficher et remplacer 
                $element = $this->getCmd(null, $nameCmd);            
            
                if (is_object($element)) {

                    if ( $this->getConfiguration('elements') == 'polution'){
                        $icone = new IconesAqi;
                        $elementTemplate = getTemplate('core', $version, 'element', 'airquality');
                        $unitreplace['#unity#'] = ($cmd->getLogicalId() != 'aqi') ? 'μg/m³' : '';

                    } else {
                        $icone = new IconesPollen;
                        $elementTemplate = getTemplate('core', $version, 'elementPollen', 'airquality');
                        $unitreplace['#unity#'] = 'particules/m³';
                    }

                    // Pour Affichage spécial 
                    if ( $nameCmd == 'uv' || $nameCmd == 'visibility') {
                        $replace[$commandValue] = $element->execCmd();
                        $replace[$info] = (self::getAqiName($element->execCmd()));
                        $replace[$commandNameId] = $element->getId();
                        $replace[$commandName] =  $element->getName();
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                        $replace[$nameIcon] = $newIcon;
                    
                    }else  if ( $nameCmd == 'tree_pollen' || $nameCmd == 'grass_pollen'  || $nameCmd == 'weed_pollen' ) {
                        $replace[$commandValue] = $element->execCmd();
                        $replace[$commandNameId] = $element->getId();
                        $replace[$commandName] =  $element->getName();
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                        $replace[$nameIcon] = $newIcon;
                        $listPollen ='#list_' . $nameCmd . '#';
                        $replace[$listPollen] = self::getListPollen($nameCmd);

                    } 
                    else  if ($nameCmd == 'grass_risk' || $nameCmd == 'tree_risk' || $nameCmd == 'weed_risk' ) {
                        $replace[$commandValue] = self::getPollenRisk($element->execCmd());
                    } 
                    else  if ( $nameCmd == 'updatedAt'){
                        $replace['#updatedAt#'] = $element->execCmd() ;
                    }
                    else  if ( $nameCmd == 'no2_min' || $nameCmd == 'no2_max' ||$nameCmd == 'so2_min' || $nameCmd == 'so2_max'
                        || $nameCmd == 'no_min' || $nameCmd == 'no_max' || $nameCmd == 'co_min' ||$nameCmd == 'co_max' 
                        || $nameCmd == 'nh3_min'|| $nameCmd == 'nh3_max' || $nameCmd == 'aqi_min'|| $nameCmd == 'aqi_max' 
                        || $nameCmd == 'o3_min'|| $nameCmd == 'o3_max' || $nameCmd == 'pm10_min'|| $nameCmd == 'pm10_max'  || $nameCmd == 'pm25_min'|| $nameCmd == 'pm25_max' 
                        || $nameCmd == 'poaceae_min'|| $nameCmd == 'poaceae_max' || $nameCmd == 'alder_min'|| $nameCmd == 'alder_max'  || $nameCmd == 'birch_min'
                        || $nameCmd == 'birch_max'|| $nameCmd == 'cypress_min' || $nameCmd == 'cypress_max'|| $nameCmd == 'elm_min'  || $nameCmd == 'elm_max'
                        || $nameCmd == 'hazel_min'|| $nameCmd == 'hazel_max' || $nameCmd == 'oak_min'|| $nameCmd == 'oak_max'  || $nameCmd == 'pine_min'
                        || $nameCmd == 'pine_max'|| $nameCmd == 'plane_min' || $nameCmd == 'plane_max'|| $nameCmd == 'poplar_min'  || $nameCmd == 'poplar_max'
                        || $nameCmd == 'chenopod_min'|| $nameCmd == 'chenopod_max' || $nameCmd == 'mugwort_min'|| $nameCmd == 'mugwort_max'  || $nameCmd == 'nettle_min'
                        || $nameCmd == 'nettle_max'|| $nameCmd == 'ragweed_min' || $nameCmd == 'ragweed_max'|| $nameCmd == 'others_min'  || $nameCmd == 'others_max'
                        )
                    {
                        $indexMinMax = '#'.$nameCmd.'#';
                        $replace[$indexMinMax] = $element->execCmd();
                    } 
                    else  if ( $nameCmd == 'days'){
                        $replace['#labels#'] = ($element->execCmd());
                    }
                     // Pour Affichage classique 
                    else {
                        
                        //Pollen 
                        // Incrémentation Compteur de pollens actifs 
                        $activePollen = ( $element->execCmd() > 0 ) ? $activePollen + 1 : $activePollen;    
                        // affichage liste pollens par categorie
                        $unitreplace['#list-info#'] =  ( $nameCmd == 'autres') ?  'class="tooltips" title="'.self::getListPollen($nameCmd).'"' : '';
                
                        // Multi Template Commun
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId(), '30px');
                        $unitreplace['#icone#'] = $newIcon;   
                        $unitreplace['#id#'] = $this->getId();
                        $unitreplace['#value#'] = ($this->getConfiguration('elements') == 'polution') ?  self::formatValueForDisplay($element->execCmd()) : $element->execCmd() ;
                        $unitreplace['#name#'] = $cmd->getLogicalId();
                        $unitreplace['#display-name#'] = $cmd->getName();
                        $unitreplace['#cmdid#'] = $cmd->getId();
                        $unitreplace['#history#'] = 'history cursor';
                        $unitreplace['#info-modalcmd#'] = 'info-modal'.$element->getId();
                        // Couleur du chart assorti au niveau live pour eviter sapin de noel
                        $color = '#color_'.$nameCmd.'#';
                        $replace[$color] =  $icone->getColor();
                
                        $replace[$commandNameId] = $element->getId();  
                    
                        // Historique Commun
                        $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                        $historyStatistique = $element->getStatistique($startHist, date('Y-m-d H:i:s'));
                        $unitreplace['#minHistoryValue#'] = self::formatValueForDisplay($historyStatistique['min'], 'short');
                        $unitreplace['#maxHistoryValue#'] = self::formatValueForDisplay($historyStatistique['max'], 'short');
                        $unitreplace['#averageHistoryValue#'] = self::formatValueForDisplay($historyStatistique['avg'], 'short');
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
                        
                        // Enregistrement dans un tableau de tous les slides
                        $tab[] = template_replace($unitreplace, $elementTemplate);

                     // Affichage central pour AQI 
                        if ($nameCmd == 'aqi') {
                            $replace[$commandValue] = $element->execCmd();
                            $replace[$info] = (self::getAqiName($element->execCmd()));
                            $replace[$commandNameId] = $element->getId();
                            $replace[$commandName] =  $element->getName();
                            $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                            $replace[$nameIcon] = $newIcon;
                        }
                    }
                }
            }
        }
        // End foreach // 


    
        if ($this->getConfiguration('elements') == 'polution') {
            // Choix du layer : pour le replace global
            $component = new ComponentAqi($tab, $this->getId(), 1);
            $replace['#index_name#'] = __('Indice',__FILE__);

        } else {
            // Pollen 
            // Pollen actifs
            $replace['#active_pollen_label#'] = __('Pollens actifs',__FILE__);
            $replace['#activePollen#'] = $activePollen;
            $component = new ComponentAqi($tab, $this->getId(), 1);
        }
        // Replace Global 
        $replace['#mini_slide#'] =  $component->getLayer();

        // Command Refresh 
        $refresh = $this->getCmd(null, 'refresh');
        $replace['#refresh#'] = is_object($refresh) ? $refresh->getId() : '';

        // Carousel 
        if ($this->getConfiguration('animation_aqi') == 'disable_anim') {
            $replace['#animation#'] = 'disabled';
            $replace['#classCaroussel#'] = 'data-interval="false"';
        } else {
            $replace['#animation#'] = 'active';
            $replace['#classCaroussel#'] = '';
        }


        if ($version == 'mobile') {
            if ( $this->getConfiguration('elements') == 'polution'){
                return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality.mobile', __CLASS__)));
            } else {
                return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'pollen.mobile', __CLASS__)));
            }
        }
        else {

           if ( $this->getConfiguration('elements') == 'polution'){
                    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality', __CLASS__)));
           } else {
                    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'pollen', __CLASS__)));
           }
        }
    }



    public static function formatValueForDisplay($value, $style = 'normal')
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

    public static function getAqiName($aqi)
    {
        switch ($aqi) {
            case  1:
                return __("Bon",__FILE__);
            case 2:
                return __("Correct",__FILE__);
            case 3:
                return __("Dégradé",__FILE__);
            case 4:
                return __("Mauvais",__FILE__);
            case 5:
                return __("Très mauvais",__FILE__);
            case 6:  
                 return __("Extrême",__FILE__);
        }
    }

    public static function getPollenRisk(string $level)
    {
        switch ($level) {
            case  'High':
                return __("Risque haut",__FILE__);
                // The air quality is good. Enjoy your usual outdoor activities  
            case 'Moderate':
                return __("Risque modéré",__FILE__);
                // Coorect
                //  Enjoy your usual outdoor activities
            case 'Low':
                return __("Risque bas",__FILE__);
                // Consider reducing intense outdoor activities, if you experience symptoms.
            case 'Very High':
                    return __("Risque très haut",__FILE__);
                    // Consider reducing intense outdoor activities, if you experience symptoms.
        }
    }
    
    public static function getListPollen($category){
        switch ($category){
            case 'tree_pollen':
                return __('Aulne',__FILE__).' - '.__('Bouleau',__FILE__).' - '.__('Cyprès',__FILE__).' - '.__('Chêne',__FILE__)
                .' - '.__('Platane',__FILE__).' - '.__('Noisetier',__FILE__).' - '.__('Orme',__FILE__).' - '.__('Pin',__FILE__);
                break;
            case 'grass_pollen':
                return __('Herbes',__FILE__).' - '.__('Poacées',__FILE__).' - '.__('Graminées',__FILE__);
                break;
            case 'weed_pollen':
                return __('Chenopod',__FILE__).' - '.__('Armoise',__FILE__).' - '.__('Ortie',__FILE__).' - '.__('Ambroisie',__FILE__);
                break;
            case 'autres':
                    return __('Autres pollens d\'origine inconnue',__FILE__);
        }
    }


    public static function postConfig_apikey()
    {
        if (config::byKey('apikey', 'airquality') == '') {
            throw new Exception('La clef API ne peut être vide');
        }
        $api = new ApiAqi;
        $checkApi = $api->getAqi(50,50);
        if (!$checkApi) {
            throw new Exception('La clef API n\'est pas valide ou pas encore active');
        }
    }

    /**
     * Pour appel  Ajax
     */
    public static function fetchReverse($longitude, $latitude)
    {
        $api = new ApiAqi;
        return $api->callApiReverseGeoLoc($longitude, $latitude);
    }

    /**
     * Pour appel  Ajax
     */
    public static function fetchGeoLoc($city, $country_code, $state_code = null)
    {
        $api = new ApiAqi;
        return $api->callApiGeoLoc($city, $country_code, $state_code = null);
    }


    // public static function setDynGeoLoc($latitude, $longitude)
    // {
    //     config::save('DynLatitude', $latitude, 'airquality');
    //     config::save('DynLongitude', $longitude, 'airquality');
    //     $resLat = trim(config::byKey('DynLatitude', 'airquality'));
    //     $resLong = trim(config::byKey('DynLongitude', 'airquality'));
    //     $api = new ApiAqi;
    //     return $api->callApiReverseGeoLoc($resLong, $resLat);
    // }


    public function getData(string $apiName){
   
        $api = new ApiAqi();
        switch ($this->getConfiguration('searchMode')) {
            case 'city_mode':
                // Récuperation de la geoloc pour éviter le double appel API
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


    public function reorderCmdPollen(){

                    // Replace item Polen by risk 
                    foreach ($this->getCmd('info') as $cmd){

                        // Fais un tableau avec les valeurs associe au nom 
                        $index = $cmd->getLogicalId();
                        switch ($index) {
                            case 'alder': case 'birch': case 'cypress': case 'elm':case 'alder': case 'hazel': case 'oak': case 'pine': case 'plane':
                            case 'poplar': case 'chenopod': case 'mugwort': case 'nettle': case 'ragweed': case 'poaceae': case 'others':
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

    public function refreshforecastPollen(){
 
        $d = new DateTime('2011-01-01T15:03:01.012345Z');
        message::add('refresh Forecast Pollen from fction', json_encode($d->format('Y-m-d\TH:i:s')));
        $forecast =  $this->getData('getForecastPollen');
        message::add('debug',json_encode($forecast));
        log::add('airquality', 'debug', json_encode( $forecast));
        $this->checkAndUpdateCmd('days', json_encode($forecast['Alder']['day']));
        $this->checkAndUpdateCmd('poaceae_min',json_encode($forecast['Poaceae']['min']));
        $this->checkAndUpdateCmd('poaceae_max', json_encode( $forecast['Poaceae']['max']));
        $this->checkAndUpdateCmd('alder_min', json_encode($forecast['Alder']['min']));
        $this->checkAndUpdateCmd('alder_max',json_encode($forecast['Alder']['max']));
        $this->checkAndUpdateCmd('birch_min', json_encode($forecast['Birch']['min']));
        $this->checkAndUpdateCmd('birch_max',json_encode($forecast['Birch']['max']));
        $this->checkAndUpdateCmd('cypress_min',json_encode($forecast['Cypress']['min']));
        $this->checkAndUpdateCmd('cypress_max',json_encode($forecast['Cypress']['max']));
        $this->checkAndUpdateCmd('elm_min',json_encode($forecast['Elm']['min']));
        $this->checkAndUpdateCmd('elm_max',json_encode($forecast['Elm']['max']));
        $this->checkAndUpdateCmd('hazel_min',json_encode($forecast['Hazel']['min']));
        $this->checkAndUpdateCmd('hazel_max',json_encode($forecast['Hazel']['max']));
        $this->checkAndUpdateCmd('oak_min',json_encode($forecast['Oak']['min']));
        $this->checkAndUpdateCmd('oak_max',json_encode($forecast['Oak']['max']));
        $this->checkAndUpdateCmd('pine_min',json_encode($forecast['Pine']['min']));
        $this->checkAndUpdateCmd('pine_max',json_encode($forecast['Pine']['max']));
        $this->checkAndUpdateCmd('plane_min',json_encode($forecast['Plane']['min']));
        $this->checkAndUpdateCmd('plane_min',json_encode($forecast['Plane']['max']));
        $this->checkAndUpdateCmd('poplar_min',json_encode($forecast['Poplar']['min']));
        $this->checkAndUpdateCmd('poplar_max',json_encode($forecast['Poplar']['max']));
        $this->checkAndUpdateCmd('chenopod_min',json_encode($forecast['Chenopod']['min']));
        $this->checkAndUpdateCmd('chenopod_max',json_encode($forecast['Chenopod']['max']));
        $this->checkAndUpdateCmd('mugwort_min',json_encode($forecast['Mugwort']['min']));
        $this->checkAndUpdateCmd('mugwort_max',json_encode($forecast['Mugwort']['max']));
        $this->checkAndUpdateCmd('nettle_min',json_encode($forecast['Nettle']['min']));
        $this->checkAndUpdateCmd('nettle_min',json_encode($forecast['Nettle']['max']));
        $this->checkAndUpdateCmd('ragweed_min',json_encode($forecast['Ragweed']['min']));
        $this->checkAndUpdateCmd('ragweed_min',json_encode($forecast['Ragweed']['max']));
        $this->checkAndUpdateCmd('others_min',json_encode($forecast['Others']['min']));
        $this->checkAndUpdateCmd('others_min',json_encode($forecast['Others']['max']));
        $this->refreshWidget();

    }

}


class airqualityCmd extends cmd
{
   
    public static $_widgetPossibility = array('custom' => false);

    public function execute($_options = array())
    {
        $eqlogic = $this->getEqLogic();
    
        switch ($this->getLogicalId()) {
            case 'refresh':
                if ($eqlogic->getConfiguration('elements') == 'polution') {
                    $d = new DateTime('2011-01-01T15:03:01.012345Z');
                    message::add('refresh AQI', json_encode($d->format('Y-m-d\TH:i:s')));
                    $data = $eqlogic->getData('getAqi');
                    $eqlogic->checkAndUpdateCmd('aqi', $data->main->aqi);
                    $eqlogic->checkAndUpdateCmd('no2', $data->components->no2);
                    $eqlogic->checkAndUpdateCmd('no', $data->components->no);
                    $eqlogic->checkAndUpdateCmd('co', $data->components->co);
                    $eqlogic->checkAndUpdateCmd('o3', $data->components->o3);
                    $eqlogic->checkAndUpdateCmd('so2', $data->components->so2);
                    $eqlogic->checkAndUpdateCmd('nh3', $data->components->nh3);
                    $eqlogic->checkAndUpdateCmd('pm25', $data->components->pm2_5);
                    $eqlogic->checkAndUpdateCmd('pm10', $data->components->pm10);
                    $data = $eqlogic->getData('getOneCallApi');
                    $eqlogic->checkAndUpdateCmd('uv', $data->uvi);
                    $eqlogic->checkAndUpdateCmd('visibility', $data->visibility);
                    $eqlogic->refreshWidget();
                    break;
                }

            case 'refresh_pollen':
                $d = new DateTime('2011-01-01T15:03:01.012345Z');
                message::add('refresh Pollen', json_encode($d->format('Y-m-d\TH:i:s')));
                if ($eqlogic->getConfiguration('elements') == 'pollen') {
                    $dataAll = $eqlogic->getData('getAmbee');
                    $dataPollen = $dataAll->data;
                    $eqlogic->checkAndUpdateCmd('poaceae', $dataPollen[0]->Species->Grass->{"Grass / Poaceae"});
                    $eqlogic->checkAndUpdateCmd('alder', $dataPollen[0]->Species->Tree->Alder);
                    $eqlogic->checkAndUpdateCmd('birch', $dataPollen[0]->Species->Tree->Birch);
                    $eqlogic->checkAndUpdateCmd('grass_pollen', $dataPollen[0]->Count->grass_pollen);
                    $eqlogic->checkAndUpdateCmd('tree_pollen', $dataPollen[0]->Count->tree_pollen);
                    $eqlogic->checkAndUpdateCmd('weed_pollen', $dataPollen[0]->Count->weed_pollen);
                    $eqlogic->checkAndUpdateCmd('weed_risk', $dataPollen[0]->Risk->weed_pollen);
                    $eqlogic->checkAndUpdateCmd('grass_risk', $dataPollen[0]->Risk->grass_pollen);
                    $eqlogic->checkAndUpdateCmd('tree_risk', $dataPollen[0]->Risk->tree_pollen);
                    $eqlogic->checkAndUpdateCmd('cypress', $dataPollen[0]->Species->Tree->Cypress);
                    $eqlogic->checkAndUpdateCmd('elm', $dataPollen[0]->Species->Tree->Elm);
                    $eqlogic->checkAndUpdateCmd('hazel', $dataPollen[0]->Species->Tree->Hazel);
                    $eqlogic->checkAndUpdateCmd('oak', $dataPollen[0]->Species->Tree->Oak);
                    $eqlogic->checkAndUpdateCmd('pine', $dataPollen[0]->Species->Tree->Pine);
                    $eqlogic->checkAndUpdateCmd('plane', $dataPollen[0]->Species->Tree->Plane);
                    $eqlogic->checkAndUpdateCmd('poplar', $dataPollen[0]->Species->Tree->{"Poplar / Cottonwood"});
                    $eqlogic->checkAndUpdateCmd('chenopod', $dataPollen[0]->Species->Weed->Chenopod);
                    $eqlogic->checkAndUpdateCmd('mugwort', $dataPollen[0]->Species->Weed->Mugwort);
                    $eqlogic->checkAndUpdateCmd('nettle', $dataPollen[0]->Species->Weed->Nettle);
                    $eqlogic->checkAndUpdateCmd('ragweed', $dataPollen[0]->Species->Weed->Ragweed);
                    $eqlogic->checkAndUpdateCmd('others', $dataPollen[0]->Species->Others);
                    $eqlogic->checkAndUpdateCmd('updatedAt',$dataPollen[0]->updatedAt);
                    $eqlogic->reorderCmdPollen();
                    $eqlogic->refreshWidget();
                    break;
                }
      
            
            case 'refresh_forecast':
                $d = new DateTime('2011-01-01T15:03:01.012345Z');
                message::add('refresh Forecast AQI', json_encode($d->format('Y-m-d\TH:i:s')));
                if($eqlogic->getConfiguration('elements') == 'polution'){
                    $forecast =  $eqlogic->getData('getForecast');
                    $eqlogic->checkAndUpdateCmd('days', json_encode($forecast['no2']['day']));
                    $eqlogic->checkAndUpdateCmd('no2_min',json_encode($forecast['no2']['min']));
                    $eqlogic->checkAndUpdateCmd('no2_max', json_encode( $forecast['no2']['max']));
                    $eqlogic->checkAndUpdateCmd('no_min', json_encode($forecast['no']['min']));
                    $eqlogic->checkAndUpdateCmd('no_max',json_encode($forecast['no']['max']));
                    $eqlogic->checkAndUpdateCmd('so2_min', json_encode($forecast['so2']['min']));
                    $eqlogic->checkAndUpdateCmd('so2_max',json_encode($forecast['so2']['max']));
                    $eqlogic->checkAndUpdateCmd('co_min',json_encode($forecast['co']['min']));
                    $eqlogic->checkAndUpdateCmd('co_max',json_encode($forecast['co']['max']));
                    $eqlogic->checkAndUpdateCmd('nh3_min',json_encode($forecast['nh3']['min']));
                    $eqlogic->checkAndUpdateCmd('nh3_max',json_encode($forecast['nh3']['max']));
                    $eqlogic->checkAndUpdateCmd('aqi_min',json_encode($forecast['aqi']['min']));
                    $eqlogic->checkAndUpdateCmd('aqi_max',json_encode($forecast['aqi']['max']));
                    $eqlogic->checkAndUpdateCmd('pm10_min',json_encode($forecast['pm10']['min']));
                    $eqlogic->checkAndUpdateCmd('pm10_max',json_encode($forecast['pm10']['max']));
                    $eqlogic->checkAndUpdateCmd('o3_min',json_encode($forecast['o3']['min']));
                    $eqlogic->checkAndUpdateCmd('o3_max',json_encode($forecast['o3']['max']));
                    $eqlogic->checkAndUpdateCmd('pm25_min',json_encode($forecast['pm2_5']['min']));
                    $eqlogic->checkAndUpdateCmd('pm25_max',json_encode($forecast['pm2_5']['max']));
                    $eqlogic->refreshWidget();
                }
            
            
            
            case 'refresh_pollen_forecast':
               
                $d = new DateTime('2011-01-01T15:03:01.012345Z');
                message::add('refresh Forecast Pollen from cmd', json_encode($d->format('Y-m-d\TH:i:s')));
             
                    if($eqlogic->getConfiguration('elements') == 'pollen'){
                        $forecast =  $eqlogic->getData('getForecastPollen');
                 
                        log::add('airquality', 'debug', json_encode( $forecast));
                        $eqlogic->checkAndUpdateCmd('days', json_encode($forecast['Alder']['day']));
                        $eqlogic->checkAndUpdateCmd('poaceae_min',json_encode($forecast['Poaceae']['min']));
                        $eqlogic->checkAndUpdateCmd('poaceae_max', json_encode( $forecast['Poaceae']['max']));
                        $eqlogic->checkAndUpdateCmd('alder_min', json_encode($forecast['Alder']['min']));
                        $eqlogic->checkAndUpdateCmd('alder_max',json_encode($forecast['Alder']['max']));
                        $eqlogic->checkAndUpdateCmd('birch_min', json_encode($forecast['Birch']['min']));
                        $eqlogic->checkAndUpdateCmd('birch_max',json_encode($forecast['Birch']['max']));
                        $eqlogic->checkAndUpdateCmd('cypress_min',json_encode($forecast['Cypress']['min']));
                        $eqlogic->checkAndUpdateCmd('cypress_max',json_encode($forecast['Cypress']['max']));
                        $eqlogic->checkAndUpdateCmd('elm_min',json_encode($forecast['Elm']['min']));
                        $eqlogic->checkAndUpdateCmd('elm_max',json_encode($forecast['Elm']['max']));
                        $eqlogic->checkAndUpdateCmd('hazel_min',json_encode($forecast['Hazel']['min']));
                        $eqlogic->checkAndUpdateCmd('hazel_max',json_encode($forecast['Hazel']['max']));
                        $eqlogic->checkAndUpdateCmd('oak_min',json_encode($forecast['Oak']['min']));
                        $eqlogic->checkAndUpdateCmd('oak_max',json_encode($forecast['Oak']['max']));
                        $eqlogic->checkAndUpdateCmd('pine_min',json_encode($forecast['Pine']['min']));
                        $eqlogic->checkAndUpdateCmd('pine_max',json_encode($forecast['Pine']['max']));
                        $eqlogic->checkAndUpdateCmd('plane_min',json_encode($forecast['Plane']['min']));
                        $eqlogic->checkAndUpdateCmd('plane_min',json_encode($forecast['Plane']['max']));
                        $eqlogic->checkAndUpdateCmd('poplar_min',json_encode($forecast['Poplar']['min']));
                        $eqlogic->checkAndUpdateCmd('poplar_max',json_encode($forecast['Poplar']['max']));
                        $eqlogic->checkAndUpdateCmd('chenopod_min',json_encode($forecast['Chenopod']['min']));
                        $eqlogic->checkAndUpdateCmd('chenopod_max',json_encode($forecast['Chenopod']['max']));
                        $eqlogic->checkAndUpdateCmd('mugwort_min',json_encode($forecast['Mugwort']['min']));
                        $eqlogic->checkAndUpdateCmd('mugwort_max',json_encode($forecast['Mugwort']['max']));
                        $eqlogic->checkAndUpdateCmd('nettle_min',json_encode($forecast['Nettle']['min']));
                        $eqlogic->checkAndUpdateCmd('nettle_min',json_encode($forecast['Nettle']['max']));
                        $eqlogic->checkAndUpdateCmd('ragweed_min',json_encode($forecast['Ragweed']['min']));
                        $eqlogic->checkAndUpdateCmd('ragweed_min',json_encode($forecast['Ragweed']['max']));
                        $eqlogic->checkAndUpdateCmd('others_min',json_encode($forecast['Others']['min']));
                        $eqlogic->checkAndUpdateCmd('others_min',json_encode($forecast['Others']['max']));
                        $eqlogic->refreshWidget();
                    }
                
            
            
            
        
            
            
        }

          
    }


   

}
