#!/usr/bin/env bash

# создаем папку для логов
mkdir -p logs;
mkdir -p logs/cron;
mkdir -p logs/info;
mkdir -p logs/nginx;
mkdir -p logs/exception;
chmod -R 0777 logs;

# создаем папку для cache
mkdir -p cache; mkdir -p cache/upload; chmod -R 0777 cache;
mkdir -p cache/utest; chmod -R 0777 cache/utest;

# создаем папку для sql
mkdir -p sql;
chmod -R 0777 sql;

# создаем папку dev
mkdir -p dev;

# создаем папку private для конфигов
mkdir -p private
