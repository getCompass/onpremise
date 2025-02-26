#!/bin/sh

sh /app/wait-for-it.sh $MYSQL_HOST:$MYSQL_PORT -t 100