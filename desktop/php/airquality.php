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
										}else {
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
									<a  id="validate-city" class="btn btn-sm btn-success"><i class="fas fa-check-circle"></i> {{Vérifier}}</a>
								</div>
							</div>

							<div id="geoloc-city-mode"><!-- Js --></div>

							<!-- longitude latitude mode -->
							<div class="form-group searchMode long_lat_mode">
								<label class="col-sm-3 control-label">{{Longitude}}</label>
								<div class="col-sm-6">
									<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="longitude" />
								</div>
							</div>
							<div class="form-group searchMode long_lat_mode">
								<label class="col-sm-3 control-label">{{Latitude}}</label>
								<div class="col-sm-6">
									<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="latitude" />
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
               
                            <div class="form-group">
								<label class="col-sm-3 control-label">{{Elements à afficher}}</label>
								<div class="col-sm-6">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="elements">
										<option value="polution">{{Polluant}}</option>
										<option value="pollen">{{Pollen}}</option>
										<!-- <option value="radiation">{{Radiation solaire}}</option> -->
									</select>
								</div>
							</div>
						</div>

						<!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">

								<div class="container">
									<!-- {{Trouver ses coordonnées sur  www.coordonnees-gps.fr}} -->
								</div>
							</div>
							<!--  -->
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

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->



<?php include_file('desktop', 'airquality', 'js', 'airquality'); ?>

<?php include_file('core', 'plugin.template', 'js'); ?>