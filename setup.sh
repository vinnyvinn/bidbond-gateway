#!/usr/bin/env bash

sudo cp setup/gateway.conf /etc/apache2/sites-available/gateway.conf
sudo a2ensite /etc/apache2/sites-available/gateway.conf
sudo systemctl restart apache2


cd $GATEWAY
composer install
cp ./.env.example .env

sudo chmod -R 755 storage/
sudo chmod -R 0777 bootstrap/cache/
php artisan key:generate
php artisan config:clear
php artisan migrate --seed
