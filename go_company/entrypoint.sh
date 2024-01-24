#!/bin/bash

mkdir -p /conf
envsubst < /app/api/conf/conf.example.json > /conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /conf/sharding.json
envsubst < /app/api/conf/socket.example.json > /conf/socket.json

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /app/api/conf/sharding.json
envsubst < /app/api/conf/socket.example.json > /app/api/conf/socket.json

sh wait-services.sh 100

mkdir -p /app/logs && ln -sf /dev/stdout /app/logs/main.log

if ! [[ "${IS_LOCAL}" == "true" ]]; then
  /app/company -confdir=/conf -logsdir=/app/logs -executabledir=/app

else
  cd /app && go build -o company -mod vendor main.go
  chmod +x /go/bin/dlv
  /app/company -confdir=/conf -logsdir=/app/logs -executabledir=/app
  tail -f /dev/null
fi