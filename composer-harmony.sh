#!/bin/bash

# This runs the internal composer install for mouf (instead of the default composer.json file that is supposed to be used by other frameworks)
COMPOSER=composer-harmony-core.json php composer.phar $@
