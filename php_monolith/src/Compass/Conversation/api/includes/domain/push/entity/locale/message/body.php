<?php

namespace Compass\Conversation;

/**
 * класс, описывающий локализацию тела пушей
 */
class Domain_Push_Entity_Locale_Message_Body extends Domain_Push_Entity_Locale_Message {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::CONVERSATION_ENTITY,
	];

	public const MESSAGE_TEXT                              = "text";                              // текст
	public const MESSAGE_IMAGE                             = "image";                             // изображение
	public const MESSAGE_VIDEO                             = "video";                             // видео
	public const MESSAGE_AUDIO                             = "audio";                             // аудио
	public const MESSAGE_DOCUMENT                          = "document";                          // документ
	public const MESSAGE_ARCHIVE                           = "archive";                           // архив
	public const MESSAGE_VOICE                             = "voice";                             // голосовое
	public const MESSAGE_FILE                              = "file";                              // файл
	public const MESSAGE_QUOTE                             = "quote";                             // цитата
	public const MESSAGE_REPOST                            = "repost";                            // репост
	public const MESSAGE_HIDDEN                            = "hidden";                            // скрытое
	public const MESSAGE_INVITE                            = "invite";                            // инвайт
	public const MESSAGE_HIRING                            = "hiring";                            // найм
	public const MESSAGE_DISMISSAL                         = "dismissal";                         // увольнение
	public const MESSAGE_SYSTEM                            = "system";                            // системное сообщение
	public const MESSAGE_EDITOR_EMPLOYEE_ANNIVERSARY       = "editor_employee_anniversary";       // уведомление о годовщине сотрудника в компании
	public const MESSAGE_COMPANY_EMPLOYEE_METRIC_STATISTIC = "company_employee_metric_statistic"; // статистика за месяц по спасибо / требовательности
	public const MESSAGE_SYSTEM_BOT_RATING                 = "system_bot_rating";                 // статистика по действиям за неделю
	public const MESSAGE_EDITOR_WORKSHEET_RATING           = "editor_worksheet_rating";           // статистика рабочих часов
	public const MESSAGE_MEDIA_CONFERENCE                  = "media_conference";                  // конференция

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::CONVERSATION_ENTITY => [
			self::MESSAGE_TEXT,
			self::MESSAGE_IMAGE,
			self::MESSAGE_VIDEO,
			self::MESSAGE_AUDIO,
			self::MESSAGE_DOCUMENT,
			self::MESSAGE_ARCHIVE,
			self::MESSAGE_VOICE,
			self::MESSAGE_FILE,
			self::MESSAGE_QUOTE,
			self::MESSAGE_REPOST,
			self::MESSAGE_HIDDEN,
			self::MESSAGE_INVITE,
			self::MESSAGE_HIRING,
			self::MESSAGE_DISMISSAL,
			self::MESSAGE_SYSTEM,
			self::MESSAGE_UNKNOWN,
			self::MESSAGE_EDITOR_EMPLOYEE_ANNIVERSARY,
			self::MESSAGE_COMPANY_EMPLOYEE_METRIC_STATISTIC,
			self::MESSAGE_SYSTEM_BOT_RATING,
			self::MESSAGE_EDITOR_WORKSHEET_RATING,
			self::MESSAGE_MEDIA_CONFERENCE,
		],
	];

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [
		self::MESSAGE_TEXT             => 1,
		self::MESSAGE_MEDIA_CONFERENCE => 1,
	];

	protected const _BASE_LOCALE_KEY = "MESSAGE_BODY"; // базовый ключ локализации
}
