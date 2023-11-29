RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NC=\033[0m # No Color

.PHONY: init
init: # Construire les images Docker
	mkdir -p src/images-source
	mkdir -p src/images-optim
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

.PHONY: clean-container
clean-container: # Supprimer les conteneurs, volumes, et images
	docker-compose down --rmi all --volumes

.PHONY: bundle
bundle: # Crée le bundle à déposer dans le FTP
	rm -rf ./bundle
	@echo "$(BLUE)Création du bundle...$(NC)"
	rsync -av ./src/ ./bundle/ \
		--exclude 'images-optim' \
		--exclude 'images-source' \
		--exclude 'composer.*'
	mkdir ./bundle/images-optim
	mkdir ./bundle/images-source
	@echo "$(GREEN) => Bundle terminé !$(NC)"

.PHONY: clean-bundle
clean-bundle: # Supprime le bundle
	rm -rf ./bundle

.PHONY: clean
clean: clean-bundle clean-container