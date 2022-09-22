#! /bin/bash
clear
php bin/console doctrine:database:drop --force
rm var/cache/* /s/q > scrap
rm -r var/cache /s /q >> scrap

php bin/console doctrine:database:create
php bin/console doctrine:schema:create

