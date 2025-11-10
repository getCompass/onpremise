<?php

namespace Compass\Thread;

/**
 * класс для валидации данных конфига
 */
class Domain_Company_Entity_Config {

	public const MODULE_EXTENDED_EMPLOYEE_CARD_KEY = "module_extended_employee_card";
	public const PUSH_BODY_DISPLAY_KEY             = "is_display_push_body";
	public const SHOW_MESSAGE_READ_STATUS          = "show_message_read_status";
	public const UNLIMITED_MESSAGES_EDITING        = "unlimited_messages_editing";
	public const UNLIMITED_MESSAGES_DELETING       = "unlimited_messages_deleting";

	// дефолтные значение настроек, если не нашли в базе
	public const CONFIG_DEFAULT_VALUE_LIST = [
		self::MODULE_EXTENDED_EMPLOYEE_CARD_KEY => 0,
		self::PUSH_BODY_DISPLAY_KEY             => 0, // для безопасности, если по какой-то причине потеряли в базе конфиг
		self::SHOW_MESSAGE_READ_STATUS          => 1,
		self::UNLIMITED_MESSAGES_EDITING        => 0,
		self::UNLIMITED_MESSAGES_DELETING       => 0,
	];
}
