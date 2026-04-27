#!/bin/bash
sed -i "s/__PORT__/${PORT:-80}/g" /etc/nginx/sites-available/default
php-fpm -D
nginx -g "daemon off;"
