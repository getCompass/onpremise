#!/bin/sh

envsubst < /app/private/main.local.php > /app/private/main.php
envsubst < /app/private/custom.local.php > /app/private/custom.php

sh /app/wait-for-it.sh $MYSQL_HOST:$MYSQL_PORT -t 10
sh /app/wait-for-it.sh $RABBIT_HOST:$RABBIT_PORT -t 10
sh /app/wait-for-it.sh $MCACHE_HOST:$MCACHE_PORT -t 10

cd /app && sh install.sh

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# запускаем кроны
bash /app/sh/cron/start_cron.sh || die "start_cron.sh unsuccessful";

docker-php-entrypoint php-fpm