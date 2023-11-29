# Smapi

Ce projet te donne tout ce dont tu as besoin pour faire tourner un environnement PHP 8 avec Nginx grâce à Docker. Il inclut un `Dockerfile`, un fichier de configuration Nginx (`nginx.conf`) et un `Makefile` pour simplifier les commandes Docker.

## Prérequis

- Avoir Docker installé sur ta machine (https://docs.docker.com/engine/install/)

## Structure du Projet

- `Dockerfile` : Contient les instructions pour créer l'image Docker avec PHP 8 et Nginx.
- `nginx.conf` : Fichier de configuration pour le serveur Nginx.
- `Makefile` : Fichier qui contient des commandes simplifiées pour construire et gérer le conteneur Docker.
- `/src` : Le dossier qui sera monté comme racine du serveur web.

## Commandes Makefile

### build

Cette commande construit une image Docker à partir du `Dockerfile`.


### up

Cette commande exécute le conteneur Docker à partir de l'image que tu as créée.


### stop

Cette commande arrête et supprime le conteneur Docker en cours d'exécution.


## Utilisation

1. Ouvre un terminal dans le dossier où se trouvent ces fichiers.
2. Lance `make build` pour construire l'image Docker (à la toute première utilisation).
3. Exécute `make run` pour démarrer le conteneur.
4. Accède à `http://localhost:1234` pour voir ton serveur en action.
5. Utilise `make stop` pour arrêter le serveur lorsque tu as terminé.


## Routes disponibles

