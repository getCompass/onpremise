<?php

namespace Compass\Pivot;

const DOMINO_CONFIG_PATH = "/pivot_config/";

define(__NAMESPACE__ . "\SERVER_UID"                                   , "${SERVER_UID}");

// константы для того чтобы знать адрес сервера
define(__NAMESPACE__ . "\ANNOUNCEMENT_PROTOCOL"                        , "${PROTOCOL}");
define(__NAMESPACE__ . "\USERBOT_PROTOCOL"					, "${PROTOCOL}");
define(__NAMESPACE__ . "\USERBOT_DOMAIN"						, "userbot.${DOMAIN}");
define(__NAMESPACE__ . "\ANALYTIC_DOMAIN"					, "${ANALYTIC_DOMAIN}");

// константы для того чтобы знать адрес сервера партнера
define(__NAMESPACE__ . "\PARTNER_PROTOCOL"                             , "${PARTNER_PROTOCOL}");
define(__NAMESPACE__ . "\PARTNER_DOMAIN"                               , "${PARTNER_DOMAIN}");

// стартовый адрес
define(__NAMESPACE__ . "\START_URL"                                    , "${START_URL}");
define(__NAMESPACE__ . "\BILLING_URL"                                  , "${BILLING_URL}");
define(__NAMESPACE__ . "\PARTNER_URL"                                  , "${PARTNER_PROTOCOL}://${PARTNER_DOMAIN}/");

// константа которая указывает на путь к куки
define(__NAMESPACE__ . "\SESSION_COOKIE_DOMAIN"                        , "${PIVOT_DOMAIN}");

// с какого значения начинается инкремент для user_id в autoincrement
define(__NAMESPACE__ . "\BEGIN_INCREMENT_USER_ID"                      , "${BEGIN_INCREMENT_USER_ID}");

// константы для отправки уведомлений
define(__NAMESPACE__ . "\NOTICE_CHANNEL_SERVICE"                       , "${NOTICE_CHANNEL_SERVICE}");
define(__NAMESPACE__ . "\SMS_EXCEPTION"                                , "${SMS_EXCEPTION}");

// endpoint для отправки уведомлений в Compass
define(__NAMESPACE__ . "\COMPASS_NOTICE_ENDPOINT"                      , "${COMPASS_NOTICE_ENDPOINT}");

// для работы notice-бота
define(__NAMESPACE__ . "\COMPASS_NOTICE_BOT_TOKEN"                     , "${COMPASS_NOTICE_BOT_TOKEN}");
define(__NAMESPACE__ . "\COMPASS_NOTICE_BOT_SIGNATURE_KEY"             , "${COMPASS_NOTICE_BOT_SIGNATURE_KEY}");
define(__NAMESPACE__ . "\COMPASS_NOTICE_BOT_TOKEN_NEW"                 , "${COMPASS_NOTICE_BOT_TOKEN_NEW}");
define(__NAMESPACE__ . "\COMPASS_NOTICE_BOT_PROJECT"                   , "${COMPASS_NOTICE_BOT_PROJECT}");

// для работы уведомлений о изменении ранга компаний
define(__NAMESPACE__ . "\COMPANY_TIER_COMPASS_NOTICE_PROJECT"          , "${COMPANY_TIER_COMPASS_NOTICE_PROJECT}");
define(__NAMESPACE__ . "\COMPANY_TIER_COMPASS_NOTICE_TOKEN"            , "${COMPANY_TIER_COMPASS_NOTICE_TOKEN}");
define(__NAMESPACE__ . "\COMPANY_TIER_COMPASS_NOTICE_GROUP_ID"         , "${COMPANY_TIER_COMPASS_NOTICE_GROUP_ID}");

// для работы уведомлений от крона проверки состояния плана тарифов
define(__NAMESPACE__ . "\PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID"   , "${PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID}");

// для отправки бизнес статистики
define(__NAMESPACE__ . "\BUSINESS_COMPASS_NOTICE_BOT_TOKEN"            , "${BUSINESS_COMPASS_NOTICE_BOT_TOKEN}");
define(__NAMESPACE__ . "\BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY"    , "${BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY}");
define(__NAMESPACE__ . "\BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID"        , "${BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID}");

// группа для уведомлений о проблемах смс в compass
define(__NAMESPACE__ . "\SMS_ALERT_COMPASS_NOTICE_GROUP_ID"            , "${SMS_ALERT_COMPASS_NOTICE_GROUP_ID}");

// контанты для доступа к email с поступающими письмами мониторинга
define(__NAMESPACE__ . "\IMAP_CONNECT"                                 , "${IMAP_CONNECT}");
define(__NAMESPACE__ . "\MONITORING_EMAIL_LOGIN"                       , "${MONITORING_EMAIL_LOGIN}");
define(__NAMESPACE__ . "\MONITORING_EMAIL_PASSWORD"                    , "${MONITORING_EMAIL_PASSWORD}");

define(__NAMESPACE__ . "\STAT_GRAPH_IMAGE_GENERATOR_DOMAIN"            , "${STAT_GRAPH_IMAGE_GENERATOR_DOMAIN}");

// расширенный ключ (32 символа)
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_KEY_DEFAULT"                 , "${EXTENDED_ENCRYPT_KEY_DEFAULT}"); // ключ
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_IV_DEFAULT"                  , "${EXTENDED_ENCRYPT_IV_DEFAULT}"); // вектор шифрования

define(__NAMESPACE__ . "\ENTRYPOINT_ANALYTIC"                          , "${ENTRYPOINT_ANALYTIC}");

// -------------------------------------------------------
// SOCKET КЛЮЧИ ДЛЯ ДОВЕРЕННОГО ОБЩЕНИЯ МЕЖДУ МОДУЛЯМИ
// -------------------------------------------------------

define(__NAMESPACE__ . "\SOCKET_KEY_PUSHER"                            , "${SOCKET_KEY_GO_PUSHER}");
define(__NAMESPACE__ . "\SOCKET_KEY_USERBOT_CACHE"                     , "${SOCKET_KEY_USERBOT_CACHE}");
define(__NAMESPACE__ . "\SOCKET_KEY_MIGRATION"                         , "${SOCKET_KEY_MIGRATION}");
define(__NAMESPACE__ . "\SOCKET_KEY_DEVELOPMENT"                       , "${SOCKET_KEY_DEVELOPMENT}");
define(__NAMESPACE__ . "\SOCKET_KEY_COLLECTOR"                         , "${SOCKET_KEY_COLLECTOR}");
define(__NAMESPACE__ . "\SOCKET_KEY_STAGE"                             , "${SOCKET_KEY_STAGE}");
define(__NAMESPACE__ . "\SOCKET_KEY_BILLING"                           , "${SOCKET_KEY_BILLING}");
define(__NAMESPACE__ . "\SOCKET_KEY_WEBSTAT"                           , "${SOCKET_KEY_WEBSTAT}");
define(__NAMESPACE__ . "\SOCKET_KEY_BOTHUB"                            , "${SOCKET_KEY_BOTHUB}");

// -------------------------------------------------------
// SALT ДЛЯ УПАКОВЩИКОВ ПРОЕКТА
// -------------------------------------------------------

// соль для формирования auth_map
define(__NAMESPACE__ . "\SALT_PACK_AUTH"                               , [
	1 => "${SALT_PACK_AUTH_V1}",
	2 => "${SALT_PACK_AUTH_V2}"
]);

// -------------------------------------------------------
// ОСТАЛЬНОЕ
// -------------------------------------------------------

// secret от Google reCAPTCHA
define(__NAMESPACE__ . "\COMTEAM_SECRET_CAPTCHA_ANDROID"               , "${COMTEAM_SECRET_CAPTCHA_ANDROID}");
define(__NAMESPACE__ . "\COMPASS_SECRET_CAPTCHA_ANDROID"               , "${COMPASS_SECRET_CAPTCHA_ANDROID}");
define(__NAMESPACE__ . "\SECRET_CAPTCHA_SITE"                          , "${SECRET_CAPTCHA_SITE}");

define(__NAMESPACE__ . "\SALT_PHONE_NUMBER"                            , "${SALT_PHONE_NUMBER}");
define(__NAMESPACE__ . "\SALT_MAIL_ADDRESS"                            , "${SALT_MAIL_ADDRESS}");
define(__NAMESPACE__ . "\SALT_CODE"                                    , [
	1 => "${SALT_CODE_V1}",
]);
define(__NAMESPACE__ . "\SALT_USERAGENT"                               , [
	1 => "${SALT_USERAGENT_V1}",
]);

define(__NAMESPACE__ . "\SMS_AGENT_LOGIN"                              , "${SMS_AGENT_LOGIN}");
define(__NAMESPACE__ . "\SMS_AGENT_PASSWORD"                           , "${SMS_AGENT_PASSWORD}");
define(__NAMESPACE__ . "\VONAGE_API_KEY"                               , "${VONAGE_API_KEY}");
define(__NAMESPACE__ . "\VONAGE_API_SECRET"                            , "${VONAGE_API_SECRET}");
define(__NAMESPACE__ . "\VONAGE_PROXY"                                 , "${VONAGE_PROXY}");
define(__NAMESPACE__ . "\TWILIO_ACCOUNT_SID"                           , "${TWILIO_ACCOUNT_SID}");
define(__NAMESPACE__ . "\TWILIO_AUTH_TOKEN"                            , "${TWILIO_AUTH_TOKEN}");

define(__NAMESPACE__ . "\COMPANY_TO_PIVOT_PUBLIC_KEY"                  , "${COMPANY_TO_PIVOT_PUBLIC_KEY}");
define(__NAMESPACE__ . "\PIVOT_TO_COMPANY_PRIVATE_KEY"                 , "${PIVOT_TO_COMPANY_PRIVATE_KEY}");

define(__NAMESPACE__ . "\IS_SMS_ENABLED"				, "${IS_SMS_ENABLED}");
define(__NAMESPACE__ . "\ONLY_OFFICE_IP"                               , "${ONLY_OFFICE_IP}");

define(__NAMESPACE__ . "\IOS_TEST_PHONE"                               , "${IOS_TEST_PHONE}");
define(__NAMESPACE__ . "\IOS_TEST_SMS_CODE"                            , "${IOS_TEST_SMS_CODE}");
define(__NAMESPACE__ . "\IOS_TEST_PHONE2"                              , "${IOS_TEST_PHONE2}");
define(__NAMESPACE__ . "\IOS_TEST_SMS_CODE2"                           , "${IOS_TEST_SMS_CODE2}");
define(__NAMESPACE__ . "\IOS_TEST_PHONE3"                              , "${IOS_TEST_PHONE3}");
define(__NAMESPACE__ . "\IOS_TEST_SMS_CODE3"                           , "${IOS_TEST_SMS_CODE3}");
define(__NAMESPACE__ . "\IOS_TEST_PHONE4"                              , "${IOS_TEST_PHONE4}");
define(__NAMESPACE__ . "\IOS_TEST_SMS_CODE4"                           , "${IOS_TEST_SMS_CODE4}");
define(__NAMESPACE__ . "\ELECTRON_TEST_PHONE"                          , "${ELECTRON_TEST_PHONE}");
define(__NAMESPACE__ . "\ELECTRON_TEST_SMS_CODE"                       , "${ELECTRON_TEST_SMS_CODE}");
define(__NAMESPACE__ . "\ANDROID_TEST_PHONE"                           , "${ANDROID_TEST_PHONE}");
define(__NAMESPACE__ . "\ANDROID_TEST_SMS_CODE"                        , "${ANDROID_TEST_SMS_CODE}");

define(__NAMESPACE__ . "\TRUSTED_AUTH_TOKEN_LIST"                      , [${TRUSTED_AUTH_TOKEN_LIST}]);

// минимальное время ожидания синхронизации конфига при старте компаний
define(__NAMESPACE__ . "\PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START" , ${PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START});

// версии эмодзи
define(__NAMESPACE__ . "\EMOJI_VERSION_LIST"                           , [
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

define(__NAMESPACE__ . "\DOMINO_TIER_1"                                , ${DOMINO_TIER_1});
define(__NAMESPACE__ . "\DOMINO_TIER_2"                                , ${DOMINO_TIER_2});
define(__NAMESPACE__ . "\DOMINO_TIER_3"                                , ${DOMINO_TIER_3});
define(__NAMESPACE__ . "\DOMINO_TIER_1_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_1_MIN_ACTIVITY_USER_COUNT});
define(__NAMESPACE__ . "\DOMINO_TIER_2_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_2_MIN_ACTIVITY_USER_COUNT});
define(__NAMESPACE__ . "\DOMINO_TIER_3_MIN_ACTIVITY_USER_COUNT"        , ${DOMINO_TIER_3_MIN_ACTIVITY_USER_COUNT});
define(__NAMESPACE__ . "\AVAILABLE_DOMINO_TIER_CONFIG_LIST"            , [${AVAILABLE_DOMINO_TIER_CONFIG_LIST}]);

# endregion
##########################################################

##########################################################
# region BITRIX
##########################################################

define(__NAMESPACE__ . "\IS_BITRIX_USER_ANALYTICS_ENABLED"             , ${IS_BITRIX_USER_ANALYTICS_ENABLED});

// endpoint для работы с API Битрикс
define(__NAMESPACE__ . "\BITRIX_AUTHORIZED_ENDPOINT_URL"               , "${BITRIX_AUTHORIZED_ENDPOINT_URL}");

define(__NAMESPACE__ . "\BITRIX_USER_REGISTERED_STAGE_ID"              , "${BITRIX_USER_REGISTERED_STAGE_ID}");
define(__NAMESPACE__ . "\BITRIX_TEST_USER_REGISTERED_STAGE_ID"         , "${BITRIX_TEST_USER_REGISTERED_STAGE_ID}");
define(__NAMESPACE__ . "\BITRIX_USER_REGISTERED_CATEGORY_ID"           , ${BITRIX_USER_REGISTERED_CATEGORY_ID});
define(__NAMESPACE__ . "\BITRIX_TEST_USER_REGISTERED_CATEGORY_ID"      , ${BITRIX_TEST_USER_REGISTERED_CATEGORY_ID});
define(__NAMESPACE__ . "\BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE"   , "${BITRIX_DEAL_USER_FIELD_NAME__HAS_OWN_SPACE}");
define(__NAMESPACE__ . "\BITRIX_DEAL_USER_FIELD_NAME__REG_DATETIME"    , "${BITRIX_DEAL_USER_FIELD_NAME__REG_DATETIME}");
define(__NAMESPACE__ . "\BITRIX_DEAL_USER_FIELD_NAME__USER_ID"         , "${BITRIX_DEAL_USER_FIELD_NAME__USER_ID}");
define(__NAMESPACE__ . "\BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID"       , "${BITRIX_DEAL_USER_FIELD_NAME__SOURCE_ID}");
define(__NAMESPACE__ . "\BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE"        , "${BITRIX_DEAL_USER_FIELD_NAME__REG_TYPE}");

define(__NAMESPACE__ . "\BITRIX_DEAL_REG_TYPE_VALUE__PRIMARY"          , "${BITRIX_DEAL_REG_TYPE_VALUE__PRIMARY}");
define(__NAMESPACE__ . "\BITRIX_DEAL_REG_TYPE_VALUE__SECONDARY"        , "${BITRIX_DEAL_REG_TYPE_VALUE__SECONDARY}");
define(__NAMESPACE__ . "\BITRIX_TEST_USER_NAME"                        , "${BITRIX_TEST_USER_NAME}");

# endregion
##########################################################

// промежуток времени с момента регистрации пользователя, за который выбираем /join/ посещения для поиска совпадений
define(__NAMESPACE__ . "\ATTRIBUTION_JOIN_SPACE_VISITS_MATCHING_PERIOD"      , ${ATTRIBUTION_JOIN_SPACE_VISITS_MATCHING_PERIOD});