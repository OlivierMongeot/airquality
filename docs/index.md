# Plugin Jeedom AirQuality

Ce plugin Air Quality & Pollen vous informe sur la qualité de l'air et les pollens.

Il s'adresse essentiellement aux personnes sensibles, allergiques, asthmatiques et sportifs, mai aussi à tous ceux qui prennent attention à l'air qu'il respire. 

Grâce aux informations des  prévisons et données live, vous pouvez manager votre planning et vous protéger plus efficacement.  

Vous accèdez aux données en direct et aux prévisions sur 5 jours pour la pollution et sur 3 jours pour les pollens.

Les Mini 10 jours, Maxi 10 jours, Moyenne 10 jours et Tendance 12h sont affichés par défault.

Cela historise aussi les données et donne accès la représentation graphique.

Vous pouvez les désactiver en décochant 'Historiser' une fois les commandes créées dans l'onglet 'Commandes'

Des alertes sont disponibles. Elles sont affichés à intervalles régulières sur le dashboard. Vous pouvez régler le niveaux des alertes.

Elles sont formatées pour Telegram et SMS : deux commandes infos sont disponibles avec des infos d'alertes mises à jour. Elles peuvent vous servir pour lancer un scénario d'envoi de message facilement ( ex : envoi si un message est disponible ( != '') dans la commande/info ()



<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen.png" class="img-responsive" alt="Pollen">
</p>

Il fonctionne sous Jeedom et est compatible avec la version 4.

Les données sont récupérées par deux API gratuites: Openwheather et Ambee.

Elles sont gratuites dans une certaine limite d'appels par jour. Dans l'applis, pour Ambee, le plugin est conçu pour être aux limites du gratuit, c'est à dire 100 appels/jour.

Ce qui veux dire que les prévisons de pollens sont mises à jour une fois par jour seulement, j'ai choisi 7h du matin, car seulement 24h sont en fait disponibles. Ce qui veux dire que pour le troisième jour de prévision, les infos vont jusqu'a 7h00 du matin et donc sont suceptiblent dévoluer en s'affinant.

C'est un choix qui est fait pour ne pas atteindre la limite de 100 appels/jour de l'API Ambee et donc que le plugin reste gratuit à l'usage. 

En effet, lors du refresh des prévisons pollens l'api compte 1 appel par heure de données fournie, donc l'appel forecast sur 48 heures coûte 48 appels sur les 100 journalier. Ce qui explique mon choix d'appel une fois par jour.  

Pour OpenWheather, la limite d'appel par jour est beaucoup plus haut, donc pas de soucis de quota. 

Les deux Api fonctionnent presque partout dans le monde et vous pouvez l'utilisez en vous géolocalisant automatiquement.

Les normes utilisées sont ceux de l'Agence européenne pour l'environnement 2021.

<p align="center">
<img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen2.jpg" class="img-responsive" alt="Pollen">
</p>

# Configuration principale du plugin

Après avoir installé le plugin, il faut l’activer puis renseigner votre clef api.

Si vous avez déjà une clef pour le plugin Weather officiel de Jeedom, la clef s'importe dans le plugin en cliquant sur import. Vous n'avez donc pas besoin de nouvelle clef.    

Pour obtenir une clef api OpenWheather il faut aller [ici](https://home.openweathermap.org), créer un compte gratuit et ensuite il faut copier votre clef api dans la zone prévue sur la page "Configuration du Plugin".

Pour obtenir la clef api ambee il faut aller [ici](https://api-dashboard.getambee.com/#/signup), c'est à peu près le même principe qu'avant : vous récupérez une clef  gratuite que vous coller dans la configuration du plugin".

# Configuration principale de l'équipement


Vous avez quatre choix de localisation : 

1. Par ville  : vous rentrez le nom d'une ville et son code pays, vous testez si l'API reconnait bien cette ville, puis vous enregister.
2. Par longitute & latitude : Si vous avez dèjà des coordonnées, vous pouvez les rentrées ici, cela permet aussi de retrouver le nom du lieu avec les coordonnées.
3. Par géolocalisation automatique du navigateur : rapide et pratique si vous accèdez à votre jeedom en déplacement ou en vacances, il se base sur les coordonnées du navigateur.
4. Grâce aux infos localisations Jeedom si elle sont déjà présentes dans la configuration générale du soft


Pour la localisation 'Par ville', vous devez vérifier que la ville est bien trouvée par l'appli en cliquant sur vérifier avant de sauvegarger. 
'Par longitude & latitude', pareil, vous devez vérifier que les coordonnées que vous entrez soient valables.

Vous pouvez également activer, le glissement automatique du carroussel, pour cela mettez Animation en 'activer' 

Ensuite choisissez Polluant ou Pollen. 

Vous pouvez créer plusieurs équipement pour plusieurs villes pour les données AQI. Par contre, pour les pollens, en faisant cela, vous dépassez directement le quota journalier. 

Vous devrez alors choisir une version payante de l'API Ambee.

Pour afficher les moyennes, mini, maxi et tendances sur le dashboard, Vous devez historiser vos données (cochez 'Historiser' sur les éléments dans la partie 'Commandes' de votre équipement, une fois l'équipement créé).   

# Utilisation

En cliquant sur les moyennes vous accèder au graphique représentant ces données (fonction native de Jeedom)

Le graphique du bas donne le mini et maxi prévu par jour de la semaine pour chaque élément analysé.

Pour l'AQI, tous les polluants sont affichés par défault, il vous suffit de désactiver l'option "Afficher" dans la partie Commandes de votre équipement pour ne plus l'afficher.   

Pour les pollens, tous sont affichés par défaut, le plugin les classe automatiquement par ordre décroissant de risque.

Les pollens non détéctés (à zéro) sont affichés à part dans un tableau à la fin du caroussel.  

Il existe une version mobile qui reprend pratiquement exactement la vue du dashboard.  

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">
</p>

**Important**

L'AQI en Europe est désormais calculée de 1 (Bon) à 6 (Extrèmement mauvais)

J'ai utilisé les code couleur officiel pour l'affichage : [voir ici](https://fr.wikipedia.org/wiki/Indice_de_qualit%C3%A9_de_l%27air)

Pour les pollens les niveaux de danger principaux sont donnés par l'API Ambee (de 1 Risque nul à 4 Risque très élevé).

J'ai rajouté des niveaux alertes par pollens, les calculs sont basés sur un Mémoire de l’École Nationale de la Santé Publique et une étude de l'ENSP (pdf dans le repertoire docs) :

Les alertes sont basées sur des seuils de 40 particules/m³ , ce qui correspond au plancher de niveau de sensibilité de la plupart des personnes allergiques. Certains sujets hypersensibles, sont touchés dès 5 part/m³.  

J'ai donc défini comme 5 part/m3 le plancher d'alerte. A partir de 5, le risque est limité, et à partir de 40 le risque est élevé. Ces seuils sont valablent pour les personnes sensibles.

Pour résumer, les personnes hypersensibles peuvent réagir dès 5 part/m3 et les sensibles dès 40.  

**Note**

Le rafraîchissement des données AQI s’effectue toutes les 30 minutes et le forecast tous les matin à 7h00

Le rafraîchissement des données Pollen s’effectue toutes les 60 minutes et le forecast tous les matin à 7h00

Ce plugin est simplement informatif, il ne remplace en aucun cas pas d'éventuels indications données par votre médecin ou tout autre corps médicale.

Lors des mise à jours, vous pouvez vous retrouver en dépassement de quota journalier de données gratuites. C'est normal, il faut attendre le lendemain matin pour que tout rentre dans l'ordre.
