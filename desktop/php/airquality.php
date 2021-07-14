<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('airquality');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div id='aqi-alert'></div>
<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i>
                <br>
                <span>{{Ajouter}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
        </div>
        <legend><i class="fas fa-table"></i> {{Mes AQI}}</legend>
        <?php
        if (count($eqLogics) == 0) {
            echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Template n\'est paramétré, cliquer sur "Ajouter" pour commencer}}</div>';
        } else {
            // Champ de recherche
            echo '<div class="input-group" style="margin:5px;">';
            echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
            echo '<div class="input-group-btn">';
            echo '<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>';
            echo '</div>';
            echo '</div>';
            // Liste des équipements du plugin
            echo '<div class="eqLogicThumbnailContainer">';
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                echo '<br>';
                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>

    </div> <!-- /.eqLogicThumbnailDisplay -->

    <!-- Page de présentation de l'équipement -->
    <div class="col-xs-12 eqLogic" style="display: none;">
        <!-- barre de gestion de l'équipement -->
        <div class="input-group pull-right" style="display:inline-flex;">
            <span class="input-group-btn">
                <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
                <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure">
                    <i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>

                </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
                </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
                </a>
            </span>
        </div>
        <!-- Onglets -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i><span class="hidden-xs"> {{Équipement}}</span></a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i><span class="hidden-xs"> {{Commandes}}</span></a></li>
            <li role="presentation"><a href="#alerttab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-exclamation-circle"></i><span class="hidden-xs"> {{Alertes}}</span></a></li>
        </ul>
        <div class="tab-content">
            <!-- Onglet de configuration de l'équipement -->
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">

                <form class="form-horizontal">
                    <fieldset>
                        <div class="col-lg-6">
                            <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Objet parent}}</label>
                                <div class="col-sm-7">
                                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                        <option value="">{{Aucun}}</option>
                                        <?php
                                        $options = '';
                                        foreach ((jeeObject::buildTree(null, false)) as $object) {
                                            $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                        }
                                        echo $options;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                                <div class="col-sm-7">
                                    <?php
                                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                        echo '<label class="checkbox-inline">';
                                        echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Options}}</label>
                                <div class="col-sm-7">
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                                </div>
                            </div>
                            <br>

                            <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Méthode de localisation}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="searchMode">
                                        <option value="city_mode">{{Par ville}}</option>
                                        <option value="long_lat_mode">{{Par longitude & latitude}}</option>
                                        <option value="dynamic_mode">{{Géolocalisation du navigateur (https requis)}}</option>
                                        <?php
                                        if ((config::byKey('info::latitude') != '') && (config::byKey('info::longitude') != '')) {
                                            echo '<option value="server_mode">{{Géolocalisation du serveur Jeedom}}</option>';
                                        } else {
                                            echo '<option disabled="disabled" value="server_mode">{{Localisation Jeedom non renseigné dans la configuration générale}}</option>';
                                        }
                                        ?>
                                        <!-- <option value="dynamic_mode_live">{{Géolocalisation Live}}</option> -->
                                    </select>
                                </div>
                            </div>
                            <!-- city_mode  -->
                            <div class="form-group searchMode city_mode">
                                <label class="col-sm-3 control-label">{{Ville}}</label>
                                <div class="col-sm-6">
                                    <input type="string" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="city" required />
                                </div>

                            </div>
                            <div class="form-group searchMode city_mode">
                                <label class="col-sm-3 control-label">{{Code pays}}
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="country_code" placeholder="ex: FR (iso 3166-1) " required />
                                </div>
                            </div>

                            <div class="form-group searchMode city_mode">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <a id="validate-city" class="btn btn-sm btn-success"><i class="fas fa-check-circle"></i> {{Vérifier}}</a>
                                </div>
                            </div>

                            <div id="geoloc-city-mode">
                                <!-- Js -->
                            </div>

                            <!-- longitude latitude mode -->
                            <div class="form-group searchMode long_lat_mode">
                                <label class="col-sm-3 control-label">{{Longitude}}</label>
                                <div class="col-sm-6">
                                    <input type="number" id="longitude-llm" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="longitude" />
                                </div>
                            </div>
                            <div class="form-group searchMode long_lat_mode">
                                <label class="col-sm-3 control-label">{{Latitude}}</label>
                                <div class="col-sm-6">
                                    <input type="number" id="latitude-llm" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="latitude" />
                                </div>
                            </div>

                            <div class="form-group searchMode long_lat_mode">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <a id="validate-llm" class="btn btn-sm btn-success"><i class="fas fa-check-circle"></i> {{Vérifier les coordonées}}</a>
                                </div>
                            </div>

                            <div style="display:none" class="form-group searchMode long_lat_mode">
                                <label class="col-sm-3 control-label">{{Lieu correspondant}}</label>
                                <div class="col-sm-6">
                                    <input id="geo-loc-llm" type="text" disabled="disabled" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="city-llm" />
                                </div>
                            </div>
                            <!-- dynamic_mode -->
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Longitude }}</label>
                                <div class="col-sm-6">
                                    <input id="longitude" type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoLongitude" />
                                </div>
                            </div>
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Latitude}}</label>
                                <div class="col-sm-6">
                                    <input id="latitude" type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoLatitude" />
                                </div>
                            </div>
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Ville}}</label>
                                <div class="col-sm-6">
                                    <input id="geoCity" type="text" disabled="disabled" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoCity" />
                                </div>
                            </div>
                            <br>
                            <div style="display:none" class="form-group">
                                <label class="col-sm-3 control-label">{{Affichage}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="displayMode">
                                        <option value="min_display">{{Digital}}</option>
                                        <option disabled value="full_display">{{Analogique}}</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Animation}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="animation_aqi">
                                        <option value="disable_anim">{{Désactiver}}</option>
                                        <option value="slow_anim">{{Activer}}</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <!-- <div class="form-group">
                                <label class="col-sm-3 control-label">{{Mini, Maxi, Moyenne, Tendance, Statistiques}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="historize">
                                        <option value="actived">{{Activer}}</option>
                                        <option value="disable">{{Désactiver}}</option>
                                    </select>
                                </div>
                            </div>
                            <br> -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Elements à afficher}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="elements">
                                        <?php
                                        if (config::byKey('apikey', 'airquality') !== '') {
                                            echo '<option value="polution">{{Polluant}}</option>';
                                        } else {
                                            echo '<option disabled="disabled" value="no-api-key">{{AQI : Veuiller renseigner la clef Openwheather avant utilisation}}</option>';
                                        }
                                        if (config::byKey('apikeyAmbee', 'airquality') !== '') {
                                            echo '<option value="pollen">{{Pollen}}</option>';
                                        } else {
                                            echo '<option disabled="disabled" value="no-pollen-key">{{Pollen : Veuiller renseigner la clef Ambee avant utilisation}}</option>';
                                        }
                                        ?>
                                        <!-- <option value="radiation">{{Radiation solaire}}</option> -->
                                    </select>
                                </div>
                            </div>


                            <div class="form-group elements pollen">
                                <label class="col-sm-3 control-label">{{Pollens Niveau Zéro}}</label>
                                <div class="col-sm-6">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="displayZeroPollen" />{{Visible}}
                                    </label>

                                </div>
                            </div>
                        </div>
                        <!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
                        <div class="col-lg-6">
                            <legend><i class="fas fa-info"></i> {{Informations}}</legend>
                            <div class="form-group elements polution">
                                <div class="container">
                                    <h5>Code Couleurs AQI</h5>
                                </div>
                                <br>
                                <div class="container">
                                    <div>
                                        <input type="color" name="head" disabled value="#00AEEC">
                                        <label for="head">Bon</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#00BD01">
                                        <label for="body">Correct</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#EFE800">
                                        <label for="body">Dégradé</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#E79C00">
                                        <label for="body">Mauvais</label>
                                        &nbsp; &nbsp;
                                        <br><br>
                                        <input type="color" name="body" disabled value="#B00000">
                                        <label for="body">Très mauvais</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#662D91">
                                        <label for="body">Extrême</label>
                                    </div>
                                    <br>
                                    <br>
                                </div>
                            </div>
                            <!--  -->
                            <div class="form-group elements pollen">
                                <div class="container">
                                    <h6>Code Couleurs Pollen</h6>
                                </div>
                                <br>
                                <div class="container">
                                    <div>
                                        <input type="color" name="head" disabled value="#00BD01">
                                        <label for="head">Risque bas</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#EFE800">
                                        <label for="body">Risque modéré</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#E79C00">
                                        <label for="body">Risque haut</label>
                                        &nbsp; &nbsp;
                                        <input type="color" name="body" disabled value="#B00000">
                                        <label for="body">Risque très haut</label>
                                    </div>
                                    <br>
                                    <br>

                                </div>
                            </div>
                            <!--  -->
                            <div class="form-group">
                                <div class="text-center">
                                    <img name="icon_visu" src="<?= $plugin->getPathImgIcon(); ?>" style="max-width:160px;" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <hr>
            </div><!-- /.tabpanel #eqlogictab-->

            <!-- Onglet des commandes de l'équipement -->
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <a class="btn btn-default btn-sm pull-left cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
                <br /><br />
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>{{Id}}</th>
                                <th>{{Nom}}</th>
                                <th>{{Type}}</th>
                                <th>{{Options}}</th>
                                <th>{{Paramètres}}</th>
                                <th>{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #commandtab-->
            <style>
                .range-slider.aqi .input-range.aqi {
                    -webkit-appearance: none;
                    height: 5px;
                    border-radius: 5px;
                    background: #ccc;
                    outline: none;
                    writing-mode: bt-lr;
                }

                .range-container {
                    width: 40%;
                }
            </style>
            <!-- Onglet reglage Alerte -->
            <div role="tabpanel" class="tab-pane" id="alerttab">
                <br /><br />
                <legend>Seuil d'alertes</legend>
                <div class="container range-container">

                    <div class="form-group elements polution">
                       
                        <div class="range-slider aqi">

                            <?php
                            foreach ($eqLogics as $eqLogic) {
                                $levelAqi_ = $eqLogic->getConfiguration('aqi_alert_level');
                                $levelPm25_ = $eqLogic->getConfiguration('pm25_alert_level');
                                $levelPm10_ = $eqLogic->getConfiguration('pm10_alert_level');
                                $levelO3_ = $eqLogic->getConfiguration('o3_alert_level');
                                $levelSo2_ = $eqLogic->getConfiguration('so2_alert_level');
                                $levelNo2_ = $eqLogic->getConfiguration('no2_alert_level');
                                $levelCo_ = $eqLogic->getConfiguration('co_alert_level');
                                $levelNh3_ = $eqLogic->getConfiguration('nh3_alert_level');
                                $levelUV_ = $eqLogic->getConfiguration('uv_alert_level');
                                $levelVisi_ = $eqLogic->getConfiguration('visibility_alert_level');

                                if ($levelAqi_ != '') {
                                    $levelAqi = $levelAqi_;
                                }
                                if ($levelPm25_ != '') {
                                    $levelPm25 = $levelPm25_;
                                }
                                if ($levelPm10_ != '') {
                                    $levelPm10 = $levelPm10_;
                                }
                                if ($levelO3_ != '') {
                                    $levelO3 = $levelO3_;
                                }
                                if ($levelSo2_ != '') {
                                    $levelSo2 = $levelSo2_;
                                }
                                if ($levelNo2_ != '') {
                                    $levelNo2 = $levelNo2_;
                                }
                                if ($levelCo_ != '') {
                                    $levelCo = $levelCo_;
                                }
                                if ($levelNh3_ != '') {
                                    $levelNh3 = $levelNh3_;
                                }
                                if ($levelUV_ != '') {
                                    $levelUV = $levelUV_;
                                }
                                if ($levelVisi_ != '') {
                                    $levelVisi = $levelVisi_;
                                }
                            }
                            ?>
                            <label for="aqi">Indice AQI</label> <span class="pull-right" id="disp_aqi"></span>
                            <input type="range" value="<?= $levelAqi ?>" min="1" max="6" class="input-range aqi" orient="vertical" name="aqi" id="aqi"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="aqi_alert_level" data-l1key="configuration" data-l2key="aqi_alert_level"></input>
                            <br><br>
                    
                            <label for="pm25">PM<sub>2.5</sub></label><div class="pull-right"><span id="disp_pm25"></span> μg/m3</div> 
                            <input type="range" value="<?= $levelPm25 ?>" min="0" max="75" class="input-range aqi" orient="horizontal" name="pm25" id="pm25"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="pm25_alert_level" data-l1key="configuration" data-l2key="pm25_alert_level"></input>
                            <br><br>

                            <label for="pm10">PM<sub>10</sub></label><div class="pull-right" ><span id="disp_pm10"></span> μg/m3</div> 
                            <input type="range" value="<?= $levelPm10 ?>" min="0" max="150" class="input-range aqi" orient="horizontal" name="pm10" id="pm10"></input>                            
                            <input type="hidden" class="eqLogicAttr form-control" id="pm10_alert_level" data-l1key="configuration" data-l2key="pm10_alert_level"></input>
                            <br><br>
                 
                            <label for="o3">O³</label><div  class="pull-right"><span id="disp_o3"></span> μg/m3</div> 
                            <input type="range" value="<?= $levelO3 ?>" min="0" max="380" class="input-range aqi" orient="horizontal" name="o3" id="o3"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="o3_alert_level" data-l1key="configuration" data-l2key="o3_alert_level"></input>
                            <br><br>
        
                            <label for="so2">SO²</label> <div class="pull-right" ><span id="disp_so2"></span> μg/m3</div>
                            <input type="range" value="<?= $levelSo2 ?>" min="0" max="750" class="input-range aqi" orient="horizontal" name="so2" id="so2"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="so2_alert_level" data-l1key="configuration" data-l2key="so2_alert_level"></input>
                            <br><br>
 
                            <label for="no2">NO²</label> <div class="pull-right"><span id="disp_no2"></span> μg/m3</div>
                            <input type="range" value="<?= $levelNo2 ?>" min="0" max="340" class="input-range aqi" orient="horizontal" name="no2" id="no2"></input>                       
                            <input type="hidden" class="eqLogicAttr form-control" id="no2_alert_level" data-l1key="configuration" data-l2key="no2_alert_level"></input>
                            <br><br>
 
                            <label for="co">CO</label><div class="pull-right"><span id="disp_co"></span> μg/m3</div> 
                            <input type="range" value="<?= $levelCo ?>" min="0" max="700" class="input-range aqi" orient="horizontal" name="co" id="co"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="co_alert_level" data-l1key="configuration" data-l2key="co_alert_level"></input>
                            <br><br>

                            <label for="nh3">NH³</label><div class="pull-right"><span  id="disp_nh3"></span> μg/m3</div>
                            <input type="range" value="<?= $levelNh3 ?>" min="0" max="300" class="input-range aqi" orient="horizontal" name="nh3" id="nh3"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="nh3_alert_level" data-l1key="configuration" data-l2key="nh3_alert_level"></input>
                            <br><br>

                            <label for="no">NO</label><div class="pull-right"><span id="disp_no"></span> μg/m3</div>
                            <input type="range" value="<?= $levelNo ?>" min="0" max="200" class="input-range aqi" orient="horizontal" name="no" id="no"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="no_alert_level" data-l1key="configuration" data-l2key="no_alert_level"></input>   
                            <br><br>

                            <label for="uv">Indice UV</label><div class="pull-right"><span id="disp_uv"></span></div>
                            <input type="range" value="<?= $levelUV ?>" min="0" max="10" class="input-range aqi" orient="horizontal" name="uv" id="uv"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="uv_alert_level" data-l1key="configuration" data-l2key="uv_alert_level"></input>
                            <br><br>

                            <label for="visibility">Visibilité</label><div class="pull-right"><span id="disp_visibility"></span> m</div>
                            <input type="range" value="<?= $levelVisi ?>" min="0" max="10000" class="input-range aqi" orient="horizontal" name="visibility" id="visibility"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="visibility_alert_level" data-l1key="configuration" data-l2key="visibility_alert_level"></input>
                            <br><br>
                        </div>
                    </div>

                    <div class="form-group elements pollen">
                       
                        <div class="range-slider aqi">

                        <?php
                            foreach ($eqLogics as $eqLogic) {
                                $levelPoaceae_ = $eqLogic->getConfiguration('poaceae_alert_level');
                                $levelElm_ = $eqLogic->getConfiguration('elm_alert_level');
                                $levelAlder_ = $eqLogic->getConfiguration('alder_alert_level');
                                $levelBirch_ = $eqLogic->getConfiguration('alder_alert_level');
                                $levelCypress_ = $eqLogic->getConfiguration('cypress_alert_level');
                                $levelOak_ = $eqLogic->getConfiguration('oak_alert_level');
                                $levelHazel_ = $eqLogic->getConfiguration('hazel_alert_level');
                                $levelPine_ = $eqLogic->getConfiguration('pine_alert_level');
                                $levelPlane_ = $eqLogic->getConfiguration('plane_alert_level');
                                $levelPoplar_ = $eqLogic->getConfiguration('poplar_alert_level');
                                $levelChenopod_ = $eqLogic->getConfiguration('chenopod_alert_level');
                                $levelMugwort_ = $eqLogic->getConfiguration('mugwort_alert_level');
                                $levelNettle_ = $eqLogic->getConfiguration('nettle_alert_level');
                                $levelRagweed_ = $eqLogic->getConfiguration('ragweed_alert_level');
                                $levelOthers_ = $eqLogic->getConfiguration('others_alert_level');

                                if ($levelPoaceae_ != '') { $levelPoaceae = $levelPoaceae_; }
                                if ($levelElm_ != '') { $levelElm = $levelElm_; }
                                if ($levelAlder_ != '') { $levelAlder = $levelAlder_; }
                                if ($levelBirch_ != '') { $levelBirch = $levelBirch_; }
                                if ($levelCypress_ != '') { $levelCypress = $levelCypress_; }
                                if ($levelOak_ != '') { $levelOak = $levelOak_; }
                                if ($levelHazel_ != '') { $levelHazel = $levelHazel_; }
                                if ($levelPine_ != '') { $levelPine = $levelPine_; }
                                if ($levelPlane_ != '') { $levelPlane = $levelPlane_; }
                                if ($levelPoplar_ != '') { $levelPoplar = $levelPoplar_; }
                                if ($levelChenopod_ != '') { $levelChenopod = $levelChenopod_; }
                                if ($levelMugwort_ != '') { $levelMugwort = $levelMugwort_; }
                                if ($levelNettle_ != '') { $levelNettle = $levelNettle_; }
                                if ($levelRagweed_ != '') { $levelRagweed = $levelRagweed_; }
                                if ($levelOthers_ != '') { $levelOthers = $levelOthers_; }
                            }
                            ?>
                            <label for="poaceae">{{Graminées / Poacées}}</label><div class="pull-right"><span id="disp_poaceae"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPm25 ?>" min="1" max="250" class="input-range aqi" orient="vertical" name="poaceae" id="poaceae"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="poaceae_alert_level" data-l1key="configuration" data-l2key="poaceae_alert_level"></input>
                            <br>
                            <label for="elm">{{Orme}}</label><div class="pull-right"><span id="disp_elm"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPm25 ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="elm" id="elm"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="elm_alert_level" data-l1key="configuration" data-l2key="elm_alert_level"></input>
                            <br>
                            <label for="alder">{{Aulne}}</label><div class="pull-right"><span id="disp_alder"></span> part/m3</div> 
                            <input type="range" value="<?= $levelAlder ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="alder" id="alder"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="alder_alert_level" data-l1key="configuration" data-l2key="alder_alert_level"></input>
                            <br>
                            <label for="birch">{{Bouleau}}</label><div class="pull-right"><span id="disp_birch"></span> part/m3</div> 
                            <input type="range" value="<?= $levelBirch ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="birch" id="birch"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="birch_alert_level" data-l1key="configuration" data-l2key="birch_alert_level"></input>
                            <br>
                            <label for="cypress">{{Cyprès}}</label><div class="pull-right"><span id="disp_cypress"></span> part/m3</div> 
                            <input type="range" value="<?= $levelCypress ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="cypress" id="cypress"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="cypress_alert_level" data-l1key="configuration" data-l2key="cypress_alert_level"></input>
                            <br>
                            <label for="oak">{{Chêne}}</label><div class="pull-right"><span id="disp_oak"></span> part/m3</div> 
                            <input type="range" value="<?= $levelOak ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="oak" id="oak"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="oak_alert_level" data-l1key="configuration" data-l2key="oak_alert_level"></input>
                            <br>
                            <label for="hazel">{{Noisetier}}</label><div class="pull-right"><span id="disp_hazel"></span> part/m3</div> 
                            <input type="range" value="<?= $levelHazel ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="hazel" id="hazel"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="hazel_alert_level" data-l1key="configuration" data-l2key="hazel_alert_level"></input>
                            <br>
                            <label for="pine">{{Pin}}</label><div class="pull-right"><span id="disp_pine"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPine ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="pine" id="pine"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="pine_alert_level" data-l1key="configuration" data-l2key="pine_alert_level"></input>
                            <br>
                            <label for="plane">{{Platane}}</label><div class="pull-right"><span id="disp_plane"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPlane ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="plane" id="plane"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="plane_alert_level" data-l1key="configuration" data-l2key="plane_alert_level"></input>
                            <br>
                            <label for="poplar">{{Peuplier}}</label><div class="pull-right"><span id="disp_poplar"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPoplar ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="poplar" id="poplar"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="poplar_alert_level" data-l1key="configuration" data-l2key="poplar_alert_level"></input>
                            <br>
                            <label for="chenopod">{{Chénopodes}}</label><div class="pull-right"><span id="disp_chenopod"></span> part/m3</div> 
                            <input type="range" value="<?= $levelChenopod ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="chenopod" id="chenopod"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="chenopod_alert_level" data-l1key="configuration" data-l2key="chenopod_alert_level"></input>
                            <br>
                            <label for="mugwort">{{Mugwort / Armoises }}</label><div class="pull-right"><span id="disp_mugwort"></span> part/m3</div> 
                            <input type="range" value="<?= $levelPlane ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="mugwort" id="mugwort"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="mugwort_alert_level" data-l1key="configuration" data-l2key="mugwort_alert_level"></input>
                            <br>
                            <label for="nettle">{{Ortie}}</label><div class="pull-right"><span id="disp_nettle"></span> part/m3</div> 
                            <input type="range" value="<?= $levelNettle ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="nettle" id="nettle"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="nettle_alert_level" data-l1key="configuration" data-l2key="nettle_alert_level"></input>
                            <br>
                            <label for="ragweed">{{Ambroisie}}</label><div class="pull-right"><span id="disp_ragweed"></span> part/m3</div> 
                            <input type="range" value="<?= $levelMugwort ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="ragweed" id="ragweed"></input> 
                            <input type="hidden" class="eqLogicAttr form-control" id="ragweed_alert_level" data-l1key="configuration" data-l2key="ragweed_alert_level"></input>
                            <br>
                            <label for="others">{{Autres Pollens}}</label><div class="pull-right"><span id="disp_others"></span> part/m3</div>
                            <input type="range" value="<?= $levelOthers ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="others" id="others"></input>
                            <input type="hidden" class="eqLogicAttr form-control" id="others_alert_level" data-l1key="configuration" data-l2key="others_alert_level"></input>
                            <br><br>
                        </div> 
                    </div>

                </div>
                <!-- </div> -->
            </div>
            <!--/.tabpanel-->



        </div><!-- /.tab-content -->
    </div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<?php include_file('desktop', 'airquality', 'js', 'airquality'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>