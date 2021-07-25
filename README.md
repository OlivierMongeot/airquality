# Plugin Jeedom Air Quality & Pollen

Air Quality & Pollen plugin display air quality & pollens informations where you want in the world.  

It works with the open source **Jeedom** soft and it's compatible with Version 4. 

<img  align="right" height="200" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen_mo.JPG">
### Built With

* [Jeedom V4.1.22](https://jeedom.com)

* [HighCharts](https://highcharts.com)   

* [Raspberry Pi 3](https://www.raspberrypi.org)

* [Raspbian GNU/Linux 10 (buster)](https://www.raspberrypi.org/software)

* [Visual Studio Code + Remote SSH](https://code.visualstudio.com/)

### Installation

You must have Jeedom Domotic Software installed before using the plugin. 

The plugin is on Jeedom Marketplace with beta option at the moment, i hope, it will be 'stable' soon.

For test you can use the CLI with a linux systeme for installation or going to the market directly whith Jeedom beta option enable (https://market.jeedom.com/index.php?v=d&p=market_display&id=4165)

1. Get a free API Key at [ openweathermap.org ](https://openweathermap.org/). If you ever have one in your Jeedom, you can use the same, the plugin automatically import the key.


2. Go to 'plugins' directory of your Jeedom server, and create folder 'airquality' and give good permission to create files into:
    ```sh
    Go here : /var/www/html/plugins
    ```

3.  Clone the repo in plugins directory :
    ```sh
    git clone https://github.com/OlivierMongeot/airquality.git
    ```

4. !!! Delete the folder .git if it's just a test. Because on uninstall plugin, Jeedom don't understand what is it and can give errors)
    ```sh
    sudo rm /airquality/.git
    ```

5. See the documentation for configuration

     https://custom-one.fr/plugin-jeedom-air-quality-pollen/

6. To have pollen data you must still get a free API Key at [ Ambee.com ](https://www.getambee.com/) but it's not required to test the plugin.


7. I've set PHP display errors to maximum, for developpement: 
    ```php
    error_reporting(E_ALL);
    ini_set('ignore_repeated_errors', TRUE);
    ini_set('display_errors', TRUE);
    ```
    So, you can see every problems, but be carrefull if you are testing, you can maybe see errors from others plugins. 

8. Enjoy and take care yourself !! 



<img  align="left" height="200" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/badwheather.JPG">

## Usage

For all people sensitive, allergic, athletic, asthmatic you can provide your planning with the forecast and so protect yourself.

## Contributing

Any contributions you make are **greatly appreciated**.


