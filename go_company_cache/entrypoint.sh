#!/bin/sh

envsubst </app/api/conf/conf.example.json >/app/api/conf/conf.json
envsubst </app/api/conf/sharding.example.json >/app/api/conf/sharding.json
envsubst </app/api/conf/test_define.example.json >/app/api/conf/test_define.json

sh wait-services.sh 100

if [[ "${IS_LOCAL}" == "true" ]]; then
  cd /app && go build -o company_cache -mod vendor main.go
fi

/app/company_cache -confdir=/app/api/conf -logsdir=/app/logs -executabledir=/app