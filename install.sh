#!/bin/sh
set -e

echo "Скачивание bitrixsetup.php и установка прав"
mkdir -p ./www/public && \
rm -f ./www/public/bitrixsetup.php && \
curl -fsSL https://www.1c-bitrix.ru/download/scripts/bitrixsetup.php -o ./www/public/bitrixsetup.php && \
sudo chown -R :www-data ./www && chmod -R 2775 ./www

echo "Копирование файла .env"
cp -n .env.default .env
sed -i 's,SITE_PATH=.*,SITE_PATH='"$(pwd)"'\/www\/,' ./.env
sed -i 's,PUBLIC_PATH=.*,PUBLIC_PATH='"$(pwd)"'\/www\/public\/,' ./.env

echo "Запуск"
docker compose down --remove-orphans
docker compose build
docker compose up -d
docker compose exec php composer install --working-dir=/var/www/bitrix
