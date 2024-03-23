PHPCS=./vendor/bin/phpcs
PHPUNIT=./bin/phpunit
CONSOLE=./bin/console

lint: lint-phpcs
test-stage: lint test
set-up-all: deps schema migration load-fixtures

up:
	docker-compose up -d --build

attach-php:
	docker-compose exec php bash

stop:
	docker-compose stop

lint-phpcs:
	$(PHPCS) src -p --colors --standard=ruleset.xml

test:
	$(PHPUNIT)

test-u:
	$(PHPUNIT) --group=Unit

test-f:
	$(PHPUNIT) --group=Functional

schema:
	$(CONSOLE) doctrine:database:create --if-not-exists \
	&& $(CONSOLE) doctrine:database:create --if-not-exists -e test --quiet \
	&& $(CONSOLE) doctrine:schema:drop --force -e dev \
	&& $(CONSOLE) doctrine:schema:update --force -e dev

deps:
	composer install --no-interaction

jwt:
	$(CONSOLE) lexik:jwt:generate-keypair

load-fixtures:
	$(CONSOLE) doctrine:fixtures:load -n

migration:
	$(CONSOLE) make:migration -n \
	&& $(CONSOLE) doctrine:migrations:migrate -n
