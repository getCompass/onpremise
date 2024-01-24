<?php

namespace Compass\Company;

const CURRENT_MODULE    = "company";
const CODE_UNIQ_VERSION = 2000;

// типы сущностей карточки
const EMPLOYEE_CARD_ENTITY_TYPE_ACHIEVEMENT  = 1;
const EMPLOYEE_CARD_ENTITY_TYPE_RESPECT      = 2;
const EMPLOYEE_CARD_ENTITY_TYPE_SPRINT       = 3;
const EMPLOYEE_CARD_ENTITY_TYPE_LOYALTY      = 4;
const EMPLOYEE_CARD_ENTITY_TYPE_EXACTINGNESS = 5;

// пользовательские типы уведомлений
// типы пользовательских уведомлений
const EVENT_TYPE_CONVERSATION_MESSAGE_MASK          = 1 << 1; // (000010) включены ли уведомления на новые сообщения в диалогах
const EVENT_TYPE_THREAD_MESSAGE_MASK                = 1 << 2; // (000100) включены ли уведомления на новые сообщения в тредах
const EVENT_TYPE_INVITE_MESSAGE_MASK                = 1 << 3; // (001000) включены ли уведомления на новые сообщения типа инвайт
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK = 1 << 4; // (010000) включены ли уведомления на новые сообщения в групповых диалогах
const EVENT_TYPE_MEMBER_NOTIFICATION_MASK           = 1 << 5; // (100000) включены ли уведомления на новые уведомления в участниках
const EVENT_TYPE_ALL_MASK_V1                        = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK;
const EVENT_TYPE_ALL_MASK_V2                        = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;
const EVENT_TYPE_ALL_MASK                           = EVENT_TYPE_CONVERSATION_MESSAGE_MASK | EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_INVITE_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK | EVENT_TYPE_MEMBER_NOTIFICATION_MASK;

// типы событий
const EVENT_TYPE_CONVERSATION_MESSAGE          = 1;
const EVENT_TYPE_THREAD_MESSAGE                = 2;
const EVENT_TYPE_INVITE_MESSAGE                = 3;
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION = 4;
const EVENT_TYPE_MEMBER_NOTIFICATION           = 5;

// значения версионности методов
const METHOD_VERSION_1 = 1;
const METHOD_VERSION_2 = 2;
const METHOD_VERSION_3 = 3;
const METHOD_VERSION_4 = 4;