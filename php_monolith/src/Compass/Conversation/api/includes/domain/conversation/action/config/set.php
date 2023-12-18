<?php

namespace Compass\Conversation;

/**
 * Action для изменения конфига компании
 */
class Domain_Conversation_Action_Config_Set {

	/**
	 *
	 * @throws \parseException
	 */
	public static function do(mixed $value, string $key):void {

		$config = Type_Conversation_Config::init();

		$config->set($key, [
			"value"      => ["value" => $value],
			"updated_at" => time(),
		]);
	}
}
