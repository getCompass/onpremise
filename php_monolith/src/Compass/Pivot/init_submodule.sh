#!/bin/bash

envsubst < /app/src/Compass/Pivot/private/custom.local.php > /app/src/Compass/Pivot/private/custom.php
envsubst < /app/src/Compass/Pivot/private/main.local.php > /app/src/Compass/Pivot/private/main.php

while ! mariadb-admin ping -h "${MYSQL_HOST}" -P "${MYSQL_PORT}" --silent --skip-ssl; do
    echo "Ждем mysql host ${MYSQL_HOST} port ${MYSQL_PORT}"
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      echo "Не дождались mysql"
      exit 1
    fi
done

sh /app/wait-for-it.sh "${RABBIT_HOST}:${RABBIT_PORT}" -t $timeout
sh /app/wait-for-it.sh "${MCACHE_HOST}:${MCACHE_PORT}" -t $timeout
sh /app/wait-for-it.sh "${GO_PIVOT_GRPC_HOST}:${GO_PIVOT_GRPC_PORT}" -t $timeout

echo "Дождались всех сервисов"