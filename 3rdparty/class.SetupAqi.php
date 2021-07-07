<?php


Class  SetupAqi {

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
    ];

    public static $setupPollen = [
        ['name' => 'grass_pollen', 'title' => 'Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 58, 'display' => 'main'],
        ['name' => 'tree_pollen', 'title' => 'Arbres', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 59, 'display' => 'main'],
        ['name' => 'weed_pollen', 'title' => 'Mauvaises Herbes', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 54, 'display' => 'main'],
        ['name' => 'grass_risk', 'title' => 'Risque herbe', 'unit' => '', 'subType' => 'string', 'order' => 55, 'display' => 'main'],
        ['name' => 'weed_risk', 'title' => 'Risque mauvaise herbe', 'unit' => '', 'subType' => 'string', 'order' => 56, 'display' => 'main'],
        ['name' => 'tree_risk', 'title' => 'Risque arbres', 'unit' => '', 'subType' => 'string', 'order' => 57, 'display' => 'main'],
        ['name' => 'poaceae', 'title' => 'Graminées', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 19, 'display' => 'slide'],
        ['name' => 'alder', 'title' => 'Aulne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 6, 'display' => 'slide'],
        ['name' => 'birch', 'title' => 'Bouleau', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 7, 'display' => 'slide'],
        ['name' => 'cypress', 'title' => 'Cyprès', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 8, 'display' => 'slide'],
        ['name' => 'elm', 'title' => 'Orme', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 9, 'display' => 'slide'],
        ['name' => 'hazel', 'title' => 'Noisetier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 10, 'display' => 'slide'],
        ['name' => 'oak', 'title' => 'Chêne', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 11, 'display' => 'slide'],
        ['name' => 'pine', 'title' => 'Pin', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 12, 'display' => 'slide'],
        ['name' => 'plane', 'title' => 'Platane', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 13, 'display' => 'slide'],
        ['name' => 'poplar', 'title' => 'Peuplier', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 14, 'display' => 'slide'],
        ['name' => 'chenopod', 'title' => 'Chenopod', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 15, 'display' => 'slide'],
        ['name' => 'mugwort', 'title' => 'Armoise', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 16, 'display' => 'slide'],
        ['name' => 'nettle', 'title' => 'Ortie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 17, 'display' => 'slide'],
        ['name' => 'ragweed', 'title' => 'Ambroisie', 'unit' => 'part/m3', 'subType' => 'numeric', 'order' => 18, 'display' => 'slide'],
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
    ];

}
