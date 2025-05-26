#!/bin/bash

# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P); VERBOSE=false;

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /app/api/conf/sharding.json

sh wait-services.sh 60
mkdir -p /app/logs && cd /app && go build -gcflags "all=-N -l" -o database_controller -mod vendor main.go && ln -sf /dev/stdout /app/logs/main.log

# приступаем к миграциям
mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" --skip-ssl < "${SCRIPT_PATH}/sql/init.sql"

migrate -path "${SCRIPT_PATH}/sql/domino_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/domino_service?tls=false up
migrate -path "${SCRIPT_PATH}/sql/domino_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/domino_service?tls=false version

/app/database_controller -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app