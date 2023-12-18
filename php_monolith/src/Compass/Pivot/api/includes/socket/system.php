<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Систменый конторллер
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getCompanySocketKey",
		"getDominoCompanyIdList",
		"getActiveDominoCompanyIdList",
		"purgeCompany",
		"clearAllCache",
		"lockBeforeMigration",
		"unlockAfterMigration",
		"getDominoMigrationOptions",
		"getActiveCompanyIdList",
		"getScriptCompanyUserList",
		"getRootUserId",
	];

	/**
	 * Метод для получения сокет ключа компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function getCompanySocketKey():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$company = Domain_Company_Entity_Company::get($company_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(404, "Company not found");
		}

		$auth_key = Domain_Company_Entity_Company::getPublicKey($company->extra);

		return $this->ok([
			"socket_key" => (string) $auth_key,
		]);
	}

	/**
	 * Метод для получения списка компаний доминошку
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getDominoCompanyIdList():array {

		$domino_id = $this->post(\Formatter::TYPE_STRING, "domino_id");

		$company_id_list = Domain_System_Scenario_Socket::getDominoCompanyIdList($domino_id);

		return $this->ok([
			"company_id_list" => (array) $company_id_list,
		]);
	}

	/**
	 * Метод для получения списка компаний доминошку
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getActiveDominoCompanyIdList():array {

		$domino_id = $this->post(\Formatter::TYPE_STRING, "domino_id");

		$company_id_list = Domain_System_Scenario_Socket::getActiveDominoCompanyIdList($domino_id);

		return $this->ok([
			"company_id_list" => (array) $company_id_list,
		]);
	}

	/**
	 * Чистим весь кэш для сброса блокировок
	 */
	public function clearAllCache():array {

		Domain_System_Scenario_Socket::clearAllCache();

		return $this->ok();
	}

	/**
	 * Залочить компанию лля процесса миграции
	 *
	 * @return array
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \returnException
	 */
	public function lockBeforeMigration():array {

		$domino_id  = $this->post(\Formatter::TYPE_STRING, "domino_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");
		$need_port  = $this->post(\Formatter::TYPE_INT, "need_port", 0) == 1;

		try {
			Domain_Company_Scenario_Socket::lockBeforeMigration($domino_id, $company_id, $need_port);
		} catch (Domain_Company_Exception_IsBusy) {
			return $this->error(1407001, "company is busy already");
		}

		return $this->ok();
	}

	/**
	 * Разалочить компанию после процесса миграции
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \returnException
	 */
	public function unlockAfterMigration():array {

		$domino_id       = $this->post(\Formatter::TYPE_STRING, "domino_id");
		$company_id      = $this->post(\Formatter::TYPE_INT, "company_id");
		$is_service_port = $this->post(\Formatter::TYPE_INT, "is_service_port", 0) == 1;

		Domain_Company_Scenario_Socket::unlockAfterMigration($domino_id, $company_id, $is_service_port);

		return $this->ok();
	}

	/**
	 * Узнать параметры, необходимые для миграции на домино
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getDominoMigrationOptions():array {

		$domino_id = $this->post(\Formatter::TYPE_STRING, "domino_id");

		[$need_backup, $need_wakeup] = Domain_Company_Scenario_Socket::getDominoMigrationOptions($domino_id);

		return $this->ok([
			"need_backup" => (bool) $need_backup,
			"need_wakeup" => (bool) $need_wakeup,
		]);
	}

	/**
	 * Получаем идентификаторы компаний находящихся в статусе COMPANY_STATUS_ACTIVE
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public function getActiveCompanyIdList():array {

		$company_id_list = Domain_Company_Scenario_Socket::getActiveCompanyIdList();

		return $this->ok([
			"company_id_list" => (array) $company_id_list,
		]);
	}

	/**
	 * получаем информацию по пользователям (!!! используется только в скрипте - удалить после релиза Участников)
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getScriptCompanyUserList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY, "user_id_list");

		$user_info_list = Gateway_Db_PivotCompany_CompanyUserList::getByUserIdList($this->company_id, $user_id_list, true);

		$getted_user_id_list     = [];
		$need_from_lobby_id_list = [];
		foreach ($user_id_list as $user_id) {

			if (!isset($user_info_list[$user_id])) {

				$need_from_lobby_id_list[] = $user_id;
				continue;
			}
			$getted_user_id_list[] = $user_id;
		}

		$not_found_user_list = [];
		foreach ($need_from_lobby_id_list as $user_id) {

			try {
				Gateway_Db_PivotUser_CompanyLobbyList::getOne($user_id, $this->company_id);
			} catch (\cs_RowIsEmpty) {

				$not_found_user_list[] = $user_id;
				continue;
			}
			$getted_user_id_list[] = $user_id;
		}

		$user_list_info = Gateway_Bus_PivotCache::getUserListInfo($getted_user_id_list);

		$delete_account_user_id_ist = [];
		foreach ($user_list_info as $user_info) {

			if (Type_User_Main::isDisabledProfile($user_info->extra)) {
				$delete_account_user_id_ist[] = $user_info->user_id;
			}
		}

		return $this->ok([
			"delete_account_user_id_ist" => (array) $delete_account_user_id_ist,
			"not_found_user_id_list"     => (array) $not_found_user_list,
		]);
	}

	/**
	 * Получить user_id рут-пользователя для онпремайз
	 */
	public function getRootUserId():array {

		return $this->ok([
			"user_id" => (int) Domain_System_Scenario_Socket::getRootUserId(),
		]);
	}
}