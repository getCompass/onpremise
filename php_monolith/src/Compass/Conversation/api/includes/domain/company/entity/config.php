<?php

namespace Compass\Conversation;

/**
 * класс для валидации данных конфига
 */
class Domain_Company_Entity_Config {

	// -------------------------------------------------------
	// !!! - часть данных дублируются в php_company
	// -------------------------------------------------------

	public const MODULE_EXTENDED_EMPLOYEE_CARD_KEY = "module_extended_employee_card";
	public const PUSH_BODY_DISPLAY_KEY             = "is_display_push_body";
	public const ADD_TO_GENERAL_CHAT_ON_HIRING     = "is_add_to_general_chat_on_hiring";

	public const GENERAL_CONVERSATION_KEY_NAME      = "general_conversation_key";
	public const HEROES_CONVERSATION_KEY_NAME       = "heroes_conversation_key";
	public const CHALLENGE_CONVERSATION_KEY_NAME    = "challenge_conversation_key";
	public const RESPECT_CONVERSATION_KEY_NAME      = "respect_conversation_key";
	public const EXACTINGNESS_CONVERSATION_KEY_NAME = "exactingness_conversation_key";
	public const ACHIEVEMENT_CONVERSATION_KEY_NAME  = "achievement_conversation_key";
	public const HIRING_CONVERSATION_KEY_NAME       = "hiring_conversation_key";
	public const NOTES_CONVERSATION_KEY_NAME        = "notes_conversation_key";
	public const SUPPORT_CONVERSATION_KEY_NAME      = "support_conversation_key";

	// дефолтные значение настроек, если не нашли в базе
	public const CONFIG_DEFAULT_VALUE_LIST = [
		self::MODULE_EXTENDED_EMPLOYEE_CARD_KEY => 0,
		self::PUSH_BODY_DISPLAY_KEY             => 0, // для безопасности, если по какой-то причине потеряли в базе конфиг
		self::ADD_TO_GENERAL_CHAT_ON_HIRING     => 1,
	];
}
