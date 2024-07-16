#!/bin/bash

# подставляем дефолтные значения в файлы констант
envsubst < /app/private/main.local.php > /app/private/main.php
envsubst < /app/private/custom.local.php > /app/private/custom.php
envsubst < /app/private/public.local.php > /app/private/public.php

echo "wait" > status

if [[ "${IS_LOCAL}" == "true" ]]; then

  rm /usr/local/etc/php/php.ini
  mv /conf/local/php.ini /usr/local/etc/php/php.ini

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.local /usr/local/etc/php-fpm.d/www.conf
fi

if [[ "${IS_LOCAL}" == "false" ]] && [[ "${DEV_SERVER}" == "true" ]]; then

  rm /usr/local/etc/php-fpm.d/www.conf
  mv /usr/local/etc/php-fpm.d/www.conf.dev /usr/local/etc/php-fpm.d/www.conf
fi

rm /usr/local/etc/php-fpm.d/www.conf.*
rm /usr/local/etc/php/php.ini.*
bash "/app/src/Compass/Conversation/init_submodule.sh" "Conversation" || exit 1;
bash "/app/src/Compass/Thread/init_submodule.sh" "Thread" || exit 1;
bash "/app/src/Compass/Company/init_submodule.sh" "Company" || exit 1;
bash "/app/src/Compass/Speaker/init_submodule.sh" "Speaker" || exit 1;
bash "/app/src/Compass/FileBalancer/init_submodule.sh" "FileBalancer" || exit 1;
bash "/app/src/Compass/Pivot/init_submodule.sh" "Pivot" || exit 1;
bash "/app/src/Compass/Userbot/init_submodule.sh" "Userbot" || exit 1;
bash "/app/src/Compass/Announcement/init_submodule.sh" "Announcement" || exit 1;
bash "/app/src/Compass/Federation/init_submodule.sh" "Federation" || exit 1;
bash "/app/src/Compass/Premise/init_submodule.sh" "Premise" || exit 1;
bash "/app/src/Compass/Jitsi/init_submodule.sh" "Jitsi" || exit 1;

# раздаем права, инициализируем пустые директории
cd /app && sh install.sh

cat /app/sql/init_system.sql | mysql --user="${MYSQL_SYSTEM_USER}" --password="${MYSQL_PASS}" --host="$MYSQL_HOST" -P $MYSQL_PORT
migrate -path /app/sql/system_compass_company -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false up
migrate -path /app/sql/system_compass_company -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\(${MYSQL_HOST}:${MYSQL_PORT}\)/system_compass_company?tls=false version

# инициализируем модули
bash /app/src/Compass/_entrypoint.sh || die "entrypoint.sh unsuccessful";

# инициализируем подписки
bash /app/src/Compass/_init_subscriptions.sh || die "_init_subscriptions.sh unsuccessful";

# запускаем кроны
bash /app/sh/cron/start_cron.sh || die "start_cron.sh unsuccessful";

echo "ready to serve" > status

# запускаем контейнер
docker-php-entrypoint php-fpm
