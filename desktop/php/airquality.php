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
            <li role="presentation"><a href="#alerttab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-exclamation-circle"></i><span class="hidden-xs"> {{Paramétrage des alertes}}</span></a></li>
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
                                    </select>
                                </div>
                            </div>
                            <!-- city_mode Par Ville  -->
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

                            <!-- longitude latitude mode long_lat_mode-->
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
                            <!-- dynamic_mode Geoloc du navigateur -->
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Longitude }}</label>
                                <div class="col-sm-6">
                                    <input id="longitude" type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoLongitude" />
                                </div>
                            </div>
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Latitude}}</label>
                                <div class="col-sm-6">
                                    <input id="latitude" type="number"  class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoLatitude" />
                                </div>
                            </div>
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label">{{Ville}}</label>
                                <div class="col-sm-6">
                                    <input id="geoCity" type="text" disabled="disabled" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="geoCity" />
                                </div>
                            </div>
                            <div class="form-group searchMode dynamic_mode">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <a id="validate_dyn_mode" class="btn btn-sm btn-success"><i class="fas fa-map-marked-alt"></i> {{Géolocaliser}}</a>
                                </div>
                            </div>
                          
                            <br />
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Animation du caroussel}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="animation_aqi">
                                        <option value="disable_anim">{{Désactiver}}</option>
                                        <option value="slow_anim">{{Activer}}</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Mini, Maxi, Moyenne, Tendance, Statistiques}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="data_history">
                                        <option value="actived">{{Activer}}</option>
                                        <option value="disable">{{Désactiver}}</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Elements à afficher}}</label>
                                <div class="col-sm-6">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="elements">
                                        <?php
                                        if (config::byKey('apikey', 'airquality') !== '') {
                                            echo '<option value="polution">{{Polluants (AQI, Ozone, PM}}2.5{{, PM}}<sub>10</sub>{{, Monoxyde de carbone,...)}}</option>';
                                        } else {
                                            echo '<option disabled="disabled" value="no-api-key">{{AQI : Veuiller renseigner la clef Openwheather avant utilisation}}</option>';
                                        }
                                        if (config::byKey('apikeyAmbee', 'airquality') !== '') {
                                            echo '<option value="pollen">{{Pollens (Graminées, Orties, Ambroisies, Chénopodes, Bouleau,...)  }}</option>';
                                        } else {
                                            echo '<option disabled="disabled" value="no-pollen-key">{{Pollen : Veuiller renseigner la clef Ambee avant utilisation}}</option>';
                                        }
                                        ?>
                                        <!-- <option value="radiation">{{Radiation solaire}}</option> -->
                                    </select>
                                </div>
                            </div>
                            <br/>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-7">
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="long_lat_view" />{{Voir la ville, longitude & latitude sur le widget}}</label>
                                </div>
                            </div>
                            <br>
                            <?php
                            //   foreach ($eqLogics as $eqLogic) {
                            //    $eqLogic->getId() ;
                            //    log::add('airquality', 'debug', 'ID EqLogic : ' . $eqLogic->getId());
                            // }
                            // $idSave = 
                            // $id = random_int(1, 100000);

                            ?>
                            <style>
                                .range-slider.aqi .input-range.aqi {
                                    -webkit-appearance: none;
                                    height: 6px;
                                    border-radius: 5px;
                                    background: #ccc;
                                    outline: none;
                                    writing-mode: bt-lr;
                                }

                                .range-container {
                                    width: 90%;
                                }
                            </style>
                            <div class="form-group elements pollen">
                                <label class="col-sm-3 control-label">{{Afficher à partir de}}</label>
                                <div class="col-sm-6">
                                    <?php
                                    foreach ($eqLogics as $eqLogic) {
                                        $levelPollenDisplay_ = $eqLogic->getConfiguration('pollen_alert_level');
                                        if ($levelPollenDisplay_ != '') {
                                            $levelPollenDisplay = $levelPollenDisplay_;
                                        }
                                    }
                                    ?>
                                    <div class="pull-right range-slider aqi" style="margin-bottom:5px"><span id="disp_pollen"></span style="margin-bottom:3px"> part/m3</div>
                                    <div class="range-slider aqi">
                                        <input type="range" value="<?= $levelPollenDisplay ?>" min="0" max="40" class="input-range aqi" orient="horizontal" name="pollen" id="pollen"></input>
                                        <input type="hidden" class="eqLogicAttr form-control" id="pollen_alert_level" data-l1key="configuration" data-l2key="pollen_alert_level"></input>
                                    </div>
                                    <br>
                                </div>
                            </div>
                        </div>
                        <!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
                        <div class="col-lg-6">
                            <legend><i class="fas fa-info"></i> {{Informations}}</legend>
                            <div class="form-group elements polution">
                                <div class="container">
                                    <h5>Code Couleur utilisé pour l'AQI et les polluants</h5>
                                </div>
                                <br>
                                <div class="container">
                                    <div>
                                        <input type="color" name="body" disabled value="#662D91">
                                        <label for="body">Extrême</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#B00000">
                                        <label for="body">Très mauvais</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#E79C00">
                                        <label for="body">Mauvais</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#EFE800">
                                        <label for="body">Dégradé</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#00BD01">
                                        <label for="body">Correct</label>
                                        <br><br>
                                        <input type="color" name="head" disabled value="#00AEEC">
                                        <label for="head">Bon</label>
                                    </div>
                                    <br>
                                    <br>
                                </div>
                            </div>
                            <!--  -->
                            <div class="form-group elements pollen">
                                <div class="container">
                                    <h6>{{Code Couleur Pollen utilisé dans le plugin}}</h6>
                                </div>
                                <br>
                                <div class="container">
                                    <div>
                                        <input type="color" name="body" disabled value="#B00000">
                                        <label for="body">Risque très haut</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#E79C00">
                                        <label for="body">Risque haut</label>
                                        <br><br>
                                        <input type="color" name="body" disabled value="#EFE800">
                                        <label for="body">Risque modéré</label>
                                        <br><br>
                                        <input type="color" name="head" disabled value="#00BD01">
                                        <label for="head">Risque bas</label>
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

            <!-- Onglet Niveaux des Alertes -->
            <div role="tabpanel" class="tab-pane" id="alerttab">

                <div class="col-lg-7" style="padding:10px 35px">
                    <legend>
                        <span> Plancher de déclenchement des alertes</span>
                    </legend>
                    <br />
                    <div class="container range-container aqi">

                        <div class="form-group elements polution col-lg-12">
                            <!-- <div class="col-lg-6"> -->

                            <!-- table  -->
                            <table class="table table-bordered table-condensed" style="text-align:center">
                                <thead>
                                    <tr>
                                        <th style="text-align:center">{{Éléments à suivre}}</th>
                                        <th style="text-align:center">{{Niveau de déclenchement des alertes}}</th>
                                        <th style="text-align:center">{{Synchroniser alerte et affichage}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label">{{AQI}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="aqi_alert_level">
                                                <option value="1">{{Bon}} : Indice 1</option>
                                                <option value="2">{{Correct}} : Indice 2</option>
                                                <option value="3">{{Dégradé}} : Indice 3</option>
                                                <option value="4">{{Mauvais}} : Indice 4</option>
                                                <option value="5">{{Très mauvais}} : Indice 5</option>
                                                <option value="6">{{Extrême}} : Indice 6</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="aqi_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{PM}}<sub>25</sub></label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pm25_alert_level">
                                                <<option value="0">{{Bon}} : 0 - 10 μg/m3</option>
                                                    <option value="10">{{Correct}} : 10 - 20 μg/m3</option>
                                                    <option value="20">{{Dégradé}} : 20 - 25 μg/m3</option>
                                                    <option value="25">{{Mauvais}} : 25 - 50 μg/m3</option>
                                                    <option value="50">{{Très mauvais}} : 50 - 75 μg/m3</option>
                                                    <option value="75">{{Extrême}} : + 75 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="pm25_synchro" />
                                        </td>
                                    </tr>

                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{PM}}<sub>10</sub></label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pm10_alert_level">
                                                <option value="0">{{Bon}} : 0 - 20 μg/m3</option>
                                                <option value="20">{{Correct}} : 20 - 40 μg/m3</option>
                                                <option value="40">{{Dégradé}} : 40 - 50 μg/m3</option>
                                                <option value="50">{{Mauvais}} : 50 - 100 μg/m3</option>
                                                <option value="100">{{Très mauvais}} : 100 - 150 μg/m3</option>
                                                <option value="150">{{Extrême}} : + 150 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="pm10_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{NO²}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="no2_alert_level">
                                                <option value="0">{{Bon}} : 0 - 40 μg/m3</option>
                                                <option value="40">{{Correct}} : 40 - 90 μg/m3</option>
                                                <option value="90">{{Dégradé}} : 90 - 120 μg/m3</option>
                                                <option value="120">{{Mauvais}} : 120 - 230 μg/m3</option>
                                                <option value="230">{{Très mauvais}} : 230 - 340 μg/m3</option>
                                                <option value="340">{{Extrême}} : + 340 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="no2_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{O³}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="o3_alert_level">
                                                <option value="0">{{Bon}} : 0 - 50 µg/m3</option>
                                                <option value="50">{{Correct}} : 50 - 100 µg/m3</option>
                                                <option value="100">{{Dégradé}} : 100 - 130 μg/m3</option>
                                                <option value="130">{{Mauvais}} : 130 - 240 μg/m3</option>
                                                <option value="240">{{Très mauvais}} : 240 - 380 μg/m3</option>
                                                <option value="380">{{Extrême}} : + 380 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="o3_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{SO²}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="so2_alert_level">
                                                <option value="0">{{Bon}} : 0 - 100 μg/m3 </option>
                                                <option value="100">{{Correct}} : 100 - 200 μg/m3 </option>
                                                <option value="200">{{Dégradé}} : 200 - 350 μg/m3</option>
                                                <option value="350">{{Mauvais}} : 350 - 500 μg/m3 </option>
                                                <option value="500">{{Très mauvais}} : 500 - 750 μg/m3</option>
                                                <option value="750">{{Extrême}} : + 750 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="so2_synchro" />
                                        </td>
                                    </tr>

                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{CO}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="co_alert_level">
                                                <option value="0">{{Bon}} : 0 - 360 μg/m3</option>
                                                <option value="360">{{Correct}} : 360 - 700 μg/m3</option>
                                                <option value="700">{{Dégradé}} : 700 - 100000 μg/m3</option>
                                                <option value="100000">{{Mauvais}} : 100000 - 250000 μg/m3</option>
                                                <option value="250000">{{Très mauvais}} : 250000 - 500000 μg/m3</option>
                                                <option value="500000">{{Extrême}} : + 500000 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="co_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label ">{{SO}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="so_alert_level">
                                                <option value="0">{{Bon}} : 0 - 100 μg/m3 </option>
                                                <option value="100">{{Correct}} : 100 - 200 μg/m3 </option>
                                                <option value="200">{{Dégradé}} : 200 - 350 μg/m3</option>
                                                <option value="350">{{Mauvais}} : 350 - 500 μg/m3 </option>
                                                <option value="500">{{Très mauvais}} : 500 - 750 μg/m3</option>
                                                <option value="750">{{Extrême}} : + 750 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="so_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label">{{NO}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="no_alert_level">
                                                <option value="0">{{Bon}} : 0 - 30 μg/m3 </option>
                                                <option value="30">{{Correct}} : 30 - 50 μg/m3 </option>
                                                <option value="50">{{Dégradé}} : 50 - 200 μg/m3</option>
                                                <option value="200">{{Mauvais}} : 200 - 300 μg/m3</option>
                                                <option value="300">{{Très mauvais}} : 300 - 600 μg/m3</option>
                                                <option value="600">{{Extrême}} : + 600 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="no_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label">{{NH³}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nh3_alert_level">
                                                <option value="0">{{Bon}} : 0 - 3 μg/m3</option>
                                                <option value="3">{{Correct}} : 3 - 7 μg/m3</option>
                                                <option value="7">{{Dégradé}} : 7 - 30 μg/m3</option>
                                                <option value="30">{{Mauvais}} : 30 - 100 μg/m3</option>
                                                <option value="100">{{Très mauvais}} : 100 - 300 μg/m3</option>
                                                <option value="300">{{Extrême}} : +300 μg/m3</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="nh3_synchro" />
                                        </td>
                                    </tr>
                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label">{{Indice UV}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="uv_alert_level">
                                                <option value="0">{{Nul}}: Indice 0</option>
                                                <option value="0.1">{{Faible}} : Indice 0 à 3 </option>
                                                <option value="3">{{Modéré}} : Indice 3 à 6 </option>
                                                <option value="6">{{Élevé}} : Indice 6 à 8 </option>
                                                <option value="8">{{Très élevé}} : Indice 8 à 11 </option>
                                                <option value="11">{{Extrême}} : Indice > 11 </option>
                                            </select>
                                        </td>
                                        <td>
                                            <!-- <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="uv_synchro" checked/> -->
                                        </td>
                                    </tr>

                                    <tr style="height: 50px !important;">
                                        <td>
                                            <label class="control-label">{{Visibilité}}</label>
                                        </td>
                                        <td>
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="visibility_alert_level">
                                                <option value="700">{{Très mauvaise}} : 0 - 700 m</option>
                                                <option value="3210">{{Mauvaise}} : 700 - 3210 m</option>
                                                <option value="8000">{{Moyenne}} : 3210 - 8000 m</option>
                                                <option value="10000">{{Bonne}} : + 8000 m</option>
                                            </select>
                                        </td>
                                        <td>
                                            <!-- <input type="checkbox" class="eqLogicAttr" data-l1key="visibility_synchro" checked /> -->
                                        </td>
                                    </tr>



                                </tbody>
                            </table>

                            <!-- </div> -->
                        </div>



                        <div class="container range-container pollen">

                            <div class="form-group elements pollen">
                                <div class="range-slider aqi">
                                    <?php
                                    foreach ($eqLogics as $eqLogic) {
                                        $levelPoaceae_ = $eqLogic->getConfiguration('poaceae_alert_level');
                                        $levelElm_ = $eqLogic->getConfiguration('elm_alert_level');
                                        $levelAlder_ = $eqLogic->getConfiguration('alder_alert_level');
                                        $levelBirch_ = $eqLogic->getConfiguration('birch_alert_level');
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
                                        if ($levelPoaceae_ != '') {
                                            $levelPoaceae = $levelPoaceae_;
                                        }
                                        if ($levelElm_ != '') {
                                            $levelElm = $levelElm_;
                                        }
                                        if ($levelAlder_ != '') {
                                            $levelAlder = $levelAlder_;
                                        }
                                        if ($levelBirch_ != '') {
                                            $levelBirch = $levelBirch_;
                                        }
                                        if ($levelCypress_ != '') {
                                            $levelCypress = $levelCypress_;
                                        }
                                        if ($levelOak_ != '') {
                                            $levelOak = $levelOak_;
                                        }
                                        if ($levelHazel_ != '') {
                                            $levelHazel = $levelHazel_;
                                        }
                                        if ($levelPine_ != '') {
                                            $levelPine = $levelPine_;
                                        }
                                        if ($levelPlane_ != '') {
                                            $levelPlane = $levelPlane_;
                                        }
                                        if ($levelPoplar_ != '') {
                                            $levelPoplar = $levelPoplar_;
                                        }
                                        if ($levelChenopod_ != '') {
                                            $levelChenopod = $levelChenopod_;
                                        }
                                        if ($levelMugwort_ != '') {
                                            $levelMugwort = $levelMugwort_;
                                        }
                                        if ($levelNettle_ != '') {
                                            $levelNettle = $levelNettle_;
                                        }
                                        if ($levelRagweed_ != '') {
                                            $levelRagweed = $levelRagweed_;
                                        }
                                        if ($levelOthers_ != '') {
                                            $levelOthers = $levelOthers_;
                                        }
                                    }
                                    ?>
                                    <label for="poaceae">{{Graminées / Poacées}}</label>
                                    <div class="pull-right"><span id="disp_poaceae"></span> part/m3</div>
                                    <input type="range" value="<?= $levelPoaceae ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="poaceae" id="poaceae"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="poaceae_alert_level" data-l1key="configuration" data-l2key="poaceae_alert_level"></input>
                                    <br>
                                    <label for="elm">{{Orme}}</label>
                                    <div class="pull-right"><span id="disp_elm"></span> part/m3</div>
                                    <input type="range" value="<?= $levelElm ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="elm" id="elm"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="elm_alert_level" data-l1key="configuration" data-l2key="elm_alert_level"></input>
                                    <br>
                                    <label for="alder">{{Aulne}}</label>
                                    <div class="pull-right"><span id="disp_alder"></span> part/m3</div>
                                    <input type="range" value="<?= $levelAlder ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="alder" id="alder"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="alder_alert_level" data-l1key="configuration" data-l2key="alder_alert_level"></input>
                                    <br>
                                    <label for="birch">{{Bouleau}}</label>
                                    <div class="pull-right"><span id="disp_birch"></span> part/m3</div>
                                    <input type="range" value="<?= $levelBirch ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="birch" id="birch"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="birch_alert_level" data-l1key="configuration" data-l2key="birch_alert_level"></input>
                                    <br>
                                    <label for="cypress">{{Cyprès}}</label>
                                    <div class="pull-right"><span id="disp_cypress"></span> part/m3</div>
                                    <input type="range" value="<?= $levelCypress ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="cypress" id="cypress"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="cypress_alert_level" data-l1key="configuration" data-l2key="cypress_alert_level"></input>
                                    <br>
                                    <label for="oak">{{Chêne}}</label>
                                    <div class="pull-right"><span id="disp_oak"></span> part/m3</div>
                                    <input type="range" value="<?= $levelOak ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="oak" id="oak"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="oak_alert_level" data-l1key="configuration" data-l2key="oak_alert_level"></input>
                                    <br>
                                    <label for="hazel">{{Noisetier}}</label>
                                    <div class="pull-right"><span id="disp_hazel"></span> part/m3</div>
                                    <input type="range" value="<?= $levelHazel ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="hazel" id="hazel"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="hazel_alert_level" data-l1key="configuration" data-l2key="hazel_alert_level"></input>
                                    <br>
                                    <label for="pine">{{Pin}}</label>
                                    <div class="pull-right"><span id="disp_pine"></span> part/m3</div>
                                    <input type="range" value="<?= $levelPine ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="pine" id="pine"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="pine_alert_level" data-l1key="configuration" data-l2key="pine_alert_level"></input>
                                    <br>
                                    <label for="plane">{{Platane}}</label>
                                    <div class="pull-right"><span id="disp_plane"></span> part/m3</div>
                                    <input type="range" value="<?= $levelPlane ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="plane" id="plane"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="plane_alert_level" data-l1key="configuration" data-l2key="plane_alert_level"></input>
                                    <br>
                                    <label for="poplar">{{Peuplier}}</label>
                                    <div class="pull-right"><span id="disp_poplar"></span> part/m3</div>
                                    <input type="range" value="<?= $levelPoplar ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="poplar" id="poplar"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="poplar_alert_level" data-l1key="configuration" data-l2key="poplar_alert_level"></input>
                                    <br>
                                    <label for="chenopod">{{Chénopodes}}</label>
                                    <div class="pull-right"><span id="disp_chenopod"></span> part/m3</div>
                                    <input type="range" value="<?= $levelChenopod ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="chenopod" id="chenopod"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="chenopod_alert_level" data-l1key="configuration" data-l2key="chenopod_alert_level"></input>
                                    <br>
                                    <label for="mugwort">{{Mugwort / Armoises }}</label>
                                    <div class="pull-right"><span id="disp_mugwort"></span> part/m3</div>
                                    <input type="range" value="<?= $levelMugwort ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="mugwort" id="mugwort"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="mugwort_alert_level" data-l1key="configuration" data-l2key="mugwort_alert_level"></input>
                                    <br>
                                    <label for="nettle">{{Ortie}}</label>
                                    <div class="pull-right"><span id="disp_nettle"></span> part/m3</div>
                                    <input type="range" value="<?= $levelNettle ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="nettle" id="nettle"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="nettle_alert_level" data-l1key="configuration" data-l2key="nettle_alert_level"></input>
                                    <br>
                                    <label for="ragweed">{{Ambroisie}}</label>
                                    <div class="pull-right"><span id="disp_ragweed"></span> part/m3</div>
                                    <input type="range" value="<?= $levelRagweed ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="ragweed" id="ragweed"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="ragweed_alert_level" data-l1key="configuration" data-l2key="ragweed_alert_level"></input>
                                    <br>
                                    <label for="others">{{Autres Pollens}}</label>
                                    <div class="pull-right"><span id="disp_others"></span> part/m3</div>
                                    <input type="range" value="<?= $levelOthers ?>" min="1" max="250" class="input-range aqi" orient="horizontal" name="others" id="others"></input>
                                    <input type="hidden" class="eqLogicAttr form-control" id="others_alert_level" data-l1key="configuration" data-l2key="others_alert_level"></input>
                                    <br><br>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- <script type="text/javascript">
                    getSlider('poaceae');
                    getSlider('elm');
                    getSlider('alder');
                    getSlider('birch');
                    getSlider('cypress');
                    getSlider('oak');
                    getSlider('hazel');
                    getSlider('pine');
                    getSlider('plane');
                    getSlider('poplar');
                    getSlider('chenopod');
                    getSlider('mugwort');
                    getSlider('nettle');
                    getSlider('ragweed');
                    getSlider('others');
                    getSlider('pollen');


                    function getSlider(element) {
                        var slider = document.getElementById(element);
                        var output = document.getElementById("disp_" + element);
                        var inputVal = document.getElementById(element + "_alert_level");
                        output.innerHTML = slider.value;
                        // Update the current slider value and the input 
                        slider.oninput = function () {
                            output.innerHTML = this.value;
                            inputVal.value = this.value;
                        }
                    }
                </script> -->

                <div class="col-lg-5" style="padding:15px 35px">
                    <legend>
                        <span style="text-align:left">Options des alertes</span>
                    </legend>
                    <br /><br />
                    <div class="container">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{Alertes dans le centre des messages}}</label>
                            <div class="col-sm-1">
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="alert_notification" />{{activer}}
                                </label>
                            </div>
                        </div>
                        <br /><br />
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{Alertes détaillées}}</label>
                            <div class="col-sm-1">
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="alert_details" />{{activer}}
                                </label>
                            </div>
                        </div>
                        <br>
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