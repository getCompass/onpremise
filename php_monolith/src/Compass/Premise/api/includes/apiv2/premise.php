<?php

namespace Compass\Premise;

use BaseFrame\Controller\Api;
use BaseFrame\Exception\Domain\ParseFatalException;
use cs_RowIsEmpty;

/**
 * контроллер для методов api/v2/premise/...
 */
class Apiv2_Premise extends Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"start",
		"getServerActivationStatus",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для получения стартовых данных
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public function start():array {

		[$onpremise_version, $permission_list] = Domain_Premise_Scenario_Api::start($this->user_id);

		$this->action->premiseProfile($this->user_id, $permission_list);

		return $this->ok([
			"premise_version" => (string) $onpremise_version,
			"server_uid"      => (string) SERVER_UID,
		]);
	}

	/**
	 * Метод для получения статуса активации сервера
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public function getServerActivationStatus():array {

		[$activation_status, $data] = Domain_Premise_Scenario_Api::getServerActivationStatus();

		return $this->ok([
			"activation_status" => (string) $activation_status,
			"data"              => (array) $data,
		]);
	}
}