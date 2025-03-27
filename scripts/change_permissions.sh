#!/bin/bash

laroot="/var/www/html/login.kama-dei.com"

mkdir -p $laroot/storage/framework/sessions
mkdir -p $laroot/storage/framework/testing
mkdir -p $laroot/storage/framework/views
mkdir -p $laroot/storage/logs
mkdir -p $laroot/storage/framework/cache/data
mkdir -p $laroot/bootstrap/cache

chown -R www-data:www-data $laroot
find $laroot -type f -exec chmod 640 {} \;
find $laroot -type d -exec chmod 750 {} \;

chmod 600 $laroot/.env
