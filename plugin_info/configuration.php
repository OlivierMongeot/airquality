<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

?>
<form class="form-horizontal">
<br>
    <fieldset>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Clef API OpenWeather}}</label>
            <div class="col-sm-6">
                <input id="api-aqi-key" class="configKey form-control" data-l1key="apikey" required />
            </div>
            <?php if (file_exists(plugin::getPathById('weather'))) : ?>
                <?= '<div class="col-sm-1 tooltips" id="import-weather-key" title="Importer la clef du plugin Weather"><a class="btn btn-xs btn-success">Importer</a></div>' ?>
            <?php endif ?>
        </div>
    </fieldset>

</form>
<script>
    $(document).on('click', '#import-weather-key', function() {
        $.ajax({
            type: "POST",
            url: "plugins/airquality/core/ajax/airquality.ajax.php",
            data: {
                action: "getApiKeyWeather"
            },
            dataType: 'json',
            error: function(request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function(data) {
                console.log("requete ajax succes : " + data.result)
                document.getElementById("api-aqi-key").value = data.result;
                if (data.state != 'ok') {
                    console.log('ereur AJAX : ' + data.result);
                }
            }
        });
    })
</script>