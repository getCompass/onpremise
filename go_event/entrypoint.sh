#!/bin/bash

mkdir -p /conf

# тут зачем-то конфиги инициализируются дважды —
# один раз в /conf, второй в /app/api/conf, нужно разобраться и убрать лишний
envsubst < /app/api/conf/conf.example.json > /conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /conf/sharding.json
envsubst < /app/api/conf/test_define.example.json > /conf/test_define.json

# для домино и монолита используем один конфигурационный шаблон
[[ "${CURRENT_SERVER}" == "pivot" ]] && envsubst < /app/api/conf/event.pivot.example.json > /conf/event.json
[[ "${CURRENT_SERVER}" == "domino" ]] && envsubst < /app/api/conf/event.domino.example.json > /conf/event.json
[[ "${CURRENT_SERVER}" == "monolith" ]] && envsubst < /app/api/conf/event.domino.example.json > /conf/event.json

envsubst < /app/api/conf/message_rule.example.json > /conf/message_rule.json
envsubst < /app/api/conf/socket.example.json > /conf/socket.json
cp /app/api/conf/locale.json /conf/locale.json

envsubst < /app/api/conf/conf.example.json > /app/api/conf/conf.json
envsubst < /app/api/conf/sharding.example.json > /app/api/conf/sharding.json
envsubst < /app/api/conf/test_define.example.json > /app/api/conf/test_define.json

# для домино и монолита используем один конфигурационный шаблон
[[ "${CURRENT_SERVER}" == "pivot" ]] && envsubst < /app/api/conf/event.pivot.example.json > /app/api/conf/event.json
[[ "${CURRENT_SERVER}" == "domino" ]] && envsubst < /app/api/conf/event.domino.example.json > /app/api/conf/event.json
[[ "${CURRENT_SERVER}" == "monolith" ]] && envsubst < /app/api/conf/event.domino.example.json > /app/api/conf/event.json

envsubst < /app/api/conf/message_rule.example.json > /app/api/conf/message_rule.json
envsubst < /app/api/conf/socket.example.json > /app/api/conf/socket.json

# ждем сервисы-зависимости
sh wait-services.sh 100

# перенаправляем вывод в stdout
mkdir -p /app/logs && ln -sf /dev/stdout /app/logs/main.log

# накатываем миграции на базу данных
if [[ "${CURRENT_SERVER}" == "pivot" ]] || [[ "${CURRENT_SERVER}" == "monolith" ]]; then

  cat /app/sql/init_pivot.sql | mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="$MYSQL_HOST" -P ${MYSQL_PORT}
fi;

if [[ "${CURRENT_SERVER}" == "domino" ]] || [[ "${CURRENT_SERVER}" == "monolith" ]]; then

  cat /app/sql/init_domino.sql | mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="$MYSQL_HOST" -P ${MYSQL_PORT}
  migrate -path /app/sql/company_system -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/company_system?tls=false up
  migrate -path /app/sql/company_system -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/company_system?tls=false version >logs/main.log
fi;

if ! [[ "${IS_LOCAL}" == "true" ]]; then
  /app/event -confdir=/conf -logsdir=/app/logs -executabledir=/app
else

  # для локального окружения собираем из исходников
  cd /app && go build -o event -mod vendor main.go
  chmod +x /go/bin/dlv
  /app/event -confdir=/conf -logsdir=/app/logs -executabledir=/app
  tail -f /dev/null
fi
