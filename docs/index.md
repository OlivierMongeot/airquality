# Plugin AirQuality

Ce plugin Air Quality & Pollen vous informe sur la qualité de l'air et les pollens.

Vous accèdez aux données live et prévisions sur 5 jours pour la pollution et 3 jour pour les pollens.

Vos données peuvent être historisées, le dashboard, affichera alors les moyennes, mini, maxi et tendances.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen2.jpg">
</p>



Il fonctionne sous Jeedom et est compatible avec la version 4.

Les données sont récupérées par deux API gratuites: Openwheather et Ambee.

Elle sont gratuite dans une certaine limite d'appels par jour. Dans l'applis, pour Ambee, le plugin est conçu pour être aux limites du gratuit, c'est à dire 100 appels/jour.

Ce qui veux dire que les prévisons de pollens sont mises à jour une fois par jour seulement, j'ai choisi 7h du matin, car seulement 24h sont en fait disponibles. Ce qui veux dire que pour le troisième jour de prévision, les infos vont jusqu'a 7h00 du matin et donc sont suceptiblent dévoluer.

C'est un choix qui est fait pour ne pas atteindre la limite de 100 appels/jour de l'API Ambee et donc que le plugin reste gratuit à l'usage. 

En effet, lors du refresh des prévisons pollens l'api compte 1 appel par heure de données fournie, donc l'appel forecast sur 48 heures coûte 48 appels sur les 100 journalier. Ce qui explique mon choix d'appel une fois par jour.  

Pour OpenWheather, la limite d'appel par jour est beaucoup plus haut, donc pas de soucis de quotas. 

Les deux Api fonctionnent presque partout dans le monde et vous pouvez l'utilisez en vous géolocalisant automatiquement.

Les normes utilisées sont ceux de l'Agence européenne pour l'environnement 2021

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen.JPG">
</p>

# Configuration principale du plugin

Après avoir installé le plugin, il faut l’activer puis renseigner votre clef api.

Pour obtenir votre clef api OpenWheather il faut aller [ici](https://home.openweathermap.org), créer un compte gratuit et ensuite il faut copier votre clef api dans la zone prévue sur la page "Configuration du Plugin".

Pour obtenir la clef api ambee il faut aller [ici](https://api-dashboard.getambee.com/#/signup), c'est à peu près le même principe qu'avant : vous récupérez une clef  gratuite que vous coller dans la configuration du plugin".

# Configuration principale de l'équipement

Pour afficher les moyennes, mini, maxi et tendances sur le dashboard, Vous devez historiser vos données (cochez 'Historiser' dans la partie 'Commandes' de votre équipement).   

Vous avez quatre choix de localisation : 

1. Par ville 
2. Par longitute & latitude
3. Par géolocalisation automatique du navigateur (La plus pratique)
4. Grâce aux infos localisations Jeedom si elle sont déjà présentes dans la configuration générale du soft

Pour la localisation 'Par ville', vous devez vérifier que la ville est bien trouvée par l'appli en cliquant sur vérifier avant de sauvegarger. 
'Par longitude & latitude', pareil, vous devez vérifier que les coordonnées que vous entrez soient valables.

Vous pouvez également activer, le glissement automatique du carroussel, pour cela mettez Animation en 'activer' 

Ensuite choisissez Polluant ou Pollen. 

Vous pouvez créer plusieurs équipement pour plusieurs villes pour les données AQI. Par contre, pour les pollens, en faisant cela, vous dépassez directement le quota journalier. 

Vous devrez alors choisir une version payante de l'API Ambee.

# Utilisation

En cliquant sur les moyennes vous accèder au graphique représentant ces données (fonction native de Jeedom)

Le graphique du bas donne le mini et maxi prévu par jour de la semaine pour chaque élément analysé.  

Pour l'AQI, tous les polluants sont affichés par défault, il vous suffit de désactiver l'option "Afficher" dans la partie Commandes de votre équipement.   

Pour les pollens, tous sont affichés par défaut, le plugin, les classe automatiquement par ordre décroissant de risque et les pollens non détéctés(à zéro) sont affiché à part dans un tableau à la fin du caroussel.  


**Important**

L'AQI en Europe est désormais calculée de 1 (Bon) à 6 (Extrèmement mauvais)

Pour les pollens les niveaux de danger principaux sont donnés par l'API Ambee (de 1 Risque Nul à 4 Risque très élevé) .

J'ai ajouté personnellement des niveaux alertes par pollens, ils sont basés sur un Mémoire de l’École Nationale de la Santé Publique et une étude de l'ENSP  :

Les alertes sont basées sur des seuils de 40 particules/m³ , ce qui correspond au plancher de niveau de sensibilité de la plupart des personnes allergiques. Certains sujets hypersensibles, sont touchés dès 5 part/m³.  

J'ai donc défini comme 5 part/m3 le plancher d'alerte. A partir de 5, le risque est limité, et à partir de 40 le risque est élevé. Ces seuils sont valablent pour les personnes sensibles.

Por résumer, les personnes hypersensibles vont réagir à 5 part/m3 et les sensibles à 40.  

**Note**

Il faut attendre quelques minutes avant de pouvoir récupérer des informations suite à la création du compte, le temps que la clef soit active.

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi.JPG">
</p>

<p align="center">
  <img height="350" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">
</p>

Le rafraîchissement des données AQI s’effectue toutes les 30 minutes et le forecast tous les matin à 7h00

Le rafraîchissement des données Pollen s’effectue toutes les 60 minutes et le forecast tous les matin à 7h00


