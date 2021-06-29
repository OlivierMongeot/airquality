# Plugin AirQuality

Le plugin Air Quality & Pollen donne des informations sur la qualité de l'air et les pollens présents dans l'air ambiant.

Vous accèdez aux niveaux actuels et aux prévisions sur 5 jours pour la pollution et 3 jours pour les pollens.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen.jpg">
</p>

Il fonctionne sous Jeedom et est compatible avec la version 4.

Les données sont récupérées par deux API gratuites: Openwheather et Ambee.

Elle sont gratuite dans une certaine limite d'appels par jour. Dans l'applis, pour Ambee, je vais aux limites du gratuit, c'est à dire 100 appels/jour.

Cela fonctionne presque partout dans le monde et vous pouvez l'utilisez en vous géolocalisant automatiquement.

Les normes utilisées sont ceux de l'Agence européenne pour l'environnement 2021

De la documentation sur mes sources est disponible dans le dossier pdf à la racine du projet.

# Configuration du plugin

Après avoir installé le plugin, il faut l’activer puis renseigner votre clef api.

Pour obtenir votre clef api il faut aller [ici](https://home.openweathermap.org), créer un compte et ensuite il faut copier votre clef api dans la zone prévue sur la page de configuration du Plugin.

Vous pouver historiser vos données, le dashboard, affichera alors les moyennes, mini, maxi et tendances. En cliquant sur les moyennes vous accèder au graphique représentant ces chiffres

**Important**

L'AQI en Europe est calculé de 1 (Bon) à 6 (Extrèmement mauvais)

Pour les pollens les niveaux de danger généraux sont données par l'API Ambee ( de 1 Risque Nul à 4 Risque très élevé ) pour les familles de pollens, J'ai ajouté des niveaux alertes par pollens, ils sont basés sur un Mémoire de l’École Nationale de la Santé Publique et une étude de l'ENSP :

Les alertes sont basé sur des seuils de 40 particules/m³, ce qui correspond au plancher de niveau de sensibilité de la plupart des personnes allergiques. Certains sujets hypersensibles, sont touchés dès 5 part/m³.  

Il faut attendre quelques minutes avant de pouvoir récupérer des informations suite à la création du compte, le temps que la clef soit active.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">
</p>

# Configuration des équipements

Le rafraîchissement des données AQI s’effectue toutes les 30 minutes et le forecast tous les matin à 7h00

Le rafraîchissement des données Pollen s’effectue toutes les 60 minutes et le forecast tous les matin à 7h00


