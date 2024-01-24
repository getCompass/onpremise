<?php

namespace Compass\Speaker;

const CURRENT_MODULE    = "speaker";
const CODE_UNIQ_VERSION = 11000;

// максимальное количество участников звонка
const CALL_MAX_MEMBER_LIMIT = 16; // максимальное количество участников звонка

// статусы звонков
const CALL_STATUS_DIALING      = 10; // гудки
const CALL_STATUS_ESTABLISHING = 20; // установление соединения
const CALL_STATUS_SPEAKING     = 30; // разговор
const CALL_STATUS_HANGUP       = 40; // повесил трубку

// типы звонков
const CALL_TYPE_SINGLE = 1; // одиночный тет-а-тет звонок
const CALL_TYPE_GROUP  = 2; // групповой звонок

// причины завершения звонка
const CALL_FINISH_REASON_NONE            = 0;   // звонок не завершен
const CALL_FINISH_REASON_LINE_IS_BUSY    = 100; // линия занята
const CALL_FINISH_REASON_IGNORED         = 200; // звонок был проигнорирован
const CALL_FINISH_REASON_LOSE_CONNECTION = 300; // потеряно соединение
const CALL_FINISH_REASON_HANGUP          = 400; // повешена трубка
const CALL_FINISH_REASON_CANCELED        = 500; // звонок был отменен (пользователь позвонил и потом сам же сбросил на этапе гудков)

// типы событий
const EVENT_TYPE_CONVERSATION_MESSAGE          = 1;
const EVENT_TYPE_THREAD_MESSAGE                = 2;
const EVENT_TYPE_INVITE_MESSAGE                = 3;
const EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION = 4;