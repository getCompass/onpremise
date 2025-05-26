#!/bin/sh

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

sh /app/wait-for-it.sh $GO_EVENT_HOST:$GO_EVENT_PORT -t 100
sh /app/wait-for-it.sh $MANTICORE_HOST:$MANTICORE_PORT -t 100

echo "Дождались всех сервисов"
