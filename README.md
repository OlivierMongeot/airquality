# Plugin Jeedom Air Quality & Pollen

Air Quality & Pollen plugin display air quality & pollens informations where you want in the world.  

It works with the open source **Jeedom** soft and it's compatible with Version 4. 

### Built With

* [Jeedom V4.1.22](https://jeedom.com)

* [HighCharts](https://highcharts.com)   

* [Raspberry Pi 3](https://www.raspberrypi.org)


### Installation

You must have Jeedom Domotic Software installed before using the plugin. 

The plugin is not on Jeedom Marketplace for the momment, i hope, it will be soon,  so for test you can : 

1. Get a free API Key at [ openweathermap.org ](https://openweathermap.org/) 

2. Go to plugins directory of your Jeedom server, an give good permission to create files into:
    ```sh
    Here : /var/www/html/plugins
    ```

3.  Clone the repo in plugins directory :
    ```sh
    git clone https://github.com/OlivierMongeot/airquality.git
    ```

4. Delete the folder .git (Otherwise on uninstall Jeedom don't understand what is it and can give errors)
    ```sh
    sudo rm /airquality/.git
    ```

5. See the documentation for configuration

     https://oliviermongeot.github.io/airquality/

6. To have pollen data you must still get a free API Key at [ Ambee.com ](https://www.getambee.com/) but it's not required to test the plugin.

7. Enjoy and take care yourself !! 


<img  align="left" height="200" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">

## Usage

For all people sensitive, allergic, athletic, asthmatic you can provide your planning with the forecast.


## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.


