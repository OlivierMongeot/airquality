<?php

class ApiAqi
{
    /**
     *  OpenWeather Api Key
     */
    private $apiKey;
    /**
     * Ambee Api Key
     */
    private $ambeeApiKey;


    public function __construct()
    {
        $this->apiKey = trim(config::byKey('apikey', 'airquality'));
        $this->ambeeApiKey = trim(config::byKey('apikeyAmbee', 'airquality'));
    }

    /**
     * Methode générique d'appel API avec curl 
     * @param string $url  The url for connect the API
     * @param string $apiKey  The apikey
     * @param string $apiName  The API Name : 'Openwheather' or 'Ambee'
     * @return array The response with maybe errors and responsecodeHttp 
     */
    private function curlApi(string $url, string $apiKey, string $apiName = 'openwheather')
    {
        $curl = curl_init();
        if ($apiName == 'openwheather'){
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER =>["Accept: application/json", "x-api-key:" . $apiKey ]
            ]);
        } else {
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [ "Content-type: application/json", "x-api-key:" . $apiKey ]
                ]);
        }
       
        $response = curl_exec($curl);
        $curlInfo = curl_getinfo($curl);
        $http_response_code = $curlInfo['http_code'];
    
        if ($http_response_code !== 200) {
          
            log::add('airquality', 'debug', 'Info Curl httpResponse != 200 : ' . json_encode($curlInfo) . ' - Url : ' . json_encode($url));
        }
        $error = curl_error($curl);
        if ($error != '') {
            log::add('airquality', 'debug', 'Problem with API : ' . json_encode($error));
        }
        curl_close($curl);
        return [$response, $error, $http_response_code];
    }



    /**
     * Retourne Longitude et latitude avec la ville et le code pays
     **/
    public function callApiGeoLoc($city, $country_code, $state_code = null)
    {
        $url = "http://api.openweathermap.org/geo/1.0/direct?q=" . $city . "," . $country_code . "," . $state_code . "&limit=1";
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        $coordinates = json_decode($response[0]);
        if ($response[1]) {
            return (__('Impossible de récupérer les coordonnées de cette ville' , __FILE__));
        } 
        if (!isset($coordinates[0]->name) ) {
          
            return [0,0];
        } 
        else {
            if (isset($coordinates[0]->lat) && isset($coordinates[0]->lon)){
                return  [$coordinates[0]->lon, $coordinates[0]->lat];
            } else {
                return [0,0];
            }
           
        }
    }

    
    /**
     * Recupère nom de la ville avec la latitude et longitude 
     * */
    public function callApiReverseGeoLoc($longitude, $latitude)
    {
        if ($longitude != '' && $latitude != '') {
            $url = "http://api.openweathermap.org/geo/1.0/reverse?lat=" . $latitude . "&lon=" . $longitude;
            $response = $this->curlApi($url, $this->apiKey ,'openwheather');

            if (empty(json_decode($response[0]))) {
                    return __("Pas de lieu trouvé par l'API Reverse Geoloc avec ces coordonnées", __FILE__);
            }
            else {
                
                $data = json_decode($response[0]);
                log::add('airquality', 'debug', 'Ville récupéré par l\'API reverse geoloc: ' . $data[0]->name);
                return  $data[0]->name;
            }
        } else {
            return (__('Les coordonnées sont vides', __FILE__));
          
        }
    }

    /**
     * Appel API AQI Pollution Live
     */
    public function getAQI($longitude, $latitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        if ($response[1]) {
            throw new Exception('No Pollution data yet : ' . $response[1]. 'HTTP responseCode =' .$response[2]);
        } else {
            $data = json_decode($response[0]);
            $result = $data->list[0];
            if ($result == [] || $result == null) {
                throw new Exception('No pollution data with these coordinates');
            } else {
                log::add('airquality', 'debug', 'Data AQI latest : '. json_encode($data->list[0]));
                return $data->list[0];
            }
        }
    }

    /**
     * Appel API OneCall OpenWheather UV et Visibilité
     */
     public function getOneCallAQI($longitude, $latitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        $data = json_decode($response[0]);

        if ($response[1] != null) {
            throw new Exception('No UV data and visibility at this time : ' . $response[1]. ' - HttpResponsecode : ' . $response[2]);
        } else {
            if ($data == [] || $data == null) {
                throw new Exception('No UV data and visibility with these coordinates');
            } else {
                log::add('airquality', 'debug', 'Data OneCallapi : '. json_encode($data->current));
                return $data->current;
            }
        }
    }

/**
     * Retourne Forecast parsé min/max/jour AQI 
     */
    public function getForecastAQI($longitude = null, $latitude = null)
    {
        $polluants = ['co', 'no', 'o3', 'no2', 'so2', 'nh3', 'aqi', 'pm10', 'pm2_5'];
        $dataList = $this->callApiForecastAQI($longitude,$latitude);

        foreach ($polluants as $polluant) {
            $newTabDay = $this->parseData($dataList, $polluant);
            $minMaxTab[$polluant] = $this->pushMinMaxByDay($newTabDay, $polluant);
        }
        return [$minMaxTab, $dataList];
    }

    /**
     * Retourne Forecast parsé min/max/jour Pollen 
     */
    public function getForecastPollen($longitude = null, $latitude = null)
    {
        $pollens = [
            "Poaceae", "Alder", "Birch", "Cypress", "Elm", "Hazel", "Oak", "Pine", "Plane", "Poplar",
            "Chenopod", "Mugwort", "Nettle", "Ragweed", "Others"
        ];
        log::add('airquality', 'debug', 'getForecastPollen Methode Start');
        $dataList = $this->callApiForecastPollen($longitude, $latitude);

        if (isset($dataList) && $dataList != []){
            foreach ($pollens as $pollen) {
                $newTabDay = $this->parseDataPollen($dataList, $pollen);
                $minMaxTab[$pollen] = $this->pushMinMaxByDay($newTabDay, $pollen);
            }
            return $minMaxTab;
        }
        else 
        {
            return [];
        }
    }



    /**
     * Appel AQI Forecast OpenWheather Pollution
     */
    public function callApiForecastAQI($longitude = null, $latitude = null)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        $data = json_decode($response[0]);
        if ($response[1] != '') {
              throw new Exception('No Forecast AQI data at this time : '. $response[1] . ' Http code : ' . $response[2]);
        }
        else {
            if ($data == [] || $data == null) {
                throw new Exception('AQI Forecast : No data with these coordinates');
            } else {
                if (property_exists($data, 'list')){
                    log::add('airquality', 'debug', 'Cell AQI forecast with Longitude: '. $longitude . ' & Latitude: '. $latitude);
                    log::add('airquality', 'debug', 'Data Aqi Forecast : '. json_encode($data->list));
                    return $data->list;
                }
            }
        }
    }



    /**
     * Appel Pollen latest GetAmbee
     */
    public function getAmbee($longitude = 7.7, $latitude = 48.5)
    {
        $longitude = (float)trim(round($longitude, 3));
        $latitude =  (float)trim(round($latitude, 3));
        log::add('airquality', 'debug', 'Call Pollen laltest For longitude: '.$longitude . ' / latitude: '.$latitude);
        $url = "https://api.ambeedata.com/latest/pollen/by-lat-lng?lat=".$latitude."&lng=".$longitude ;
        $response = $this->curlApi($url, $this->ambeeApiKey, 'ambee');

            if ( $response[2] == '429'){
                message::add('Ambee',__('Quota journalier données pollen dépassé',__FILE__));
                log::add('airquality', 'debug', 'Quota journalier données pollen dépassé');

            } else  if ($response[2] == '401'){
                throw new Exception('Api Key is not actived');

            } else if( $response[2] == '200'){
                $data = json_decode($response[0]);
                if (property_exists($data, 'data')){
                    log::add('airquality', 'debug', 'Pollen latest for Longitude: '. $longitude . ' & Latitude: '. $latitude);
                    log::add('airquality', 'debug', 'Data Ambee latest : '. json_encode($data));
                    return $data;
                }
            } else {
                    throw new Exception('No data pollen server response - Http code : ' . $response[2]);
            }        
    }



    /**
     * Appel Forecast Pollen Getambee
     */
    public function callApiForecastPollen($longitude , $latitude)
    {

        $longitude = (float)trim(round($longitude, 3));
        $latitude =  (float)trim(round($latitude, 3));
        log::add('airquality', 'debug', 'Call API Forecast Pollen for Longitude: '.$longitude . ' & Latitude: '. $latitude);
        $url = "https://api.ambeedata.com/forecast/pollen/by-lat-lng?lat=".$latitude."&lng=".$longitude;
        $response = $this->curlApi($url, $this->ambeeApiKey, 'ambee');
      
        if ($response[2] == '429') {
            message::add('Ambee', __('Quota journalier données pollen dépassé', __FILE__));
        } else if ($response[2] == '401') {
            message::add('Ambee', __('Clef API fournie non valide', __FILE__));
        } else if ($response[2] == '403') {
            message::add('Ambee', __('Clef API n\'a pas les bonnes permission', __FILE__));
        } else if ($response[2] == '404') {
            message::add('Ambee', __('La source demandé n\'existe pas', __FILE__));
        } else if ($response[2] == '200') {     
            $data = json_decode($response[0]);
            
            log::add('airquality', 'debug', 'Data Pollen Forecast : ' . json_encode($response));
            return $data->data;
        } else {
            throw new Exception('No data pollen response - Http code : ' . $response[2]);
        }
        // Test
        // $response = file_get_contents(dirname(__DIR__) . '/core/dataModel/pollen2f.json', 1);     
        // return json_decode($response);
    }


    


    /**
     * Return array with min max by day for an element 
     * This is data preparation for highCharts  
     */
    private function pushMinMaxByDay($newTabDay, $element)
    {
        $newTabDayElement = $newTabDay[$element];
        foreach ($newTabDayElement as $k => $value) {
            $forecast['day'][] = $k;
            $forecast['min'][] = min($value);
            $forecast['max'][] = max($value);
        }
        return $forecast;
    }


    /**
     * Combine les données en tableau avec index nommé par jour + recupération du nom du jour de la semaine avec le timestamp
     */
    private function parseDataPollen($response, $element)
    {
        $beginOfDay = strtotime("today", time());
        $day = 86399; // in seconds
        foreach ($response as $hourCast) {
            if ($hourCast->time >= $beginOfDay && $hourCast->time <= ($beginOfDay + 5 * $day)) {
                $weekday = date('N', ($hourCast->time + 100));
                $nameDay = new DisplayInfo();
                $dayName =  $nameDay->getNameDay($weekday);
                switch ($element) {
                    case "Poaceae":
                        $newTabAqiDay[$element][$dayName][] =  $hourCast->Species->Grass->{"Grass / Poaceae"};
                        break;
                    case "Poplar":
                        $newTabAqiDay[$element][$dayName][] = $hourCast->Species->Tree->{"Poplar / Cottonwood"};
                        break;
                    case "Alder":
                    case "Birch":
                    case "Cypress":
                    case "Elm":
                    case "Hazel":
                    case "Oak":
                    case "Pine":
                    case "Plane":
                        $newTabAqiDay[$element][$dayName][] = $hourCast->Species->Tree->$element;
                        break;
                    case "Chenopod":
                    case "Mugwort":
                    case "Nettle":
                    case "Ragweed":
                        $newTabAqiDay[$element][$dayName][] = $hourCast->Species->Weed->$element;
                        break;
                    case "Others":
                        $newTabAqiDay[$element][$dayName][] = $hourCast->Species->$element;
                        break;
                }
            }
        }
        return $newTabAqiDay;
    }


    /**
     * Combine les données sur 5 jours par jour + recupération du nom du jour de la semaine avec le timestamp
     */
    private function parseData($response, $component)
    {
        $beginOfDay = strtotime("today",  time());
        $day = 86399; //day in seconds
        foreach ($response as $hourCast) {
            if ($hourCast->dt >= $beginOfDay && $hourCast->dt <= ($beginOfDay + 5 * $day)) {
                $weekday = date('N', ($hourCast->dt + 100));
                $nameDay = new DisplayInfo();
                $dayName =  $nameDay->getNameDay($weekday);
                $newTabAqiDay[$component][$dayName][] = ($component == 'aqi') ?  $hourCast->main->aqi : $hourCast->components->$component;
            }
        }
        return $newTabAqiDay;
    }
 

    /**
     * Unuse 
     */
    public static function convertToPPM($microGramByM3, $molecule)
    {
        $molecularWeight = [
            'nh3' => 10.03, 'co' => 28.1, 'no2' => 46.01, 'o3' => 48, 'so2' => 64.06, 'no' => 31.01
        ];
        $ppm = 24.45 * ($microGramByM3 / 1000) / $molecularWeight[$molecule];
        return number_format((float)$ppm, 3, '.', '');
    }


    /**
     * Pour future version 
     */
    // public function makeMessageForecast($forecast){
    //     $message = '';
    //     // find the max of week foreach element 
    //     foreach ($forecast as $hourcast) {
         
    //         //build array with datetime for index
    //         $date = $hourcast->dt;
    //         $values['aqi'] = $hourcast->main->aqi;
    //         $values['pm25'] = $hourcast->components->pm2_5;  
    //         $values['pm10'] = $hourcast->components->pm10;
    //         $values['o3'] = $hourcast->components->o3;
    //         $values['so2'] = $hourcast->components->so2;
    //         $values['co'] = $hourcast->components->co;
    //         $values['no2'] = $hourcast->components->no2;
    //         $values['no'] = $hourcast->components->no;
    //         $values['nh3'] = $hourcast->components->nh3;
    //         $hourcastArray[$date] = $values;         
    //     }
    //     log::add('airquality', 'debug', json_encode($hourcastArray));
    // }
}
