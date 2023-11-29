# Smapi

Ce projet te donne tout ce dont tu as besoin pour faire tourner un environnement PHP 8 avec Nginx grâce à Docker. Il inclut un `Dockerfile`, un fichier de configuration Nginx (`nginx.conf`) et un `Makefile` pour simplifier les commandes Docker.

## Prérequis

- Avoir Docker installé sur ta machine (https://docs.docker.com/engine/install/)
- Cloner ce repo

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

Accéder à la ligne de commande (cli) du conteneur.

### bundle

Prépare un dossier `bundle` dont le contenu est à déposer dans le répertoire de l'API en ligne (ex: via votre FTP ou autre solution).

## Utilisation

1. Ouvre un terminal dans le dossier où se trouvent ces fichiers.
2. Lance `make init` pour construire l'image Docker (à la toute première utilisation).
3. Exécute `make up` pour démarrer le conteneur.
4. Accède à `http://localhost:1234` pour voir ton serveur en action.
5. Utilise `make down` pour arrêter le serveur lorsque tu as terminé.

## Où stocker les images ?

L'ensemble des images sources sont stockées dans un sous dossier par tématique dans `./src/images-sources/**/image.jpg`.

Note : pour l'instant le script ne fonctionne qu'avec un seul niveau de sous dossier.

## Routes disponibles

Les routes sont décrites dans la documentation openAPI `swagger.yaml`.

## CRONJOB

Pour synchronier et optimiser les images pour le web, configurer une cronjob (période à définir) sur la route :`http://localhost/cron-sync-and-optim.php`.

Le script peut être long à lancer pour la première fois et va timeout régulièrement. A chaque lancement il avancera dans son traitement, jusqu'a avoir tout synchronisé.

Les images sont à stocker dans le dossier ./src/images-sources, et seront copiées optimisées dans le répertoire ./src/images-optim.
