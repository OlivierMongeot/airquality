<?PHP

class ApiAqi
{
    /**
     *  OpenWeather API Key
     */
    private $apiKey;
    /**
     * Ambee Aip Key
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
     * Methode générique d'appel API
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
        $err = curl_error($curl);
        curl_close($curl);
        return [$response, $err];
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
     * Appel AQI 
     */
    public function getAqi($latitude, $longitude)
    {

        $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey);
        if ($response[1]) {
            throw new Exception('Pas de données de pollution pour l\'instant');
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
     * Appel OneCall OpenWheather AQI 
     */
    public function getOneCallApi($latitude, $longitude)
    {
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude . "&exclude=hourly,daily";
        $response = $this->curlApi($url, $this->apiKey);
        $data = json_decode($response[0]);

        if ($response[1] != null) {
            throw new Exception('Pas de données  UV et visibilité pour l\'instant');
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
        // message::add('debug','Gat aqi Method Aqi'. $this->ambeeApiKey);
        // message::add('debug','Latitude'. $latitude);
        // message::add( 'Debug','Longitude'. $longitude);
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
    function callApiForecastAQI($latitude = null, $longitude = null)
    {
        $url = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=" . $latitude . "&lon=" . $longitude;
        $response = $this->curlApi($url, $this->apiKey);
        $data = json_decode($response[0]);
        if ($response[1] != '') {
            echo ('Pas de données  Forecast pour l\'instant');
        } else {
            if ($data == [] || $data == null) {
                echo ('Pas de données Forecast avec ces coordonnées');
            } else {
                return $data->list;
            }
        }
    }


    public function getForecast($latitude = null, $longitude = null){

        $components = ['co','no','no2','o3','pm2_5','pm10'];

        $dataList = $this->callApiForecastAQI($latitude, $longitude);

        foreach ($components as $component) {
            $newTabDay = $this->parseData($dataList, $component);
            $minMaxTab[] = $this->pushMinMaxByDay($newTabDay, $component);
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
            $forecast[$element]['day'][] = $k;
            $forecast[$element]['min'][] = min($value);
            $forecast[$element]['max'][] = max($value);
        }
        return $forecast;
    }



    /**
     * Combine les données sur 5 jours par jour + recupération du nom du jour de la semaine avec le timestamp
     */
    private function parseData($response, $component)
    {
        $beginOfDay = strtotime("today",  time());
        $day = 86399; // in seconds
        foreach ($response as $hourCast) {

            if ($hourCast->dt >= $beginOfDay) {

                if (($hourCast->dt) <= ($beginOfDay + $day)) {
                    $weekday = date('N', ($hourCast->dt + 1000));
                    $dayName =  $this->getNameDay($weekday);
                    $newTabAqiDay[$component][$dayName][] = $hourCast->components->$component;
                }
                if (($hourCast->dt) > ($beginOfDay + $day) && ($hourCast->dt) <= ($beginOfDay + (2 * $day))) {
                    $weekday = date('N', ($hourCast->dt + 1000));
                    $dayName =  $this->getNameDay($weekday);
                    $newTabAqiDay[$component][$dayName][] = $hourCast->components->$component;
                }
                if (($hourCast->dt) > ($beginOfDay + 2 * $day) && ($hourCast->dt) <= ($beginOfDay + (3 * $day))) {
                    $weekday = date('N', ($hourCast->dt + 1000));
                    $dayName =  $this->getNameDay($weekday);
                    $newTabAqiDay[$component][$dayName][] = $hourCast->components->$component;
                }
                if (($hourCast->dt) > ($beginOfDay + 3 * $day) && ($hourCast->dt) <= ($beginOfDay + (4 * $day))) {
                    $weekday = date('N', ($hourCast->dt + 1000));
                    $dayName =  $this->getNameDay($weekday);
                    $newTabAqiDay[$component][$dayName][] = $hourCast->components->$component;
                }
                if (($hourCast->dt) > ($beginOfDay + 4 * $day) && ($hourCast->dt) <= ($beginOfDay + 5 * $day)) {
                    $weekday = date('N', ($hourCast->dt + 1000));
                    $dayName = $this->getNameDay($weekday);
                    $newTabAqiDay[$component][$dayName][] = $hourCast->components->$component;
                }
            }
        }
        return $newTabAqiDay;
    }

    private function getNameDay($numDay)
    {
        switch ($numDay) {
            case 1:
                return 'lundi';
            case 2:
                return 'mardi';
            case 3:
                return 'mercredi';
            case 4:
                return 'jeudi';
            case 5:
                return 'vendredi';
            case 6:
                return 'samedi';
            case 7:
                return 'dimanche';
        }
    }

    /**
     * Unuse
     */
    public function setDynGeoLoc($latitude, $longitude)
    {
        config::save('DynLatitude', $latitude, 'airquality');
        config::save('DynLongitude', $longitude, 'airquality');
        $resLat = trim(config::byKey('DynLatitude', 'airquality'));
        $resLong = trim(config::byKey('DynLongitude', 'airquality'));
        // return  self::callApiReverseGeoLoc($resLong,$resLat);
        return  $this->callApiReverseGeoLoc($latitude, $longitude);
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
