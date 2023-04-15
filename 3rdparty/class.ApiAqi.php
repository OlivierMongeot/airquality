<?php

class ApiAqi
{
    /**
     *  OpenWeather Api Key
     */
    private $apiKey;


    public function __construct()
    {
        $this->apiKey = trim(config::byKey('apikey', 'airquality'));
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
        if ($apiName == 'openwheather') {
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ["Accept: application/json", "x-api-key:" . $apiKey]
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
                CURLOPT_HTTPHEADER => ["Content-type: application/json", "x-api-key:" . $apiKey]
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

        log::add('airquality', 'debug', 'callApiGeoLoc coordinates: ' . $response[0]);
        log::add('airquality', 'debug', 'callApiGeoLoc : ' . ($response[1]));
        log::add('airquality', 'debug', 'callApiGeoLoc Response : ' . ($response[2]));

        $coordinates = json_decode($response[0]);
        // if ($response[1]) {
        //     return (__('Impossible de récupérer les coordonnées de cette ville', __FILE__));
        // }
        if (!is_object($coordinates[0]) && !isset($coordinates[0]->name)) {
            return [0, 0];
        } else {
            if (isset($coordinates[0]->lat) && isset($coordinates[0]->lon)) {
                return  [$coordinates[0]->lon, $coordinates[0]->lat];
            } else {
                return [0, 0];
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
            $response = $this->curlApi($url, $this->apiKey, 'openwheather');

            log::add('airquality', 'debug', 'callApiReverseGeoLoc coordinates 0: ' . $response[0]);
            log::add('airquality', 'debug', 'callApiReverseGeoLoc 1: ' . ($response[1]));
            log::add('airquality', 'debug', 'callApiReverseGeoLoc 2: ' . ($response[2]));

            if (empty(json_decode($response[0]))) {
                return __("Pas de lieu trouvé par l'API Reverse Geoloc avec ces coordonnées", __FILE__);
            } else {

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

        log::add('airquality', 'debug', 'Response 0 getAQI : ' . ($response[0]));
        log::add('airquality', 'debug', 'Response 1 getAQI : ' . ($response[1]));
        log::add('airquality', 'debug', 'Response 2 OneCgetAQIallapi : ' . ($response[2]));

        if (json_decode($response[2]) !== 200) {
            $decodedResponse0 = json_decode($response[0]);
            message::add('One Call API Openweather', ($decodedResponse0->message));
            return [];
        }

        if ($response[1]) {
            // throw new Exception('No Pollution data yet : ' . $response[1]. 'HTTP responseCode =' .$response[2]);
            log::add('airquality', 'debug', 'No Pollution data yet : ' . $response[1]);
            message::add('Error HTTP code = ',  json_decode($response[2]));
            return [];
        } else {
            $data = json_decode($response[0]);
            $result = $data->list[0];
            if ($result == [] || $result == null) {
                message::add('airquality', 'No pollution data with these coordinates');
                log::add('airquality', 'debug', 'No pollution data with these coordinates');
                return [];
            } else {
                log::add('airquality', 'debug', 'Data AQI latest : ' . json_encode($data->list[0]));
                return $data->list[0];
            }
        }
    }

    /**
     * Appel API OneCall 2.5 OpenWheather pour UV et Visibilité
     */
    public function getOneCallAQI($longitude, $latitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi(
            $url,
            $this->apiKey,
            'openwheather'
        );
        $data = json_decode($response[0]);

        log::add('airquality', 'debug', 'Response 0 OneCallapi : ' . ($response[0]));
        log::add('airquality', 'debug', 'Response 1 OneCallapi : ' . ($response[1]));
        log::add('airquality', 'debug', 'Response 2 OneCallapi : ' . ($response[2]));

        if (json_decode($response[2]) !== 200) {
            $decodedResponse0 = json_decode($response[0]);
            message::add('One Call API Openweather', ($decodedResponse0->message));
            return [];
        }


        if ($response[1] != null) {
            message::add('Erreur', $response[1] . ' - HttpResponsecode : ' . $response[2]);
            return [];
        } else {
            if ($data == [] || $data == null) {
                message::add('Erreur', 'No UV data and visibility with these coordinates');
                return [];
            } else {
                log::add('airquality', 'debug', 'Data OneCallapi : ' . json_encode($data->current));
                return $data->current;
            }
        }
    }

    /**
     * Appel API OneCall 3.0 OpenWheather pour UV et Visibilité
     */
    public function getOneCallAQI30($longitude, $latitude)
    {
        $url = "http://api.openweathermap.org/data/3.0/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi(
            $url,
            $this->apiKey,
            'openwheather'
        );
        $data = json_decode($response[0]);

        log::add('airquality', 'debug', 'Response 0 OneCallapi : ' . ($response[0]));
        log::add('airquality', 'debug', 'Response 1 OneCallapi : ' . ($response[1]));
        log::add('airquality', 'debug', 'Response 2 OneCallapi : ' . ($response[2]));

        if (json_decode($response[2]) !== 200) {
            $decodedResponse0 = json_decode($response[0]);
            message::add('One Call API Openweather', ($decodedResponse0->message));
            return [];
        }


        if ($response[1] != null) {
            message::add('Erreur', $response[1] . ' - HttpResponsecode : ' . $response[2]);
            return [];
        } else {
            if ($data == [] || $data == null) {
                message::add('Erreur', 'No UV data and visibility with these coordinates');
                return [];
            } else {
                log::add('airquality', 'debug', 'Data OneCallapi : ' . json_encode($data->current));
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
        $dataList = $this->callApiForecastAQI($longitude, $latitude);

        if (empty($dataList)) {
            return [];
        }

        foreach ($polluants as $polluant) {
            $newTabDay = $this->parseData($dataList, $polluant);
            $minMaxTab[$polluant] = $this->pushMinMaxByDay($newTabDay, $polluant);
        }
        return [$minMaxTab, $dataList];
    }



    /**
     * Appel AQI Forecast OpenWheather Pollution
     */
    public function callApiForecastAQI($longitude = null, $latitude = null)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey, 'openwheather');

        log::add('airquality', 'debug', 'Response 0 callApiForecastAQI : ' . ($response[0]));
        log::add('airquality', 'debug', 'Response 1 callApiForecastAQI : ' . ($response[1]));
        log::add('airquality', 'debug', 'Response 2 callApiForecastAQI : ' . ($response[2]));

        if (json_decode($response[2]) !== 200) {
            $decodedResponse0 = json_decode($response[0]);
            message::add('Error API Forecast AQI Openweather', ($decodedResponse0->message));
            return [];
        }

        $data = json_decode($response[0]);
        if ($response[1] != '') {
            message::add('Error Api Forecast AQI', 'No Forecast AQI data available ');
        } else {
            if ($data == [] || $data == null) {
                message::add('Error Api Forecast AQI', 'No Forecast AQI data available ');
            } else {
                if (property_exists($data, 'list')) {
                    log::add('airquality', 'debug', 'Cell AQI forecast with Longitude: ' . $longitude . ' & Latitude: ' . $latitude);
                    log::add('airquality', 'debug', 'Data Aqi Forecast : ' . json_encode($data->list));
                    return $data->list;
                }
            }
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
