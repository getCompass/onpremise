#!/bin/sh

iteration_count=0
timeout=$1

while ! mysqladmin ping -h "${MYSQL_HOST}" -P ${MYSQL_PORT} -u ${MYSQL_USER} -p${MYSQL_PASS} --silent --skip-ssl; do
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      exit 1
    fi
done