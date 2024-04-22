<?php

namespace Compass\Premise;

use BaseFrame\Controller\Api;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;

/**
 * контроллер для методов api/v2/premiselicense/...
 */
class Apiv2_PremiseLicense extends Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getAuthenticationToken",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для получения токена аутентификации
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public function getAuthenticationToken():array {

		try {
			$auth_token = Domain_License_Scenario_Api::getAuthenticationToken($this->user_id);
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		} catch (Domain_Premise_Exception_ServerNotFound) {
			throw new CaseException(6201001, "server not found");
		}

		return $this->ok([
			"authentication_token" => (string) $auth_token->authentication_token,
			"need_refresh_at"      => (int) $auth_token->need_refresh_at,
		]);
	}
}