#!/bin/bash

envsubst </app/api/conf/conf.example.json >/app/api/conf/conf.json
envsubst </app/api/conf/sharding.example.json >/app/api/conf/sharding.json
envsubst </app/api/conf/test_define.example.json >/app/api/conf/test_define.json

sh wait-services.sh

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o activity -mod vendor main.go
fi

ln -sf /dev/stdout /app/logs/main.log
/app/activity -confdir=/app/api/conf -logsdir=/app/logs/ -executabledir=/app