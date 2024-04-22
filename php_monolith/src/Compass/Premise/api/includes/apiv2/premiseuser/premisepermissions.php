<?php

namespace Compass\Premise;

use BaseFrame\Controller\Api;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use Formatter;

/**
 * контроллер для методов api/v2/premiseUser/premisePermissions/...
 */
class Apiv2_PremiseUser_PremisePermissions extends Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"set",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для добавления прав пользователю
	 *
	 * @throws ParseFatalException
	 * @throws ParamException
	 * @throws CaseException
	 */
	public function set():array {

		$premise_permissions = $this->post(Formatter::TYPE_JSON, "premise_permissions");
		$user_id             = $this->post(Formatter::TYPE_INT, "premise_user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PREMISE_PERMISSIONS_SET);

		try {

			Domain_User_Scenario_Api::setPermissions($this->user_id, $user_id, $premise_permissions);
		} catch (Domain_User_Exception_SelfDisabledPermissions) {
			throw new CaseException(7201002, "try disabled self permissions");
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		} catch (Domain_User_Exception_IsDisabled) {
			throw new CaseException(7201004, "user is deleted");
		} catch (Domain_User_Exception_NotFound) {
			throw new ParamException("user not found");
		}

		return $this->ok();
	}
}