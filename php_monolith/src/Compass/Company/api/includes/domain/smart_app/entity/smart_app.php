<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с smart app
 */
class Domain_SmartApp_Entity_SmartApp {

	public const ENTITY_CONVERSATION = "conversation";
	public const ENTITY_THREAD       = "thread";

	/**
	 * проверяем корректность entity
	 *
	 * @param string|false $entity
	 *
	 * @return bool
	 */
	public static function isCorrectEntity(string|false $entity):bool {

		return in_array($entity, [self::ENTITY_CONVERSATION, self::ENTITY_THREAD]);
	}
}