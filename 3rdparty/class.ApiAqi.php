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
     * Retourne Longitude et latitude avec la ville et le code pays
     **/
    public function callApiGeoLoc($city, $country_code, $state_code = null)
    {
        $url = "http://api.openweathermap.org/geo/1.0/direct?q=" . $city . "," . $country_code . "," . $state_code . "&limit=1";
        $response = $this->curlApi($url, $this->apiKey);

        if ($response[1]) {
            throw new Exception(__('Impossible de récupérer les coordonnées de cette ville :' . json_encode($response[1]), __FILE__));
        } else {
            $coordinates = json_decode($response[0]);
            return  [$coordinates[0]->lat, $coordinates[0]->lon];
        }
    }

    /**
     * Methode générique d'appel API avec curl 
     */
    private function curlApi(string $url, string $apiKey, int $timeOut = 30)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeOut,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "x-api-key:" . $apiKey
            ],
        ]);
       
        $response = curl_exec($curl);
        
        $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_response_code != 200){
             message::add('Error Http response ',  json_encode($http_response_code). json_encode($url));
             log::add('airquality','debug','Error Http Code : '.json_encode($http_response_code). ' - Url : '. json_encode($url));
        }
        $err = curl_error($curl);
        if ($err != '') {
            throw new Exception(__('Problème API : ' . json_encode($err), __FILE__));
        } 
        curl_close($curl);
        return [$response, $err, $http_response_code];
    }


    /**
     * Recupère nom de la ville avec la latitude et longitude 
     * */
    public function callApiReverseGeoLoc($longitude, $latitude)
    {
        if ($longitude != '' && $latitude != '') {

            $url = "http://api.openweathermap.org/geo/1.0/reverse?lat=" . $latitude . "&lon=" . $longitude;
            $response = $this->curlApi($url, $this->apiKey);

            if ($response[1]) {
                throw new Exception(__('Impossible de récupérer cette ville en reverse géolocalisation :' . json_encode($response[1]), __FILE__));
            } else {
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
     * Appel API AQI 
     */
    public function getAqi($latitude, $longitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey);
        if ($response[1]) {
            throw new Exception('Pas de données de pollution pour l\'instant : '.$response[1] );
        } else {
            $data = json_decode($response[0]);
            $result = $data->list[0];
            if ($result == [] || $result == null) {
                throw new Exception('Pas de données de pollution avec ces coordonnées');
            } else {
                return $data->list[0];
            }
        }
    }

    /**
     * Appel API OneCall OpenWheather AQI 
     */
    public function getOneCallApi($latitude, $longitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi($url, $this->apiKey);
        $data = json_decode($response[0]);

        if ($response[1] != null) {
            throw new Exception('Pas de données  UV et visibilité pour l\'instant : '. $response[1] );
        } else {
            if ($data == [] || $data == null) {
                throw new Exception('Pas de données UV et visibilité avec ces coordonnées');
            } else {
                return $data->current;
            }
        }
    }

    /**
     * Appel Pollen Ambee 
     */
    public function getAmbee($latitude = null, $longitude = null)
    {
        // Param auto pour test clef avant insertion des params
        if ($latitude === null && $longitude === null) {
            $latitude = 50 && $longitude = 50;
        }
        $url =  "https://api.ambeedata.com/latest/pollen/by-lat-lng?lat=".trim(round($latitude, 4))."&lng=".trim(round($longitude, 4));
        $response = $this->curlApi($url, $this->ambeeApiKey);
        
        if ($response[1]) {
            throw new Exception('Pas de données de Pollen pour l\'instant');
        } else {
            $data = json_decode($response[0]);
            $result = $data->data;
            if ($result == [] || $result == null) {
                throw new Exception('Pas de données de Polen avec ces coordonnées');
            } else {
                return $data;
            }
        }
    }

    /**
     * Appel Forecast OpenWheather AQI 
     */
    public function callApiForecastAQI($latitude = null, $longitude = null)
    { 
        message::add('debug','use  ApiForecastAQI');
        $url = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey);
        $data = json_decode($response[0]);
        if ($response[1] != '') {
            echo ('Pas de données Forecast AQI pour l\'instant : '. $response[1]);
        } else {
            if ($data == [] || $data == null) {
                echo ('Pas de données Forecast AQI avec ces coordonnées');
            } else {
                return $data->list;
            }
        }
    }


  /**
     * Appel Forecast Ambee Pollen AQI 
     */
    public function callApiForecastPollen($latitude = null, $longitude = null)
    { 
        message::add('debug','use  ApiForecastPollen');
        $url = "https://api.ambeedata.com/forecast/pollen/by-lat-lng?lat".trim(round($latitude, 4))."&ln=".trim(round($longitude, 4));
        $response = $this->curlApi($url, $this->apiKey);
        log::add('airquality','debug',json_encode($response));
        $data = json_decode($response[0]);
        if ($response[1] != '') {
            echo ('Pas de données Forecast Pollen pour l\'instant : ' . $response[1]);
        } else {
            if ($data == [] || $data == null) {
                echo ('Pas de données Forecast Pollen : ' . $data->message );
            } else {
                return $data->data;
            }
        }
    }



    public function getForecast($latitude = null, $longitude = null){

        $components = ['co','no','o3','no2','so2','nh3','aqi','pm10','pm2_5'];
        $dataList = $this->callApiForecastAQI($latitude, $longitude);

        foreach ($components as $component) {
            $newTabDay = $this->parseData($dataList, $component);
            $minMaxTab[$component] = $this->pushMinMaxByDay($newTabDay, $component);
        }
        return $minMaxTab;        
    }

    public function getForecastPollen($latitude = null, $longitude = null){

        $components = ["Poaceae","Alder","Birch","Cypress","Elm","Hazel","Oak","Pine","Plane", "Poplar", 
        "Chenopod", "Mugwort","Nettle","Ragweed","Others"];
        $dataList = $this->callApiForecastPollen($latitude, $longitude);
        log::add('airquality', 'debug', json_encode( $dataList));
        foreach ($components as $component) {
            $newTabDay = $this->parseDataPollen($dataList, $component);
            $minMaxTab[$component] = $this->pushMinMaxByDay($newTabDay, $component);
        }
        return $minMaxTab;        
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
     * Combine les données sur 5 jours par jour + recupération du nom du jour de la semaine avec le timestamp
     */
    private function parseDataPollen($response, $component)
    {
        $beginOfDay = strtotime("today", time());
        $day = 86399; // in seconds
        foreach ($response as $hourCast) {
            if ($hourCast->dt >= $beginOfDay && $hourCast->dt <= ($beginOfDay + 5 * $day)) {
                $weekday = date('N', ($hourCast->dt + 100));
                $dayName =  $this->getNameDay($weekday);
                
                switch ($component) {
                    case "Poaceae":
                        $newTabAqiDay[$component][$dayName][] = $hourCast->Species->Grass->{"Grass / Poaceae"};
                        break;

                    case "Poplar":   
                            $newTabAqiDay[$component][$dayName][] = $hourCast->Species->Tree->{"Poplar / Cottonwood"};
                        break;

                    case "Alder": case "Birch": case "Cypress": case "Elm":
                    case "Hazel": case "Oak": case "Pine": case "Plane":    
                        $newTabAqiDay[$component][$dayName][] = $hourCast->Species->Tree->{$component};
                        break;
                    
                    case "Chenopod": case "Mugwort": case "Nettle": case "Ragweed":
                        $newTabAqiDay[$component][$dayName][] = $hourCast->Species->Weed->{$component};
                        break;
                    
                    case "Others":
                        $newTabAqiDay[$component][$dayName][] = $hourCast->Species->Others;
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
        $day = 86399; // in seconds
        foreach ($response as $hourCast) {
            if ($hourCast->dt >= $beginOfDay && $hourCast->dt <= ($beginOfDay + 5 * $day)) {
                $weekday = date('N', ($hourCast->dt + 100));
                $dayName =  $this->getNameDay($weekday);
                $newTabAqiDay[$component][$dayName][] = ($component == 'aqi') ?  $hourCast->main->aqi : $hourCast->components->$component;
            }
        }
        return $newTabAqiDay;
    }


    private function getNameDay($numDay)
    {
        switch ($numDay) {
            case 1:
                return __('Lundi',__FILE__);
            case 2:
                return __('Mardi',__FILE__);
            case 3:
                return __('Mercredi',__FILE__);
            case 4:
                return __('Jeudi',__FILE__);
            case 5:
                return  __('Vendredi',__FILE__);
            case 6:
                return  __('Samedi',__FILE__);
            case 7:
                return __('Dimanche',__FILE__);
        }
    }

    /**
     * todo
     */
    public function setDynGeoLoc($latitude, $longitude)
    {
        config::save('DynLatitude', $latitude, 'airquality');
        config::save('DynLongitude', $longitude, 'airquality');
        // $resLat = trim(config::byKey('DynLatitude', 'airquality'));
        // $resLong = trim(config::byKey('DynLongitude', 'airquality'));
        // return  self::callApiReverseGeoLoc($resLong,$resLat);
        return  $this->callApiReverseGeoLoc($latitude, $longitude);
    }

    /**
     * todo 
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
