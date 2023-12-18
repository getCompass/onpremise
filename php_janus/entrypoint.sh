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
# endregion script-header

echo "wait"

envsubst </app/private/main.local.php >/app/private/main.php
envsubst </app/private/custom.local.php >/app/private/custom.php

if [[ "${IS_LOCAL}" == "true" ]]; then

  rm /usr/local/etc/php/php.ini
  mv /usr/local/etc/php/php.ini.local /usr/local/etc/php/php.ini
  cp /app/dev/configs/etc/php/xdebug.ini /usr/local/etc/php/conf.d/

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.local /usr/local/etc/php-fpm.d/www.conf
fi

if [[ "${IS_LOCAL}" == "false" ]] && [[ "${DEV_SERVER}" == "true" ]]; then

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.dev /usr/local/etc/php-fpm.d/www.conf
fi

cd /app && sh install.sh

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# запускаем кроны
bash /app/cron/start_cron.sh || die "start_cron.sh unsuccessful";

echo "ready to serve"
docker-php-entrypoint php-fpm

