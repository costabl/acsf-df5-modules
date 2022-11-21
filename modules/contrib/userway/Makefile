SHELL = /bin/sh

all: clean build cleanFolder

cleanFolder:
	rm -rf drupal-8-9-accessibility-plugin

clean:
	rm -rf *.tar drupal-8-9-accessibility-plugin

build:
	mkdir drupal-8-9-accessibility-plugin;
	cp -r js drupal-8-9-accessibility-plugin;
	cp -r src drupal-8-9-accessibility-plugin;
	cp -r *.yml templates drupal-8-9-accessibility-plugin;
	cp -r *.install templates drupal-8-9-accessibility-plugin;
	cp -r *.module templates drupal-8-9-accessibility-plugin;

	tar -zcvf drupal-8-9-accessibility-plugin.tar.gz drupal-8-9-accessibility-plugin;