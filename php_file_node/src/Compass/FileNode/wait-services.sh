#!/bin/sh

iteration_count=0
timeout=100

while ! mariadb-admin ping -h "${MYSQL_HOST}" -P ${MYSQL_PORT} --silent --skip-ssl; do
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      exit 1
    fi
done

sh /app/wait-for-it.sh $RABBIT_HOST:$RABBIT_PORT -t 100
sh /app/wait-for-it.sh $MCACHE_HOST:$MCACHE_PORT -t 100

echo "Дождались всех сервисов"
