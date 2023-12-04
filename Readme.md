# Smapi

<p align="center">
  <img src="https://github.com/thomas-leroy/smapi/blob/main/logo.png?raw=true" width="200">
</p>


L'objectif de ce projet est de produire une API la plus simple possible pour optimiser et diffuser des images.

Ce projet te donne tout ce dont tu as besoin pour faire tourner un environnement local PHP 8 avec Nginx grâce à Docker. Il inclut un `Dockerfile`, un fichier de configuration Nginx (`nginx.conf`) et un `Makefile` pour simplifier les commandes Docker.

Des commandes simples permettent de lancer rapidement le projet en local ou préparer la version prête à uploader.

## Prérequis

- Avoir Docker installé sur ta machine (https://docs.docker.com/engine/install/)
- Cloner ce repo `git clone git@github.com:thomas-leroy/smapi.git`

## Utilisation

1. Ouvre un terminal dans le dossier où se trouvent ces fichiers.
2. Lance `make init` pour construire l'image Docker (à la première utilisation).
3. Exécute `make up` pour démarrer le conteneur.
4. Accède à `http://localhost:1234` pour voir ton serveur en action.
5. Utilise `make down` pour arrêter le serveur lorsque tu as terminé.

## Où stocker les images ?

L'ensemble des images sources sont stockées dans un sous dossier par tématique dans `./src/images-sources/**/image.jpg`.

Il est possible de créer des sous-dossiers pour ranger les images dans images-sources. 

Les sous-dossiers sont utilisés dans les appels API.

Note : pour l'instant le script ne fonctionne qu'avec un seul niveau de sous dossier.

## Commandes Makefile

Les commandes sont accessibles pour effectuer des actions communes du projet.

Par exemple : `make up` pour lancer le projet.

### init

Cette commande construit une image Docker à partir du `Dockerfile`.

### up

Cette commande exécute le conteneur Docker à partir de l'image que tu as créée.

### down

Cette commande arrête et supprime le conteneur Docker en cours d'exécution.

### shell

Accéder à la ligne de commande (CLI) du conteneur.

### bundle

Prépare un dossier `bundle` dont le contenu est à déposer dans le répertoire de l'API en ligne (ex: via votre FTP ou autre solution).

## Routes disponibles

Les routes sont décrites dans la documentation openAPI `swagger.yaml`.

## CRONJOB : optimiser et compresser les images

Pour synchronier et optimiser les images pour le web, configurer une cronjob (période à définir) sur la route :`http://localhost/cron-sync-and-optim.php`.

Le script peut être long à lancer pour la première fois et va timeout régulièrement. A chaque lancement il avancera dans son traitement, jusqu'a avoir tout synchronisé.

Les images sont à stocker dans le dossier ./src/images-sources, et seront copiées optimisées dans le répertoire ./src/images-optim.
