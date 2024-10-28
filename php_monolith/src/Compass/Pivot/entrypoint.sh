#!/bin/bash

# region script-header
# set -Eeuo pipefail
# trap cleanup SIGINT SIGTERM ERR EXIT
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
NO_COLOR='\033[0m';BLACK='\033[0;30m';RED='\033[0;31m';GREEN='\033[0;32m';YELLOW='\033[0;33m';BLUE='\033[0;34m';PURPLE='\033[0;35m';CYAN='\033[0;36m';WHITE='\033[0;37m';
# shellcheck disable=SC2034
SCRIPT_PATH=$(cd -- "$(dirname "$0")" || exit 1 >/dev/null 2>&1 ; pwd -P);
# выводит сообщение в консоль, подавляется -v
function msg() { if $VERBOSE; then return; fi; echo >&2 -e "${1-}"; }
# выводит предупреждение в консоль
function wrn() { echo >&2 -e "${1-}"; }
# завершает работу выводя указанное сообщение с ошибкой
function die() { local MESSAGE=$1; local CODE=${2-1}; wrn "${RED}ERR${NO_COLOR}: ${MESSAGE}"; exit "${CODE}"; }
# вызывается при завершении скрипта, здесь нужно подчистить весь мусор, что мог оставить скрипт
function cleanup() { trap - SIGINT SIGTERM ERR EXIT; }
# endregion script-header

# подставляем глобальные для модуля переменные
envsubst < "${SCRIPT_PATH}/private/main.local.php" > "${SCRIPT_PATH}/private/main.php"
envsubst < "${SCRIPT_PATH}/private/custom.local.php" > "${SCRIPT_PATH}/private/custom.php"

# дожидаемся сервисов
# возможно это стоит делать отдельным шагом инициализации
bash "/app/wait-services.sh" || die "service waiting failed"

# чиним миграции
php "${SCRIPT_PATH}/sh/php/update/fix_migration.php"

# приступаем к миграциям
mysql --user="${MYSQL_USER}" --password="${MYSQL_PASS}" --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" < "${SCRIPT_PATH}/sql/init_pivot.sql"

migrate -path "${SCRIPT_PATH}/sql/pivot_phone" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_phone?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_phone" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_phone?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2021?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2021?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2022?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2022?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2023?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2023?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2024?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2024?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2025?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2025?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2026?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2026?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2027?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2027?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2028?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_auth_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_auth_2028?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2021?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2021?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2022?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2022?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2023?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2023?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2024?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2024?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2025?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2025?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2026?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2026?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2027?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2027?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_file_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2028?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_file_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_file_2028?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_user_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_user_10m?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_user_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_user_10m?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_user_20m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_user_20m?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_user_20m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_user_20m?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_company_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_company_10m?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_company_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_company_10m?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_sms_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_sms_service?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_sms_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_sms_service?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2021?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2021" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2021?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2022?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2022" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2022?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2023?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2023" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2023?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2024?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2024" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2024?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2025?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2025" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2025?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2026?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2026" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2026?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2027?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2027" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2027?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2028?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_history_logs_2028" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_history_logs_2028?tls=false version

mysql --user="${MYSQL_SYSTEM_USER}" --password="${MYSQL_SYSTEM_PASS}" --host="$MYSQL_SYSTEM_HOST" --port="$MYSQL_SYSTEM_PORT" < "${SCRIPT_PATH}/sql/init_system.sql"

migrate -path "${SCRIPT_PATH}/sql/pivot_system" -database mysql://${MYSQL_SYSTEM_USER}:${MYSQL_SYSTEM_PASS}@tcp\(${MYSQL_SYSTEM_HOST}:${MYSQL_SYSTEM_PORT}\)/pivot_system?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_system" -database mysql://${MYSQL_SYSTEM_USER}:${MYSQL_SYSTEM_PASS}@tcp\(${MYSQL_SYSTEM_HOST}:${MYSQL_SYSTEM_PORT}\)/pivot_system?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_data?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_data?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_company_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_company_service?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_company_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_company_service?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_userbot" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_userbot?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_userbot" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_userbot?tls=false version

migrate -path "${SCRIPT_PATH}/sql/partner_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/partner_data?tls=false up
migrate -path "${SCRIPT_PATH}/sql/partner_data" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/partner_data?tls=false version

migrate -path "${SCRIPT_PATH}/sql/partner_invite_link" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/partner_invite_link?tls=false up
migrate -path "${SCRIPT_PATH}/sql/partner_invite_link" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/partner_invite_link?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_business" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_business?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_business" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_business?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_rating_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_rating_10m?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_rating_10m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_rating_10m?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_rating_20m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_rating_20m?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_rating_20m" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_rating_20m?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_attribution" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_attribution?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_attribution" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_attribution?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_mail" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_mail?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_mail" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_mail?tls=false version

migrate -path "${SCRIPT_PATH}/sql/pivot_mail_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_mail_service?tls=false up
migrate -path "${SCRIPT_PATH}/sql/pivot_mail_service" -database mysql://${MYSQL_USER}:${MYSQL_PASS}@tcp\($MYSQL_HOST:$MYSQL_PORT\)/pivot_mail_service?tls=false version

# тоже миграция, но особенная
php "${SCRIPT_PATH}/sh/php/domino/migrate_database.php"

# запускаем всякие служебные скрипты
runuser -l www-data -c "php ${SCRIPT_PATH}/sh/php/config/init_config.php"
runuser -l www-data -c "php ${SCRIPT_PATH}/sh/php/config/init_config_v2.php"
runuser -l www-data -c "php ${SCRIPT_PATH}/sh/php/config/update_config.php"
runuser -l www-data -c "php ${SCRIPT_PATH}/sh/php/config/set_block_level.php"
