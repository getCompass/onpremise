<?php

declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с smart app
 */
class Apiv2_SmartApp extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"getSuggestionList",
	];

	/**
	 * Метод для получения каталога приложений
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public function getSuggestionList():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$response = Domain_SmartApp_Scenario_Api::getSuggestionList($this->user_id, $company_id, $this->method_version);
		} catch (cs_UserNotFound|cs_CompanyNotExist|cs_CompanyIsNotActive|cs_UserNotInCompany|cs_CompanyIncorrectCompanyId|cs_CompanyIsHibernate|CompanyNotServedException) {
			throw new ParamException("incorrect params");
		}

		return $this->ok($response);
	}
}