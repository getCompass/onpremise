<?php

namespace Compass\Conversation;

/**
 * класс, описывающий локализацию заголовка пушей
 */
class Domain_Push_Entity_Locale_Message_Title extends Domain_Push_Entity_Locale_Message {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [
		self::CONVERSATION_ENTITY,
	];

	public const MESSAGE_REMIND  = "remind"; // напоминание
	public const MESSAGE_SUPPORT = "support"; // служба поддержки

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [
		self::CONVERSATION_ENTITY => [
			self::MESSAGE_REMIND,
			self::MESSAGE_SUPPORT,
		],
	];

	protected const _BASE_LOCALE_KEY = "MESSAGE_TITLE"; // базовый ключ локализации
}
