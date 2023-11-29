.PHONY: init
init: # Construire les images Docker
	docker-compose build

.PHONY: up
up: # Démarrer les conteneurs
	docker-compose up -d

.PHONY: shell
shell: # Accéder à la console du conteneur PHP-FPM
	docker-compose exec php /bin/bash

.PHONY: down
down: # Arrêter les conteneurs
	docker-compose down

.PHONY: clean
clean: # Supprimer les conteneurs, volumes, et images
	docker-compose down --rmi all --volumes