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

echo "wait" > status

envsubst < /app/private/custom.local.php > /app/private/custom.php
envsubst < /app/private/main.local.php > /app/private/main.php

if [[ "${IS_LOCAL}" == "true" ]] || [[ "${DEV_SERVER}" == "true" ]]; then
  chown -R www-data:www-data /app/www/files
fi

chmod 777 /tmp/files

cd /app && runuser -l billy -c "sh install.sh"

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# запускаем кроны
bash /app/cron/start_cron.sh || die "start_cron.sh unsuccessful";

echo "ready to serve" > status
/usr/sbin/php-fpm8.1 --nodaemonize --fpm-config /etc/php/8.1/fpm/php-fpm.conf