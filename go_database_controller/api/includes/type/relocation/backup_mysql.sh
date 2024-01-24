#!/bin/bash

########################################################
# FUNCTIONS
########################################################

# проверяем объявлена ли нужная переменная
function assertEnvSet() {

	if ! [[ -n "$1" ]]; then

		echoError "One of variables is not set. Pass --help for .env file example";
		exit 1;
	fi
}

# функция для логирования
function doLog() {

	STR="`date` $1";
	echo ${STR} >> ${GLOBAL_LOG_FILE};
	echo ${STR} >> ${PATH_BACKUP_LOG};
}

# показываем ошибку
function echoError() {

	echo "ERROR: $1";
}

# получаем список баз данных для backup
function getNeedBackupDatabaseList() {

	# empty
	if [[ $DATABASE_IGNORE_LIST == "" ]];then
		echo $(mysql -u$MYSQL_USER -p$MYSQL_PASS -S $SOCKET -e "SHOW DATABASES"|awk -F " " '{if (NR!=1) print $1}');
	else

		IN=$(echo "$DATABASE_IGNORE_LIST" | tr ',' "\n" | xargs -I{} printf "'%s'," {} | sed 's#,$##')
		echo $(mysql -u$MYSQL_USER -p$MYSQL_PASS -S $SOCKET -e "SHOW DATABASES WHERE \`Database\` NOT IN ($IN)"|awk -F " " '{if (NR!=1) print $1}');
	fi
}

########################################################
# check script options
########################################################

# выводит сообщение в консоль, подавляется -v
function msg() { if $VERBOSE; then return; fi; echo >&2 -e "${1-}"; }

# парсит входные параметры; добавить новый параметр:  «--VALUE) something; ;;» перед блоком -?*),
# писать можно не в строчку, дефолтные просто для удобства так оформлены
function parse_params() {

  while true; do

    case "${1-}" in
    --verbose) VERBOSE=true; ;;
    -S | --socket)
      SOCKET="${2-}";
      shift;
      ;;
    -u | --user)
      MYSQL_USER="${2-}";
      shift;
      ;;
    -p | --pass)
      MYSQL_PASS="${2-}";
      shift;
      ;;
    --datadir)
      DATADIR="${2-}";
      shift;
      ;;
    --backup_path)
      BACKUP_PATH="${2-}";
      shift;
      ;;
    --folder)
      FOLDER="${2-}";
      shift;
      ;;
    --backup_pass)
      BACKUP_PASS="${2-}";
      shift;
      ;;
    -?*) die "передан неизвестный параметр, используй --help чтобы получить информацию"; ;;
    *) break ;;
    esac
    shift;
  done

  return 0;
}

########################################################
# include and check env file
########################################################

# первым делом парсим входные параметры
parse_params "$@"
msg

# сначала здесь - в файле могут переопределить ;)
BACKUP_NAME=$HOSTNAME
SERVER_BACKUP_DIR="/home/backup_mysql/"

assertEnvSet $SERVER_BACKUP_DIR;
assertEnvSet $SOCKET;
assertEnvSet $MYSQL_USER;
assertEnvSet $MYSQL_PASS;
assertEnvSet $DATADIR;
assertEnvSet $BACKUP_NAME;

########################################################
# set global vars
########################################################

# PID файл
PID_FILE="${SERVER_BACKUP_DIR}backup.pid"

# файл для логирования
GLOBAL_LOG_FILE="${SERVER_BACKUP_DIR}logs.log"

# все для текущего бекапа
TIMESTAMP=$(date +%Y_%m_%d__%H_%M)
PATH_BACKUP_ZIP="${BACKUP_PATH}.zip"
PATH_BACKUP_LOG="${SERVER_BACKUP_DIR}${TIMESTAMP}.log"
PATH_BACKUP_ZIP_LOG="${SERVER_BACKUP_DIR}${TIMESTAMP}.zip.log"

# redirecting all output to logfile
exec >> $PATH_BACKUP_LOG 2>&1

########################################################
# doing backup
########################################################

# проверяем, не запущен ли скрипт уже и создаем pid файл
if [[ -e ${PID_FILE} ]]; then

  echo "Backup script already running";
  exit 1;
fi
touch ${PID_FILE};

# создаем папки для бекапа
mkdir -p "${SERVER_BACKUP_DIR}"

#
doLog "---- START ----";

# получаем список баз данных
DATABASES=$(getNeedBackupDatabaseList)
doLog "Database list: $DATABASES";

# время чтобы знать сколько будет выполнять бекап
START=$(date +%s)
doLog "XTRABACKUP START";

# делаем бекап в нужную директорию
xtrabackup --backup --datadir="$DATADIR" --user=$MYSQL_USER --password=$MYSQL_PASS -S $SOCKET --target-dir=$BACKUP_PATH --databases="$DATABASES" --no-server-version-check;
if [[ ! $? == '0' ]]; then

  rm $PID_FILE;
  doLog "XTRABACKUP FINISH FAIL";
  exit 1;
fi

FINISH=$(date +%s);
doLog "XTRABACKUP OK. Time: $((FINISH - START))";

# делаем ZIP директории
# архивируем backup
START=$(date +%s)
doLog "ZIP START";

# хз почему
RESULT=$(cd "$BACKUP_PATH/../"; zip --password "$BACKUP_PASS" -r "$PATH_BACKUP_ZIP" "$FOLDER" > $PATH_BACKUP_ZIP_LOG);
if [[ ! $? == '0' ]]; then

	rm $PID_FILE;
	doLog "ZIP FAIL";
	exit 1;
fi

# удаляем весь вывод zip за ненадобностью
rm $PATH_BACKUP_ZIP_LOG;

# считаем размер zip
ZIP_SIZE=$(stat -c%s $PATH_BACKUP_ZIP)
ZIP_SIZE=$(( ${ZIP_SIZE%% *} / 1024 / 1024))

FINISH=$(date +%s);
doLog "ZIP OK. Time: $((FINISH - START)); Zip size: ${ZIP_SIZE}MB";

# не забываем удалить pid
rm $PID_FILE;

#
doLog "---- FINISH ----";
