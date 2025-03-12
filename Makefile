SHELL := /bin/bash

tests: export APP_ENV=test
tests:
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create
#	symfony console doctrine:s:c
	symfony console doctrine:migrations:migrate -n
	symfony console doctrine:fixtures:load -n -vv
	symfony php bin/phpunit $@
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process src --dry-run
tests2: export APP_ENV=test
tests2:
	vendor/bin/phpstan --memory-limit=2G
	vendor/bin/rector process --dry-run
.PHONY: tests
