#!/bin/bash

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o api_gateway -mod vendor main.go
fi

ln -sf /dev/stdout /app/logs/main.log
/app/api_gateway
