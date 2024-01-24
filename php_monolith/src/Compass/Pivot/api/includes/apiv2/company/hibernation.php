<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;
use \BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для гибернации компании
 */
class Apiv2_Company_Hibernation extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"wakeUp",
	];

	/**
	 * Разбудить компанию
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \queryException|Domain_System_Exception_IsNotAllowedServiceTask
	 */
	public function wakeUp():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {

			$need_check_at = Domain_Company_Scenario_Api::wakeup($this->user_id, $company_id);
		} catch (cs_UserNotInCompany) {

			throw new CaseException(1203002, "user is not member of company");
		} catch (cs_CompanyNotExist|Domain_Company_Exception_ConfigNotExist) {

			throw new CaseException(1203003, "not exist company");
		} catch (cs_CompanyIncorrectCompanyId) {

			throw new ParamException("invalid company id");
		} catch (Domain_Company_Exception_IsNotHibernated) {

			throw new CaseException(1203004, "company isnt hibernated");
		}

		return $this->ok([
			"need_check_at" => (int) $need_check_at,
		]);
	}
}