#!/bin/bash

# запуск инициализации кронов все модулей при инициализации сервиса;
# вызывается из entrypoint контейнера

# region script-header
set -Eeuo pipefail
trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }
# endregion script-header

# загружаем кронтаб
CRONTAB=$(bash "${SCRIPT_PATH}/Pivot/provide_crontab.sh");
CRONTAB+="\r\n"
CRONTAB+=$(bash "${SCRIPT_PATH}/Userbot/provide_crontab.sh");
CRONTAB+="\r\n"
CRONTAB+=$(bash "${SCRIPT_PATH}/Announcement/provide_crontab.sh");
CRONTAB+="\r\n"
CRONTAB+=$(bash "${SCRIPT_PATH}/Company/provide_crontab.sh");
CRONTAB+="\r\n"
CRONTAB+=$(bash "${SCRIPT_PATH}/Speaker/provide_crontab.sh");
CRONTAB+="\r\n"
echo "${CRONTAB}";