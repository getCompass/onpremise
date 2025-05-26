#!/bin/bash

mkdir -p /conf

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/socket.example.json > /app/api/conf/socket.json

# ждем сервисы-зависимости
# sh wait-services.sh 100

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o file_auth -mod vendor main.go
fi

/app/file_auth -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app
