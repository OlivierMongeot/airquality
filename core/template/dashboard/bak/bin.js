


  

$('.eqLogic[data-eqLogic_uid=#uid#] .cmd').on('click', function () { jeedom.cmd.execute({ id: $(this).data('cmd_id') }); });





$(document).ready ( () => {

     // Boite Modal Info
     $(document).on ("click", "#info-modal#pm10id#", function () {
          $('#md_modal2').dialog({
          title: "Info Particules Fines"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoPm25&id=#pm10id#').dialog('open');
          }); 
    
     $(document).on ("click", "#info-modal#aqiid#", function () {
          $('#md_modal2').dialog({
          title: "Info AQI"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoAqi&id=#id#&logical_id=#aqiid#').dialog('open');
          });
     $(document).on ("click", "#info-modal#o3id#", function () {
          $('#md_modal2').dialog({
          title: "Info Ozone"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoO3&id=#o3id#').dialog('open');
          });
     $(document).on ("click", "#info-modal#nh3id#", function () {
          $('#md_modal2').dialog({
          title: "Info Ammoniac"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoNh3&id=#nh3id#').dialog('open');
          });
     $(document).on ("click", "#info-modal#so2id#", function () {
          $('#md_modal2').dialog({
          title: "Info Dioxyde de Souffre"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoSo2&id=#so2id#').dialog('open');
          });
     $(document).on ("click", "#info-modal#no2id#", function () {
          $('#md_modal2').dialog({
          title: "Info Dioxyde d'Azote"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoNo2&id=#no2id#').dialog('open');
          });
     $(document).on ("click", "#info-modal#noid#", function () {
          $('#md_modal2').dialog({
          title: "Info Monoxyde d'Azote"
          });
          $('#md_modal2').load('index.php?v=d&plugin=airquality&modal=InfoNo&id=#noid#').dialog('open');
          });

});



// Config Générale HighChart
var mainConfig = {
     chart: {
          type: "gauge",
          height: 190,
          width: 190,
          plotBackgroundColor: null,
          plotBackgroundImage: null,
          plotBorderWidth: 0,
          plotShadow: false,
          spacingTop:0,
          spacingRight:0,
          spacingLeft:0

     },
     credits: {
          enabled: false
     },
     title: {
          text: null,
     },
     legend: {
          enabled: false
     },

     pane: {
          center: ['50%', '50%'],
          size :'90%',
          startAngle: -150,
          endAngle: 150,
          background: null
     },
     exporting: {
          enabled: false
     },
     yAxis: {
          min: 0,
          minorTickInterval: "auto",
          minorTickWidth: 1,
          minorTickLength: 5,
          minorTickPosition: "outside",
          minorTickColor: "#666",
          tickPixelInterval: 50,
          tickWidth: 2,
          tickPosition: "outside",
          tickLength: 7,
          labels: {
               step: 1,
               rotation: "auto",
               distance: '77%',
               style: {
                    fontSize: '75%'
               }
          },
          title: {
               y: 22,
               style: {
                    fontSize: '180%'
               }
          }
     },
     plotOptions: {
          series: {
               dataLabels: {
                    enabled: false,
                    y: 25,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    className: 'highlight',
                    borderWidth: 1,
                    shadow: false,
                    style: {
                         textTransform: 'uppercase',
                         fontSize: '13px',
                         color: 'black',
                    }
               }
          },
          gauge: {
               dial: {
                    backgroundColor: 'currentColor',
                    radius: '70%'

               },
               pivot: {
                    backgroundColor: 'currentColor'
               }
          }
     }
};

// Config Spécifique à l'élément
var particulesConfig = (particle) => {
     if (particle === 'pm10') {
          if (#pm10# > 100){
               return {
                    'title': '<span>PM</span><sub style="font-size:50%">10</sub>',
                    'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
                    'softMax': 180,
                    'value': #pm10#,
                    'plotBands': [
                    ['#00AEEC', [0, 20]],
                    ['#00BD01', [20, 40]],
                    ['#EFE800', [40, 50]],
                    ['#E79C00', [50, 100]],
                    ['#B00000', [100, 150]],
                    ['#662D91', [150, 2000]]
                    ]};
          }
          return {
               'title': '<span>PM</span><sub style="font-size:50%">10</sub>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 110,
               'value': #pm10#,
               'plotBands': [
               ['#00AEEC', [0, 20]],
               ['#00BD01', [20, 40]],
               ['#EFE800', [40, 50]],
               ['#E79C00', [50, 100]],
               ['#B00000', [100, 150]]
               ]};
     }

     if (particle === 'pm25') { 
          if ( #pm25# > 150){
               return{
                    'title': '<span>PM</span><sub style="font-size:50%">25</sub>',
                    'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
                    'softMax': 360,
                    'value': #pm25#,
                    'plotBands': [
                    ['#00AEEC', [0, 10]],
                    ['#00BD01', [10, 20]],
                    ['#EFE800', [25, 25]],
                    ['#E79C00', [25, 50]],
                    ['#B00000', [50, 75]],
                     ['#662D91', [75, 750]]
                    ]};
          }
          return{
               'title': '<span>PM</span><sub style="font-size:50%">25</sub>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 60,
               'value': #pm25#,
               'plotBands': [
               ['#00AEEC', [0, 10]],
               ['#00BD01', [10, 20]],
               ['#EFE800', [20, 25]],
               ['#E79C00', [25, 50]],
               ['#B00000', [50, 160]],
               ]};
          }

     if (particle === 'co') {
          return {
               'title': '<span>CO</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 400,
               'value': #co#,
               'plotBands': [
               ['#00AEEC', [0, 90]],
               ['#00BD01', [90, 180]],
               ['#EFE800', [180, 250]],
               ['#E79C00', [250, 340]],
               ['#B00000', [340, 390]],
               ['#662D91', [390, 4000]]]};
          }

     if (particle === 'o3') {
          return {
               'title': '<span>O³</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 400,
               'value': #o3#,
               'plotBands': [
                    ['#00AEEC', [0, 50]],
                    ['#00BD01', [50, 100]],
                    ['#EFE800', [100, 130]],
                    ['#E79C00', [130, 240]],
                    ['#B00000', [240, 380]],
                    ['#662D91', [380, 4000]]]};
               }

     if (particle === 'nh3') {
          return {
               'title': '<span>NH³</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 80,
               'value': #nh3#,
               'plotBands': [
                    ['#00AEEC', [0, 8]],
                    ['#00BD01', [8, 20]],
                    ['#EFE800', [20, 35]],
                    ['#E79C00', [35, 50]],
                    ['#B00000', [50, 70]],
                    ['#662D91', [70, 300]]]};
                         }
     
     if (particle === 'so2') {
          return {
               'title': '<span>SO²</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 400,
               'value': #so2#,
               'plotBands': [
               ['#00AEEC', [0, 50]],
               ['#00BD01', [50, 100]],
               ['#EFE800', [100, 150]],
               ['#E79C00', [150, 240]],
               ['#B00000', [240, 380]],
               ['#662D91', [380, 4000]]]};
               }
     
     if (particle === 'no2') {
          return {
               'title': '<span>NO²</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 375,
               'value': #no2#,
               'plotBands': [
               ['#00AEEC', [0, 40]],
               ['#00BD01', [40, 90]],
               ['#EFE800', [90, 120]],
               ['#E79C00', [120, 230]],
               ['#B00000', [230, 340]],
               ['#662D91', [340, 4000]]]};
               }
     
     if (particle === 'no') {
          if (#no# > 150) {
          return {
               'title': '<span>NO</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 350,
               'value': #no#,
               'plotBands': [
               ['#00AEEC', [0, 40]],
               ['#00BD01', [40, 90]],
               ['#EFE800', [90, 120]],
               ['#E79C00', [120, 230]],
               ['#B00000', [230, 340]],
               ['#662D91', [340, 4000]]]};
          }
          return {
               'title': '<span>NO</span>',
               'valueSuffix': '<br style="font-size:0.6rem">μg/m³',
               'softMax': 150,
               'value': #no#,
               'plotBands': [
               ['#00AEEC', [0, 40]],
               ['#00BD01', [40, 90]],
               ['#EFE800', [90, 120]],
               ['#E79C00', [120, 230]]
               ]};
          }
   
     if (particle === 'aqi') {
               return {
                    'title': '<span>AQI</span>',
                    'valueSuffix': '',
                    'softMax': 6.5,
                    'value': #aqi#,
                    'plotBands': [
                    ['#00AEEC', [0, 1.5]],
                    ['#00BD01', [1.5, 2.5]],
                    ['#EFE800', [2.5, 3.5]],
                    ['#E79C00', [3.5, 4.5]],
                    ['#B00000', [4.5, 5.5]],
                    ['#662D91', [5.5, 6.5]]]};
                    }
   
}


// Retourne le Plotband de l'élément
var getPlotBands = (plotBandsConfig, thickness = '6%') => {
     var plotbands = [];
     for (let element of plotBandsConfig) {
          let layer = {
               from: element[1][0],
               to: element[1][1],
               thickness: thickness,
               color: element[0]
          }
          plotbands.push(layer);
     }
     return plotbands;
};


// Ajout des valeurs spécifiques à la configuration du Chart 
var getMainConfig = (element) => {
      
      
        mainConfig.tooltip = {
            valueSuffix: particulesConfig(element).valueSuffix
       };
       mainConfig.yAxis.softMax = particulesConfig(element).softMax;
       mainConfig.yAxis.title.text = particulesConfig(element).title;
       mainConfig.series = [{
            name: particulesConfig(element).title,
            data: [particulesConfig(element).value]
       }];
       mainConfig.yAxis.plotBands = getPlotBands(particulesConfig(element).plotBands);
      
  
       if (element === 'aqi'){
          console.log(element)
            mainConfig.yAxis.min = 0.5;
            mainConfig.yAxis.title.style.fontSize = '220%';
         /*   mainConfig.plotOptions.series.dataLabels.style.fontSize = '14px'; */    
            mainConfig.yAxis.minorTickLength = 0;
            mainConfig.yAxis.tickLength = 0;
            mainConfig.pane.startAngle = -100;
            mainConfig.pane.endAngle = 100;
            mainConfig.yAxis.labels.distance = '105%';
            mainConfig.plotOptions.series.dataLabels.enabled = false;
            mainConfig.chart.height = 190;
            mainConfig.chart.width = 220;
            mainConfig.chart.spacingTop = 0;
            mainConfig.chart.spacingBottom = 0;
       }
    //    let retrn = mainConfig
    //    mainConfig ='';
       return mainConfig
  };
  

  var aqiChart = new Highcharts.chart('particulesaqi#id#', getMainConfig('aqi'));


  
            var pm10Chart =Highcharts.chart('particulespm10#id#', getMainConfig('pm10'));
            var pm25Chart  =  Highcharts.chart('particulespm25#id#', getMainConfig('pm25'));
            var coChart  =  Highcharts.chart('particulesco#id#', getMainConfig('co'));
            var o3Chart   = Highcharts.chart('particuleso3#id#', getMainConfig('o3'));
            var nh3Chart  =  Highcharts.chart('particulesnh3#id#', getMainConfig('nh3'));
            var so2Chart   = Highcharts.chart('particulesso2#id#', getMainConfig('so2'));
            var no2Chart =   Highcharts.chart('particulesno2#id#', getMainConfig('no2'));
            var noChart  = Highcharts.chart('particulesno#id#', getMainConfig('no'));


 // activation carousel
if (false == true) {

var t#uid#;
var start#uid# = $('#airquality#uid#').find('.active').attr('data-interval');
t#uid# = setTimeout("$('#airquality#uid#').carousel({interval: 1000});", start#uid# - 1000);
$('#airquality#uid#').on('slid.bs.carousel', function () {
clearTimeout(t#uid#);
var duration#uid# = $(this).find('.active').attr('data-interval');
$('#airquality#uid#').carousel('pause');
t#uid# = setTimeout("$('#airquality#uid#').carousel();", duration#uid# - 1000);
})
$('.right.carousel-control.my#id#').on('click', function () {
clearTimeout(t#uid#);
});
$('.left.carousel-control.my#id#').on('click', function () {
clearTimeout(t#uid#);
});
}



/*   if (navigator.geolocation) {
console.log('check New Location from front');
navigator.geolocation.getCurrentPosition(maPosition);
}
function maPosition(position) {
setDynGeoloc(position.coords.latitude, position.coords.longitude)
document.cookie="latitude="+position.coords.latitude+"";
document.cookie="longitude="+position.coords.longitude+"";
}
var setDynGeoloc = (latitude,longitude) => {
$.ajax({
type: "POST",
url: "plugins/airquality/core/ajax/airquality.ajax.php",
data: {
  action: "setDynGeoloc",
  longitude: longitude,
  latitude: latitude
},
dataType: 'json',
error: function (request, status, error) {
  handleAjaxError(request, status, error);
},
success: function (data) {
console.log("requete Dyn Geoloc Setup succes : " + data.result)
$('#aqi-#id#-city').text(data.result);
console.log($('#aqi-#id#-city').text())
  if (data.state != 'ok') {
    console.log('ereur AJAX : ' + data.result);
  }
}
});
}*/

