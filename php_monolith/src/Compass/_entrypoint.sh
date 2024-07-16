#!/bin/bash

# запуск инициализации кронов все модулей при инициализации сервиса;
# вызывается из entrypoint контейнера

# region script-header
set -Eeuo pipefail
trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# выводит предупреждение в консоль
function wrn() { echo >&2 -e "${1-}"; }
# завершает работу выводя указанное сообщение с ошибкой
function die() { local MESSAGE=$1; local CODE=${2-1}; wrn "ERR: ${MESSAGE}"; exit "${CODE}"; }
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }
# endregion script-header

#
bash "${SCRIPT_PATH}/Userbot/entrypoint.sh" || die "userbot entrypoint error";
bash "${SCRIPT_PATH}/Announcement/entrypoint.sh" || die "announcement entrypoint error";
bash "${SCRIPT_PATH}/Federation/entrypoint.sh" || die "federation entrypoint error";
bash "${SCRIPT_PATH}/Premise/entrypoint.sh" || die "premise entrypoint error";
bash "${SCRIPT_PATH}/Jitsi/entrypoint.sh" || die "jitsi entrypoint error";
bash "${SCRIPT_PATH}/Pivot/entrypoint.sh" || die "pivot entrypoint error";
