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
     * @return array  The response with errors and responsecodeHttp 
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
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "x-api-key:" . $apiKey
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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

        if ($response[1]) {
            throw new Exception(__('Impossible de récupérer les coordonnées de cette ville :' . json_encode($response[1]), __FILE__));
        } else {
            $coordinates = json_decode($response[0]);
            return  [$coordinates[0]->lat, $coordinates[0]->lon];
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
                $city = $data[0]->name;
                return  $city;
            }
        } else {
            throw new Exception(__('Les coordonnées sont vides', __FILE__));
            return null;
        }
    }

    /**
     * Appel API AQI Pollution Live
     */
    public function getAqi($latitude, $longitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        if ($response[1]) {
            throw new Exception('Pas de données de pollution pour l\'instant : ' . $response[1]. 'HTTP responseCode =' .$response[2]);
        } else {
            $data = json_decode($response[0]);
            $result = $data->list[0];
            if ($result == [] || $result == null) {
                throw new Exception('Pas de données de pollution avec ces coordonnées');
            } else {
                log::add('airquality', 'debug', 'Data AQI live : '. json_encode($data->list[0]));
                return $data->list[0];
            }
        }
    }

    /**
     * Appel API OneCall OpenWheather UV et Visibilité
     */
    public function getOneCallApi($latitude, $longitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        $data = json_decode($response[0]);

        if ($response[1] != null) {
            throw new Exception('Pas de données  UV et visibilité pour l\'instant : ' . $response[1]. ' - HttpResponsecode : ' . $response[2]);
        } else {
            if ($data == [] || $data == null) {
                throw new Exception('Pas de données UV et visibilité avec ces coordonnées');
            } else {
                log::add('airquality', 'debug', 'Données OneCallapi : '. json_encode($data->current));
                return $data->current;
            }
        }
    }

    /**
     * Appel AQI Forecast OpenWheather Pollution
     */
    public function callApiForecastAQI($latitude = null, $longitude = null)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');
        $data = json_decode($response[0]);
        if ($response[1] != '') {
              throw new Exception('Pas de données Forecast AQI pour l\'instant'. $response[1] . ' Http code : ' . $response[2]);
        }
        else {
            if ($data == [] || $data == null) {
                throw new Exception('AQI Forecast','Pas de données avec ces coordonnées');
            } else {
                if (property_exists($data, 'list')){
                    log::add('airquality', 'debug', 'Data Aqi Forecast : '. json_encode($data->list));
                    return $data->list;
                }
            }
        }
    }

    /**
     * Appel Pollen Live
     */
    public function getAmbee($latitude = null, $longitude = null)
    {
        // Param auto pour test clef avant insertion des params
        if ($latitude === null && $longitude === null) {
            $latitude = 50 && $longitude = 50;
        }
        $url =  "https://api.ambeedata.com/latest/pollen/by-lat-lng?lat=" . trim(round($latitude, 4)) . "&lng=" . trim(round($longitude, 4));
        $response = $this->curlApi($url, $this->ambeeApiKey, 'ambee');

            if ( $response[2] == '429'){
                message::add('Ambee','Quota journalier données pollen dépassé');
            } else  if ($response[2] == '401'){
                throw new Exception('Clef Api non active');
            } else if( $response[2] == '200'){
                $data = json_decode($response[0]);
                if (property_exists($data, 'data')){
                    log::add('airquality', 'debug', 'Data Ambee Live : '. json_encode($data));
                    return $data;
                }
            } else {
                    throw new Exception(__('Pas de données de Polen - Http code : ' . $response[2], __FILE__));
            } 
         
       
    }




    /**
     * Appel Forecast Pollen 
     */
    public function callApiForecastPollen($latitude = null, $longitude = null)
    {
        $url = "https://api.ambeedata.com/forecast/pollen/by-lat-lng?lat=" . trim(round($latitude, 4)) . "&lng=" . trim(round($longitude, 4));
        $response = $this->curlApi($url, $this->ambeeApiKey ,'ambee');

        $data = json_decode($response[0]);
        if ($response[1] != '') {
            throw new Exception('Pas de données Forecast Pollen pour l\'instant : ' . $response[1]);
        } 
        else if ($response[2] == '429'){
            message::add('Ambee','Quota journalier données pollen dépassé pour le Forecast');
        }
        else {
            if ($data == [] || $data == null) {
                throw new Exception('Pas de données Forecast Pollen : ' . $data->message);
            } else {
                log::add('airquality', 'debug', 'Data Pollen Forecast : '. json_encode($data->data));
                return $data->data;
            }
        }
        // $response = file_get_contents(__DIR__. '../pollen.json');
        // return  json_decode($response);
        // return;
    }


    /**
     * Retourne Forecast parsé min/max/jour AQI 
     */
    public function getForecast($latitude = null, $longitude = null)
    {
        $polluants = ['co', 'no', 'o3', 'no2', 'so2', 'nh3', 'aqi', 'pm10', 'pm2_5'];
        $dataList = $this->callApiForecastAQI($latitude, $longitude);

        foreach ($polluants as $polluant) {
            $newTabDay = $this->parseData($dataList, $polluant);
            $minMaxTab[$polluant] = $this->pushMinMaxByDay($newTabDay, $polluant);
        }
        return $minMaxTab;
    }

    /**
     * Retourne Forecast parsé min/max/jour Pollen 
     */
    public function getForecastPollen($latitude = null, $longitude = null)
    {
        $pollens = [
            "Poaceae", "Alder", "Birch", "Cypress", "Elm", "Hazel", "Oak", "Pine", "Plane", "Poplar",
            "Chenopod", "Mugwort", "Nettle", "Ragweed", "Others"
        ];
        $dataList = $this->callApiForecastPollen($latitude, $longitude);
        log::add('airquality', 'debug', json_encode($dataList));
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
}
