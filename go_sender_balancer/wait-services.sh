#!/bin/sh

sh /app/wait-for-it.sh $RABBIT_HOST:$RABBIT_PORT -t 100