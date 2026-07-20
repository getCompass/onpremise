#!/bin/bash

# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P); VERBOSE=false;

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o auth -mod vendor main.go
fi

ln -sf /dev/stdout /app/logs/main.log

# ждем сервисы-зависимости
sh wait-services.sh 100

# приступаем к миграциям
mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" --skip-ssl < "${SCRIPT_PATH}/sql/init.sql"

migrate -path "${SCRIPT_PATH}/sql/api_token_list" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/auth?tls=false up
migrate -path "${SCRIPT_PATH}/sql/api_token_list" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/auth?tls=false version

/app/auth
