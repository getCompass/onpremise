<?php

namespace Compass\Premise;

use BaseFrame\Controller\Api;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CaseException;

/**
 * контроллер для методов api/v2/premisespace/...
 */
class Apiv2_PremiseSpace extends Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getList",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для получения списка команд
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws CaseException
	 */
	public function getList():array {

		try {
			$premise_space_list = Domain_Company_Scenario_Api::getList($this->user_id);
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		}

		return $this->ok([
			"premise_space_list" => $premise_space_list,
		]);
	}
}