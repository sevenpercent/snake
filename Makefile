SHELL=/bin/bash

default: build

build: dependencies
	@bin/compile
	@chmod +x snake.phar

clean:
	@rm -rf \
		vendor/ \
		snake.phar

dependencies: composer
	@php composer install \
		--ansi \
		--dev

composer:
	@curl -sS https://getcomposer.org/installer | php -- \
		--ansi \
		--filename=$@

install: build
	@cp snake.phar /usr/bin/snake

.PHONY: \
	default \
	build \
	clean \
	dependencies \
	install
