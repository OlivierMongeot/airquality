<?php
// Setup Error : only dev 
// error_reporting(E_ALL);
// ini_set('ignore_repeated_errors', TRUE);
// ini_set('display_errors', TRUE);

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

    public static function cronHourly()
    {
        foreach (self::byType(__CLASS__, true) as $airQuality) {
            if ($airQuality->getConfiguration('elements') == 'pollen') {
                $airQuality->updatePollen();
            }
        }
    }

    public static function cron()
    {

        foreach (self::byType('airquality') as $airQuality) {

        
            if ($airQuality->getIsEnable() == 1 && $airQuality->getConfiguration('elements') == 'polution') {
             
                // Cron Pollution Toutes demie heure decalé de  1 minute 
                try {
                    $c = new Cron\CronExpression('1,31 * * * *', new Cron\FieldFactory);
                    if ($c->isDue()) {
                        $airQuality->updatePollution();
                    }
                } catch (Exception $e) {
                    log::add('airquality', 'debug', __('Expression cron non valide pour update Pollution', __FILE__) . $airQuality->getHumanName() . ' : ' . json_encode($e));
                }

                // Pollution refresh forecast 3x 
                try {
                    $c = new Cron\CronExpression('2 7,14,20 * * *', new Cron\FieldFactory);
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
                    log::add('airquality', 'debug', __('Expression cron non valide pour Pollution refresh forecast', __FILE__) . $airQuality->getHumanName() . ' : ' . json_encode($e));
                }
                // Refresh/delete Alert Pollution after x min 
                try {
                    $specialCron =  $airQuality->getConfiguration('alertAqiCronTwoMin');
                    $cManual = new Cron\CronExpression($specialCron, new Cron\FieldFactory);
                    if (!empty($specialCron) && $cManual->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_alert_aqi');
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
                    log::add('airquality', 'debug', __('Expression cron non valide ', __FILE__) . 'pour Refresh Alert AQI : ' . $specialCron . ' - ' . $airQuality->getHumanName() . ' : ' .  json_encode($e));
                }
            }


            // Pollen
            if ($airQuality->getIsEnable() == 1 && $airQuality->getConfiguration('elements') == 'pollen') {
                //  Refresh forecast 
                try {
                    $c = new Cron\CronExpression('3 7 * * *', new Cron\FieldFactory);
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
                    log::add('airquality', 'debug', __('Expression cron non valide pour Pollen refresh forecast', __FILE__) . $airQuality->getHumanName() . ' - ' .  $e->getMessage());
                }
                // Refresh alert Message
                try {
                    $specialCron =  $airQuality->getConfiguration('alertPollenCronTwoMin');
                    $cManual = new Cron\CronExpression($specialCron, new Cron\FieldFactory);
                    if (!empty($specialCron) && $cManual->isDue()) {
                        try {
                            $refresh = $airQuality->getCmd(null, 'refresh_alert_pollen');
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
                    log::add('airquality', 'debug', __('Expression cron non valide pour Refresh alert Pollen', __FILE__) . $airQuality->getHumanName() . ' : ' . json_encode($specialCron)  . json_encode($e));
                }

                //  Refresh forecast test if new data available / date collect 
                try {
                    $c = new Cron\CronExpression('35 7,8 * * *', new Cron\FieldFactory);
                    if ($c->isDue()) {
                        try {
                            // Check if date collecté est normal 
                            $cmdXToTest = $airQuality->getCmd(null, 'others_min');
                            if (is_object($cmdXToTest)) {
                                $collectDate = $cmdXToTest->getCollectDate();
                                $datetimeCollected = DateTime::createFromFormat('Y-m-d H:i:s', $collectDate);
                                $dateNow = new DateTime();
                                $dateNow->setTimezone(new DateTimeZone('Europe/Paris'));
                                $interval = $datetimeCollected->diff($dateNow);
                                log::add('airquality', 'debug', 'Intervale : derniere heure Collecte / maintenant pour forecast Pollen : ' . $interval->h);
                                if ($interval->h >= 23) {
                                            log::add('airquality', 'debug', 'Date de collecte trop ancienne : 2eme cron car le 1er refresh de 7h03 n\'a pas marcher');
                                            $refresh = $airQuality->getCmd(null, 'refresh_pollen_forecast');
                                            if (is_object($refresh)) {
                                                $refresh->execCmd();
                                            } else {
                                                log::add('airquality', 'debug', 'Impossible de trouver la commande refresh pour ' . $airQuality->getHumanName());
                                            }
                                } else {
                                    log::add('airquality', 'debug', 'Test Date de collecte forecast Pollen OK : pas de relance du cron -> refresh');
                                }
                            } else {
                                log::add('airquality', 'debug', 'Impossible de trouver la commande others_min pour ' . $airQuality->getHumanName());
                            }


                        } catch (Exception $e) {
                            log::add('airquality', 'debug', __('Erreur pour ', __FILE__) . $airQuality->getHumanName() . ' : ' . $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    log::add('airquality', 'debug', __('Expression cron non valide pour Pollen refresh forecast', __FILE__) . $airQuality->getHumanName() . ' - ' .  $e->getMessage());
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
            $cmdXCheckNull =  $this->getCmd(null, 'co');
            if (is_object($cmdXCheckNull) && $cmdXCheckNull->execCmd() == null) {
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
        if ($this->getIsEnable() && $this->getConfiguration('elements') == 'pollen') {
            $cmdXCheckNull =  $this->getCmd(null, 'poaceae_max');
            if (is_object($cmdXCheckNull) && $cmdXCheckNull->execCmd() == null) {
                $cmd = $this->getCmd(null, 'refresh');
                if (is_object($cmd)) {
                    $cmd->execCmd();
                }
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
            // Remove pollen cmd 
            $setupPollen = SetupAqi::$setupPollen;
            foreach ($setupPollen as $command) {
                $cmdInfo = $this->getCmd(null, $command['name']);
                if (is_object($cmdInfo)) {
                    log::add('airquality', 'debug', 'Remove command ' . $command['name'] );
                    $cmdInfo->remove();
                }
            }

            $refreshForecast = $this->getCmd(null, 'refresh_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast');
            }
            $refreshForecast->setEqLogic_id($this->getId())
                ->setLogicalId('refresh_forecast')
                ->setType('action')
                ->setSubType('other')->save();
                
            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId())
                ->setLogicalId('refresh')
                ->setType('action')
                ->setSubType('other')->save();
               
            $refresh = $this->getCmd(null, 'refresh_alert_aqi');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir les alertes');
            }
            $refresh->setEqLogic_id($this->getId())
                ->setLogicalId('refresh_alert_aqi')
                ->setType('action')
                ->setSubType('other')->save();
              
            $setup = SetupAqi::$setupAqi;
        }

        if ($this->getConfiguration('elements') == 'pollen') {

            $setupAqi = SetupAqi::$setupAqi;
            foreach ($setupAqi as $command) {
                $cmdInfo = $this->getCmd(null, $command['name']);
                if (is_object($cmdInfo)) {
                    $cmdInfo->remove();
                }
            }

            $refreshForecast = $this->getCmd(null, 'refresh_pollen_forecast');
            if (!is_object($refreshForecast)) {
                $refreshForecast = new airqualityCmd();
                $refreshForecast->setName('Rafraichir Forecast Pollen');
            }
            $refreshForecast->setEqLogic_id($this->getId())
                ->setLogicalId('refresh_pollen_forecast')
                ->setType('action')
                ->setSubType('other')->save();

            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir');
            }
            $refresh->setEqLogic_id($this->getId())
                ->setLogicalId('refresh')
                ->setType('action')
                ->setSubType('other')->save();

            $refresh = $this->getCmd(null, 'refresh_alert_pollen');
            if (!is_object($refresh)) {
                $refresh = new airqualityCmd();
                $refresh->setName('Rafraichir les alertes pollens');
            }
            $refresh->setEqLogic_id($this->getId())
                ->setLogicalId('refresh_alert_pollen')
                ->setType('action')
                ->setSubType('other')->save();

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
                ->setTemplate('dashboard', 'tile')
                ->setSubType($command['subType'])
                ->setUnite($command['unit'])
                ->setDisplay('generic_type', 'GENERIC_INFO')
                ->setConfiguration($command['name'], $command['display']);
            if ($command['subType'] == 'numeric' && $this->getConfiguration('data_history') == 'actived') {

                $cmdInfo->setIsHistorized(1)->save();
            } else {
                $cmdInfo->setIsHistorized(0)->save();
            }
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
        $tabUnitReplace = [];
        // Pollution 
        if ($this->getConfiguration('elements') == 'polution') {
            $counterActivePolluant = 0;
            $elementTemplate = getTemplate('core', $version, 'element', 'airquality');
            $icone = new IconesAqi;
            foreach ($this->getCmd('info') as $cmd) {
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';
                $isObjet = is_object($cmd);

                if ($nameCmd == 'uv') {
                    $value = $isObjet ? $cmd->execCmd() : '';
                    $replace[$commandValue] = $value;
                    $replace[$commandNameId] = $isObjet ? $cmd->getId() : '';
                    $replace[$commandName] = $isObjet ?  __($cmd->getName(), __FILE__) : '';
                    $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                    $replace[$nameIcon] = $isObjet ? $newIcon : '';
                    [$uvLevel, $indiceLevel] = $display->getUVLevel($cmd->execCmd());
                    $replace['#uv_level#'] = $isObjet ?  $uvLevel : '';
                    if ($indiceLevel >= 3) {
                        $counterActivePolluant++;
                    }
                } else if ($nameCmd == 'visibility') {
                    $value = $isObjet ? $cmd->execCmd() : '';
                    $replace[$commandValue] = $value;
                    $replace[$commandNameId] = $isObjet ? $cmd->getId() : '';
                    $replace[$commandName] = $isObjet ? __($cmd->getName(), __FILE__) : '';
                    $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                    $replace[$nameIcon] = $isObjet ? $newIcon : '';
                    [$visibilityLevel, $indiceLevel] = $display->getVisibilityLevel($cmd->execCmd());
                    $replace['#visibility_level#'] =  $isObjet ? $visibilityLevel : '';
                    if ($indiceLevel >= 2) {
                        $counterActivePolluant++;
                    }
                } else if ($nameCmd == 'telegramPollution') {
                    $message_alert =  $isObjet ? $cmd->execCmd() : '';
                    $alert = (!empty($message_alert)) ? true : false;
                    if ($alert) {
                        $htmlAlertAqi = '<div style="text-align: center;">';
                        $htmlAlertAqi .= '<marquee scrollamount="4" width="85%" height="20px" class="state" style="font-size: 110%;">' . $cmd->execCmd() . '</marquee>';
                        $htmlAlertAqi .= '</div>';
                        $replace['#message#'] =  $htmlAlertAqi;
                        self::makeThreeMinuteAction('alertAqiCronTwoMin');
                    }
                } else  if ($cmd->getConfiguration($nameCmd) == 'slideAqi' || $cmd->getConfiguration($nameCmd) == 'both') {

                    $setupAlert = $this->getParamAlertAqi();
                    $index = $nameCmd . '_alert_level';
                    $maxAlertLevel = $setupAlert[$index];
                    $valueCurrent = $isObjet ? $cmd->execCmd() : '';
                    // is it synchro
                    $indexSync = $nameCmd . '_synchro';
                    $isSynchro = $setupAlert[$indexSync];              

                    if ($cmd->getIsVisible() == 1 && $maxAlertLevel <= $valueCurrent && $isSynchro == 1  || $cmd->getIsVisible() == 1 && $isSynchro == 0)   {
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), '30px');
                        $unitreplace['#icone#'] =  $isObjet ? $newIcon : '';
                        $unitreplace['#id#'] =  $isObjet ? $this->getId() : '';
                        $unitreplace['#value#'] =  $isObjet ?  $display->formatValueForDisplay($valueCurrent) : '';
                        $unitreplace['#name#'] = $isObjet ? $cmd->getLogicalId() : '';
                        $unitreplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                        $unitreplace['#cmdid#'] = $isObjet ?  $cmd->getId() : '';
                        $unitreplace['#history#'] =  $isObjet ? 'history cursor' : '';
                        $unitreplace['#info-modalcmd#'] = $isObjet ?  'info-modal' . $cmd->getLogicalId() . $this->getId() : '';
                        $unitreplace['#unity#'] =  $isObjet ? $cmd->getUnite() : '';
                        $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                        $unitreplace['#max#'] = (is_object($maxCmd) && !empty($maxCmd->execCmd())) ? $maxCmd->execCmd() : "[0,0,0]";
                        $minCmd = $this->getCmd(null, $nameCmd . '_min');
                        $unitreplace['#min#'] = (is_object($minCmd) && !empty($minCmd->execCmd())) ? $minCmd->execCmd() : "[0,0,0]";
                        $unitreplace['#color#'] =  ($isObjet && !empty($icone->getColor())) ?  $icone->getColor() : '#333333';
                        $labels = $this->getCmd(null, 'days');
                        $unitreplace['#labels#'] = (is_object($labels) && !empty($labels->execCmd())) ? $labels->execCmd() :  "['no','-','data']";
                        [$levelRiskAQI, $indiceLevel] = $display->getElementRiskAqi($icone->getColor());
                        $unitreplace['#level-particule#'] =  $isObjet ?  $levelRiskAQI : '';
                        if ($indiceLevel >= 3) {
                            $counterActivePolluant++;
                        }
                        $unitreplace['#info-tooltips#'] =   __("Cliquez pour + d'info", __FILE__);
                        $unitreplace['#mini#'] = __("Mini 10 jours", __FILE__);
                        $unitreplace['#maxi#'] = __("Maxi 10 jours", __FILE__);
                        $unitreplace['#tendency#'] = __("Tendance 12h", __FILE__);
                        $unitreplace['#average#'] = __("Moyenne 10 jours", __FILE__);
                        if ($cmd->getIsHistorized() == 1) {
                            $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                            $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
                            $unitreplace['#minHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['min'], 'short') : '';
                            $unitreplace['#maxHistoryValue#'] =  $isObjet ? $display->formatValueForDisplay($historyStatistique['max'], 'short') : '';
                            $unitreplace['#averageHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['avg'], 'short') : '';
                            $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 12 . ' hour'));
                            $tendance = $cmd->getTendance($startHist, date('Y-m-d H:i:s'));
                            if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-up' : '';
                            } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-down' : '';
                            } else {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-minus' : '';
                            }
                            $unitreplace['#display#'] = '';
                        } else {
                            $unitreplace['#display#'] =  $isObjet ? 'hidden' : '';
                        }
                        $tabUnitReplace[] = [template_replace($unitreplace, $elementTemplate), $indiceLevel];
                    }

                    // Affichage central pour AQI à la fin : double affichage
                    if ($nameCmd == 'aqi') {
                        $replace[$commandValue] =  $isObjet ? $cmd->execCmd() : '';
                        $replace[$info] =   $isObjet ? $display->getAqiName($cmd->execCmd()) : '';
                        $replace[$commandNameId] =   $isObjet ? $cmd->getId() : '';
                        $replace[$commandName] = $isObjet ?  $cmd->getName() : '';
                        $newIcon = $icone->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId());
                        $replace[$nameIcon] = $isObjet ? $newIcon : '';
                        $updatedAt = ($isObjet && $cmd->execCmd()) ? $display->parseDate($cmd->getCollectDate()) : 'No data';
                        }
                }
            }
            if (!$alert) {
                if ($counterActivePolluant == 0) {
                    $active_aqi_label = __("Pas d'indice en alerte", __FILE__);
                    $htmlActivePollen = '<div title="'.$updatedAt.'" style="text-align: center; font-size:110%; margin:10px 0px;"';
                    $htmlActivePollen .= ' class="cmd noRefresh tooltips"  data-type="info" data-subtype="other" data-cmd_id="'.$cmd->getId().'">';
                    $htmlActivePollen .=  $active_aqi_label. ' </div>';
                    $replace['#message#'] = $htmlActivePollen;
                } else {
                    $active_aqi_label = __('Indices en alerte', __FILE__);
                    $htmlActivePollen = '<div title="'.$updatedAt.'" style="text-align: center; font-size:110%; margin:10px 0px;"';
                    $htmlActivePollen .= ' class="cmd noRefresh tooltips"  data-type="info" data-subtype="other" data-cmd_id="'.$cmd->getId().'">';
                    $htmlActivePollen .=  $active_aqi_label.'&nbsp;&nbsp;'. $counterActivePolluant . ' / 11 </div>';
                    $replace['#message#'] = $htmlActivePollen;
                }
              
            }
            $tabUnityValue  = array_column($tabUnitReplace, 1);
            $tabUnityHtml = array_column($tabUnitReplace, 0);
            array_multisort($tabUnityValue, SORT_DESC, $tabUnityHtml);
            $elementHtml = new CreateHtmlAqi($tabUnityHtml, $this->getId(), 1, $version, $this->getConfiguration('elements'), 0);
        }

        // Pollen 
        if ($this->getConfiguration('elements') == 'pollen') {
            $tabHeader = [];
            $elementTemplate = getTemplate('core', $version, 'elementPollen', 'airquality');
            $headerTemplate = getTemplate('core', $version, 'headerPollen', 'airquality');

            foreach ($this->getCmd('info') as $cmd) {
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#' . $nameCmd . '_name#';
                $info = '#' . $nameCmd . 'info#';
                $isObjet = is_object($cmd);
                $iconePollen = new IconesPollen;

                if ($nameCmd == 'tree_pollen' || $nameCmd == 'grass_pollen'  || $nameCmd == 'weed_pollen') {
                    switch ($nameCmd) {
                        case 'tree_pollen':
                            $treePollenCmd = $this->getCmd(null, 'tree_risk');
                            $headerReplace['#main_risk#'] =  $isObjet ? $display->getPollenRisk($treePollenCmd->execCmd()) : '';
                            break;
                        case 'grass_pollen':
                            $grassPollenCmd = $this->getCmd(null, 'grass_risk');
                            $headerReplace['#main_risk#'] =  $isObjet ? $display->getPollenRisk($grassPollenCmd->execCmd()) : '';
                            break;
                        case 'weed_pollen':
                            $weedPollenCmd = $this->getCmd(null, 'weed_risk');
                            $headerReplace['#main_risk#'] =  $isObjet ? $display->getPollenRisk($weedPollenCmd->execCmd()) : '';
                    }
                    $headerReplace['#id#'] =  $isObjet ? $this->getId() : '';
                    $headerReplace['#main_cmd_pollen_id#'] =   $isObjet ? $cmd->getId() : '';
                    $headerReplace['#main_pollen_name#'] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                    $headerReplace['#list_main_pollen#'] =  $isObjet ?  $display->getListPollen($nameCmd) : '';
                    $value = $isObjet ? $cmd->execCmd() : '';
                    // Hack Value tree //
                    // if ($nameCmd == 'tree_pollen') {
                    // $value = rand(0, 0);
                    // $headerReplace['#main_pollen_value#'] = $value;
                    // $newIcon = $iconePollen->getIcon($nameCmd, $value, $cmd->getId(), false);
                    // } else {
                    $headerReplace['#main_pollen_value#'] = $value;
                    $newIcon = $iconePollen->getIcon($nameCmd, $value, $cmd->getId(), false);
                    // }                   
                    $headerReplace['#icone__pollen#'] = $isObjet ?  $newIcon : '';
                    $tabHederOne = template_replace($headerReplace, $headerTemplate);
                    $tabHeader[] = [$tabHederOne, $value];

                } else  if ($nameCmd == 'updatedAt') {
                  
                    $updatedAt = ($isObjet && $cmd->execCmd()) ? $display->parseDate($cmd->getCollectDate()) : '';
                
                } else if ($nameCmd == 'telegramPollen') {
                    $message_alert =  $isObjet ? $cmd->execCmd() : '';
                    $alert = (!empty($message_alert)) ? true : false;
                    if ($alert) {
                        $htmlAlertPollen = '<div style="text-align: center; margin-top:20px">';
                        $htmlAlertPollen .= '<marquee scrollamount="4" width="85%" class="state" style="font-size: 110%;height:20px">' . $message_alert . '</marquee>';
                        $htmlAlertPollen .= '</div>';
                        $replace['#message_alert#'] =  $htmlAlertPollen;
                        self::makeThreeMinuteAction('alertPollenCronTwoMin', 2);
                    }
                } else if ($cmd->getConfiguration($nameCmd) == 'slide') {

                    $activePollenCounter = ($cmd->execCmd() > 0) ? $activePollenCounter + 1 : $activePollenCounter;
                    // $setupAlert = $this->getParamAlertPollen();
                    // $index = $nameCmd . '_alert_level';
                    // $maxDisplayLevel = $setupAlert[$index];
                    $valueCurrent = $isObjet ? $cmd->execCmd() : '';
                    $maxDisplayLevel = $this->getConfiguration('pollen_alert_level');

                    if ($cmd->getIsVisible() == 1 && $maxDisplayLevel <= $valueCurrent && $valueCurrent > 0) {
                        $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                        $unitreplace['#icone#'] =  $isObjet ? $newIcon : '';
                        $unitreplace['#id#'] =  $isObjet ? $this->getId() : '';
                        $value = $isObjet ? $cmd->execCmd() : '';
                        $unitreplace['#value#'] =  $value;
                        $unitreplace['#name#'] = $isObjet ? $cmd->getLogicalId() : '';
                        $unitreplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                        $unitreplace['#cmdid#'] = $isObjet ?  $cmd->getId() : '';
                        $unitreplace['#history#'] =  $isObjet ? 'history cursor' : '';
                        $unitreplace['#info-modalcmd#'] = $isObjet ?  'info-modal' . $cmd->getLogicalId() . $this->getId() : '';
                        $unitreplace['#unity#'] =  $isObjet ? $cmd->getUnite() : '';

                        $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                        $unitreplace['#max#'] = (is_object($maxCmd) && !empty($maxCmd->execCmd())) ? $maxCmd->execCmd() : "[0,0,0]";
                        $minCmd = $this->getCmd(null, $nameCmd . '_min');
                        $unitreplace['#min#'] = (is_object($minCmd) && !empty($minCmd->execCmd())) ? $minCmd->execCmd() : "[0,0,0]";
                        $unitreplace['#color#'] =  ($isObjet &&  !empty($iconePollen->getColor())) ?  $iconePollen->getColor() : '#222222';
                        $labels = $this->getCmd(null, 'daysPollen');
                        $unitreplace['#labels#'] =  (is_object($labels) && !empty($labels->execCmd())) ? $labels->execCmd() : "['no','-','data']";

                        $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                        $unitreplace['#risk#'] =  $isObjet ?  $display->getElementRiskPollen($iconePollen->getColor()) : '';

                        $unitreplace['#info-tooltips#'] =   __("Cliquez pour + d'info", __FILE__);
                        $unitreplace['#mini#'] = __("Mini 10 jours", __FILE__);
                        $unitreplace['#maxi#'] = __("Maxi 10 jours", __FILE__);
                        $unitreplace['#tendency#'] = __("Tendance 12h", __FILE__);
                        $unitreplace['#average#'] = __("Moyenne 10 jours", __FILE__);
                        if ($cmd->getIsHistorized() == 1) {
                            $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                            $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
                            $unitreplace['#minHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['min'], 'short') : '';
                            $unitreplace['#maxHistoryValue#'] =  $isObjet ? $display->formatValueForDisplay($historyStatistique['max'], 'short') : '';
                            $unitreplace['#averageHistoryValue#'] =  $isObjet ?  $display->formatValueForDisplay($historyStatistique['avg'], 'short') : '';
                            $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 12 . ' hour'));
                            $tendance = $cmd->getTendance($startHist, date('Y-m-d H:i:s'));
                            if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-up' : '';
                            } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-arrow-down' : '';
                            } else {
                                $unitreplace['#tendance#'] = $isObjet ? 'fas fa-minus' : '';
                            }
                            $unitreplace['#display#'] = '';
                        } else {
                            $unitreplace['#display#'] =  $isObjet ? 'hidden' : '';
                        }
                        $tabUnitReplace[] = [template_replace($unitreplace, $elementTemplate), $value];
                    } else 
                        // Cas Pollen à ZERO 
                        if ($this->getConfiguration('pollen_alert_level') == 0 && $cmd->execCmd() == 0) {
                        // if ($this->getConfiguration('displayZeroPollen') == 1 && $cmd->execCmd() == 0) {
                            $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                            $pollenZeroReplace['#icone#'] = $isObjet ? $newIcon : '';
                            $pollenZeroReplace['#id#'] = $isObjet ? $this->getId() : '';
                            $pollenZeroReplace['#value#'] = $isObjet ? 0 : '';
                            $pollenZeroReplace['#name#'] = $isObjet ?  $cmd->getLogicalId() : '';
                            $pollenZeroReplace['#display-name#'] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                            $pollenZeroReplace['#cmdid#'] = $isObjet ?  $cmd->getId() : '';
                            $pollenZeroReplace['#info-modalcmd#'] =  $isObjet ? 'info-modal' . $cmd->getLogicalId() . $this->getId() : '';
                            $pollenZeroReplace['#message#'] = __('Aucune Détection', __FILE__);
                            $templateZero = getTemplate('core', $version, 'elementPollenZero', 'airquality');
                            $tabZero[] = template_replace($pollenZeroReplace, $templateZero);
                        
                    }


                    // Affichage central pour Others à la fin/(double passage if) car double affichage
                    if ($nameCmd == 'others') {
                        $headerReplace['#main_pollen_value#'] =  $isObjet ? $cmd->execCmd() : '';
                        $headerReplace['#id#'] =  $isObjet ? $this->getId() : '';
                        $headerReplace['#main_cmd_pollen_id#'] =   $isObjet ? $cmd->getId() : '';
                        $headerReplace['#main_pollen_name#'] =  $isObjet ? __($cmd->getName(), __FILE__) : '';
                        $newIcon = $iconePollen->getIcon($nameCmd, $cmd->execCmd(), $cmd->getId(), false);
                        $headerReplace['#icone__pollen#'] = $isObjet ?  $newIcon : '';
                        $headerReplace['#list_main_pollen#'] =  $isObjet ?  $display->getListPollen($nameCmd) : '';
                        $headerReplace['#main_risk#'] =  $isObjet ? $display->getElementRiskPollen($iconePollen->getColor($cmd->execCmd(), $nameCmd)) : '';
                        $value = $isObjet ? $cmd->execCmd() : '';
                        // Hack value
                        // $headerReplace['#main_pollen_value#'] =  $isObjet ? 1000 : '';
                        // $value = $isObjet ? 1000 : '';
                        $tabHeaderOne = template_replace($headerReplace, $headerTemplate);
                        $tabHeader[] = [$tabHeaderOne, $value];
                    }
                }
            }

            $tabUnityValue  = array_column($tabUnitReplace, 1);
            $tabUnityHtml = array_column($tabUnitReplace, 0);
            array_multisort($tabUnityValue, SORT_DESC, $tabUnityHtml);

            $counterPollenZero = 0;
            if (isset($tabZero)) {
                $newArray = array_chunk($tabZero, 4);
                foreach ($newArray as $arr) {
                    $tabUnityHtml[] = implode('', $arr);
                    $counterPollenZero++;
                }
            }
            if (!$alert) {
                if($activePollenCounter == 0) {
                    $active_pollen_label = __('Aucun pollen actif', __FILE__);
                    $htmlActivePollen = '<div title="' . $updatedAt . '" class="cmd noRefresh header-' . $this->getId() . '-mini ';
                    $htmlActivePollen .= 'active-pollen-' . $this->getId() . ' " data-type="info" data-subtype="string" data-cmd_id="' . $cmd->getId() . '">';
                    $htmlActivePollen .= $active_pollen_label . '</div>';
                    $replace['#message_alert#'] = $htmlActivePollen;
                } else {
                    $active_pollen_label = __('Pollens actifs', __FILE__);
                    $htmlActivePollen = '<div title="' . $updatedAt . '" class="cmd noRefresh header-' . $this->getId() . '-mini ';
                    $htmlActivePollen .= 'active-pollen-' . $this->getId() . ' " data-type="info" data-subtype="string" data-cmd_id="' . $cmd->getId() . '">';
                    $htmlActivePollen .= $active_pollen_label . '&nbsp;&nbsp;' . $activePollenCounter . ' / 15 </div>';
                    $replace['#message_alert#'] = $htmlActivePollen;
                }
             
            }
            $tabValue  = array_column($tabHeader, 1);
            $tabHtml = array_column($tabHeader, 0);
            array_multisort($tabValue, SORT_DESC, $tabHtml);
           
            if ( in_array(0, $tabValue) && $this->getConfiguration('pollen_alert_level') > 0  ) {
            // if (in_array(0, $tabValue) && $this->getConfiguration('displayZeroPollen') == 0) {
                array_pop($tabHtml);
            }
            $replace['#header#'] = implode('', $tabHtml);
            $elementHtml = new CreateHtmlAqi($tabUnityHtml, $this->getId(), 1, $version, $this->getConfiguration('elements'), $counterPollenZero);
        }

        // Replace Global        
        $replace['#info-tooltips#'] = __("Cliquez pour + d'info", __FILE__);
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

    private static function makeThreeMinuteAction($configName, $delay = 2)
    {
        $now = new \DateTime();
        $hour = $now->format('H');
        $minute = $now->format('i');
        $minute = $minute + $delay;
        $cron =  $minute . ' ' . $hour . ' * * *';
        log::add('airquality', 'debug', 'Make cron + ' . $delay . ' - ' . $cron);
        foreach (self::byType('airquality') as $airQuality) {
            $airQuality->setConfiguration($configName, $cron)->save();
        }
    }


    public static function postConfig_apikey()
    {
        if (config::byKey('apikey', 'airquality') == '' && config::byKey('apikeyAmbee', 'airquality') == '') {
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
    private function getApiData(string $apiName)
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


    private function getParamAlertAqi()
    {
        $arrayLevel['aqi_alert_level'] = $this->getConfiguration('aqi_alert_level');
        $arrayLevel['pm25_alert_level'] = $this->getConfiguration('pm25_alert_level');
        $arrayLevel['pm10_alert_level'] = $this->getConfiguration('pm10_alert_level');
        $arrayLevel['no2_alert_level'] = $this->getConfiguration('no2_alert_level');
        $arrayLevel['so2_alert_level'] = $this->getConfiguration('so2_alert_level');
        $arrayLevel['o3_alert_level'] = $this->getConfiguration('o3_alert_level');
        $arrayLevel['co_alert_level'] = $this->getConfiguration('co_alert_level');
        $arrayLevel['nh3_alert_level'] = $this->getConfiguration('nh3_alert_level');
        $arrayLevel['no_alert_level'] = $this->getConfiguration('no_alert_level');
        $arrayLevel['uv_alert_level'] = $this->getConfiguration('uv_alert_level');
        $arrayLevel['visibility_alert_level'] = $this->getConfiguration('visibility_alert_level');
        $arrayLevel['alert_notification'] = $this->getConfiguration('alert_notification');
        $arrayLevel['alert_details'] = $this->getConfiguration('alert_details');
        $arrayLevel['aqi_synchro'] = $this->getConfiguration('aqi_synchro');
        $arrayLevel['pm25_synchro'] = $this->getConfiguration('pm25_synchro');
        $arrayLevel['pm10_synchro'] = $this->getConfiguration('pm10_synchro');
        $arrayLevel['no2_synchro'] = $this->getConfiguration('no2_synchro');
        $arrayLevel['so2_synchro'] = $this->getConfiguration('so2_synchro');
        $arrayLevel['o3_synchro'] = $this->getConfiguration('o3_synchro');
        $arrayLevel['co_synchro'] = $this->getConfiguration('co_synchro');
        $arrayLevel['nh3_synchro'] = $this->getConfiguration('nh3_synchro');
        $arrayLevel['no_synchro'] = $this->getConfiguration('no_synchro');
        $arrayLevel['so_synchro'] = $this->getConfiguration('so_synchro');
        return $arrayLevel;
    }

    private function getParamAlertPollen()
    {
        $arrayLevel['poaceae_alert_level'] = $this->getConfiguration('poaceae_alert_level');
        $arrayLevel['alder_alert_level'] = $this->getConfiguration('alder_alert_level');
        $arrayLevel['birch_alert_level'] = $this->getConfiguration('birch_alert_level');
        $arrayLevel['cypress_alert_level'] = $this->getConfiguration('cypress_alert_level');
        $arrayLevel['elm_alert_level'] = $this->getConfiguration('elm_alert_level');
        $arrayLevel['hazel_alert_level'] = $this->getConfiguration('hazel_alert_level');
        $arrayLevel['oak_alert_level'] = $this->getConfiguration('oak_alert_level');
        $arrayLevel['pine_alert_level'] = $this->getConfiguration('pine_alert_level');
        $arrayLevel['plane_alert_level'] = $this->getConfiguration('plane_alert_level');
        $arrayLevel['poplar_alert_level'] = $this->getConfiguration('poplar_alert_level');
        $arrayLevel['chenopod_alert_level'] = $this->getConfiguration('chenopod_alert_level');
        $arrayLevel['mugwort_alert_level'] = $this->getConfiguration('mugwort_alert_level');
        $arrayLevel['nettle_alert_level'] = $this->getConfiguration('nettle_alert_level');
        $arrayLevel['ragweed_alert_level'] = $this->getConfiguration('ragweed_alert_level');
        $arrayLevel['others_alert_level'] = $this->getConfiguration('others_alert_level');
        $arrayLevel['alert_notification'] = $this->getConfiguration('alert_notification');
        $arrayLevel['alert_details'] = $this->getConfiguration('alert_details');
        return $arrayLevel;
    }

    /**
     * Creation tableau associatif avec data de pollution ou de pollen + nom en index
     */
    private function getCurrentValues()
    {
        $dataArray = [];
        foreach ($this->getCmd('info') as $cmd) {
            $logicId = is_object($cmd) ?  $cmd->getLogicalId() : '';
            $value = is_object($cmd) ? $cmd->execCmd() : '';
            $dataArray[$logicId] = $value;
        }
        return $dataArray;
    }


    /**
     * Appel api Pollen Live + Update des Commands + reorder by level  
     */
    public function updatePollen()
    {
        $dataAll = $this->getApiData('getAmbee');
        if (isset($dataAll->data)) {
            $oldData = $this->getCurrentValues();
            $dataPollen = $dataAll->data;
            $this->checkAndUpdateCmd('tree_risk', $dataPollen[0]->Risk->tree_pollen);
            $this->checkAndUpdateCmd('weed_risk', $dataPollen[0]->Risk->weed_pollen);
            $this->checkAndUpdateCmd('grass_risk', $dataPollen[0]->Risk->grass_pollen);
            $this->checkAndUpdateCmd('tree_pollen', $dataPollen[0]->Count->tree_pollen);
            $this->checkAndUpdateCmd('weed_pollen', $dataPollen[0]->Count->weed_pollen);
            $this->checkAndUpdateCmd('grass_pollen', $dataPollen[0]->Count->grass_pollen);
            $this->checkAndUpdateCmd('poaceae', $dataPollen[0]->Species->Grass->{"Grass / Poaceae"});
            $this->checkAndUpdateCmd('alder', $dataPollen[0]->Species->Tree->Alder);
            $this->checkAndUpdateCmd('birch', $dataPollen[0]->Species->Tree->Birch);
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
            $paramAlertPollen = $this->getParamAlertPollen();
            $display = new DisplayInfo;
            $messagesPollens =  $display->getAllMessagesPollen($oldData, $dataPollen, $paramAlertPollen);
            $this->checkAndUpdateCmd('messagePollen', $messagesPollens[0]);
            $telegramMess = !empty($messagesPollens[0]) ? $messagesPollens[1] : '';
            $this->checkAndUpdateCmd('telegramPollen', $telegramMess);
            $smsMess = !empty($messagesPollens[0]) ? $messagesPollens[2] : '';
            $this->checkAndUpdateCmd('smsPollen',  $smsMess);
            $markdownMessage = !empty($messagesPollens[0]) ? $messagesPollens[3] : '';
            $this->checkAndUpdateCmd('markdownPollen', $markdownMessage);
            $this->refreshWidget();
        }
    }

    /**
     * Appel api AQI live + UV + Visibility + Update des Commands 
     */
    public function updatePollution()
    {
        $paramAlertAqi = $this->getParamAlertAqi();
        $oldData = $this->getCurrentValues();
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
        $dataOneCall = $this->getApiData('getOneCallApi');
        $this->checkAndUpdateCmd('uv', $dataOneCall->uvi);
        $this->checkAndUpdateCmd('visibility', $dataOneCall->visibility);
        $display = new DisplayInfo;
        $messagesPollution = $display->getAllMessagesPollution($oldData, $data, $dataOneCall, $paramAlertAqi);
        $this->checkAndUpdateCmd('messagePollution', ($messagesPollution[0]));
        $telegramMess = !empty($messagesPollution[0]) ? $messagesPollution[1] : '';
        $this->checkAndUpdateCmd('telegramPollution', $telegramMess);
        $smsMess = !empty($messagesPollution[0]) ? $messagesPollution[2] : '';
        $this->checkAndUpdateCmd('smsPollution',  $smsMess);
        $markdownMessage = !empty($messagesPollution[0]) ? $messagesPollution[3] : '';
        $this->checkAndUpdateCmd('markdownPollution', $markdownMessage);
        $this->refreshWidget();
    }

    /**
     * Appel api Forecast AQI + Update des Commands 
     */
    public function updateForecastAQI()
    {
        $forecastRaw =  $this->getApiData('getForecast');
        $forecast = $forecastRaw[0];
        // $forecastFull = $forecast[1];
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
        if (is_array($forecast) && $forecast != []) {
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

    public function deleteAlertAqi()
    {
        $this->checkAndUpdateCmd('messagePollution', '');
        $this->checkAndUpdateCmd('telegramPollution', '');
        $this->checkAndUpdateCmd('smsPollution', '');
        $this->checkAndUpdateCmd('markdownPollution', '');
        $this->refreshWidget();
    }

    public function deleteAlertPollen()
    {
        $this->checkAndUpdateCmd('messagePollen', '');
        $this->checkAndUpdateCmd('telegramPollen', '');
        $this->checkAndUpdateCmd('smsPollen', '');
        $this->checkAndUpdateCmd('markdownPollen', '');
        $this->refreshWidget();
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

        if ($this->getLogicalId() == 'refresh_alert_aqi') {
            $this->getEqLogic()->deleteAlertAqi();
        }

        if ($this->getLogicalId() == 'refresh_alert_pollen') {
            $this->getEqLogic()->deleteAlertPollen();
        }
    }
}
