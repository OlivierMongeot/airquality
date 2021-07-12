<?php
// Setup Error : only dev 
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', TRUE);
ini_set('display_errors', TRUE);

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
require dirname(__FILE__) . '/../../core/php/airquality.inc.php';


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
                try {
                    $c = new Cron\CronExpression('2 7 * * *', new Cron\FieldFactory);
                    if ($c->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_forecast');
                            if (is_object($refresh)) {
                                $refresh->execCmd();
                            } else {
                                log::add('airquality', 'debug', __('Impossible de trouver la commande refresh pour ', __FILE__) . $airQuality->getHumanName());
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
                    log::add('airquality', 'debug', __('Expression cron non valide pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $autorefresh . ' - ' .  $e->getMessage());
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
        if ($this->getIsEnable() && $this->getConfiguration('elements') == 'polution') {
            $cmd = $this->getCmd(null, 'refresh');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
            $cmd = $this->getCmd(null, 'refresh_forecast');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
        }
        if ($this->getIsEnable() && $this->getConfiguration('elements') == 'pollen') {

            $cmd = $this->getCmd(null, 'refresh');
            if (is_object($cmd)) {
                $cmd->execCmd();
            }
              // !!  1 appel décompté comme 48 appels (2x 24h de données) de l'API ambee sur un quota de 100 appels gratuits/ jours 
              // Annulation du refresh inutile à la sauvegarde si il y a déjà des data
            $cmdCheckNull =  $this->getCmd(null, 'poaceae_max');
            if (is_object($cmdCheckNull) && $cmdCheckNull->execCmd() == null) {
                
            $cmd = $this->getCmd(null, 'refresh_pollen_forecast');
                if (is_object($cmd)) {
                    $cmd->execCmd();
                }
            }
        }
    }


    public function preSave()
    {
        $this->setDisplay("width", "265px");
        $this->setDisplay("height", "375px");
    }

    public function postUpdate()
    {
        if ($this->getConfiguration('elements') == 'polution') {

            $refreshForecast = $this->getCmd(null, 'refresh_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast');
            }
            $refreshForecast->setEqLogic_id($this->getId())
            ->setLogicalId('refresh_forecast')
            ->setType('action')
            ->setOrder(100)
            ->setSubType('other')
            ->save();

            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId())
            ->setLogicalId('refresh')
            ->setType('action')
            ->setOrder(99)
            ->setSubType('other')
            ->save();
            $setup = SetupAqi::$setupAqi;
        }

        if ($this->getConfiguration('elements') == 'pollen') {

            $refreshForecast = $this->getCmd(null, 'refresh_pollen_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast Pollen');
            }
            $refreshForecast->setEqLogic_id($this->getId())
            ->setLogicalId('refresh_pollen_forecast')
            ->setType('action')
            ->setOrder(100)
            ->setSubType('other')
            ->save();

            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId())
            ->setLogicalId('refresh')
            ->setType('action')
            ->setOrder(99)
            ->setSubType('other')
            ->save();
            $setup = SetupAqi::$setupPollen;

        }

        foreach ($setup as $command) {
            $cmdInfo = $this->getCmd(null, $command['name']);
            if (!is_object($cmdInfo)) {
                $cmdInfo = new airqualityCmd();
                $cmdInfo->setName($command['title']);
            }
            $cmdInfo->setEqLogic_id($this->getId())
            ->setLogicalId($command['name'])
            ->setType('info')
            ->setOrder($command['order'])
            ->setTemplate('dashboard', 'tile')
            ->setSubType($command['subType'])
            ->setUnite($command['unit'])
            ->setDisplay('generic_type', 'GENERIC_INFO')
            ->setConfiguration($command['name'], $command['display'])
            ->save();
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
        $activePollenCounter = 0;
        $display = new DisplayInfo;

        // Pollution 
        if ($this->getConfiguration('elements') == 'polution') {
        
            $elementTemplate = getTemplate('core', $version, 'element', 'airquality');
          
            foreach ($this->getCmd('info') as $cmd) {
                // Preparation dynamique des valeurs à remplacer 
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';
    
                $isObjet = is_object($cmd);
    
                    if ($nameCmd == 'uv') {
                        $replace[$commandValue] = $isObjet ? $cmd->execCmd() : '';
                        $replace[$commandNameId] = $isObjet ? $cmd->getId(): '';
                        $replace[$commandName] = $isObjet ?  __($cmd->getName(), __FILE__): '';
                        $icone = new IconesAqi;
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $isObjet ? $newIcon: '';
                        $replace['#uv_level#'] = $isObjet ?  $display->getUVRapport($cmd->execCmd()): '';
    
                    } else if ($nameCmd == 'visibility') {
                        $replace[$commandValue] = $isObjet ?$cmd->execCmd(): '';
                        $replace[$commandNameId] = $isObjet ?$cmd->getId(): '';
                        $replace[$commandName] = $isObjet ?__($cmd->getName(), __FILE__): '';
                        $icone = new IconesAqi;
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $isObjet ? $newIcon: '';
                        $replace['#visibility_level#'] =  $isObjet ? $display->getVisibilityRapport($cmd->execCmd()): '';
    
                    } else if ($cmd->getConfiguration($nameCmd) == 'slideAqi' || $cmd->getConfiguration($nameCmd) == 'both') {
                       
                        if ( $cmd->getIsVisible() == 1 ) {
                            $icone = new IconesAqi;
                            $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), '30px');
                            $unitreplace['#icone#'] =  $isObjet ? $newIcon: '';
                            $unitreplace['#id#'] =  $isObjet ? $this->getId(): '';
                            $unitreplace['#value#'] =  $isObjet ?  $display->formatValueForDisplay($cmd->execCmd()) :'';
                            $unitreplace['#name#'] = $isObjet ? $cmd->getLogicalId(): '';
                            $unitreplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__): '';
                            $unitreplace['#cmdid#'] = $isObjet ?  $cmd->getId(): '';
                            $unitreplace['#history#'] =  $isObjet ? 'history cursor': '';
                            $unitreplace['#info-modalcmd#'] = $isObjet ?  'info-modal' . $cmd->getLogicalId() . $this->getId(): '';
                            $unitreplace['#unity#'] =  $isObjet ? $cmd->getUnite(): '';
                            $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                            $unitreplace['#max#'] = is_object($maxCmd) ?  $maxCmd->execCmd(): '[0,0,0]';
                            $minCmd = $this->getCmd(null, $nameCmd . '_min');
                            $unitreplace['#min#'] = is_object($minCmd) ? $minCmd->execCmd(): '[0,0,0]';
                            $unitreplace['#color#'] =  $isObjet ?  $icone->getColor(): '';
                            $labels = $this->getCmd(null, 'days');
                            $unitreplace['#labels#'] = is_object($labels) ? $labels->execCmd(): "['no','-','data']";
                            $unitreplace['#level-particule#'] =  $isObjet ?  $display->getElementRiskAqi($icone->getColor()): '';
                            $unitreplace['#info-tooltips#'] =   __("Cliquez pour + d'info", __FILE__);
                            $unitreplace['#mini#'] = __("Mini 10 jours", __FILE__);
                            $unitreplace['#maxi#'] = __("Maxi 10 jours", __FILE__);
                            $unitreplace['#tendency#'] = __("Tendance 12h", __FILE__);
                            $unitreplace['#average#'] = __("Moyenne 10 jours", __FILE__);
                            if ($cmd->getIsHistorized() == 1) {
                                // Historique Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                                $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
                                $unitreplace['#minHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['min'], 'short'): '';
                                $unitreplace['#maxHistoryValue#'] =  $isObjet ? $display->formatValueForDisplay($historyStatistique['max'], 'short'): '';
                                $unitreplace['#averageHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['avg'], 'short'): '';
                                // Tendance Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 12 . ' hour'));
                                $tendance = $cmd->getTendance($startHist, date('Y-m-d H:i:s'));
                                if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-up': '';
                                } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-down': '';
                                } else {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-minus': '';
                                }
                                $unitreplace['#display#'] = '';
                            } else {
                                $unitreplace['#display#'] =  $isObjet ? 'hidden': '';
                            }
                            $tab[] = template_replace($unitreplace, $elementTemplate);
                        }
    
                        // Affichage central pour AQI à la fin/(double passage if) car double affichage
                        if ($nameCmd == 'aqi') {
                            $replace[$commandValue] =  $isObjet ? $cmd->execCmd(): '';
                            $replace[$info] =   $isObjet ? $display->getAqiName($cmd->execCmd()): '';
                            $replace[$commandNameId] =   $isObjet ? $cmd->getId(): '';
                            $replace[$commandName] = $isObjet ?  $cmd->getName(): '';
                            $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                            $replace[$nameIcon] = $isObjet ? $newIcon: '';
                            $replace['#updateAt#'] = ($isObjet && $cmd->execCmd()) ? $display->parseDate(): 'No data';
                        }
                    }
            } 
            $replace['#index_name#'] = __('Indice', __FILE__);
            $k = 0;
        } 
        
        // Pollen 
        if ($this->getConfiguration('elements') == 'pollen') {
          
            $elementTemplate = getTemplate('core', $version, 'elementPollen', 'airquality');

            foreach ($this->getCmd('info') as $cmd) {

                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';
                $isObjet = is_object($cmd);
    
                if ($nameCmd == 'tree_pollen' || $nameCmd == 'grass_pollen'  || $nameCmd == 'weed_pollen') {
                        $replace[$commandValue] =  $isObjet ? $cmd->execCmd() : '';
                        $replace[$commandNameId] =   $isObjet ? $cmd->getId() : '';
                        $replace[$commandName] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                        $iconePollen = new IconesPollen;
                        $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                        $replace[$nameIcon] = $isObjet ?  $newIcon : '';
                        $listPollen = '#list_' . $nameCmd . '#';
                        $replace[$listPollen] =  $isObjet ?  $display->getListPollen($nameCmd) : '';
    
                    } else  if ($nameCmd == 'grass_risk' || $nameCmd == 'tree_risk' || $nameCmd == 'weed_risk') {
                        $replace[$commandValue] =  $isObjet ? $display->getPollenRisk($cmd->execCmd()) : '';
    
                    } else  if ($nameCmd == 'updatedAt') {
                       
                        $replace['#updatedAt#'] = ($isObjet && $cmd->execCmd()) ? $display->parseDate(): 'No data';
    
                    } else if ($cmd->getConfiguration($nameCmd) == 'slide') {
                        // Incrémentation Compteur de pollens actifs 
                        $activePollenCounter = ($cmd->execCmd() > 0) ? $activePollenCounter + 1 : $activePollenCounter;
    
                        // Check si les previsons pollen sont > 0 en partant d'une string-data pour l'inclure ou pas dans les chart
                        $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                        $max = $maxCmd->execCmd();
                        $max = str_replace(['[', ']'], '', $max);
                        $max = array_map('self::toInt', explode(",", $max));
                        $displaySlide = (array_sum($max) > 0) ? true : false;
    
                        if ($cmd->execCmd() > 0 && $cmd->getIsVisible() == 1 ||  $displaySlide === true ) {
                            $iconePollen = new IconesPollen;
                            $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                            $unitreplace['#icone#'] =  $isObjet ? $newIcon: '';
                            $unitreplace['#id#'] =  $isObjet ? $this->getId(): '';
                            $unitreplace['#value#'] =  $isObjet ?  $cmd->execCmd() :'';
                            $unitreplace['#name#'] = $isObjet ? $cmd->getLogicalId(): '';
                            $unitreplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__): '';
                            $unitreplace['#cmdid#'] = $isObjet ?  $cmd->getId(): '';
                            $unitreplace['#history#'] =  $isObjet ? 'history cursor': '';
                            $unitreplace['#info-modalcmd#'] = $isObjet ?  'info-modal' . $cmd->getLogicalId() . $this->getId(): '';
                            $unitreplace['#unity#'] =  $isObjet ? $cmd->getUnite(): '';
                            // Chart 
                            $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                            $unitreplace['#max#'] = is_object($maxCmd) ?  $maxCmd->execCmd(): '';
                            $minCmd = $this->getCmd(null, $nameCmd . '_min');
                            $unitreplace['#min#'] = is_object($minCmd) ? $minCmd->execCmd(): '';
                            $unitreplace['#color#'] =  $isObjet ?  $iconePollen->getColor(): '';
                            $labels = $this->getCmd(null, 'daysPollen');
                            $unitreplace['#labels#'] = is_object($labels) ? $labels->execCmd(): '';
                            //  Message
                            $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                            $unitreplace['#risk#'] =  $isObjet ?  $display->getElementRiskPollen($iconePollen->getColor()): '';
                            // Moyenne Min Max Tendance 
                            $unitreplace['#info-tooltips#'] =   __("Cliquez pour + d'info", __FILE__);
                            $unitreplace['#mini#'] = __("Mini 10 jours", __FILE__);
                            $unitreplace['#maxi#'] = __("Maxi 10 jours", __FILE__);
                            $unitreplace['#tendency#'] = __("Tendance 12h", __FILE__);
                            $unitreplace['#average#'] = __("Moyenne 10 jours", __FILE__);
                            if ($cmd->getIsHistorized() == 1) {
                                // Historique Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                                $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
                                $unitreplace['#minHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['min'], 'short'): '';
                                $unitreplace['#maxHistoryValue#'] =  $isObjet ? $display->formatValueForDisplay($historyStatistique['max'], 'short'): '';
                                $unitreplace['#averageHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['avg'], 'short'): '';
                                // Tendance Commun
                                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 12 . ' hour'));
                                $tendance = $cmd->getTendance($startHist, date('Y-m-d H:i:s'));
                                if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-up': '';
                                } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-down': '';
                                } else {
                                    $unitreplace['#tendance#'] = $isObjet ? 'fas fa-minus': '';
                                }
                                $unitreplace['#display#'] = '';
                            } else {
                                $unitreplace['#display#'] =  $isObjet ? 'hidden': '';
                            }
                            $tab[] = template_replace($unitreplace, $elementTemplate);
                        } else {
                            // Cas Pollen à ZERO 
                           if ( $this->getConfiguration('displayZeroPollen') == 1){
                   
                            $iconePollen = new IconesPollen;
                            $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false );
                            $pollenZeroReplace['#icone#'] = $isObjet ? $newIcon: '';
                            $pollenZeroReplace['#id#'] = $isObjet ? $this->getId(): '';
                            $pollenZeroReplace['#value#'] = $isObjet ?  $cmd->execCmd() : '';
                            $pollenZeroReplace['#name#'] = $isObjet ?  $cmd->getLogicalId(): '';
                            $pollenZeroReplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__): '';
                            $pollenZeroReplace['#cmdid#'] = $isObjet ?  $cmd->getId(): '';
                            $pollenZeroReplace['#info-modalcmd#'] =  $isObjet ? 'info-modal' . $cmd->getLogicalId() . $this->getId(): '';
                            $pollenZeroReplace['#message#'] = __('Aucune Détection', __FILE__);
                            $elementTemplate2 = getTemplate('core', $version, 'elementPollenZero', 'airquality');
                            $tabZero[] = template_replace($pollenZeroReplace, $elementTemplate2);

                           }
                              
                        }
                    }
            }
         
            // Compteur de slide pollen à data zero 
            $k = 0;
            if (isset($tabZero)){
                $newArray = array_chunk($tabZero, 3);
                foreach ($newArray as $arr) {
                 $tab[] = implode('', $arr);
                 $k++;
             }
            }
            $replace['#active_pollen_label#'] = __('Pollens actifs', __FILE__);
            $replace['#activePollen#'] = $activePollenCounter;
        }


        // Replace Global        
        $replace['#info-tooltips#'] = __("Cliquez pour + d'info", __FILE__);

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

    public static function toInt($string)
    {
        return (int)$string;
    }

    
    public static function postConfig_apikey()
    {
        if (config::byKey('apikey', 'airquality') == '' && config::byKey('apikeyAmbee', 'airquality') == '' ) {
            throw new Exception('Au moins une clef OpenWeather est requise pour faire marcher le plugin');
        }
    }

    /**
     * Pour recevoir appel Ajax. Utilisé dans la configuration mode "Geolocalisation du Navigateur"
     */
    public static function ReverseGeoLoc($longitude, $latitude)
    {
        $api = new ApiAqi;
        return $api->callApiReverseGeoLoc($longitude, $latitude);
    }

    /**
     * Pour appel Ajax. Utilisé dans la configuration mode "Par ville"
     */
    public static function GeoLoc($city, $country_code, $state_code = null)
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
                    throw new Exception(__('Les coordonnées sont vides, testez la ville dans la configuration', __FILE__));
                }
            case 'long_lat_mode':
                return $api->$apiName($this->getConfiguration('latitude'), $this->getConfiguration('longitude'));

            case 'dynamic_mode':
                if ($this->getConfiguration('geoLongitude') == '' || $this->getConfiguration('geoLatitude') == '') {
                    throw new Exception(__('Probleme de localisation dynamique', __FILE__));
                }
                return $api->$apiName($this->getConfiguration('geoLatitude'), $this->getConfiguration('geoLongitude'));

            case 'server_mode':
                return $api->$apiName(config::byKey('info::latitude'), config::byKey('info::longitude'));
        }
    }

    /**
     * To do 
     */
    public function getMessagePollen(){
        $value = 0;
        $pollen = 'birch';
        $display = new DisplayInfo;
        return $display->getMessage($value,$pollen);
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
        if(isset($dataAll->data)){
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
            $this->checkAndUpdateCmd('messagePollen', $this->getMessagePollen());

        }
      
    }

    /**
     * Appel api AQI live + UV + Visibility + Update des Commands 
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
     * Appel api Forecast AQI + Update des Commands 
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
        if (is_array($forecast) && $forecast != []){
            log::add('airquality', 'debug', json_encode($forecast));
            $this->checkAndUpdateCmd('daysPollen', json_encode($forecast['Alder']['day']));
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
        foreach ($tabOrder as $key => $unuse) {
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

    public function execute($_options = [])
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
