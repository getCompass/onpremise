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

# загружаем кронтаб пивота
PIVOT_CRONTAB=$(bash "${SCRIPT_PATH}/Janus/provide_crontab.sh");
echo "${PIVOT_CRONTAB}";