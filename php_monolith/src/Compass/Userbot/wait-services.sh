#!/bin/sh

iteration_count=0
timeout=100
success_count=0
while true; do

    /usr/bin/mysql -h ${MYSQL_HOST} -P ${MYSQL_PORT} -u ${MYSQL_USER} -p${MYSQL_PASS} -e "SHOW DATABASES" >> /dev/null
    status=$?
    if [ $status -eq 0 ]; then

      success_count=$((success_count+1))
      if [ $success_count -gt 3 ]; then
        break;
      fi
      continue;
    fi
    success_count=0
    echo "Ждем mysql"
    sleep 1
    iteration_count=$((iteration_count+1))
    if [ $iteration_count -gt $timeout ]; then
      echo "Не дождались mysql"
      exit 1
    fi
done


