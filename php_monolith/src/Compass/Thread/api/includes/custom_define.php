<?php

namespace Compass\Thread;

const CURRENT_MODULE = "thread";

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

// типы сообщений в диалогах
const CONVERSATION_MESSAGE_TYPE_TEXT       = 1;
const CONVERSATION_MESSAGE_TYPE_FILE       = 2;
const CONVERSATION_MESSAGE_TYPE_INVITE     = 3;
const CONVERSATION_MESSAGE_TYPE_QUOTE      = 4;
const CONVERSATION_MESSAGE_TYPE_MASS_QUOTE = 41;
const CONVERSATION_MESSAGE_TYPE_REPOST     = 5;
const CONVERSATION_MESSAGE_TYPE_SYSTEM     = 7;
const CONVERSATION_MESSAGE_TYPE_DELETED    = 8;
const CONVERSATION_MESSAGE_TYPE_CALL       = 9;

// типы сообщений репостов из тредов
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST            = 10; // сообщение-репост из треда
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT  = 11; // репостнутое из треда сообщение-текст
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE  = 12; // репостнутое из треда сообщение-файл
const CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE = 13; // репостнутое из треда сообщение-цитата
const CONVERSATION_MESSAGE_TYPE_RESPECT                  = 14; // новое сообщение типа респект

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
const THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND       = 35; // сообщение-Напоминание

// поделиться контактом
const CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER = 40;

// company_employee_metric_statistic
// типы пользовательских уведомлений
const EVENT_TYPE_CONVERSATION_MESSAGE_MASK          = 1 << 1; // (000010) включены ли уведомления на новые сообщения в диалогах
const EVENT_TYPE_THREAD_MESSAGE_MASK                = 1 << 2; // (000100) включены ли уведомления на новые сообщения в тредах
const EVENT_TYPE_INVITE_MESSAGE_MASK                = 1 << 3; // (001000) включены ли уведомления на новые сообщения типа инвайт
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK = 1 << 4; // (010000) включены ли уведомления на новые сообщения в группах
const EVENT_TYPE_ALL_MASK                           = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;

// типы тредов
const THREAD_TYPE_PRIVATE = 1;
const THREAD_TYPE_PUBLIC  = 2;
const THREAD_TYPE_OPEN    = 3;

// типы родительских сущностей, к которым может быть прикреплен тред
const PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE = 1; // родительская сущность - диалог
const PARENT_ENTITY_TYPE_THREAD_MESSAGE       = 2; // родительская сущность - тред
const PARENT_ENTITY_TYPE_HIRING_REQUEST       = 3; // родительская сущность - заявка найма
const PARENT_ENTITY_TYPE_DISMISSAL_REQUEST    = 4; // родительская сущность - заявка увольнения

// типы мета сущностей, к которым может быть прикреплен тред
const SOURCE_PARENT_ENTITY_TYPE_CONVERSATION = 1; // meta сущность - диалог
const SOURCE_PARENT_ENTITY_TYPE_THREAD       = 2; // meta сущность - тред

// права участников треда
const THREAD_MEMBER_ACCESS_READ   = 1 << 1; // (0001) право читать сообшения треда
const THREAD_MEMBER_ACCESS_WRITE  = 1 << 2; // (0010) право писать сообшения в тред
const THREAD_MEMBER_ACCESS_REACT  = 1 << 3; // (0100) право ставить реакции на сообщения в треде
const THREAD_MEMBER_ACCESS_MANAGE = 1 << 4; // (1000) право управлять тредом - удалять чужие сообщения и сам тред
const THREAD_MEMBER_ACCESS_ALL    = THREAD_MEMBER_ACCESS_READ | THREAD_MEMBER_ACCESS_WRITE | THREAD_MEMBER_ACCESS_REACT; // обычный набор прав участника треда любого типа

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