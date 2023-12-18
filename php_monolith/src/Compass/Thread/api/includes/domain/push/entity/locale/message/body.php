<?php

namespace Compass\Thread;

/**
 * класс, описывающий локализацию тела пушей
 */
class Domain_Push_Entity_Locale_Message_Body extends Domain_Push_Entity_Locale_Message {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::THREAD_ENTITY,
	];

	public const MESSAGE_TEXT     = "text"; // текст
	public const MESSAGE_IMAGE    = "image"; // изображение
	public const MESSAGE_VIDEO    = "video"; // видео
	public const MESSAGE_AUDIO    = "audio"; // аудио
	public const MESSAGE_DOCUMENT = "document"; // документ
	public const MESSAGE_ARCHIVE  = "archive"; // архив
	public const MESSAGE_VOICE    = "voice"; // голосовое
	public const MESSAGE_FILE     = "file"; // файл
	public const MESSAGE_QUOTE    = "quote"; // цитата
	public const MESSAGE_REPOST   = "repost"; // репост
	public const MESSAGE_HIDDEN   = "hidden"; // скрытое

	// типы для системных сообщений тредов
	public const MESSAGE_CREATE_HIRING_REQUEST         = "create_hiring_request";
	public const MESSAGE_ACCEPT_HIRING_REQUEST         = "accept_hiring_request";
	public const MESSAGE_CONFIRM_HIRING_REQUEST        = "confirm_hiring_request";
	public const MESSAGE_CANDIDATE_JOIN_COMPANY        = "candidate_join_company";
	public const MESSAGE_REJECT_HIRING_REQUEST         = "reject_hiring_request";
	public const MESSAGE_REVOKE_HIRING_REQUEST         = "revoke_hiring_request";
	public const MESSAGE_MEMBER_LEFT_COMPANY           = "member_left_company";
	public const MESSAGE_CREATE_DISMISSAL_REQUEST      = "create_dismissal_request";
	public const MESSAGE_CREATE_DISMISSAL_REQUEST_SELF = "create_dismissal_request_self";
	public const MESSAGE_APPROVE_DISMISSAL_REQUEST     = "approve_dismissal_request";
	public const MESSAGE_REJECT_DISMISSAL_REQUEST      = "reject_dismissal_request";
	public const MESSAGE_USER_FOLLOWED_THREAD          = "user_followed_thread";
	public const MESSAGE_USER_RECEIVED_RESPECT         = "user_received_respect";
	public const MESSAGE_USER_RECEIVED_EXACTINGNESS    = "user_received_exactingness";
	public const MESSAGE_USER_RECEIVED_ACHIEVEMENT     = "user_received_achievement";

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::THREAD_ENTITY => [
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
			self::MESSAGE_CREATE_HIRING_REQUEST,
			self::MESSAGE_ACCEPT_HIRING_REQUEST,
			self::MESSAGE_CONFIRM_HIRING_REQUEST,
			self::MESSAGE_CANDIDATE_JOIN_COMPANY,
			self::MESSAGE_REJECT_HIRING_REQUEST,
			self::MESSAGE_REVOKE_HIRING_REQUEST,
			self::MESSAGE_MEMBER_LEFT_COMPANY,
			self::MESSAGE_CREATE_DISMISSAL_REQUEST,
			self::MESSAGE_CREATE_DISMISSAL_REQUEST_SELF,
			self::MESSAGE_APPROVE_DISMISSAL_REQUEST,
			self::MESSAGE_REJECT_DISMISSAL_REQUEST,
			self::MESSAGE_USER_FOLLOWED_THREAD,
			self::MESSAGE_UNKNOWN,
			self::MESSAGE_USER_RECEIVED_RESPECT,
			self::MESSAGE_USER_RECEIVED_EXACTINGNESS,
			self::MESSAGE_USER_RECEIVED_ACHIEVEMENT,
		],
	];

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [
		self::MESSAGE_TEXT => 1,
	];

	protected const _BASE_LOCALE_KEY = "MESSAGE_BODY"; // базовый ключ локализации
}