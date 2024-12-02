#!/bin/bash

# region script-header
# set -Eeuo pipefail
# trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# выводит предупреждение в консоль
function wrn() { echo >&2 -e "${1-}"; }
# завершает работу выводя указанное сообщение с ошибкой
function die() { local MESSAGE=$1; local CODE=${2-1}; wrn "ERR: ${MESSAGE}"; exit "${CODE}"; }
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }

# преобразовыаем SERVER_TAG_LIST
FORMATTED_SERVER_TAG_LIST=$(echo $SERVER_TAG_LIST | sed 's/\[\|\"\|\]//g' | tr ',' ' ')
IFS=' ' read -r -a SERVER_TAG_ARRAY <<< "$FORMATTED_SERVER_TAG_LIST"

# функции для проверки типа сервера
function isLocal() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " local " ]] && { return 0; } || { return 1; } }
function isDev() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " dev " ]] && { return 0; } || { return 1; } }
function isMaster() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " master " ]] && { return 0; } || { return 1; } }
function isStage() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " stage " ]] && { return 0; } || { return 1; } }
function isProduction() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " production " ]] && { return 0; } || { return 1; } }
function isCi() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " ci " ]] && { return 0; } || { return 1; } }
function isSaas() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " saas " ]] && { return 0; } || { return 1; } }
function isOnPremise() { [[ " ${SERVER_TAG_ARRAY[@]} " =~ " on-premise " ]] && { return 0; } || { return 1; } }
function isTest() {
    if isDev || isCi || isMaster || isLocal; then
        return 0
    else
        return 1
    fi
}
# endregion script-header

echo "wait" > status

envsubst < /app/private/custom.local.php > /app/private/custom.php
envsubst < /app/private/main.local.php > /app/private/main.php

if isLocal; then

  rm /usr/local/etc/php/php.ini
  mv /usr/local/etc/php/php.ini.local /usr/local/etc/php/php.ini

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.local /usr/local/etc/php-fpm.d/www.conf
fi

if ! isLocal && isDev; then

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.dev /usr/local/etc/php-fpm.d/www.conf
fi

if ! isLocal && isMaster; then

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.master /usr/local/etc/php-fpm.d/www.conf
fi

rm /usr/local/etc/php-fpm.d/www.conf.*

if isTest; then
  chown -R www-data:www-data /app/www/files
fi

chmod 777 /tmp/files

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# запускаем кроны
bash /app/cron/start_cron.sh || die "start_cron.sh unsuccessful";

echo "ready to serve" > status
docker-php-entrypoint php-fpm