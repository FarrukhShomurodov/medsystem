.DEFAULT_GOAL := help

COMPOSE ?= docker compose
APP     := $(COMPOSE) exec app
# Tests must never run against the development database: RefreshDatabase wipes it.
TEST    := $(COMPOSE) exec -e DB_DATABASE=medsystem_testing app

.PHONY: help up down restart build install shell logs migrate fresh seed test coverage lint fix analyse refactor ci assets assets-watch

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-13s\033[0m %s\n", $$1, $$2}'

up: ## Build images and start the stack in the background
	$(COMPOSE) up -d --build

down: ## Stop the stack
	$(COMPOSE) down

restart: down up ## Restart the stack

build: ## Rebuild images without cache
	$(COMPOSE) build --no-cache

install: ## First-time setup: dependencies, app key, schema, demo data and the front-end build
	@test -f .env || cp .env.example .env
	$(APP) composer install
	$(APP) php artisan key:generate
	$(APP) php artisan storage:link
	$(APP) php artisan migrate:fresh --seed
	$(MAKE) assets

assets: ## Build the front-end into public/build (Node container)
	$(COMPOSE) run --rm --no-deps vite sh -c "npm ci --no-audit --no-fund && npm run build"

assets-watch: ## Run the Vite dev server with hot reload (Ctrl+C stops it)
	$(COMPOSE) --profile assets up vite

shell: ## Open a shell in the PHP container
	$(APP) sh

logs: ## Tail the container logs: php-fpm with Laravel's errors, nginx
	$(COMPOSE) logs -f app web

migrate: ## Run pending migrations
	$(APP) php artisan migrate

fresh: ## Recreate the schema and seed demo data
	$(APP) php artisan migrate:fresh --seed

seed: ## Seed demo data
	$(APP) php artisan db:seed

test: ## Run the test suite (against the separate medsystem_testing database)
	$(TEST) composer test

coverage: ## Run the test suite with coverage
	$(TEST) composer test:coverage

lint: ## Check code style
	$(APP) composer lint

fix: ## Fix code style
	$(APP) composer lint:fix

analyse: ## Run static analysis
	$(APP) composer analyse

refactor: ## Preview automated refactorings
	$(APP) composer refactor

ci: ## Everything CI runs
	$(TEST) composer ci
