#!/bin/bash

iteration_count=0
timeout=100

envsubst < /app/src/Compass/Premise/private/custom.local.php > /app/src/Compass/Premise/private/custom.php
envsubst < /app/src/Compass/Premise/private/main.local.php > /app/src/Compass/Premise/private/main.php

while ! mysqladmin ping -h "${MYSQL_HOST}" -P ${MYSQL_PORT} --silent; do

    echo "Ждем mysql host ${MYSQL_HOST} port ${MYSQL_PORT}"
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      echo "Не дождались mysql"
      exit 1
    fi
done

sh /app/wait-for-it.sh "${GO_PIVOT_GRPC_HOST}:${GO_PIVOT_GRPC_PORT}" -t $timeout

echo "Дождались всех сервисов"