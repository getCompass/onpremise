#!/bin/bash

# запуск инициализации подписок модуля на события go-event
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
php "${SCRIPT_PATH}/Conversation/sh/php/init_subscriptions.php" || die "conversation init_subscriptions.sh error";
php "${SCRIPT_PATH}/Thread/sh/php/init_subscriptions.php" || die "thread init_subscriptions.sh error";
php "${SCRIPT_PATH}/Company/sh/php/init_subscriptions.php" || die "company init_subscriptions.sh error";
php "${SCRIPT_PATH}/Federation/sh/php/init_subscriptions.php" || die "federation init_subscriptions.sh error";