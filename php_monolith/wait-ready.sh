#!/bin/sh

timeout=100
iteration_count=0
while true; do

    status=$(cat status)

    if [ "$status" = "ready to serve" ]; then
      exit 0
    fi
    if [ $iteration_count -gt $timeout ]; then
      echo "Не дождались"
      exit 1
    fi
    iteration_count=$((iteration_count+1))
    echo "Ждем готовности world"
    sleep 1
done