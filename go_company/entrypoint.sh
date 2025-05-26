#!/bin/sh

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /app/api/conf/sharding.json
envsubst < /app/api/conf/socket.example.json > /app/api/conf/socket.json

sh wait-services.sh 100

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o company -mod vendor main.go
fi

ln -sf /dev/stdout /app/logs/main.log
/app/company -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app