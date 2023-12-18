#!/bin/bash

iteration_count=0
timeout=100

envsubst < /app/src/Compass/Speaker/private/custom.local.php > /app/src/Compass/Speaker/private/custom.php
envsubst < /app/src/Compass/Speaker/private/config.local.php > /app/src/Compass/Speaker/private/config.php

while ! mysqladmin ping -h "${MYSQL_HOST}" -P ${MYSQL_PORT} --silent; do

    echo "Ждем mysql host ${MYSQL_HOST} port ${MYSQL_PORT}"
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      echo "Не дождались mysql"
      exit 1
    fi
done
echo "Дождались всех сервисов"