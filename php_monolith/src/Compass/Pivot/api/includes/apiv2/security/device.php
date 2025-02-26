<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для методов работы с устройствами пользователя
 */
class Apiv2_Security_Device extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getAuthenticatedList",
		"invalidate",
		"invalidateOther",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Получить авторизованные устройства пользователя.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \queryException
	 */
	public function getAuthenticatedList():array {

		$authenticated_device_list = Domain_User_Scenario_Api_Security_Device::getAuthenticatedList($this->user_id, $this->session_uniq);

		return $this->ok([
			"authenticated_device_list" => (array) $authenticated_device_list,
		]);
	}

	/**
	 * Инвалидировать устройство пользователя.
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_blockException
	 */
	public function invalidate():array {

		$session_id = $this->post(\Formatter::TYPE_STRING, "session_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::DEVICE_INVALIDATE);

		try {
			Domain_User_Scenario_Api_Security_Device::invalidate($this->user_id, $session_id);
		} catch (Domain_User_Exception_Security_Device_IncorrectSessionId) {
			throw new ParamException("incorrect session_id");
		} catch (\cs_RowIsEmpty) {
			// в этом случае не кидаем ошибку
		}

		return $this->ok();
	}

	/**
	 * Инвалидировать остальные устройства пользователя.
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_blockException
	 */
	public function invalidateOther():array {

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::DEVICE_INVALIDATE_OTHER);

		Domain_User_Scenario_Api_Security_Device::invalidateOther($this->user_id, $this->session_uniq);

		return $this->ok();
	}
}