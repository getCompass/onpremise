#!/bin/bash

# region script-header
# set -Eeuo pipefail
# trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
NO_COLOR='\033[0m';BLACK='\033[0;30m';RED='\033[0;31m';GREEN='\033[0;32m';YELLOW='\033[0;33m';BLUE='\033[0;34m';PURPLE='\033[0;35m';CYAN='\033[0;36m';WHITE='\033[0;37m';
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# выводит сообщение в консоль, подавляется -v
function msg() { if $VERBOSE; then return; fi; echo >&2 -e "${1-}"; }
# выводит предупреждение в консоль
function wrn() { echo >&2 -e "${1-}"; }
# завершает работу выводя указанное сообщение с ошибкой
function die() { local MESSAGE=$1; local CODE=${2-1}; wrn "${RED}ERR${NO_COLOR}: ${MESSAGE}"; exit "${CODE}"; }
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }
# endregion script-header

# подставляем глобальные для модуля переменные
envsubst < "${SCRIPT_PATH}/private/main.local.php" > "${SCRIPT_PATH}/private/main.php"
envsubst < "${SCRIPT_PATH}/private/custom.local.php" > "${SCRIPT_PATH}/private/custom.php"

# дожидаемся сервисов
# возможно это стоит делать отдельным шагом инициализации
bash "/app/wait-services.sh" || die "service waiting failed"

mariadb --user="${MYSQL_SYSTEM_USER}" --password="${MYSQL_PASS}" --host="$MYSQL_HOST" -P "$MYSQL_PORT" --skip-ssl < "${SCRIPT_PATH}/sql/init_system.sql"

migrate -path "${SCRIPT_PATH}/sql/system_compass_company" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false up
migrate -path "${SCRIPT_PATH}/sql/system_compass_company" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false version