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
        // if ($this->getIsEnable()) {
        //     $cmd = $this->getCmd(null, 'refresh');
        //     if (is_object($cmd)) {
        //         $cmd->execCmd();
        //     }
        // }
    }


    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave()
    {
        if ($this->getConfiguration('displayMode') == 'full_display') {
            $this->setDisplay("width", "250px");
            $this->setDisplay("height", "205px");
        } else if ($this->getConfiguration('displayMode') == 'min_display') {
            $this->setDisplay("width", "270px");
            $this->setDisplay("height", "455px");
        }
    }


    public function postUpdate()
    {
        if ($this->getConfiguration('elements') == 'polution') {
            $setup = [
                ['name' => 'aqi', 'title' => 'AQI', 'unit' => '', 'subType'=>'numeric', 'order' => 1],
                ['name' => 'no2', 'title' => 'NO²', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 6],
                ['name' => 'no', 'title' => 'NO', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 4],
                ['name' => 'co', 'title' => 'CO', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 7],
                ['name' => 'o3', 'title' => 'O³', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 3],
                ['name' => 'so2', 'title' => 'SO²', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 8],
                ['name' => 'nh3', 'title' => 'NH³', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 9],
                ['name' => 'pm10', 'title' => 'PM 10', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 2],
                ['name' => 'pm25', 'title' => 'PM 2.5', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 5],
                ['name' => 'visibility', 'title' => 'Visibilité', 'unit' => 'm', 'subType'=>'numeric', 'order' => 10],
                ['name' => 'uv', 'title' => 'Indice UV', 'unit' => 'μg/m3', 'subType'=>'numeric', 'order' => 11],

            ];
        }

        if ($this->getConfiguration('elements') == 'pollen') {
            $setup = [
                ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType'=>'numeric', 'order' => 1],
                ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 2],
                ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 3],
                ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType'=>'string' ,'order' => 4],
                ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType'=>'string' ,'order' => 5],
                ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType'=>'string' ,'order' => 20],
                ['name' => 'grass', 'title' => 'Herbes/Graminées', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 19],
                ['name' => 'auln', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 6],
                ['name' => 'boul', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 7],
                ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 8],
                ['name' => 'orme', 'title' => 'Orme', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 9],
                ['name' => 'noisetier', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 10],
                ['name' => 'chene', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 11],
                ['name' => 'pin', 'title' => 'Pin', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 12],
                ['name' => 'platane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 13],
                ['name' => 'peuplier', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 14],
                ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 15],
                ['name' => 'armoise', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 16],
                ['name' => 'ortie', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 17],
                ['name' => 'ambroisie', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 18],
                ['name' => 'autres', 'title' => 'Autres', 'unit' => 'part/m3', 'subType'=>'numeric' ,'order' => 22],
                ['name' => 'updatedAt', 'title' => 'Update at', 'unit' => '', 'subType'=>'string' ,'order' => 21],
            ];

            // Voir si autre option a remover todo 
            //  $allCmd = $this->getCmd('info');
            //  foreach ($allCmd as $key => $oldCmd) {
            //      if(in_array($oldCmd, $setup)){

            //      }
            //  }
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

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new airqualityCmd();
            $refresh->setName(__('Rafraichir', __FILE__));
        }
        $refresh->setEqLogic_id($this->getId());
        $refresh->setLogicalId('refresh');
        $refresh->setType('action');
        $refresh->setOrder(0);
        $refresh->setTemplate('dashboard', 'tile');
        $refresh->setSubType('other');
        $refresh->save();
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
            if ($this->getConfiguration($cmd->getLogicalId(), 0) == 1 ){
          
                // Preparation des valeurs à remplacer 
                $nameCmd = $cmd->getLogicalId();
                $nameIcon = '#icone_' . $nameCmd . '#';
                $commandValue =  '#' . $nameCmd . '#';
                $commandNameId =  '#' . $nameCmd . 'id#';
                $commandName = '#'.$nameCmd.'_name#';
                $info = '#' . $nameCmd . 'info#';
                // $nom = '#'.$nameCmd.'#';
            
                // Commande/Element  à afficher et remplacer 
                $element = $this->getCmd(null, $nameCmd);            
            
                if (is_object($element)) {

                    if ( $this->getConfiguration('elements') == 'polution'){
                        $icone = new IconesAqi;
                        $elementTemplateMini = getTemplate('core', $version, 'element.mini', 'airquality');
                        $unitreplace['#unity#'] = ($cmd->getLogicalId() != 'aqi') ? 'μg/m³' : '';
                    } else {
                        $icone = new IconesPollen;
                        $elementTemplateMini = getTemplate('core', $version, 'elementPollen.mini', 'airquality');
                        $unitreplace['#unity#'] = 'particules/m³';
                    }
                   
                    // Pour Affichage central 
                    if ($nameCmd == 'aqi') {
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
                        // $replace[$commandNameId] = $element->getId();
                    } 
                    
                    else  if ($nameCmd == 'uv' || $nameCmd == 'visibility'  ) {
                        $replace[$commandValue] = $element->execCmd();
                        $replace[$commandNameId] = $element->getId();
                        $replace[$commandName] =  $element->getName();
                        $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                        $replace[$nameIcon] = $newIcon;
                    } 

                    else  if ( $nameCmd == 'updatedAt'){
                        // message::add('debug', $element->execCmd() );
                        $replace['#updatedAt#'] = $element->execCmd() ;
                   }
                    else {
                 
                    // Incrémentation Compteur de pollens si actif 
                    $activePollen = ( $element->execCmd() > 0 ) ? $activePollen + 1 : $activePollen;    
     
                    // Multi Template 
                    $newIcon = $icone->getIcon($nameCmd, $element->execCmd(), $element->getId());
                    $unitreplace['#icone#'] = $newIcon;   
                    $unitreplace['#id#'] = $this->getId();
                    $unitreplace['#value#'] = ($this->getConfiguration('elements') == 'polution') ?  self::formatValueForDisplay($element->execCmd()) : $element->execCmd() ;
                    $unitreplace['#name#'] = $cmd->getLogicalId();
                    $unitreplace['#display-name#'] = $cmd->getName();
                    $unitreplace['#cmdid#'] = $cmd->getId();
                    $unitreplace['#history#'] = 'history cursor';
                    // Todo
                    $unitreplace['#mini-label#'] = '';
                
                    $replace[$commandNameId] = $element->getId();  

                    $unitreplace['#info-modalcmd#'] = 'info-modal'.$element->getId();
                    // affichage liste pollens par categorie
                    $unitreplace['#list-info#'] =  ( $nameCmd == 'autres') ?  'class="tooltips" title="'.self::getListPollen($nameCmd).'"' : '';
               
                    // Historique
                    $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 240 . ' hour'));
                    $historyStatistique = $element->getStatistique($startHist, date('Y-m-d H:i:s'));
                    $unitreplace['#minHistoryValue#'] = self::formatValueForDisplay($historyStatistique['min'], 'short');
                    $unitreplace['#maxHistoryValue#'] = self::formatValueForDisplay($historyStatistique['max'], 'short');
                    $unitreplace['#averageHistoryValue#'] = self::formatValueForDisplay($historyStatistique['avg'], 'short');
                    // Tendance
                    $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . 10 . ' hour'));
                    $tendance = $element->getTendance($startHist, date('Y-m-d H:i:s'));
                    if ($tendance > config::byKey('historyCalculTendanceThresholddMax')) {
                        $unitreplace['#tendance#'] = 'fas fa-arrow-up';
                    } else if ($tendance < config::byKey('historyCalculTendanceThresholddMin')) {
                        $unitreplace['#tendance#'] = 'fas fa-arrow-down';
                    } else {
                        $unitreplace['#tendance#'] = 'fas fa-minus';
                    }

                    // Remplacement multi slider  unitreplace
                    $slideMini =  template_replace($unitreplace, $elementTemplateMini);
                    
                    // Enregistrement dans un tableau de tous les slides
                    $tab[] = $slideMini;
                    }
                }
            }
        }  // Fin foreach 


        // Choix du layer a finir
        if ($this->getConfiguration('elements') == 'polution') {
            $component = new ComponentAqi($tab, $this->getId());


        } else {
            // Pollen 
            $component = new ComponentAqi($tab, $this->getId(), 2);
        }

        // log::add('airquality', 'debug', json_encode($res));
        $replace['#mini_slide#'] =  $component->getLayer();



        // Affichage direct du forecast sans passer par l'enregistrement/création d'une commande 
        $forecast = $this->getData('getForecast');
        message::add('debug', json_encode($forecast));

        // $replace['#labelday#'] = implode(',',$forecast['co']['day']);
        // $replace['#min#'] =  implode(',',$forecast['co']['min']);
        // $replace['#max#'] =  implode(',',$forecast['co']['max']);

        // message::add('debug', ($forecast[0]['co']['max']));

        // $replace['#labeldayo3#'] = implode(',',$forecast['o3']['day']);
        // $replace['#mino3#'] =  implode(',',$forecast['o3']['min']);
        // $replace['#maxo3#'] =  implode(',',$forecast['o3']['max']);

        // message::add('debug', ($forecast[1]['o3']['max']));

        // message::add("debug", $forecast[0]);
        //  message::add("debug", $forecast[1]);

        // message::add('debug', ($forecast[0]['o3']['max']));
        foreach ($forecast as $nameElement => $elementsArray) {
              message::add("debug", $elementsArray);
              message::add("debug", $nameElement);

            $indexLabel = '#labelday'.$nameElement.'#';
            $replace[$indexLabel] = implode(',',$elementsArray['day']);
            $indexMin = '#min'.$nameElement.'#';
            $replace[$indexMin] =  implode(',',$elementsArray['min']);
            $indexMax = '#max'.$nameElement.'#';
            $replace[$indexMax] =  implode(',',$elementsArray['max']);
        }


      
        // message::add('debug', implode(',',$forecast[0]['co']['min']));

        $replace['#index_name#'] = __('Indice',__FILE__);
        $replace['#active_pollen_label#'] = __('Pollens actifs',__FILE__);
        $replace['#activePollen#'] = $activePollen;

        // Carousel 
        if ($this->getConfiguration('animation_aqi') == 'disable_anim') {
            $replace['#animation#'] = 'disabled';
            $replace['#classCaroussel#'] = 'data-interval="false"';
        } else {
            $replace['#animation#'] = 'active';
            $replace['#classCaroussel#'] = '';
        }

        // Command Refresh 
        $refresh = $this->getCmd(null, 'refresh');
        $replace['#refresh#'] = is_object($refresh) ? $refresh->getId() : '';

     


        if ($version == 'mobile') {
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality.mobile.min', __CLASS__)));
        }
        else {

           if ( $this->getConfiguration('elements') == 'polution'){
                    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'airquality.min', __CLASS__)));
           } else {
                    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'pollen.min', __CLASS__)));
           }
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


                    // $forecast =  $eqlogic->getData('getForecast');
                    // $eqlogic->checkAndUpdateCmd('no2_min', $forecast['no2']['min'][0]);
                    // $eqlogic->checkAndUpdateCmd('no2_max',  $forecast['no2']['max'][0]);
                    // $eqlogic->checkAndUpdateCmd('day',  $forecast['no2']['day'][0]);

                    // $eqlogic->checkAndUpdateCmd('no2_min+1',  $forecast['no2']['min'][1]);
                    // $eqlogic->checkAndUpdateCmd('no2_max+1', $forecast['no2']['max'][1]);
                    // $eqlogic->checkAndUpdateCmd('day+1', $forecast['no2']['day'][1]);

                    // $eqlogic->checkAndUpdateCmd('no2_min+2',  $forecast['no2']['min'][2]);
                    // $eqlogic->checkAndUpdateCmd('no2_max+2', $forecast['no2']['max'][2]);
                    // $eqlogic->checkAndUpdateCmd('day+2', $forecast['no2']['day'][2]);

                    // $eqlogic->checkAndUpdateCmd('no2_min+3',  $forecast['no2']['min'][3]);
                    // $eqlogic->checkAndUpdateCmd('no2_max+3', $forecast['no2']['max'][3]);
                    // $eqlogic->checkAndUpdateCmd('day+3', $forecast['no2']['day'][3]);

                    // $eqlogic->checkAndUpdateCmd('no2_min+4',  $forecast['no2']['min'][4]);
                    // $eqlogic->checkAndUpdateCmd('no2_max+4', $forecast['no2']['max'][4]);
                    // $eqlogic->checkAndUpdateCmd('day+4', $forecast['no2']['day'][4]);


                    $eqlogic->refreshWidget();
                    break;
                }

                if ($eqlogic->getConfiguration('elements') == 'pollen') {
                    $dataAll = $eqlogic->getData('getAmbee');
                    $dataPollen = $dataAll->data;
                    $eqlogic->checkAndUpdateCmd('grass', $dataPollen[0]->Species->Grass->{"Grass / Poaceae"});
                    $eqlogic->checkAndUpdateCmd('auln', $dataPollen[0]->Species->Tree->Alder);
                    $eqlogic->checkAndUpdateCmd('boul', $dataPollen[0]->Species->Tree->Birch);
                    $eqlogic->checkAndUpdateCmd('grass_pollen', $dataPollen[0]->Count->grass_pollen);
                    $eqlogic->checkAndUpdateCmd('tree_pollen', $dataPollen[0]->Count->tree_pollen);
                    $eqlogic->checkAndUpdateCmd('weed_pollen', $dataPollen[0]->Count->weed_pollen);
                    $eqlogic->checkAndUpdateCmd('weed_risk', $dataPollen[0]->Risk->weed_pollen);
                    $eqlogic->checkAndUpdateCmd('grass_risk', $dataPollen[0]->Risk->grass_pollen);
                    $eqlogic->checkAndUpdateCmd('tree_risk', $dataPollen[0]->Risk->tree_pollen);
                    $eqlogic->checkAndUpdateCmd('cypress', $dataPollen[0]->Species->Tree->Cypress);
                    $eqlogic->checkAndUpdateCmd('orme', $dataPollen[0]->Species->Tree->Elm);
                    $eqlogic->checkAndUpdateCmd('noisetier', $dataPollen[0]->Species->Tree->Hazel);
                    $eqlogic->checkAndUpdateCmd('chene', $dataPollen[0]->Species->Tree->Oak);
                    $eqlogic->checkAndUpdateCmd('pin', $dataPollen[0]->Species->Tree->Pine);
                    $eqlogic->checkAndUpdateCmd('platane', $dataPollen[0]->Species->Tree->Plane);
                    $eqlogic->checkAndUpdateCmd('peuplier', $dataPollen[0]->Species->Tree->{"Poplar / Cottonwood"});
                    $eqlogic->checkAndUpdateCmd('chenopod', $dataPollen[0]->Species->Weed->Chenopod);
                    $eqlogic->checkAndUpdateCmd('armoise', $dataPollen[0]->Species->Weed->Mugwort);
                    $eqlogic->checkAndUpdateCmd('ortie', $dataPollen[0]->Species->Weed->Nettle);
                    $eqlogic->checkAndUpdateCmd('ambroisie', $dataPollen[0]->Species->Weed->Ragweed);
                    $eqlogic->checkAndUpdateCmd('autres', $dataPollen[0]->Species->Others);
                    $eqlogic->checkAndUpdateCmd('updatedAt',$dataPollen[0]->updatedAt);
                    $eqlogic->refreshWidget();
                    break;
                }
        }
    }
}
