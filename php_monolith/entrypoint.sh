#!/bin/bash

# region script-header
# set -Eeuo pipefail
# trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
NO_COLOR='\033[0m';BLACK='\033[0;30m';RED='\033[0;31m';GREEN='\033[0;32m';YELLOW='\033[0;33m';BLUE='\033[0;34m';PURPLE='\033[0;35m';CYAN='\033[0;36m';WHITE='\033[0;37m';
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);

# подставляем дефолтные значения в файлы констант
envsubst < /app/private/main.local.php > /app/private/main.php
envsubst < /app/private/custom.local.php > /app/private/custom.php
envsubst < /app/private/public.local.php > /app/private/public.php

echo "wait" > status

if [[ "${IS_LOCAL}" == "true" ]]; then

  rm /usr/local/etc/php/php.ini
  mv /conf/local/php.ini /usr/local/etc/php/php.ini

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.local /usr/local/etc/php-fpm.d/www.conf
fi

if [[ "${IS_LOCAL}" == "false" ]] && [[ "${DEV_SERVER}" == "true" ]]; then

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.dev /usr/local/etc/php-fpm.d/www.conf
fi

# shellcheck disable=SC2155
if [ -f "/run/secrets/compass_database_encryption_secret_key" ]; then
  export DATABASE_CRYPT_SECRET_KEY=$(cat "/run/secrets/compass_database_encryption_secret_key");
fi;

rm /usr/local/etc/php-fpm.d/www.conf.*
rm /usr/local/etc/php/php.ini.*
bash "/app/src/Compass/Conversation/init_submodule.sh" "Conversation" || exit 1;
bash "/app/src/Compass/Thread/init_submodule.sh" "Thread" || exit 1;
bash "/app/src/Compass/Company/init_submodule.sh" "Company" || exit 1;
bash "/app/src/Compass/Speaker/init_submodule.sh" "Speaker" || exit 1;
bash "/app/src/Compass/FileBalancer/init_submodule.sh" "FileBalancer" || exit 1;
bash "/app/src/Compass/Pivot/init_submodule.sh" "Pivot" || exit 1;
bash "/app/src/Compass/Userbot/init_submodule.sh" "Userbot" || exit 1;
bash "/app/src/Compass/Announcement/init_submodule.sh" "Announcement" || exit 1;
bash "/app/src/Compass/Federation/init_submodule.sh" "Federation" || exit 1;
bash "/app/src/Compass/Premise/init_submodule.sh" "Premise" || exit 1;
bash "/app/src/Compass/Jitsi/init_submodule.sh" "Jitsi" || exit 1;

# раздаем права, инициализируем пустые директории
cd /app && sh install.sh

# нужны ли миграции на резервном сервере
IS_STOP_MIGRATE=$(php "/app/sh/php/tools/reserve/check_server.php");
if [[ "${IS_STOP_MIGRATE}" == "true" ]]; then
  CURRENT_FOLDER=$(basename "$SCRIPT_PATH")
  echo "reserve: запуск миграции пропускается для монолита"
else
  mariadb --user="${MYSQL_SYSTEM_USER}" --password="${MYSQL_PASS}" --host="$MYSQL_HOST" --port="$MYSQL_PORT" --skip-ssl < "${SCRIPT_PATH}/sql/init_system.sql"
  migrate -path /app/sql/system_compass_company -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false up
  migrate -path /app/sql/system_compass_company -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false version
fi

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# инициализируем подписки
bash /app/src/Compass/_init_subscriptions.sh || die "_init_subscriptions.sh unsuccessful";

# запускаем кроны
bash /app/sh/cron/start_cron.sh || die "start_cron.sh unsuccessful";

echo "ready to serve" > status

# запускаем контейнер
docker-php-entrypoint php-fpm
