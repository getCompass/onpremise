#!/bin/bash

# запуск инициализации cron-job при инициализции модуля;
# вызывается из entrypoint контейнера

# region script-header
set -Eeuo pipefail
trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
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

# возвращаем содержимое подходящего файла crontab
cat "${SCRIPT_PATH}/sh/cron/crontab.cron"
