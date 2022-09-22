#! /bin/bash
clear
rm var/cache/* /s/q > scrap
rm var/cache /s /q >> scrap

php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:schema:update --force

