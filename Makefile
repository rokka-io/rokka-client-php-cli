SRC_DIR="src/"
SRC_FILES= $(shell find $(SRC_DIR) -name "*.php")

dist/rokka-cli.phar: vendor box.json tools/box bin/rokka-cli $(SRC_FILES) composer.lock
	./tools/box compile --quiet

tools/box:
	wget --directory-prefix=tools --quiet https://github.com/box-project/box/releases/download/3.14.0/box.phar
	mv tools/box.phar tools/box
	chmod +x tools/box

vendor:
	composer config platform.php 8.0.0
	composer update --optimize-autoloader --no-dev --no-suggest --quiet

dist: dist/rokka-cli.phar

clean:
	rm -Rf tools/ dist/ vendor/

.PHONY: clean vendor
