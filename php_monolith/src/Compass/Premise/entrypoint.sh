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

# приступаем к миграциям
mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" < "${SCRIPT_PATH}/sql/init_premise.sql"

migrate -path "${SCRIPT_PATH}/sql/premise_system" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_system?tls=false up
migrate -path "${SCRIPT_PATH}/sql/premise_system" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_system?tls=false version

migrate -path "${SCRIPT_PATH}/sql/premise_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_data?tls=false up
migrate -path "${SCRIPT_PATH}/sql/premise_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_data?tls=false version

migrate -path "${SCRIPT_PATH}/sql/premise_user" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_user?tls=false up
migrate -path "${SCRIPT_PATH}/sql/premise_user" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/premise_user?tls=false version
