<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для сокет-методов работы с компанией
 */
class Socket_Company_Main extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"doActionsOnCreateCompany",
		"changeCompanyCreatedAt",
		"exists",
		"delete",
		"getCompanyConfigStatus",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод выполнения действий при создании компании
	 *
	 * @throws \Exception
	 */
	public function doActionsOnCreateCompany():array {

		$creator_user_id           = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$company_name              = $this->post(\Formatter::TYPE_STRING, "company_name");
		$created_at                = $this->post(\Formatter::TYPE_INT, "created_at");
		$default_file_key_list     = $this->post(\Formatter::TYPE_ARRAY, "default_file_key_list");
		$bot_list                  = $this->post(\Formatter::TYPE_ARRAY, "bot_list");
		$hibernation_immunity_till = $this->post(\Formatter::TYPE_INT, "hibernation_immunity_till");
		$is_enabled_employee_card  = $this->post(\Formatter::TYPE_INT, "is_enabled_employee_card");
		$creator_locale            = $this->post(\Formatter::TYPE_STRING, "creator_locale");

		Domain_Company_Scenario_Socket::actionsOnCreateCompany(
			$creator_user_id,
			$company_name,
			$created_at,
			$default_file_key_list,
			$bot_list,
			$hibernation_immunity_till,
			$is_enabled_employee_card,
			$creator_locale,
		);

		return $this->ok();
	}

	/**
	 * Меняем время создания компании
	 *
	 * @return array
	 * @throws paramException
	 * @throws \parseException
	 */
	public function changeCompanyCreatedAt():array {

		$created_at = $this->post(\Formatter::TYPE_INT, "created_at");

		// устанавливаем время, когда создана компания
		$value = ["value" => $created_at];
		Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_CREATED_AT);

		return $this->ok();
	}

	/**
	 * Проверяем, существует ли компания
	 *
	 * @return array
	 * @throws \parseException|\returnException
	 */
	public function exists():array {

		$exists = Domain_Company_Scenario_Socket::checkIfExistCurrent();

		return $this->ok([
			"exists" => (int) $exists,
		]);
	}

	/**
	 * Удалить компанию
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function delete():array {

		$deleted_at = $this->post(\Formatter::TYPE_INT, "deleted_at");

		try {
			Domain_Company_Scenario_Socket::delete($this->user_id, $deleted_at);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "User is not a company owner");
		}

		return $this->ok();
	}

	/**
	 * Возвращает текущий статус компании из конфига.
	 *
	 * Этот сокет вызывает, когда компания выходит из гибернации или завершает релокацию,
	 * поскольку синхронизация конфига может занимать какое-то время.
	 */
	public function getCompanyConfigStatus():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "check_company_id");

		try {

			// получаем данные из конфига
			$company_status = (new \CompassApp\Conf\Company($company_id))->get("COMPANY_STATUS");
		} catch (\BaseFrame\Exception\Request\CompanyConfigNotFoundException) {
			$company_status = 0;
		}

		return $this->ok([
			"status" => (int) $company_status,
		]);
	}
}
