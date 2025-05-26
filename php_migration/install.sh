#!/usr/bin/env bash

# создаем папку для логов
mkdir logs;
mkdir logs/cron;
mkdir logs/info;
mkdir logs/nginx;
mkdir logs/exception;
chmod -R 0777 logs;

# создаем папку для cache
mkdir cache; mkdir cache/upload; chmod -R 0777 cache;
mkdir cache/utest; chmod -R 0777 cache/utest;

# создаем папку для sql
mkdir sql;
chmod -R 0777 sql;

# создаем папку dev
mkdir dev;
mkdir dev/configs;
mkdir dev/configs/etc;
mkdir dev/configs/etc/nginx;
mkdir dev/configs/etc/php;
mkdir dev/configs/etc/sphinxsearch;
mkdir dev/php;
mkdir dev/txt;

# создаем папку private для конфигов
mkdir private
