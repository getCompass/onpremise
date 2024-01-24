<?php

namespace Compass\Pivot;

// @formatter:off

const CURRENT_MODULE = "pivot";
const CODE_UNIQ_VERSION = 1000;

// типы пользовательских уведомлений
const EVENT_TYPE_CONVERSATION_MESSAGE_MASK		= 1 << 1; // (00010) включены ли уведомления на новые сообщения в диалогах
const EVENT_TYPE_THREAD_MESSAGE_MASK			= 1 << 2; // (00100) включены ли уведомления на новые сообщения в тредах
const EVENT_TYPE_INVITE_MESSAGE_MASK			= 1 << 3; // (01000) включены ли уведомления на новые сообщения типа инвайт
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK	= 1 << 4; // (10000) включены ли уведомления на новые сообщения в групповых диалогах
const EVENT_TYPE_MEMBER_NOTIFICATION_MASK         	 = 1 << 5; // (100000) включены ли уведомления на новые уведомления в участниках

const EVENT_TYPE_ALL_MASK_V1 = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK;
const EVENT_TYPE_ALL_MASK_V2 = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;
const EVENT_TYPE_ALL_MASK    = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK | EVENT_TYPE_MEMBER_NOTIFICATION_MASK;

//типы событий
const EVENT_TYPE_CONVERSATION_MESSAGE		= 1;
const EVENT_TYPE_THREAD_MESSAGE			= 2;
const EVENT_TYPE_INVITE_MESSAGE			= 3;
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION	= 4;

// максимальное количество участников звонка
const CALL_MAX_MEMBER_LIMIT = 16; // максимальное количество

// @formatter:on