#!/bin/bash

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /app/api/conf/sharding.json

sh wait-services.sh 60
mkdir -p /app/logs && cd /app && go build -gcflags "all=-N -l" -o database_controller -mod vendor main.go && ln -sf /dev/stdout /app/logs/main.log

if ! [[ "${IS_LOCAL}" == "true" ]]; then
  /app/database_controller -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app

else
  cd /app && go build -o database_controller -mod vendor main.go
  chmod +x /go/bin/dlv
  /app/database_controller -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app
  tail -f /dev/null
fi