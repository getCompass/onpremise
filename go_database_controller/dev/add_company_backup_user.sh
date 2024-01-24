#!/bin/bash

DOMINO="";
PASSWORD="";

# region script-header
set -Eeuo pipefail
# shellcheck disable=SC2034
NO_COLOR='\033[0m';BLACK='\033[0;30m';RED='\033[0;31m';GREEN='\033[0;32m';YELLOW='\033[0;33m';BLUE='\033[0;34m';PURPLE='\033[0;35m';CYAN='\033[0;36m';WHITE='\033[0;37m';
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P); VERBOSE=false;
# выводит сообщение в консоль, подавляется -v
function msg() { if $VERBOSE; then return; fi; echo >&2 -e "${1-}"; }
# выводит предупреждение в консоль
function wrn() { echo >&2 -e "${1-}"; }
# завершает работу выводя указанное сообщение с ошибкой
function die() { local MESSAGE=$1; local CODE=${2-1}; wrn "${RED}ERR${NO_COLOR}: ${MESSAGE}"; exit "${CODE}"; }
# убирает цвета из стандартного вывода скрипта
# shellcheck disable=SC2034
function desaturate() { NO_COLOR='';BLACK='';RED='';GREEN='';YELLOW='';BLUE='';PURPLE='';CYAN='';WHITE=''; }
# выводит справку для использования, для каждого скрипта ее необходимо полностью и детально описать
function usage() {

  msg "Скрипт для добавления бекап-пользователя.";
  msg "";
  msg "Параметры: --verbose         [opt] уменьшает количество выводимой информации";
  msg "           --no-color        [opt] отключает цветовые выделения в выводе";
  msg "           --help            [opt] показывает это сообщение";

  return 0;
}

# парсит входные параметры; добавить новый параметр:  «--param) something; ;;» перед блоком -?*),
# писать можно не в строчку, дефолтные просто для удобства так оформлены
function parse_params() {

  while true; do

    case "${1-}" in
    --help) usage; exit 0; ;;
    --verbose) VERBOSE=true; ;;
    --no-color) desaturate; ;;
    -d | --domino)
      DOMINO="${2-}";
      shift;
      ;;
    -p | --password)
      PASSWORD="${2-}";
      shift;
      ;;
    -?*) die "передан неизвестный параметр"; ;;
    *) break ;;
    esac
    shift;
  done

  [[ -z "${DOMINO}" ]] && die "не передано домино (--domino {domino})";
  [[ -z "${PASSWORD}" ]] && die "не передан рут-пароль (--password {password})";

  return 0;
}

# первым делом парсим входные параметры
parse_params "$@"
msg

cd /var/run/mysqld/

for file in /var/run/mysqld/*; do

  # если файл не заканчивается на .sock
  if [ "${file##*.}" != "sock" ]; then
    continue;
  fi;

  # если файл не принадлежит указанному домино
  domino=`echo $file | cut -f2 -d "-"`;
  if [ "${domino}" != "${DOMINO}" ]; then
    continue;
  fi;

  # проверяем, что мускул доступен
  while ! mysql -u root -p${PASSWORD} -S ${file} -e ";" ; do

    echo "mysql for ${file} not allow";
    continue;
  done

  echo "добавляем пользователя для ${file}";

  mysql -u root -p${PASSWORD} -S ${file} --force -s -D mysql -e "DELETE FROM user WHERE User='backup_user';"
  mysql -u root -p${PASSWORD} -S ${file} --force -s -D mysql -e "FLUSH PRIVILEGES;"

  mysql -u root -p${PASSWORD} -S ${file} --force -s -D mysql -e "CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'backup_user_password';"
  mysql -u root -p${PASSWORD} -S ${file} --force -s -D mysql -e "GRANT RELOAD, BACKUP_ADMIN, LOCK TABLES, REPLICATION CLIENT, CREATE TABLESPACE, PROCESS, SUPER, CREATE, INSERT, SELECT ON * . * TO 'backup_user'@'localhost';"
  mysql -u root -p${PASSWORD} -S ${file} --force -s -D mysql -e "FLUSH PRIVILEGES;"

done

