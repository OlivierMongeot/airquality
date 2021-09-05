<?php

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

// error_reporting(E_ALL);
// ini_set('ignore_repeated_errors', TRUE);
// ini_set('display_errors', TRUE);
// ini_set('log_errors', TRUE); // Error/Exception file logging engine.
// ini_set('error_log', __DIR__ . '/../../../../plugins/airquality/errors.log'); // Logging file path

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require dirname(__FILE__) . '/../../core/php/airquality.inc.php';

class airquality extends eqLogic
{

    public static $_widgetPossibility = ['custom' => true, 'custom::layout' => false];

    public static function cron()
    {
        // Assignation d'une minute de refresh aléatoire pour éviter 'saturation' server gratuit OpenWeatherMap (cf problem getAmbee)
        foreach (self::byType('airquality') as $airQuality) {

            if ($airQuality->getIsEnable() == 1) {
                // Pollution Current Toutes demi-heure 
                try {
                    $minutePollution = (int)trim(config::byKey('cron_aqi_minute', 'airquality'));
                    $thirtyMinMore = $minutePollution + 30;
                    if ($thirtyMinMore > 59) {
                        $thirtyMinMore = $thirtyMinMore - 60;
                    }
                    $crontab = $minutePollution . "," . $thirtyMinMore . " * * * *";
                    // log::add('airquality', 'debug', 'Cron refresh de l aqi current : ' . $crontab);
                    $c = new Cron\CronExpression($crontab, new Cron\FieldFactory);
                    if ($c->isDue()) {
                        $airQuality->updatePollution();
                    }
                } catch (Exception $e) {
                    log::add('airquality', 'debug', __('Expression cron non valide pour update Pollution current', __FILE__). ' Expression = ' . $crontab. ' pour '  . $airQuality->getHumanName() . ' : ' . json_encode($e));
                }

                // Forecast : 2x jours si activé à 6h et 13h 
                if ($airQuality->getConfiguration('data_forecast') == 'actived') {
                    try {
                        $minForecast = abs((int)$thirtyMinMore - 1);
                        $cronForecast = $minForecast . " 6,13 * * *";
                        $c = new Cron\CronExpression($cronForecast, new Cron\FieldFactory);
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
                        log::add('airquality', 'debug', 'Cron Forecast aqi : ' . $cronForecast);
                        log::add('airquality', 'debug', __('Expression cron non valide pour Pollution refresh forecast', __FILE__) . $airQuality->getHumanName() . ' : ' . json_encode($e));
                    }
                }

                // Delete Alert Pollution message after x min 
                try {
                    $specialCron =  $airQuality->getConfiguration('alertAqiCronTwoMin');
                    if (empty($specialCron)) {
                        $specialCron = '0 0 1 1 *';
                    }
                    $cManual = new Cron\CronExpression($specialCron, new Cron\FieldFactory);
                    if ($cManual->isDue()) {
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
        }
    }

    /**
     * Retourne le delta par rapport à la dernière mise à jour en minute
     */
    public function getIntervalLastRefresh($cmdXToTest)
    {
        if (is_object($cmdXToTest)) {

            $collectDate = $cmdXToTest->getCollectDate();
            if ($collectDate == null) {
                return 5000;
            }
            $datetimeCollected = DateTime::createFromFormat('Y-m-d H:i:s', $collectDate);
            $dateNow = new DateTime();
            $dateNow->setTimezone(new DateTimeZone('Europe/Paris'));
            $interval = $datetimeCollected->diff($dateNow);
            log::add('airquality', 'debug', '----------------------------------------------------------------------');
            log::add('airquality', 'debug', 'Check Intervale derniere Collecte pour ' . $cmdXToTest->getHumanName() . '  : ' . $interval->i . ' m ' . $interval->h . ' h et ' . $interval->d . ' jours');
            $minuteToAdd = 0;
            if ($interval->d > 0) {
                $minuteToAdd = $interval->d * 24 * 60;
            }
            if ($interval->h > 0) {
                $minuteToAdd .= $interval->h * 60;
            }
            $total = $interval->i + $minuteToAdd;
            return $total;
        } else {
            throw new Exception("Commande non trouvée pour calculer l'interval de temps");
        }
    }

    public function preInsert()
    {
        log::add('airquality', 'debug', 'Start Function preInsert');
        $this->setCategory('heating', 1);
        $this->setIsEnable(1);
        $this->setIsVisible(1);
        //SetUp a time bettween 2 and 58 min for refresh at start : to not all Jeedom box call api in same time  
        $minute = rand(2, 58);
        config::save('cron_aqi_minute', $minute, 'airquality');
        log::add('airquality', 'debug', 'Set New Cron aqi minute at start  : minute ' . $minute . ' for pollution');
    }

    public function preUpdate()
    {
        log::add('airquality', 'debug', 'Start Function preUpdate pour ' . $this->getHumanName());
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
            }
        }
    }

    public function postSave()
    {
        log::add('airquality', 'debug', 'Start Function postSave pour ' . $this->getHumanName());
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

    public function preSave()
    {
        $this->setDisplay("width", "265px");
        if ($this->getConfiguration('data_forecast') == 'disable') {
            $this->setDisplay("height", "225px");
        } else {
            $this->setDisplay("height", "390px");
        }
    }


    public function postUpdate()
    {
        log::add('airquality', 'debug', 'Start Function postUpdate pour ' . $this->getHumanName());
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

        $commands = SetupAqi::$setupAqi;

        foreach ($commands as $command) {
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
        $this->emptyCacheWidget(); //vide le cache
        $version = jeedom::versionAlias($_version);
        $display = new DisplayInfo;
        $tabUnitReplace = [];

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
                $arrayUvLevel = $display->getUVLevel($cmd->execCmd());
                $uvLevel = $arrayUvLevel[0];
                $indiceLevel = $arrayUvLevel[1];

                $replace['#history#'] =  $isObjet ? 'history cursor' : '';
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
                $arrayVisibilityLevel = $display->getVisibilityLevel($cmd->execCmd());
                $visibilityLevel = $arrayVisibilityLevel[0];
                $indiceLevel = $arrayVisibilityLevel[1];

                $replace['#visibility_level#'] =  $isObjet ? $visibilityLevel : '';
                $replace['#history#'] =  $isObjet ? 'history cursor' : '';
                if ($indiceLevel >= 2) {
                    $counterActivePolluant++;
                }
            } else if ($nameCmd == 'telegramPollution') {
                $message_alert =  $isObjet ? $cmd->execCmd() : '';
                $alert = (!empty($message_alert)) ? true : false;
                if ($alert) {
                    $htmlAlertAqi = '<div style="text-align: center;margin-top: 15px">';
                    $htmlAlertAqi .= '<marquee scrollamount="4" width="85%" height="18px" class="state" style="font-size: 100%;margin: -10px 0px !important;">' . $cmd->execCmd() . '</marquee>';
                    $htmlAlertAqi .= '</div>';
                    $replace['#message#'] =  $htmlAlertAqi;
                }
            } else  if ($cmd->getConfiguration($nameCmd) == 'slideAqi' || $cmd->getConfiguration($nameCmd) == 'both') {

                $setupAlert = $this->getParamAlertAqi();
                $index = $nameCmd . '_alert_level';
                $maxAlertLevel = $setupAlert[$index];
                $valueCurrent = $isObjet ? $cmd->execCmd() : '';
                // is it synchro : alert & display
                $indexSync = $nameCmd . '_synchro';
                $isSynchro = $setupAlert[$indexSync];

                if ($cmd->getIsVisible() == 1 && $maxAlertLevel <= $valueCurrent && $isSynchro == 1  || $cmd->getIsVisible() == 1 && $isSynchro == 0) {
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
                    //Forecast Display 
                    if ($this->getConfiguration('data_forecast') != 'disable') {
                        $maxCmd = $this->getCmd(null, $nameCmd . '_max');
                        $unitreplace['#max#'] = (is_object($maxCmd) && !empty($maxCmd->execCmd())) ? $maxCmd->execCmd() : "[0,0,0]";
                        $minCmd = $this->getCmd(null, $nameCmd . '_min');
                        $unitreplace['#min#'] = (is_object($minCmd) && !empty($minCmd->execCmd())) ? $minCmd->execCmd() : "[0,0,0]";
                        $unitreplace['#color#'] =  ($isObjet && !empty($icone->getColor())) ?  $icone->getColor() : '#333333';
                        $labels = $this->getCmd(null, 'days');
                        $unitreplace['#labels#'] = (is_object($labels) && !empty($labels->execCmd())) ? $labels->execCmd() :  "['no','-','data']";
                        $unitreplace['#height0#'] = '';
                        $unitreplace['#hidden#'] = '';
                    } else {
                        $unitreplace['#labels#'] = "['0','0','0']";
                        $unitreplace['#max#'] = "[0,0,0]";
                        $unitreplace['#min#'] =  "[0,0,0]";
                        $unitreplace['#color#'] = '#333333';
                        $unitreplace['#height0#'] = 'style="height:0"';
                        $unitreplace['#hidden#'] = 'hidden';
                    }
                    // Fin Forecast 
                    $arrayLevelRiskAQI = $display->getElementRiskAqi($icone->getColor());
                    $levelRiskAQI = $arrayLevelRiskAQI[0];
                    $indiceLevel = $arrayLevelRiskAQI[1];
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
                    $replace['#updateAt#'] =  'Lieu : ' . $this->getCurrentCityName();
                }
            }
        }
        // Message statique d'alert
        if (!isset($alert) || !$alert) {
            if ($counterActivePolluant == 0) {
                $active_aqi_label = __("Pas d'alerte", __FILE__);
                $htmlActivePollen = '<div title="' .  $updatedAt . '" style="text-align: center; font-size:110%; margin:10px 0px;"';
                $htmlActivePollen .= ' class="cmd noRefresh tooltips"  data-type="info" data-subtype="other" data-cmd_id="' . $cmd->getId() . '">';
                $htmlActivePollen .=  $active_aqi_label . ' </div>';
                $replace['#message#'] = $htmlActivePollen;
            } else {
                $active_aqi_label = __('Indices en alerte', __FILE__);
                $htmlActivePollen = '<div title="' . $updatedAt . '" style="text-align: center; font-size:110%; margin:10px 0px;"';
                $htmlActivePollen .= ' class="cmd noRefresh tooltips"  data-type="info" data-subtype="other" data-cmd_id="' . $cmd->getId() . '">';
                $htmlActivePollen .=  $active_aqi_label . '&nbsp;&nbsp;' . $counterActivePolluant . ' / 11 </div>';
                $replace['#message#'] = $htmlActivePollen;
            }
        }
        // Classement par valeur
        $tabUnityValue  = array_column($tabUnitReplace, 1);
        $tabUnityHtml = array_column($tabUnitReplace, 0);
        array_multisort($tabUnityValue, SORT_DESC, $tabUnityHtml);
        $elementHtml = new CreateHtmlAqi($tabUnityHtml, $this->getId(), 1, $version);

        // Global  ----------------
        if ($this->getConfiguration('searchMode') == 'follow_me') {
            $arrayCurrentLL = $this->getCurrentLonLat();
            $lon = $arrayCurrentLL[0];
            $lat = $arrayCurrentLL[1];
            $replace['#button#'] = '<span><i class="fas fa-map-marker-alt fa-lg"></i></span> ' . $this->getCurrentCityName();
            $replace['#long_lat#'] = 'Lat ' . $display->formatValueForDisplay($lat, null, 4) . '° - Lon ' . $display->formatValueForDisplay($lon, null, 4) . '°';
            $replace['#height_footer#'] = 'height:50px';
            $replace['#stateRefreshDesktop#'] = 'style="display:none"';
            $replace['#padding#'] = '5px';
        } else {
            $replace['#button#'] = '';
            $replace['#long_lat#'] = '';
            $replace['#height_footer#'] = 'height:0px';
            $replace['#stateRefreshDesktop#'] = '';
            $replace['#padding#'] = '0px';
        }
        
        $minaqi = (int)(config::byKey('cron_aqi_minute', 'airquality'));
        $min30aqi =   (($minaqi + 30) > 59) ? $minaqi - 30 : $minaqi + 30;
        if ($min30aqi > $minaqi) {
            $replace['#updatetimeaqi#'] = "Mise à jour à la minute " .  $minaqi . " et " . $min30aqi . " de chaque heure";
        } else {
            $replace['#updatetimeaqi#'] = "Mise à jour à la minute " .  $min30aqi . " et " . $minaqi . " de chaque heure";
        }

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

        return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality', __CLASS__)));
    }

    /**
     * Set a cron for stop message in $delay min 
     */
    private function setMinutedAction($configName, $delay = 2)
    {
        $now = new \DateTime();
        $hour = $now->format('H');
        $minute = $now->format('i');
        $minuteEnd = $minute + $delay;
        if ($minuteEnd > 59) {
            $minuteEnd = str_replace('6', '', $minuteEnd);
            $hour = $hour + 1;
        }
        $cron =  $minuteEnd . ' ' . $hour . ' * * *';
        log::add('airquality', 'debug', 'Set cron + ' . $delay . ' - ' . $cron . ' to stop message alert for equipement ' . $this->getName());
        $this->setConfiguration($configName, $cron)->save();
    }


    public static function postConfig_apikey()
    {
        if (config::byKey('apikey', 'airquality') == '') {
            throw new Exception('Une clef OpenWeather est requise pour faire marcher le plugin');
        }
    }


    private function getCurrentCityName()
    {
        if ($this->getConfiguration('searchMode') == 'city_mode') {
            $city =  $this->getConfiguration('city');
        } else if ($this->getConfiguration('searchMode') == 'long_lat_mode') {
            $city = $this->getConfiguration('city-llm');
        } else if ($this->getConfiguration('searchMode') == 'dynamic_mode') {
            $city = $this->getConfiguration('geoCity');
        } else if ($this->getConfiguration('searchMode') == 'follow_me') {
            $city =  config::byKey('DynCity', 'airquality');
        } else if ($this->getConfiguration('searchMode') == 'server_mode') {
            $city = config::byKey('info::city');
        }
        return isset($city) ? $city : 'No city';
    }

    public function getCurrentLonLat()
    {
        if ($this->getConfiguration('searchMode') == 'city_mode') {
            log::add('airquality', 'debug', 'Mode city_mode concerning ' . $this->getHumanName());
            $lon =  $this->getConfiguration('city_longitude');
            $lat =  $this->getConfiguration('city_latitude');
        } elseif ($this->getConfiguration('searchMode') == 'long_lat_mode') {
            log::add('airquality', 'debug', 'Mode long_lat_mode concerning ' . $this->getHumanName());
            $lon = $this->getConfiguration('longitude');
            $lat = $this->getConfiguration('latitude');
        } elseif ($this->getConfiguration('searchMode') == 'dynamic_mode') {
            log::add('airquality', 'debug', 'Mode dynamic_mode concerning ' . $this->getHumanName());
            $lon = $this->getConfiguration('geoLongitude');
            $lat = $this->getConfiguration('geoLatitude');
        } else if ($this->getConfiguration('searchMode') == 'follow_me') {
            log::add('airquality', 'debug', 'Mode follow_me concerning ' . $this->getHumanName());
            $lon = config::byKey('DynLongitude', 'airquality');
            $lat = config::byKey('DynLatitude', 'airquality');
        } else if ($this->getConfiguration('searchMode') == 'server_mode') {
            log::add('airquality', 'debug', 'Mode server_mode concerning ' . $this->getHumanName());
            $lon = config::byKey('info::longitude');
            $lat = config::byKey('info::latitude');
        }
        return [$lon, $lat];
    }


    /**
     * Redirige l'appel API vers la bonne fonction + check des coordonnées 
     */
    private function getApiData(string $apiName)
    {
        $api = new ApiAqi();
        $city = $this->getCurrentCityName();
        // [$lon, $lat] = $this->getCurrentLonLat();
        $arratLonLat = $this->getCurrentLonLat();
        $lon = $arratLonLat[0];
        $lat = $arratLonLat[1];
        log::add('airquality', 'debug', $this->getHumanName() . ' -> Start API ' . $apiName . ' Calling for City : ' . $city . ' - Long :' . $lon . ' Lat :' . $lat);
        return $api->$apiName($lon, $lat);
    }


    /**
     * Pour recevoir appel Ajax. Utilisé dans la configuration mode "Geolocalisation du Navigateur"
     */
    public static function getCityName($longitude, $latitude, $save = false)
    {
        $api = new ApiAqi;
        $city  = $api->callApiReverseGeoLoc($longitude, $latitude);
        if ($save) {
            log::add('airquality', 'debug', 'Save City : ' . $city . ' en config general');
            config::save('DynCity', $city, 'airquality');
        }
        return $city;
    }


    /**
     * Pour appel Ajax. Utilisé dans la configuration mode "Par ville" et Follow me 
     */
    public static function getCoordinates($city, $country_code, $state_code = null)
    {
        $api = new ApiAqi;
        log::add('airquality', 'debug', 'Get new Coordinate Ajax for config -By City- or -Follow Me-');
        return $api->callApiGeoLoc($city, $country_code, $state_code = null);
    }


    /**
     * Utlise en ajax pour mode follow me 
     */
    public static function setNewGeoloc($longitude, $latitude)
    {
        log::add('airquality', 'debug', 'Save latitude et longitude en config generale pour mode Follow Me');
        config::save('DynLatitude', $latitude, 'airquality');
        config::save('DynLongitude', $longitude, 'airquality');
        return [$latitude, $longitude];
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


    /**
     * Création tableau associatif avec data courante pour comparaison / nouvelles valeurs 
     * 
     *  */
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
     * Appel API AQI live + UV + Visibility + Update des Commands 
     */
    public function updatePollution()
    {

        // Verifier la date de dernier maj pour faire ou pas maj
        $cmToTest = $this->getCmd(null, 'co');
        $iMinute = 6;
        if (is_object($cmToTest)) {
            $iMinute = $this->getIntervalLastRefresh($cmToTest);
        }
        if ($iMinute > 2) {
            log::add('airquality', 'debug', 'Interval OK Refresh > 2 min Pollution latest');
            $paramAlertAqi = $this->getParamAlertAqi();
            $oldData = $this->getCurrentValues();
            $data = $this->getApiData('getAQI');
            $this->checkAndUpdateCmd('aqi', $data->main->aqi);
            $this->checkAndUpdateCmd('no2', $data->components->no2);
            $this->checkAndUpdateCmd('no', $data->components->no);
            $this->checkAndUpdateCmd('co', $data->components->co);
            $this->checkAndUpdateCmd('o3', $data->components->o3);
            $this->checkAndUpdateCmd('so2', $data->components->so2);
            $this->checkAndUpdateCmd('nh3', $data->components->nh3);
            $this->checkAndUpdateCmd('pm25', $data->components->pm2_5);
            $this->checkAndUpdateCmd('pm10', $data->components->pm10);
            $dataOneCall = $this->getApiData('getOneCallAQI');
            $this->checkAndUpdateCmd('uv', $dataOneCall->uvi);
            $this->checkAndUpdateCmd('visibility', $dataOneCall->visibility);
         
            $display = new DisplayInfo;
            $messagesPollution = $display->getAllMessagesPollution($oldData, $data, $dataOneCall, $paramAlertAqi, $this->getCurrentCityName());
            $this->checkAndUpdateCmd('messagePollution', ($messagesPollution[0]));
            $telegramMess = !empty($messagesPollution[0]) ? $messagesPollution[1] : '';
            $this->checkAndUpdateCmd('telegramPollution', $telegramMess);
            $smsMess = !empty($messagesPollution[0]) ? $messagesPollution[2] : '';
            $this->checkAndUpdateCmd('smsPollution',  $smsMess);
            $markdownMessage = !empty($messagesPollution[0]) ? $messagesPollution[3] : '';
            $this->checkAndUpdateCmd('markdownPollution', $markdownMessage);
            $this->refreshWidget();
            if (!empty($messagesPollution[0])) {
                $this->setMinutedAction('alertAqiCronTwoMin', 2);
            }
        } else {
            log::add('airquality', 'debug', 'Dernier AQI latest Update < 2 min, veuiller patienter svp');
        }
    }

    /**
     * Appel API Forecast AQI + Update des Commands 
     */
    public function updateForecastAQI()
    {
        // Verifier la date de dernier maj pour faire ou pas maj
        $interval = $this->getIntervalLastRefresh($this->getCmd(null, 'aqi_max'));
        log::add('airquality', 'debug', 'Refresh Forecast AQI : Interval = ' . $interval . ' min');
        if ($interval > 10) {
            $forecastRaw =  $this->getApiData('getForecastAQI');
            $forecast = $forecastRaw[0];
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
        } else {
            log::add('airquality', 'debug', 'Dernier Forecast AQI Update < 10 min, veuillez patienter svp');
        }
    }


    /**
     * Pour supprimer le message de warning et refresh le widget
     */
    public function deleteAlertAqi()
    {
        $this->checkAndUpdateCmd('messagePollution', '');
        $this->checkAndUpdateCmd('telegramPollution', '');
        $this->checkAndUpdateCmd('smsPollution', '');
        $this->checkAndUpdateCmd('markdownPollution', '');
        $this->refreshWidget();
    }
}

class airqualityCmd extends cmd
{

    public static $_widgetPossibility = array('custom' => false);

    public function execute($_options = [])
    {
        if ($this->getLogicalId() == 'refresh') {
            log::add('airquality', 'debug', '---------------------------------------------------');
            log::add('airquality', 'debug', 'Refresh equipement ' . $this->getEqLogic()->getHumanName());
            $this->getEqLogic()->updatePollution();
        }

        if ($this->getLogicalId() == 'refresh_forecast') {
            log::add('airquality', 'debug', '---------------------------------------------------');
            log::add('airquality', 'debug', 'Refresh Forecast AQI equipement ' . $this->getEqLogic()->getHumanName());
            $this->getEqLogic()->updateForecastAQI();
        }

        if ($this->getLogicalId() == 'refresh_alert_aqi') {
            $this->getEqLogic()->deleteAlertAqi();
            log::add('airquality', 'debug', 'Cron Action : Delete/Refresh Alert AQI for equipement ' . $this->getEqLogic()->getHumanName());
            log::add('airquality', 'debug', '---------------------------------------------------');
        }
    }
}
