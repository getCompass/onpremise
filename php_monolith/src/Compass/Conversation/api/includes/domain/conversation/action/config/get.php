<?php

namespace Compass\Conversation;

/**
 * Action для получения конфига диалогов
 */
class Domain_Conversation_Action_Config_Get {

	public static function do(string $key):array {

		$config = Type_Conversation_Config::init();

		return $config->get($key);
	}

}
