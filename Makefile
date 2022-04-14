vendor: composer.json
	composer update --ansi
	touch vendor

.PHONY:
test: clear test-phpunit test-phpcs test-psalm

.PHONY:
test-phpunit: vendor
	vendor/bin/phpunit --colors=always

.PHONY:
test-phpcs: vendor
	vendor/bin/phpcs --colors

.PHONY:
test-psalm: vendor
	vendor/bin/psalm

.PHONY:
cbf: vendor
	vendor/bin/phpcbf

.PHONY:
clear:
	rm -f .phpcs-cache .phpunit.result.cache *.sqlite

.PHONY:
cleardist: clear
	rm -rf vendor composer.lock
