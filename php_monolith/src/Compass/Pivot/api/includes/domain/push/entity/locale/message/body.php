<?php

namespace Compass\Pivot;

/**
 * Класс, описывающий локализацию тела пушей
 */
class Domain_Push_Entity_Locale_Message_Body extends Domain_Push_Entity_Locale_Message {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::SPACE_ENTITY,
	];

	// типы для системных сообщений тредов
	public const MESSAGE_CONFIRM_JOIN_REQUEST = "confirm_join_request";
	public const MESSAGE_REJECT_JOIN_REQUEST  = "reject_join_request";

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::SPACE_ENTITY => [
			self::MESSAGE_CONFIRM_JOIN_REQUEST,
			self::MESSAGE_REJECT_JOIN_REQUEST,
			self::MESSAGE_UNKNOWN,
		],
	];

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [];

	protected const _BASE_LOCALE_KEY = "MESSAGE_BODY"; // базовый ключ локализации
}