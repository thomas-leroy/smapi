RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NC=\033[0m # No Color

.PHONY: init
init: # Build Docker images
	mkdir -p src/images-source
	mkdir -p src/images-optim
	docker-compose build

.PHONY: up
up: # Start the containers
	docker-compose up -d

.PHONY: shell
shell: # Access the PHP-FPM container console
	docker-compose exec php /bin/bash

.PHONY: down
down: # Stop the containers
	docker-compose down

.PHONY: clean-container
clean-container: # Remove containers, volumes, and images
	docker-compose down --rmi all --volumes

.PHONY: bundle
bundle: # Create the bundle to be placed on the FTP
	rm -rf ./bundle
	@echo "$(BLUE)Creating the bundle...$(NC)"
	rsync -av ./src/ ./bundle/ \
		--exclude 'images-optim' \
		--exclude 'images-source' \
		--exclude 'composer.*'
	mkdir ./bundle/images-optim
	mkdir ./bundle/images-source
	@echo "$(GREEN) => Bundle completed!$(NC)"

.PHONY: clean-bundle
clean-bundle: # Delete the bundle
	rm -rf ./bundle

.PHONY: clean
clean: clean-bundle clean-container
