#!/bin/bash

# region script-header
#set -Eeuo pipefail
#trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }
# endregion script-header

rm /app/cache/*.lock > /dev/null 2>&1;

# создаем файл для кронов и меняем ему владельца
touch "${SCRIPT_PATH}/.cronjob";
chown www-data:www-data "${SCRIPT_PATH}/.cronjob"

# формируем список для всех кронов
MODULE_CRONTAB=$(bash "${SCRIPT_PATH}/../src/Compass/_provide_crontab.sh");
APPLICATION_CRONTAB=$(cat "${SCRIPT_PATH}/crontab.cron");
CRONTAB=$(echo -e "${APPLICATION_CRONTAB}\n\n${MODULE_CRONTAB}");

echo "${CRONTAB}" > "${SCRIPT_PATH}/.cronjob";
crontab -u www-data - < "${SCRIPT_PATH}/.cronjob";

/etc/init.d/cron start;

crontab -l | grep -v '^#' | cut -f 6- -d ' ' | while read -r CRON; do

  sleep 0.25;
  su -c "$CRON > /dev/null 2>&1 &" www-data;

  echo $CRON;
done
