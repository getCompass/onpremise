<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс action для валидации параметров
 */
class Domain_SmartApp_Action_ValidateParams {

	/**
	 * выполняем действие
	 *
	 * @throws ParamException
	 * @long
	 */
	public static function do(string|false $entity, string|false $entity_key, int $smart_app_id, int $client_width, int $client_height):void {

		if ($entity !== false && (!Domain_SmartApp_Entity_Validator::isCorrectEntity($entity) || mb_strlen($entity_key) < 1)) {
			throw new ParamException("passed incorrect params");
		}

		if ($smart_app_id < 1 || $client_width < 1 || $client_height < 1) {
			throw new ParamException("passed incorrect params");
		}
	}
}