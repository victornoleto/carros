.PHONY: help install env setup dev serve vite build test phpunit lint format migrate migrate-fresh queue tinker clear optimize routes views shell

SHELL := /bin/bash
PHP := php
ARTISAN := $(PHP) artisan
COMPOSER := composer
NPM := npm

help: ## Lista os comandos disponíveis
	@awk 'BEGIN {FS = ":.*##"; printf "Comandos disponíveis:\n"} /^[a-zA-Z0-9_-]+:.*##/ {printf "  make %-16s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Instala dependências PHP e frontend
	$(COMPOSER) install
	$(NPM) install

setup: install ## Prepara a aplicação localmente
	@test -f .env || cp .env.example .env
	$(ARTISAN) key:generate --ansi
	$(ARTISAN) migrate

env: ## Garante .env e APP_KEY local
	@test -f .env || cp .env.example .env
	@grep -q '^APP_KEY=base64:' .env || $(ARTISAN) key:generate --ansi

dev: env clear ## Roda Laravel e Vite em modo desenvolvimento
	@trap 'kill 0' INT TERM EXIT; \
	$(ARTISAN) serve & \
	$(NPM) run dev & \
	wait

serve: ## Roda somente o servidor Laravel
	$(ARTISAN) serve

vite: ## Roda somente o Vite dev server
	$(NPM) run dev

build: ## Gera assets de produção com Vite
	$(NPM) run build

test: ## Executa a suíte de testes
	$(ARTISAN) test

phpunit: ## Executa PHPUnit diretamente
	./vendor/bin/phpunit

lint: ## Verifica estilo PHP com Pint
	./vendor/bin/pint --test

format: ## Formata PHP com Pint
	./vendor/bin/pint

migrate: ## Executa migrations pendentes
	$(ARTISAN) migrate

migrate-fresh: ## Recria o banco e roda seeders
	$(ARTISAN) migrate:fresh --seed

queue: ## Processa a fila local
	$(ARTISAN) queue:work

tinker: ## Abre o Laravel Tinker
	$(ARTISAN) tinker

clear: ## Limpa caches da aplicação
	$(ARTISAN) optimize:clear

optimize: ## Gera caches otimizados para produção
	$(ARTISAN) optimize

routes: ## Lista rotas registradas
	$(ARTISAN) route:list

views: ## Recompila views Blade
	$(ARTISAN) view:clear
	$(ARTISAN) view:cache

shell: ## Abre shell no diretório do projeto
	$(SHELL)
