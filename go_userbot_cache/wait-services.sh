#!/bin/sh

sh /app/wait-for-it.sh $MYSQL_HOST:$MYSQL_PORT -t 100
sh /app/wait-for-it.sh $RABBIT_HOST:$RABBIT_PORT -t 100

