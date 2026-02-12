.PHONY: help build up down restart logs shell composer node assets watch test behat twigcs clean

# Variables
DOCKER_COMPOSE = docker compose
DOCKER = sudo docker
PHP_CONTAINER = sylius_php
NODE_CONTAINER = sylius_node
NODE_SERVICE = node
MYSQL_CONTAINER = sylius_mysql

## —— Docker Commands ——————————————————————————————————————
help: ## Affiche cette aide
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

build: ## Construit les containers Docker
	$(DOCKER_COMPOSE) build

up: ## Démarre tous les containers
	$(DOCKER_COMPOSE) up -d

down: ## Arrête tous les containers
	$(DOCKER_COMPOSE) down

restart: down up ## Redémarre tous les containers

logs: ## Affiche les logs en temps réel
	$(DOCKER_COMPOSE) logs -f

status: ## Affiche le statut des containers
	$(DOCKER_COMPOSE) ps

## —— Application Commands ——————————————————————————————————
install: build up composer-update assets-install assets-build db-setup ## Installation complète du projet
	@echo "\n✅ Installation terminée !"
	@echo "🌐 Application: http://localhost:8080"
	@echo "📧 MailHog: http://localhost:8025"
	@echo "🔍 Selenium VNC: http://localhost:7900"
	@echo "\n💡 Pour développer:"
	@echo "   make dev         # Lance l'environnement"
	@echo "   make assets-watch # Surveille et recompile les assets"

shell: ## Ouvre un shell dans le container PHP
	$(DOCKER) exec $(PHP_CONTAINER) bash

shell-node: ## Ouvre un shell dans le container Node
	$(DOCKER) exec $(NODE_CONTAINER) sh

## —— Composer / PHP ————————————————————————————————————————
composer-install: ## Installe les dépendances PHP
	@echo "📦 Installation des dépendances PHP..."
	$(DOCKER) exec $(PHP_CONTAINER) composer install --no-interaction
	@echo "✅ Dépendances PHP installées"

composer-update: ## Met à jour les dépendances PHP et le lock file
	@echo "🔄 Mise à jour des dépendances PHP..."
	$(DOCKER) exec $(PHP_CONTAINER) composer update --no-interaction
	@echo "✅ Dépendances PHP mises à jour"

composer-require: ## Installe un package PHP (usage: make composer-require PKG=vendor/package)
	$(DOCKER) exec $(PHP_CONTAINER) composer require $(PKG)

twigcs: ## Vérifie la qualité du code Twig
	$(DOCKER) exec $(PHP_CONTAINER) bin/twigcs --twig-version=2 app/Resources/ --severity error

twigcs-all: ## Vérifie tous les templates Twig
	$(DOCKER) exec $(PHP_CONTAINER) bin/twigcs --twig-version=2 app/Resources/

phpstan: ## Analyse statique du code PHP
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/phpstan analyse

## —— Assets / Frontend —————————————————————————————————————
assets-install: ## Installe les dépendances Node.js
	$(DOCKER_COMPOSE) run --rm $(NODE_SERVICE) yarn install

assets-build: ## Compile les assets (production)
	$(DOCKER_COMPOSE) run --rm $(NODE_SERVICE) yarn build

assets-build-admin: ## Compile les assets admin uniquement
	$(DOCKER_COMPOSE) run --rm $(NODE_SERVICE) yarn build:admin

assets-build-shop: ## Compile les assets shop uniquement
	$(DOCKER_COMPOSE) run --rm $(NODE_SERVICE) yarn build:shop

assets-watch: ## Surveille et recompile les assets (développement)
	$(DOCKER) exec -it $(NODE_CONTAINER) yarn watch

assets-watch-admin: ## Surveille les assets admin
	$(DOCKER) exec -it $(NODE_CONTAINER) yarn watch:admin

assets-watch-shop: ## Surveille les assets shop
	$(DOCKER) exec -it $(NODE_CONTAINER) yarn watch:shop

lint-js: ## Vérifie la qualité du code JavaScript
	$(DOCKER_COMPOSE) run --rm $(NODE_SERVICE) yarn lint

## —— Database ——————————————————————————————————————————————
db-setup: ## Crée la base de données et charge les fixtures personnalisées
	$(DOCKER) exec $(PHP_CONTAINER) bin/console doctrine:database:create --if-not-exists
	$(DOCKER) exec $(PHP_CONTAINER) bin/console doctrine:schema:update --force
	$(DOCKER) exec $(PHP_CONTAINER) bin/console sylius:fixtures:load fleuriste_base --no-interaction
	$(DOCKER) exec $(PHP_CONTAINER) bin/console sylius:fixtures:load fleuriste_taxons --no-interaction
	$(DOCKER) exec $(PHP_CONTAINER) bin/console sylius:fixtures:load fleuriste_products --no-interaction

db-reset: ## Réinitialise la base de données
	$(DOCKER) exec $(PHP_CONTAINER) bin/console doctrine:database:drop --force --if-exists
	$(MAKE) db-setup

db-user_admin: ## Réinitialise la base de données
	$(DOCKER) exec $(PHP_CONTAINER) php create_admin.php


db-migrate: ## Exécute les migrations de base de données
	$(DOCKER) exec $(PHP_CONTAINER) bin/console doctrine:migrations:migrate --no-interaction

db-backup: ## Sauvegarde la base de données
	$(DOCKER) exec $(MYSQL_CONTAINER) mysqldump -u sylius -psylius sylius > backup_$(shell date +%Y%m%d_%H%M%S).sql

## —— Tests —————————————————————————————————————————————————
test: ## Lance tous les tests
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/phpunit

behat: ## Lance les tests Behat
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/behat --strict

behat-js: ## Lance les tests Behat avec JavaScript
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/behat --tags="@javascript" --strict

behat-catalog: ## Lance les tests Behat pour le catalogue
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/behat features/catalog/ --strict

behat-custom: ## Lance les tests Behat pour la personnalisation
	$(DOCKER) exec $(PHP_CONTAINER) vendor/bin/behat features/customization/ --strict

## —— Cache & Cleanup ———————————————————————————————————————
cache-clear: ## Vide le cache Symfony
	$(DOCKER) exec $(PHP_CONTAINER) bin/console cache:clear
	$(DOCKER) exec -u root $(PHP_CONTAINER) chmod -R 777 /var/www/html/var/cache

cache-warmup: ## Précharge le cache Symfony
	$(DOCKER) exec $(PHP_CONTAINER) bin/console cache:warmup

clean: down ## Nettoie complètement le projet
	rm -rf vendor node_modules var/cache/* var/logs/* web/assets/admin web/assets/shop
	$(DOCKER_COMPOSE) down -v

clean-assets: ## Nettoie les assets compilés
	rm -rf web/assets/admin web/assets/shop node_modules

## —— Development Workflow ——————————————————————————————————
dev: up ## Lance l'environnement de développement complet
	@echo "✅ Environnement de développement lancé !"
	@echo "🌐 Application: http://localhost:8080"
	@echo ""
	@echo "📦 Lancer le watch des assets dans un autre terminal:"
	@echo "   make assets-watch"

dev-watch: ## Lance l'environnement + watch des assets en arrière-plan
	@$(MAKE) up
	@echo "🚀 Démarrage du watch des assets en arrière-plan..."
	@$(DOCKER) exec -d $(NODE_CONTAINER) sh -c "yarn install && yarn watch"
	@echo "✅ Environnement complet lancé !"
	@echo "🌐 Application: http://localhost:8080"
	@echo "📦 Assets en mode watch (arrière-plan)"

dev-stop: ## Arrête l'environnement de développement
	$(DOCKER_COMPOSE) stop $(NODE_CONTAINER)
	$(DOCKER_COMPOSE) stop

quality: twigcs lint-js phpstan ## Vérifie la qualité du code (Twig, JS, PHP)

## —— Utilitaires ———————————————————————————————————————————
permissions: ## Corrige les permissions des fichiers
	$(DOCKER) exec $(PHP_CONTAINER) chown -R www-data:www-data var/
	$(DOCKER) exec $(PHP_CONTAINER) chmod -R 775 var/

dump-autoload: ## Régénère l'autoloader Composer
	$(DOCKER) exec $(PHP_CONTAINER) composer dump-autoload