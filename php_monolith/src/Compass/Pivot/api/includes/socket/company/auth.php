<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * контроллер для работы с авторизацией в компанию
 */
class Socket_Company_Auth extends \BaseFrame\Controller\Socket
{
	// список доступных методов
	public const ALLOW_METHODS = [
		"checkUserSessionToken",
	];

	/**
	 * Валидирует данные для авторизацию в компанию по токену
	 */
	public function checkUserSessionToken(): array
	{

		$user_company_session_token = $this->post(\Formatter::TYPE_STRING, "user_company_session_token");

		try {
			Domain_Company_Scenario_Socket::checkUserCompanySessionToken($this->user_id, $this->company_id, $user_company_session_token);
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		} catch (cs_InvalidUserCompanySessionToken) {
			return $this->error(1, "passed invalid token");
		}

		return $this->ok();
	}
}
