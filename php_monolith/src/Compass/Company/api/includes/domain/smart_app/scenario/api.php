<?php

namespace Compass\Company;

/**
 * сценарии smart app для API
 */
class Domain_SmartApp_Scenario_Api {

	/**
	 * редактируем бота
	 *
	 * @param int          $user_id
	 * @param string|false $entity
	 * @param string|false $entity_key
	 * @param string       $smart_app_name
	 * @param int          $client_width
	 * @param int          $client_height
	 *
	 * @return string
	 */
	public static function getAuthorizationToken(int $user_id, string|false $entity, string|false $entity_key, string $smart_app_name, int $client_width, int $client_height):string {

		// валидируем параметры
		Domain_SmartApp_Action_ValidateParams::do($entity, $entity_key, $smart_app_name, $client_width, $client_height);

		return Domain_SmartApp_Action_GetAuthorizationToken::do($user_id, $entity, $entity_key, $smart_app_name, $client_width, $client_height);
	}
}
