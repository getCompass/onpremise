<?php

namespace Compass\Premise;

use BaseFrame\Controller\Api;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use cs_RowIsEmpty;
use Formatter;

/**
 * контроллер для методов api/v2/premiseuser/...
 */
class Apiv2_PremiseUser extends Api
{
	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getPersonalCode",
		"getCounters",
		"getSignature",
		"getInfoBatching",
		"getIdList",
		"getGroupedByPermissionList",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для получения персонального кода
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Gateway_Premise_Exception_ServerNotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 */
	public function getPersonalCode(): array
	{

		try {
			$personal_code = Domain_User_Scenario_Api::getPersonalCode($this->user_id);
		} catch (Domain_Premise_Exception_ServerNotFound) {
			throw new CaseException(6201001, "server not found");
		}

		return $this->ok([
			"personal_code" => $personal_code,
		]);
	}

	/**
	 * Метод для получения количества участников и гостей
	 *
	 * @throws CaseException
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public function getCounters(): array
	{

		try {
			[$member_count, $guest_count] = Domain_User_Scenario_Api::getCounters($this->user_id);
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		}

		return $this->ok([
			"member_count" => (int) $member_count,
			"guest_count"  => (int) $guest_count,
		]);
	}

	/**
	 * Метод для получения подписи
	 */
	public function getSignature(): array
	{

		$signature = Domain_User_Scenario_Api::getSignature($this->user_id);

		return $this->ok([
			"signature" => $signature,
		]);
	}

	/**
	 * Метод для получения информации по пользователям
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function getInfoBatching(): array
	{

		$batch_premise_user_list = $this->post(Formatter::TYPE_JSON, "batch_premise_user_list");
		$need_premise_user_list  = $this->post(Formatter::TYPE_JSON, "need_premise_user_list", []);

		try {
			$formatted_premise_user_list = Domain_User_Scenario_Api::getInfoBatching($batch_premise_user_list, $need_premise_user_list);
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect need_premise_user_list");
		} catch (cs_WrongSignature) {
			throw new ParamException("incorrect signature in batch_premise_user_list");
		}

		return $this->ok([
			"premise_user_list" => (array) $formatted_premise_user_list,
		]);
	}

	/**
	 * Метод для получения пользователей по поисковому запросу
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public function getIdList(): array
	{

		$query = $this->post(Formatter::TYPE_STRING, "query", "");

		try {
			$user_id_list = Domain_User_Scenario_Api::getIdList($this->user_id, $query);
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		}

		$this->action->premiseUsers($user_id_list);

		return $this->ok([
			"premise_user_id_list" => (array) $user_id_list,
		]);
	}

	/**
	 * Метод для получения пользователей, сгруппированных по правам
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public function getGroupedByPermissionList(): array
	{

		$premise_space_id = $this->post(Formatter::TYPE_INT, "premise_space_id", 0);

		try {
			[$premise_administrator_list, $premise_accountant_list] = Domain_User_Scenario_Api::getGroupedByPermissionList($this->user_id, $premise_space_id);
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			throw new CaseException(7201001, "user have not permissions");
		}

		$this->action->premiseUsers(array_unique(array_merge($premise_administrator_list, $premise_accountant_list)));

		return $this->ok([
			"premise_administrator_list" => (array) $premise_administrator_list,
			"premise_accountant_list"    => (array) $premise_accountant_list,
		]);
	}
}
