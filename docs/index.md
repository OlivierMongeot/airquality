# Plugin AirQuality

Le plugin Air Quality & Pollen donne des informations sur la qualité de l'air et les pollens présents dans l'air.

Vous accèdez aux niveaux en direct ainsi qu'aux prévisions sur 5 jours pour la pollution et 3 jours pour les pollens.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen.jpg">
</p>

Il fonctionne sous Jeedom et est compatible avec la version 4.

Les données sont récupérées par deux API gratuites: Openwheather et Ambee.

Elle sont gratuite dans une certaine limite d'appels par jour. Dans l'applis, pour Ambee, le plugin est conçu pour être aux limites du gratuit, c'est à dire 100 appels/jour.

Ce qui veux dire que les prévisons de pollens sont mises à jour une fois par jour, j'ai choisi 7h du matin, car seulement 24h sont en fait disponibles. Ce qui veux dire que pour le troisième jour de prévision, les infos vont jusqu'a 7h00 du matin et donc sont suceptiblent dévoluer fortement.

C'est un choix de ma part qui est fait pour ne pas atteindre la limite de 100 appels/jour de l'API Ambee et donc que le plugin reste gratuit à l'usage. 

En effet, lors du refresh des prévisons pollen l'api compte 1 appel par heure de données fournie, donc l'appel forecast sur 48 heures coute 48 appels sur 100 journalier. Ce qui explique, l'appel une fois par jour.  

Pour OpenWheather, leur limite d'appel par jour est beaucoup plus haut, donc pas de soucis de quotas. :) 

Cela fonctionne presque partout dans le monde et vous pouvez l'utilisez en vous géolocalisant automatiquement.

Les normes utilisées sont ceux de l'Agence européenne pour l'environnement 2021

De la documentation sur mes sources est disponible dans le dossier pdf à la racine du projet.

# Configuration du plugin

Après avoir installé le plugin, il faut l’activer puis renseigner votre clef api.

Pour obtenir votre clef api il faut aller [ici](https://home.openweathermap.org), créer un compte et ensuite il faut copier votre clef api dans la zone prévue sur la page de configuration du Plugin.


Pour afficher les moyennes, mini, maxi et tendances , Vous devez historiser vos données. 

En cliquant sur les moyennes vous accèder au graphique représentant ces données (fonction native de Jeedom)

Le graphique du bas donne le mini et maxi prévu par jour de la semaine pour chaque élément analysé.  


**Important**

L'AQI en Europe est désormais calculée de 1 (Bon) à 6 (Extrèmement mauvais)

Pour les pollens les niveaux de danger principaux sont donnés par l'API Ambee ( de 1 Risque Nul à 4 Risque très élevé ) .

J'ai ajouté personnellement des niveaux alertes par pollens, ils sont basés sur un Mémoire de l’École Nationale de la Santé Publique et une étude de l'ENSP  :

Les alertes sont basées sur des seuils de 40 particules/m³ , ce qui correspond au plancher de niveau de sensibilité de la plupart des personnes allergiques. Certains sujets hypersensibles, sont touchés dès 5 part/m³.  

J'ai donc défini comme 5 part/m3 le plancher d'alerte. A partir de 5, le risque est limité, et à partir de 40 le risque est élevé. Ces seuils sont valablent pour les personnes sensibles.

Por résumer, les personnes hypersensibles vont réagir à 5 part/m3 et les perssones sensibles à 40.  

**Note**

Il faut attendre quelques minutes avant de pouvoir récupérer des informations suite à la création du compte, le temps que la clef soit active.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">
</p>

# Configuration des équipements

Le rafraîchissement des données AQI s’effectue toutes les 30 minutes et le forecast tous les matin à 7h00

Le rafraîchissement des données Pollen s’effectue toutes les 60 minutes et le forecast tous les matin à 7h00


