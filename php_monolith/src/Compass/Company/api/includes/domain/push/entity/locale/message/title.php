<?php

namespace Compass\Company;

/**
 * Класс для перевода заголовков в пуше
 */
class Domain_Push_Entity_Locale_Message_Title extends Domain_Push_Entity_Locale_Message
{
	// разрешенные сущности, где может быть пуш
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::SPACE_ENTITY,
	];

	protected const _BASE_LOCALE_KEY = "NOTIFICATION_TITLE"; // базовый ключ локализации

	// новый активный участник
	public const MESSAGE_ACTIVE_MEMBER = "active_member";

	// новая заявка на вступление в компанию
	public const MESSAGE_JOIN_REQUEST = "join_request";

	// новый гость в компании
	public const MESSAGE_GUEST_MEMBER = "guest_member";

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::SPACE_ENTITY => [
			self::MESSAGE_ACTIVE_MEMBER,
			self::MESSAGE_JOIN_REQUEST,
			self::MESSAGE_GUEST_MEMBER,
		],
	];

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [
		self::MESSAGE_ACTIVE_MEMBER => 1,
		self::MESSAGE_JOIN_REQUEST  => 1,
		self::MESSAGE_GUEST_MEMBER  => 1,
	];
}
