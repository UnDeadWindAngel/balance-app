#!/bin/bash

# Генерируем ключ приложения
php artisan key:generate

# Запускаем миграции
php artisan migrate --force

# Запускаем PHP-FPM в фоне
php-fpm &

# Запускаем nginx
nginx -g "daemon off;"
