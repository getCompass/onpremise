<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * контроллер для работы с smart app
 */
class Apiv2_SmartApp extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"getAuthorizationToken",
	];

	/**
	 * Метод для получения токена авторизации в smart app
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getAuthorizationToken():array {

		$entity         = $this->post(\Formatter::TYPE_STRING, "entity", false);
		$entity_key     = $this->post(\Formatter::TYPE_STRING, "entity_key", false);
		$smart_app_name = $this->post(\Formatter::TYPE_STRING, "smart_app_name");
		$client_width   = $this->post(\Formatter::TYPE_INT, "client_width");
		$client_height  = $this->post(\Formatter::TYPE_INT, "client_height");

		try {
			$authorization_token = Domain_SmartApp_Scenario_Api::getAuthorizationToken(
				$this->user_id, $entity, $entity_key, $smart_app_name, $client_width, $client_height
			);
		} catch (cs_PlatformNotFound) {
			throw new ParamException("passed incorrect params");
		}

		return $this->ok([
			"authorization_token" => (string) $authorization_token,
		]);
	}
}