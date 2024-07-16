<?php

namespace Compass\Conversation;

const CURRENT_MODULE    = "conversation";
const CODE_UNIQ_VERSION = 6000;

// -------------------------------------------------------
// типы файлов
// -------------------------------------------------------

const FILE_TYPE_DEFAULT  = 0; // файл
const FILE_TYPE_IMAGE    = 1; // картинка
const FILE_TYPE_VIDEO    = 2; // видео
const FILE_TYPE_AUDIO    = 3; // аудиозапись
const FILE_TYPE_DOCUMENT = 4; // документ
const FILE_TYPE_ARCHIVE  = 5; // архив
const FILE_TYPE_VOICE    = 6; // голосовое

// -------------------------------------------------------
// допустимые file_source
// -------------------------------------------------------

const FILE_SOURCE_AVATAR                = 1; // аватарка пользователя/группы
const FILE_SOURCE_MESSAGE_DEFAULT       = 2; // обычный файл в сообщении
const FILE_SOURCE_MESSAGE_IMAGE         = 3; // картинка в сообщении
const FILE_SOURCE_MESSAGE_VIDEO         = 4; // видео в сообщении
const FILE_SOURCE_MESSAGE_AUDIO         = 5; // аудиозапись в сообщении
const FILE_SOURCE_MESSAGE_DOCUMENT      = 6; // документ в сообщении
const FILE_SOURCE_MESSAGE_ARCHIVE       = 7; // архив в сообщении
const FILE_SOURCE_MESSAGE_VOICE         = 8; // голосовое сообщение прикрепленное к сообщению
const FILE_SOURCE_MESSAGE_PREVIEW_IMAGE = 9; // изображение из URL PREVIEW прикрепленное к сообщению

// -------------------------------------------------------
// file_source для дефолтных файлов
// -------------------------------------------------------

const FILE_SOURCE_AVATAR_DEFAULT = 11; // дефолтный файл аватара

// ---
const FILE_SOURCE_MESSAGE_ANY = 88; // любой тип файла в сообщении (нужен для поддержки старых клиентов) - временный

// -------------------------------------------------------
// php_conversation
// -------------------------------------------------------

// типы диалогов - при изменении/добавлении типа обязательно продублировать в php_thread!!!
const CONVERSATION_TYPE_SINGLE_DEFAULT         = 1;
const CONVERSATION_TYPE_GROUP_DEFAULT          = 2;
const CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT = 3;
const CONVERSATION_TYPE_PUBLIC_DEFAULT         = 4;
const CONVERSATION_TYPE_GROUP_HIRING           = 5;
const CONVERSATION_TYPE_GROUP_GENERAL          = 6;
const CONVERSATION_TYPE_SINGLE_NOTES           = 7;
const CONVERSATION_TYPE_GROUP_SUPPORT          = 8;
const CONVERSATION_TYPE_GROUP_RESPECT          = 9;

// параметры allow_status
const ALLOW_STATUS_GREEN_LIGHT = 1; // зеленый свет - можно отправлять сообщения
const ALLOW_STATUS_NEED_CHECK  = 2; // нужно проверить и выставить allow_status

// один из участников диалога был заблокирован
// статус нужен чтобы ему нельзя было писать и диалог (вместе с тредами) оставался в readonly режиме
// при блокировке пользователя все его single диалоги помечаются этим статусом, при разблокировке статусом NEED_CHECK
// при этом нам и клиенту нет разницы какой именно из двух пользователей был заблокирован, так как заблокированный не сможет физически открыть диалог
// а значит достаточно одного статуса и плашки в духе "пользователь был удален (заблокирован)"
const ALLOW_STATUS_MEMBER_DISABLED = 10;
const ALLOW_STATUS_MEMBER_DELETED  = 11;

// пользовательский бот был деактивирован
// статус нужен чтобы ему нельзя было писать и диалог (вместе с тредами) оставался в readonly режиме
// при деактивации бота все его single-диалоги помечаются статусом DISABLED, при разблокировке - статусом NEED_CHECK
// при удалении бота все его single-диалоги помечаются статусом DELETED, при разблокировке - статусом NEED_CHECK
const ALLOW_STATUS_USERBOT_DISABLED = 20;
const ALLOW_STATUS_USERBOT_DELETED  = 21;

// типы сообщений в диалогах
const CONVERSATION_MESSAGE_TYPE_TEXT       = 1;
const CONVERSATION_MESSAGE_TYPE_FILE       = 2;
const CONVERSATION_MESSAGE_TYPE_INVITE     = 3;
const CONVERSATION_MESSAGE_TYPE_QUOTE      = 4;
const CONVERSATION_MESSAGE_TYPE_MASS_QUOTE = 41;
const CONVERSATION_MESSAGE_TYPE_REPOST     = 5;
// define("MESSAGE_TYPE_URL"		, 6); // этот ковбой появится чуть позже :woman-tipping-hand: // к сожалению этот ковбой никогда не появится
const CONVERSATION_MESSAGE_TYPE_SYSTEM  = 7;
const CONVERSATION_MESSAGE_TYPE_DELETED = 8;
const CONVERSATION_MESSAGE_TYPE_CALL    = 9;
// типы сообщений репостов из тредов
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST            = 10; // сообщение-репост из треда
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT  = 11; // репостнутое из треда сообщение-текст
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE  = 12; // репостнутое из треда сообщение-файл
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE = 13; // репостнутое из треда сообщение-цитата
// типы сообщений карточки
const CONVERSATION_MESSAGE_TYPE_RESPECT          = 14; // новое сообщение типа респект
const CONVERSATION_MESSAGE_TYPE_SHARED_WIKI_PAGE = 15; // сообщение содержащее wiki-заметку, которой поделились через api-метод wiki.page.doShareList
// типы сообщений для диалога найма и увольнения
const CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST    = 16; // тип сообщения заявки на найм
const CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST = 17; // тип сообщения заявки на увольнение
const CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE    = 18; // тип сообщения - медиа конференция

// типы сообщений в тредах
const THREAD_MESSAGE_TYPE_TEXT                    = 1;
const THREAD_MESSAGE_TYPE_FILE                    = 2;
const THREAD_MESSAGE_TYPE_QUOTE                   = 3;
const THREAD_MESSAGE_TYPE_MASS_QUOTE              = 31;
const THREAD_MESSAGE_TYPE_DELETED                 = 4;
const THREAD_MESSAGE_TYPE_SYSTEM                  = 5;
const THREAD_MESSAGE_TYPE_REPOST                  = 6;
const THREAD_MESSAGE_TYPE_CONVERSATION_TEXT       = 20;
const THREAD_MESSAGE_TYPE_CONVERSATION_FILE       = 21;
const THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE = 22;
const THREAD_MESSAGE_TYPE_CONVERSATION_REPOST     = 23;
const THREAD_MESSAGE_TYPE_CONVERSATION_CALL       = 29;
const THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE       = 30;
const THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND       = 35; // сообщение-Напоминание в треде

// типы сообщений от системных ботов
const CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT                        = 20;
const CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING                      = 21;
const CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE                        = 22;
const CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA                  = 23;
const CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY            = 24;
const CONVERSATION_MESSAGE_TYPE_EDITOR_FEEDBACK_REQUEST                = 25;
const CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING                = 26;
const CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC      = 27;
const CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY                   = 28;
const CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE          = 30;
const CONVERSATION_MESSAGE_TYPE_WORK_TIME_AUTO_LOG_NOTICE              = 31;
const CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE       = 32;
const CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_MESSAGES_MOVED_NOTIFICATION = 33;
const CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND                      = 34; // сообщение-Напоминание в чате

// поделиться контактом
const CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER = 40;

// company_employee_metric_statistic
// типы пользовательских уведомлений
const EVENT_TYPE_CONVERSATION_MESSAGE_MASK          = 1 << 1; // (000010) включены ли уведомления на новые сообщения в диалогах
const EVENT_TYPE_THREAD_MESSAGE_MASK                = 1 << 2; // (000100) включены ли уведомления на новые сообщения в тредах
const EVENT_TYPE_INVITE_MESSAGE_MASK                = 1 << 3; // (001000) включены ли уведомления на новые сообщения типа инвайт
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK = 1 << 4; // (010000) включены ли уведомления на новые сообщения в группах
const EVENT_TYPE_ALL_MASK                           = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;

// типы нод
const PERMANENT_NODE_TYPE = "pdn";
const TEMPORARY_NODE_TYPE = "tdn";

// типы превью
const PREVIEW_TYPE_SITE           = 1;
const PREVIEW_TYPE_IMAGE          = 2;
const PREVIEW_TYPE_PROFILE        = 3;
const PREVIEW_TYPE_CONTENT        = 4;
const PREVIEW_TYPE_RESOURCE       = 5;
const PREVIEW_TYPE_VIDEO          = 6;
const PREVIEW_TYPE_COMPASS_INVITE = 7;
const PREVIEW_TYPE_SIMPLE         = 8;

// тип инвайта
const SINGLE_INVITE_TO_GROUP = 0; // приглашение в групповой диалог отправленное через одиночный диалог

const MESSAGE_TYPE_CONVERSATION = 1;
const MESSAGE_TYPE_THREAD       = 2;

// имена заявок
const DISMISSAL_REQUEST_NAME_TYPE = "dismissal_request";
const HIRING_REQUEST_NAME_TYPE    = "hiring_request";