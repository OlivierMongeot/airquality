<?PHP

class ApiAqi
{

    private $apiKey;

    public function __construct()
    {
        $this->apiKey = trim(config::byKey('apikey', 'airquality'));
    }

    //  Retourne Longitude et latitude avec la ville et le pays
    public function callApiGeoLoc($city, $country_code, $state_code = null)
    {
        // message::add('debug','aqi callApiGeoLoc');
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://api.openweathermap.org/geo/1.0/direct?q=" . $city . "," . $country_code . "," . $state_code . "&limit=1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "x-api-key:".$this->apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            throw new Exception(__('Impossible de récupérer les coordonnées de cette ville :'. json_encode($error),__FILE__) );
        } else {
            $coordinates = json_decode($response);
            return  [$coordinates[0]->lat, $coordinates[0]->lon];
        }
    }


    // Recupère nom de la ville avec Lat Long 
    public function callApiReverseGeoLoc($longitude, $latitude)
    {
        // message::add('debug','aqi callApiReverseGeoLoc');
        if ($longitude != '' && $latitude != '') {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "http://api.openweathermap.org/geo/1.0/reverse?lat=" . $latitude . "&lon=" . $longitude,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Content-type: application/json",
                    "x-api-key:".$this->apiKey
                ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                    throw new Exception(__('Impossible de récupérer cette ville en reverse geoloc : :'. json_encode($err),__FILE__) );
            } else {
                $data = json_decode($response);
                $city = $data[0]->name;
                return  $city;
            }
        } else {
            throw new Exception(__('Les coordonnées sont vides',__FILE__));
            return null;
        }
    }


    public function getAqi($latitude , $longitude )
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://api.openweathermap.org/data/2.5/air_pollution?lat=" . $latitude . "&lon=" . $longitude,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "x-api-key:".$this->apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $data = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            throw new Exception('Pas de données de pollution pour l\'instant');
        } else {
            $result = $data->list[0];
            if ($result == [] || $result == null) {
                throw new Exception('Pas de données de pollution avec ces coordonnées');
            } else {
                return $data->list[0];
            }
        }
    }

    // public function setDynGeoLoc($latitude, $longitude)
    // {
    //     config::save('DynLatitude', $latitude, 'airquality');
    //     config::save('DynLongitude', $longitude, 'airquality');
    //     $resLat = trim(config::byKey('DynLatitude', 'airquality'));
    //     $resLong = trim(config::byKey('DynLongitude', 'airquality'));
    //     // return  self::callApiReverseGeoLoc($resLong,$resLat);
    //     return  $this->callApiReverseGeoLoc($latitude, $longitude);
    // }

    public function getOneCallApi($latitude , $longitude )
    {
              
        $url = "http://api.openweathermap.org/data/2.5/onecall?lat=" . $latitude . "&lon=" . $longitude. "&exclude=hourly,daily";
        $response = $this->curlApi($url);
        $data = json_decode($response[0]);

        if ($response[1] != null ) {
            throw new Exception('Pas de données  UV et visibilité pour l\'instant');
        } else {
          
            if ($data == [] || $data == null) {
                throw new Exception('Pas de données UV et visibilité avec ces coordonnées');
            } else {
                return $data->current;
            }
        }
    }


    private function curlApi(string $url, int $timeOut = 30){

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
                "x-api-key:".$this->apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return [$response, $err];

    }

    // public static function convertToPPM($microGramByM3, $molecule)
    // {
    //     $molecularWeight = [
    //         'nh3' => 10.03, 'co' => 28.1, 'no2' => 46.01, 'o3' => 48, 'so2' => 64.06, 'no' => 31.01
    //     ];
    //     $ppm = 24.45 * ($microGramByM3 / 1000) / $molecularWeight[$molecule];
    //     return number_format((float)$ppm, 3, '.', '');
    // }


    public function getAmbee($latitude = null, $longitude = null)
    {
        // message::add('debug','Gat aqi Method Aqi'. $this->apiKey);
        // Param auto pour test clef avant insertion des params
        if ($latitude === null && $longitude === null) {
            $latitude = 50 && $longitude = 50;
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.ambeedata.com/latest/pollen/by-lat-lng?lat=48.532&lng=7.713",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "x-api-key: hxPAYiCZ021Ipnh3xSbVI1huas2EtCDt71bkvNDV"
            ],
        ]);
        $response = curl_exec($curl);
        $data = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            throw new Exception('Pas de données de Pollen pour l\'instant');
        } else {
            $result = $data->data;
            if ($result == [] || $result == null) {
                throw new Exception('Pas de données de Polen avec ces coordonnées');
            } else {
                return $data;
            }
        }
    }

}
