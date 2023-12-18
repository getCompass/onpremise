<?php

namespace Compass\Pivot;

/**
 * Класс для перевода заголовков в пуше
 */
class Domain_Push_Entity_Locale_Message_Title extends Domain_Push_Entity_Locale_Message {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::SPACE_ENTITY,
	];

	protected const _BASE_ARGS_COUNT = 0; // сколько изначально нужно аргументов
	protected const _BASE_LOCALE_KEY = "MESSAGE_TITLE"; // базовый ключ локализации

	// типы для сущности компании
	public const MESSAGE_CONFIRM_JOIN_REQUEST = "confirm_join_request";
	public const MESSAGE_REJECT_JOIN_REQUEST  = "reject_join_request";

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [
		self::MESSAGE_CONFIRM_JOIN_REQUEST => 1,
		self::MESSAGE_REJECT_JOIN_REQUEST  => 1,
	];

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::SPACE_ENTITY => [
			self::MESSAGE_CONFIRM_JOIN_REQUEST,
			self::MESSAGE_REJECT_JOIN_REQUEST,
		],
	];
}