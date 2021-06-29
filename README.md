# Plugin pour Jeedom Air Quality & Pollen

Le plugin Air Quality & Pollen donne des informations sur la qualité de l'air et les pollen présents dans l'air ambiant.  

Vous accèdez aux données live et prévisions sur 5 jours pour la pollution et 3 jour pour les pollens.

Vous pouver historiser vos données, le dashboard, affichera alors les moyennes, mini, maxi et tendances. En cliquant sur les moyennes vous accèder au graphique représentant ces chiffres

Il fonctionne sous **Jeedom** et est compatible avec la version 4.

<img  align="right" height="250" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/aqi2.jpg">Les données sont récupérées par deux API gratuites: Openwheather et Ambee. 

 - Elle sont gratuite dans une certaine limite d'appels par jour. Dans l'applis, pour Ambee, je vais aux limites du gratuit, c'est à dire 100 appels/jour.  

 - Cela fonctionne presque partout dans le monde et vous pouvez l'utilisez en vous géolocalisant automatiquement.

 - Les normes utilisées sont ceux de l'Agence européenne pour l'environnement 2021 : https://fr.wikipedia.org/wiki/Indice_de_qualit%C3%A9_de_l%27air

## Les indices et niveaux d'alerte

L'AQI en Europe est calculé de 1 (Bon) à 6 (Extrèmement mauvais) 

Pour les pollens les niveaux de danger généraux sont données par l'API ( de 1 Risque Null à 4 Risque très élevé ) pour les familles de pollens,
J'ai ajouté des niveaux alertes par pollens, ils sont basés sur un Mémoire de l’École Nationale de la Santé Publique et une étude de l'ENSP.

<img  align="left" height="250" src="https://github.com/OlivierMongeot/airquality/blob/Master/docs/photos/pollen.jpg">De la documentation sur mes sources est disponible dans le dossier pdf à la racine du projet.

Les alertes sont basé sur des seuils de 40 particules/m³, ce qui correspond au plancher de niveau de sensibilité de la plupart des personnes allergiques. Certains sujets hypersensibles, sont touchés dès 5 part/m³.
