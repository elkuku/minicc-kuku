SHELL := /bin/bash

tests: export APP_ENV=test
tests:
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create
	symfony php vendor/bin/phpunit $@
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process src --dry-run
tests2: export APP_ENV=test
tests2:
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process --dry-run
.PHONY: tests
