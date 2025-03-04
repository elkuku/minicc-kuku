SHELL := /bin/bash

tests: export APP_ENV=test
tests:
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate -n
	symfony console doctrine:fixtures:load -n
	symfony php bin/phpunit $@
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process src --dry-run
tests2:
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process src --dry-run
.PHONY: tests
