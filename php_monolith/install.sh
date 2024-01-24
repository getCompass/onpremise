#!/usr/bin/env bash

# инициализирует директорию с логами
# $1 — корневая директория, где нужно инициализировать логи
initLogDirectory() {

  # создаем папку для логов
  mkdir -p "${1}/logs";
  mkdir -p "${1}/logs/cron";
  mkdir -p "${1}/logs/info";
  mkdir -p "${1}/logs/nginx";
  mkdir -p "${1}/logs/exception";
  chmod -R 0777 logs;
}

# создаем логи для общего проекта
initLogDirectory "/app"

# создаем логи для каждого подпроекта
for SUBMODULE in src/Compass/*/ ; do
  initLogDirectory "/app/${SUBMODULE}"
done;

# создаем папку для cache
mkdir -p cache; mkdir -p cache/upload; chmod -R 0777 cache;
mkdir -p cache/utest; chmod -R 0777 cache/utest;

# создаем папку dev
mkdir -p dev;
mkdir -p dev/php;
