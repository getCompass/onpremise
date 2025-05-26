#!/bin/bash

envsubst < /app/src/Compass/FileBalancer/private/custom.local.php > /app/src/Compass/FileBalancer/private/custom.php
envsubst < /app/src/Compass/FileBalancer/private/main.local.php > /app/src/Compass/FileBalancer/private/main.php

iteration_count=0
timeout=100

while ! mariadb-admin ping -h "${MYSQL_HOST}" -P ${MYSQL_PORT} --silent --skip-ssl; do
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      exit 1
    fi
done