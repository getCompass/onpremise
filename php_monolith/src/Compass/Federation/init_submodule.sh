#!/bin/bash

envsubst < /app/src/Compass/Federation/private/custom.local.php > /app/src/Compass/Federation/private/custom.php
envsubst < /app/src/Compass/Federation/private/main.local.php > /app/src/Compass/Federation/private/main.php

iteration_count=0
timeout=100

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

echo "Дождались всех сервисов"