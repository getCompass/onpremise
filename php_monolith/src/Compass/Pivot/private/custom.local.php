<?php

namespace Compass\Pivot;

const DOMINO_CONFIG_PATH = "/pivot_config/";

define("SERVER_UID"                                   , "${SERVER_UID}");

// константы для того чтобы знать адрес сервера
define("ANNOUNCEMENT_PROTOCOL"                        , "${PROTOCOL}");
define("USERBOT_PROTOCOL"					, "${PROTOCOL}");
define("USERBOT_DOMAIN"						, "userbot.${DOMAIN}");
define("ANALYTIC_DOMAIN"					, "${ANALYTIC_DOMAIN}");

// константы для того чтобы знать адрес сервера партнера
define("PARTNER_PROTOCOL"                             , "${PARTNER_PROTOCOL}");
define("PARTNER_DOMAIN"                               , "${PARTNER_DOMAIN}");

// стартовый адрес
define("START_URL"                                    , "${START_URL}");
define("BILLING_URL"                                  , "${BILLING_URL}");
define("PARTNER_URL"                                  , "${PARTNER_PROTOCOL}://${PARTNER_DOMAIN}/");

// константа которая указывает на путь к куки
define("SESSION_COOKIE_DOMAIN"                        , "${PIVOT_DOMAIN}");

// с какого значения начинается инкремент для user_id в autoincrement
define("BEGIN_INCREMENT_USER_ID"                      , "${BEGIN_INCREMENT_USER_ID}");

// константы для отправки уведомлений
define("NOTICE_CHANNEL_SERVICE"                       , "${NOTICE_CHANNEL_SERVICE}");
define("SMS_EXCEPTION"                                , "${SMS_EXCEPTION}");

// endpoint для отправки уведомлений в Compass
define("COMPASS_NOTICE_ENDPOINT"                      , "${COMPASS_NOTICE_ENDPOINT}");

// для работы notice-бота
define("COMPASS_NOTICE_BOT_TOKEN"                     , "${COMPASS_NOTICE_BOT_TOKEN}");
define("COMPASS_NOTICE_BOT_SIGNATURE_KEY"             , "${COMPASS_NOTICE_BOT_SIGNATURE_KEY}");
define("COMPASS_NOTICE_BOT_TOKEN_NEW"                 , "${COMPASS_NOTICE_BOT_TOKEN_NEW}");
define("COMPASS_NOTICE_BOT_PROJECT"                   , "${COMPASS_NOTICE_BOT_PROJECT}");

// для работы уведомлений о изменении ранга компаний
define("COMPANY_TIER_COMPASS_NOTICE_PROJECT"          , "${COMPANY_TIER_COMPASS_NOTICE_PROJECT}");
define("COMPANY_TIER_COMPASS_NOTICE_TOKEN"            , "${COMPANY_TIER_COMPASS_NOTICE_TOKEN}");
define("COMPANY_TIER_COMPASS_NOTICE_GROUP_ID"         , "${COMPANY_TIER_COMPASS_NOTICE_GROUP_ID}");

// для работы уведомлений от крона проверки состояния плана тарифов
define("PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID"   , "${PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID}");

// для отправки бизнес статистики
define("BUSINESS_COMPASS_NOTICE_BOT_TOKEN"            , "${BUSINESS_COMPASS_NOTICE_BOT_TOKEN}");
define("BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY"    , "${BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY}");
define("BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID"        , "${BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID}");

// группа для уведомлений о проблемах смс в compass
define("SMS_ALERT_COMPASS_NOTICE_GROUP_ID"            , "${SMS_ALERT_COMPASS_NOTICE_GROUP_ID}");

// контанты для доступа к email с поступающими письмами мониторинга
define("IMAP_CONNECT"                                 , "${IMAP_CONNECT}");
define("MONITORING_EMAIL_LOGIN"                       , "${MONITORING_EMAIL_LOGIN}");
define("MONITORING_EMAIL_PASSWORD"                    , "${MONITORING_EMAIL_PASSWORD}");

define("STAT_GRAPH_IMAGE_GENERATOR_DOMAIN"            , "${STAT_GRAPH_IMAGE_GENERATOR_DOMAIN}");

// расширенный ключ (32 символа)
define("EXTENDED_ENCRYPT_KEY_DEFAULT"                 , "${EXTENDED_ENCRYPT_KEY_DEFAULT}"); // ключ
define("EXTENDED_ENCRYPT_IV_DEFAULT"                  , "${EXTENDED_ENCRYPT_IV_DEFAULT}"); // вектор шифрования

define("ENTRYPOINT_ANALYTIC"                          , "${ENTRYPOINT_ANALYTIC}");

// -------------------------------------------------------
// SOCKET КЛЮЧИ ДЛЯ ДОВЕРЕННОГО ОБЩЕНИЯ МЕЖДУ МОДУЛЯМИ
// -------------------------------------------------------

define("SOCKET_KEY_PUSHER"                            , "${SOCKET_KEY_GO_PUSHER}");
define("SOCKET_KEY_USERBOT_CACHE"                     , "${SOCKET_KEY_USERBOT_CACHE}");
define("SOCKET_KEY_MIGRATION"                         , "${SOCKET_KEY_MIGRATION}");
define("SOCKET_KEY_DEVELOPMENT"                       , "${SOCKET_KEY_DEVELOPMENT}");
define("SOCKET_KEY_COLLECTOR"                         , "${SOCKET_KEY_COLLECTOR}");
define("SOCKET_KEY_STAGE"                             , "${SOCKET_KEY_STAGE}");
define("SOCKET_KEY_PARTNER_WEB"                       , "${SOCKET_KEY_PARTNER_WEB}");
define("SOCKET_KEY_BILLING"                           , "${SOCKET_KEY_BILLING}");
define("SOCKET_KEY_WEBSTAT"                           , "${SOCKET_KEY_WEBSTAT}");
define("SOCKET_KEY_BOTHUB"                            , "${SOCKET_KEY_BOTHUB}");

// -------------------------------------------------------
// SALT ДЛЯ УПАКОВЩИКОВ ПРОЕКТА
// -------------------------------------------------------

// соль для формирования auth_map
define("SALT_PACK_AUTH"                               , [
	1 => "${SALT_PACK_AUTH_V1}",
	2 => "${SALT_PACK_AUTH_V2}"
]);

// -------------------------------------------------------
// ОСТАЛЬНОЕ
// -------------------------------------------------------

// secret от Google reCAPTCHA
define("COMTEAM_SECRET_CAPTCHA_ANDROID"               , "${COMTEAM_SECRET_CAPTCHA_ANDROID}");
define("COMPASS_SECRET_CAPTCHA_ANDROID"               , "${COMPASS_SECRET_CAPTCHA_ANDROID}");
define("SECRET_CAPTCHA_SITE"                          , "${SECRET_CAPTCHA_SITE}");

define("SALT_PHONE_NUMBER"                            , "${SALT_PHONE_NUMBER}");
define("SALT_CODE"                                    , [
	1 => "${SALT_CODE_V1}",
]);
define("SALT_USERAGENT"                               , [
	1 => "${SALT_USERAGENT_V1}",
]);

define("SMS_AGENT_LOGIN"                              , "${SMS_AGENT_LOGIN}");
define("SMS_AGENT_PASSWORD"                           , "${SMS_AGENT_PASSWORD}");
define("VONAGE_API_KEY"                               , "${VONAGE_API_KEY}");
define("VONAGE_API_SECRET"                            , "${VONAGE_API_SECRET}");
define("VONAGE_PROXY"                                 , "${VONAGE_PROXY}");
define("TWILIO_ACCOUNT_SID"                           , "${TWILIO_ACCOUNT_SID}");
define("TWILIO_AUTH_TOKEN"                            , "${TWILIO_AUTH_TOKEN}");

define("COMPANY_TO_PIVOT_PUBLIC_KEY"                  , "${COMPANY_TO_PIVOT_PUBLIC_KEY}");
define("PIVOT_TO_COMPANY_PRIVATE_KEY"                 , "${PIVOT_TO_COMPANY_PRIVATE_KEY}");

define("IS_SMS_ENABLED"				, "${IS_SMS_ENABLED}");
define("ONLY_OFFICE_IP"                               , "${ONLY_OFFICE_IP}");

define("IOS_TEST_PHONE"                               , "${IOS_TEST_PHONE}");
define("IOS_TEST_SMS_CODE"                            , "${IOS_TEST_SMS_CODE}");
define("IOS_TEST_PHONE2"                              , "${IOS_TEST_PHONE2}");
define("IOS_TEST_SMS_CODE2"                           , "${IOS_TEST_SMS_CODE2}");
define("IOS_TEST_PHONE3"                              , "${IOS_TEST_PHONE3}");
define("IOS_TEST_SMS_CODE3"                           , "${IOS_TEST_SMS_CODE3}");
define("IOS_TEST_PHONE4"                              , "${IOS_TEST_PHONE4}");
define("IOS_TEST_SMS_CODE4"                           , "${IOS_TEST_SMS_CODE4}");
define("ELECTRON_TEST_PHONE"                          , "${ELECTRON_TEST_PHONE}");
define("ELECTRON_TEST_SMS_CODE"                       , "${ELECTRON_TEST_SMS_CODE}");
define("ANDROID_TEST_PHONE"                           , "${ANDROID_TEST_PHONE}");
define("ANDROID_TEST_SMS_CODE"                        , "${ANDROID_TEST_SMS_CODE}");

define("TRUSTED_AUTH_TOKEN_LIST"                      , [${TRUSTED_AUTH_TOKEN_LIST}]);

// минимальное время ожидания синхронизации конфига при старте компаний
define("PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START" , ${PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START});

// версии эмодзи
define("EMOJI_VERSION_LIST"                           , [
	"ru-RU" => "${EMOJI_VERSION_RU}",
	"en-US" => "${EMOJI_VERSION_EN}",
	"de-DE" => "${EMOJI_VERSION_DE}",
	"fr-FR" => "${EMOJI_VERSION_FR}",
	"es-ES" => "${EMOJI_VERSION_ES}",
	"it-IT" => "${EMOJI_VERSION_IT}",
]);

##########################################################
# region COMPANY TIER
##########################################################

define("DOMINO_TIER_1"                                , ${DOMINO_TIER_1});
define("DOMINO_TIER_2"                                , ${DOMINO_TIER_2});
define("DOMINO_TIER_3"                                , ${DOMINO_TIER_3});
define("DOMINO_TIER_1_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_1_MIN_ACTIVITY_USER_COUNT});
define("DOMINO_TIER_2_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_2_MIN_ACTIVITY_USER_COUNT});
define("DOMINO_TIER_3_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_3_MIN_ACTIVITY_USER_COUNT});
define("AVAILABLE_DOMINO_TIER_CONFIG_LIST"            , [${AVAILABLE_DOMINO_TIER_CONFIG_LIST}]);

# endregion
##########################################################

define("IS_PARTNER_WEB_ENABLED"                       , ${IS_PARTNER_WEB_ENABLED});

##########################################################
# region BITRIX
##########################################################

define("IS_BITRIX_USER_ANALYTICS_ENABLED"             , ${IS_BITRIX_USER_ANALYTICS_ENABLED});

// endpoint для работы с API Битрикс
define("BITRIX_AUTHORIZED_ENDPOINT_URL"               , "${BITRIX_AUTHORIZED_ENDPOINT_URL}");

define("BITRIX_USER_REGISTERED_STAGE_ID"              , "${BITRIX_USER_REGISTERED_STAGE_ID}");
define("BITRIX_TEST_USER_REGISTERED_STAGE_ID"         , "${BITRIX_TEST_USER_REGISTERED_STAGE_ID}");
define("BITRIX_USER_REGISTERED_CATEGORY_ID"           , ${BITRIX_USER_REGISTERED_CATEGORY_ID});
define("BITRIX_TEST_USER_REGISTERED_CATEGORY_ID"      , ${BITRIX_TEST_USER_REGISTERED_CATEGORY_ID});
define("BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE"   , "${BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE}");
define("BITRIX_DEAL_USER_FIELD_NAME__REG_DATETIME"    , "${BITRIX_DEAL_USER_FIELD_NAME__REG_DATETIME}");
define("BITRIX_DEAL_USER_FIELD_NAME__USER_ID"         , "${BITRIX_DEAL_USER_FIELD_NAME__USER_ID}");
define("BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID"       , "${BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID}");
define("BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE"        , "${BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE}");

define("BITRIX_DEAL_REG_TYPE_VALUE__PRIMARY"          , "${BITRIX_DEAL_REG_TYPE_VALUE__PRIMARY}");
define("BITRIX_DEAL_REG_TYPE_VALUE__SECONDARY"        , "${BITRIX_DEAL_REG_TYPE_VALUE__SECONDARY}");
define("BITRIX_TEST_USER_NAME"                        , "${BITRIX_TEST_USER_NAME}");

# endregion
##########################################################

// включен ли функционал приглашения в пространство через атрибуцию
define("ATTRIBUTION_JOIN_SPACE_ENABLED"			, ${ATTRIBUTION_JOIN_SPACE_ENABLED});