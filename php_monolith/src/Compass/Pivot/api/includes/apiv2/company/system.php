<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Контроллер системных вызовов для компании
 */
class Apiv2_Company_System extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"status",
	];

	/**
	 * Проверяем статус компании
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function status():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$company_status = Domain_Company_Scenario_Api::checkStatus($this->user_id, $company_id);
		} catch (cs_UserNotInCompany) {
			throw new CaseException(1203002, "user is not member of company");
		} catch (cs_CompanyNotExist|Domain_Company_Exception_ConfigNotExist) {
			throw new CaseException(1203003, "not exist company");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		}

		return $this->ok(Apiv2_Format::companySystemStatus($company_status));
	}
}