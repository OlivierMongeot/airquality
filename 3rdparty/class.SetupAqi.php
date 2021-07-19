<?php


class  SetupAqi
{

    public static $setupAqi =  [
        ['name' => 'aqi', 'title' => 'AQI', 'unit' => '', 'subType' => 'numeric', 'order' => 1, 'display' => 'both'],
        ['name' => 'pm10', 'title' => 'PM10', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 2, 'display' => 'slideAqi'],
        ['name' => 'o3', 'title' => 'O³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 5, 'display' => 'slideAqi'],
        ['name' => 'no2', 'title' => 'NO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 7, 'display' => 'slideAqi'],
        ['name' => 'no', 'title' => 'NO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 4, 'display' => 'slideAqi'],
        ['name' => 'co', 'title' => 'CO', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 6, 'display' => 'slideAqi'],
        ['name' => 'so2', 'title' => 'SO²', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 8, 'display' => 'slideAqi'],
        ['name' => 'nh3', 'title' => 'NH³', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 9, 'display' => 'slideAqi'],
        ['name' => 'pm25', 'title' => 'PM2.5', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 3, 'display' => 'slideAqi'],
        ['name' => 'visibility', 'title' => 'Visibilité', 'unit' => 'm', 'subType' => 'numeric', 'order' => 10, 'display' => 'main'],
        ['name' => 'uv', 'title' => 'Indice UV', 'unit' => 'μg/m3', 'subType' => 'numeric', 'order' => 11, 'display' => 'main'],
        ['name' => 'days', 'title' => 'Forecast days', 'unit' => '', 'subType' => 'string', 'order' => 12, 'display' => 'chart'],
        ['name' => 'no2_min', 'title' => 'NO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 13, 'display' => 'chart'],
        ['name' => 'no2_max', 'title' => 'NO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 14, 'display' => 'chart'],
        ['name' => 'so2_min', 'title' => 'SO² Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 15, 'display' => 'chart'],
        ['name' => 'so2_max', 'title' => 'SO² Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 16, 'display' => 'chart'],
        ['name' => 'no_min', 'title' => 'NO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 17, 'display' => 'chart'],
        ['name' => 'no_max', 'title' => 'NO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 18, 'display' => 'chart'],
        ['name' => 'co_min', 'title' => 'CO Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 19, 'display' => 'chart'],
        ['name' => 'co_max', 'title' => 'CO Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 20, 'display' => 'chart'],
        ['name' => 'nh3_min', 'title' => 'NH3 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 21, 'display' => 'chart'],
        ['name' => 'nh3_max', 'title' => 'NH3 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 22, 'display' => 'chart'],
        ['name' => 'aqi_min', 'title' => 'AQI Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'aqi_max', 'title' => 'AQI Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'o3_min', 'title' => 'O³ Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'o3_max', 'title' => 'O³ Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'pm25_min', 'title' => 'PM2.5 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 25, 'display' => 'chart'],
        ['name' => 'pm25_max', 'title' => 'PM2.5 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 26, 'display' => 'chart'],
        ['name' => 'pm10_min', 'title' => 'PM10 Mini prévision', 'unit' => '', 'subType' => 'string', 'order' => 27, 'display' => 'chart'],
        ['name' => 'pm10_max', 'title' => 'PM10 Maxi prévision', 'unit' => '', 'subType' => 'string', 'order' => 28, 'display' => 'chart'],
        ['name' => 'messagePollution', 'title' => 'Alerte Pollution', 'unit' => '', 'subType' => 'string', 'order' => 29, 'display' => 'none'],
        ['name' => 'smsPollution', 'title' => 'SMS Pollution', 'unit' => '', 'subType' => 'string', 'order' => 30, 'display' => 'none'],
        ['name' => 'telegramPollution', 'title' => 'Telegram Pollution', 'unit' => '', 'subType' => 'string', 'order' => 31, 'display' => 'none'],
        ['name' => 'markdownPollution', 'title' => 'Markdown Pollution', 'unit' => '', 'subType' => 'string', 'order' => 32, 'display' => 'none']     
    ];

    public static $setupPollen = [
        ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 58, 'display' => 'main'],
        ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 59, 'display' => 'main'],
        ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 54, 'display' => 'main'],
        ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType' => 'string', 'order' => 55, 'display' => 'main'],
        ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType' => 'string', 'order' => 56, 'display' => 'main'],
        ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType' => 'string', 'order' => 57, 'display' => 'main'],

        ['name' => 'poaceae', 'title' => 'Graminées', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 6, 'display' => 'slide'],

        ['name' => 'alder', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 19, 'display' => 'slide'],
        ['name' => 'birch', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 18, 'display' => 'slide'],
        ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 8, 'display' => 'slide'],
        ['name' => 'elm', 'title' => 'Orme', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 16, 'display' => 'slide'],
        ['name' => 'hazel', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 17, 'display' => 'slide'],
        ['name' => 'oak', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 11, 'display' => 'slide'],
        ['name' => 'pine', 'title' => 'Pin', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 12, 'display' => 'slide'],
        ['name' => 'plane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 13, 'display' => 'slide'],
        ['name' => 'poplar', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 14, 'display' => 'slide'],

        ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 15, 'display' => 'slide'],
        ['name' => 'mugwort', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 9, 'display' => 'slide'],
        ['name' => 'nettle', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 10, 'display' => 'slide'],
        ['name' => 'ragweed', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 7, 'display' => 'slide'],

        ['name' => 'others', 'title' => 'Autres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 22, 'display' => 'slide'],

        ['name' => 'updatedAt', 'title' => 'Update at', 'unit' => '', 'subType' => 'string', 'order' => 60, 'display' => 'main'],
        ['name' => 'daysPollen', 'title' => 'Forecast days Pollen', 'unit' => '', 'subType' => 'string', 'order' => 23, 'display' => 'chart'],
        ['name' => 'poaceae_min', 'title' => "Grass-Poaceae Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 24, 'display' => 'chart'],
        ['name' => 'poaceae_max', 'title' => 'Grass-Poaceae Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 25, 'display' => 'chart'],
        ['name' => 'alder_min', 'title' => "Alder Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 26, 'display' => 'chart'],
        ['name' => 'alder_max', 'title' => 'Alder Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 27, 'display' => 'chart'],
        ['name' => 'birch_min', 'title' => "Birch Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 28, 'display' => 'chart'],
        ['name' => 'birch_max', 'title' => "Birch Maxi prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 29, 'display' => 'chart'],
        ['name' => 'cypress_min', 'title' => "Cypress Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 30, 'display' => 'chart'],
        ['name' => 'cypress_max', 'title' => 'Cypress Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 31, 'display' => 'chart'],
        ['name' => 'elm_min', 'title' => "Elm Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 32, 'display' => 'chart'],
        ['name' => 'elm_max', 'title' => 'Elm Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 33, 'display' => 'chart'],
        ['name' => 'hazel_min', 'title' => "Hazel Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 34, 'display' => 'chart'],
        ['name' => 'hazel_max', 'title' => 'Hazel Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 35, 'display' => 'chart'],
        ['name' => 'oak_min', 'title' => "Oak Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 36, 'display' => 'chart'],
        ['name' => 'oak_max', 'title' => 'Oak Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 37, 'display' => 'chart'],
        ['name' => 'pine_min', 'title' => "Pine Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 38, 'display' => 'chart'],
        ['name' => 'pine_max', 'title' => 'Pine Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 39, 'display' => 'chart'],
        ['name' => 'plane_min', 'title' => "Plane Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 40, 'display' => 'chart'],
        ['name' => 'plane_max', 'title' => 'Plane Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 41, 'display' => 'chart'],
        ['name' => 'poplar_min', 'title' => "Poplar Cottonwood Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 42, 'display' => 'chart'],
        ['name' => 'poplar_max', 'title' => 'Poplar Cottonwood Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 43, 'display' => 'chart'],
        ['name' => 'chenopod_min', 'title' => "Chenopod Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 44, 'display' => 'chart'],
        ['name' => 'chenopod_max', 'title' => 'Chenopod Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 45, 'display' => 'chart'],
        ['name' => 'mugwort_min', 'title' => "Mugwort Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 46, 'display' => 'chart'],
        ['name' => 'mugwort_max', 'title' => 'Mugwort Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 47, 'display' => 'chart'],
        ['name' => 'nettle_min', 'title' => "Nettle Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 48, 'display' => 'chart'],
        ['name' => 'nettle_max', 'title' => 'Nettle Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 49, 'display' => 'chart'],
        ['name' => 'ragweed_min', 'title' => "Ragweed Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 50, 'display' => 'chart'],
        ['name' => 'ragweed_max', 'title' => 'Ragweed Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 51, 'display' => 'chart'],
        ['name' => 'others_min', 'title' => "Others Mini prévision", 'unit' => 'part/m³', 'subType' => 'string', 'order' => 52, 'display' => 'chart'],
        ['name' => 'others_max', 'title' => 'Others Maxi prévision', 'unit' => 'part/m³', 'subType' => 'string', 'order' => 53, 'display' => 'chart'],
        ['name' => 'messagePollen', 'title' => 'Alerte Pollen', 'unit' => '', 'subType' => 'string', 'order' => 54, 'display' => 'none'],
        ['name' => 'telegramPollen', 'title' => 'Telegram Pollen', 'unit' => '', 'subType' => 'string', 'order' => 55, 'display' => 'none'],
        ['name' => 'smsPollen', 'title' => 'SMS Pollen', 'unit' => '', 'subType' => 'string', 'order' => 56, 'display' => 'none'],
        ['name' => 'markdownPollen', 'title' => 'Markdown Pollen', 'unit' => '', 'subType' => 'string', 'order' => 57, 'display' => 'none']
    ];


    public static $pollenRange =
    [
        'poaceae' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 60],
            '#E79C00' => [60, 120],
            '#B00000' => [120, 1000]

        ],
        'elm' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'alder' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'birch' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'grass_pollen' => [
            '#00BD01' => [0, 30],
            '#EFE800' => [30, 60],
            '#E79C00' => [60, 120],
            '#B00000' => [120, 1000]
        ],
        'tree_pollen' => [
            '#00BD01' => [0, 80],
            '#EFE800' => [80, 200],
            '#E79C00' => [200, 300],
            '#B00000' => [300, 1000]
        ],
        'weed_pollen' => [
            '#00BD01' => [0, 25],
            '#EFE800' => [25, 75],
            '#E79C00' => [75, 270],
            '#B00000' => [270, 10000]
        ],
        'cypress' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'oak' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'hazel' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'pine' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'plane' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'poplar' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],  
            '#B00000' => [100, 1000]
        ],
        'chenopod' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]

        ],
        'mugwort' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'nettle' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 200],
            '#B00000' => [200, 1000]
        ],
        'ragweed' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ],
        'others' => [
            '#00BD01' => [0, 5],
            '#EFE800' => [5, 40],
            '#E79C00' => [40, 100],
            '#B00000' => [100, 1000]
        ]
    ];


    public static $aqiRange =
    [
        'aqi' => [
            '#00AEEC' => [1, 2],
            '#00BD01' => [2, 3],
            '#EFE800' => [3, 4],
            '#E79C00' => [4, 5],
            '#B00000' => [5, 6],
            '#662D91' => [6, 7]
        ],
        'o3' => [
            '#00AEEC' => [0, 50],
            '#00BD01' => [50, 100],
            '#EFE800' => [100, 130],
            '#E79C00' => [130, 240],
            '#B00000' => [240, 380],
            '#662D91' => [380, 10000]
        ],
        'no2' => [
            '#00AEEC' => [0, 40],
            '#00BD01' => [40, 90],
            '#EFE800' => [90, 120],
            '#E79C00' => [120, 220],
            '#B00000' => [230, 340],
            '#662D91' => [340, 10000]
        ],
        'no' => [
            '#00AEEC' => [0, 30],
            '#00BD01' => [30, 50],
            '#EFE800' => [50, 200],
            '#E79C00' => [200, 300],
            '#B00000' => [300, 600],
            '#662D91' => [600, 10000]
        ],
        'co' => [
            '#00AEEC' => [0, 360],
            '#00BD01' => [360, 700],
            '#EFE800' => [700, 100000],
            '#E79C00' => [100000, 250000],
            '#B00000' => [250000, 500000],
            '#662D91' => [500000, 100000000]
        ],
        'so2' => [
            '#00AEEC' => [0, 100],
            '#00BD01' => [100, 200],
            '#EFE800' => [200, 350],
            '#E79C00' => [350, 500],
            '#B00000' => [500, 750],
            '#662D91' => [750, 10000]
        ],
        'nh3' => [
            '#00AEEC' => [0, 3],
            '#00BD01' => [3, 7],
            '#EFE800' => [7, 30],
            '#E79C00' => [30, 100],
            '#B00000' => [100, 300],
            '#662D91' => [300, 10000]
        ],
        'pm10' => [
            '#00AEEC' => [0, 20],
            '#00BD01' => [20, 40],
            '#EFE800' => [40, 60],
            '#E79C00' => [60, 100],
            '#B00000' => [100, 150],
            '#662D91' => [150, 10000]
        ],
        'pm25' => [
            '#00AEEC' => [0, 10],
            '#00BD01' => [10, 20],
            '#EFE800' => [20, 25],
            '#E79C00' => [25, 50],
            '#B00000' => [50, 75],
            '#662D91' => [75, 10000]
        ],
        'uv' => [
            '#00AEEC' => [0, 2],
            '#00BD01' => [2, 4],
            '#EFE800' => [4, 6],
            '#E79C00' => [6, 8],
            '#B00000' => [8, 10],
            '#662D91' => [10, 100]
        ],
        'visibility' => [
            '#662D91' => [0, 700],
            '#B00000' => [700, 3200],
            '#E79C00' => [3200, 8000],
            '#00AEEC' => [8000, 100000]
        ]
    ];
}
