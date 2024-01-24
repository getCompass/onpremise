<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * контроллер для работы с авторизацией в компанию
 */
class Socket_Company_Calls extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getBusyUsers",
		"getUserLastCall",
		"setLastCall",
		"getAllCompanyActiveCalls",
		"tryMarkLineAsBusy",
	];

	/**
	 * получить занятых звонками пользователей
	 */
	public function getBusyUsers():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		$user_id_busy_call_list = Domain_User_Scenario_Socket::getUserListActiveLastCall($user_id_list);

		return $this->ok([
			"user_id_busy_call_list" => (array) $user_id_busy_call_list,
		]);
	}

	/**
	 * получить все активные звонки
	 */
	public function getAllCompanyActiveCalls():array {

		$user_active_call_list = Domain_User_Scenario_Socket::getAllActiveCalls($this->company_id);

		$output = [];
		foreach ($user_active_call_list as $user_last_call) {
			$output[] = $this->_structToUserLastCall($user_last_call);
		}
		return $this->ok([
			"user_last_call_list" => (array) $output,
		]);
	}

	/**
	 * получить звонок пользователя
	 */
	public function getUserLastCall():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$user_last_call = Domain_User_Scenario_Socket::getUserLastCall($user_id, $this->company_id);

		if ($user_last_call === false) {
			return $this->error(404, "call not found");
		}

		return $this->ok([
			"user_last_call" => (array) $this->_structToUserLastCall($user_last_call),
		]);
	}

	/**
	 * установить звонок для пользователя
	 */
	public function setLastCall():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");
		$call_key     = $this->post(\Formatter::TYPE_STRING, "call_key");
		$is_finished  = $this->post(\Formatter::TYPE_INT, "is_finished", 0);
		$company_id   = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_User_Scenario_Socket::setLastCall($user_id_list, $call_key, $is_finished, $company_id);
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		} catch (cs_InvalidUserCompanySessionToken) {
			return $this->error(1, "passed invalid token");
		}

		return $this->ok();
	}

	// переводим структуру в ответ для метода
	protected function _structToUserLastCall(Struct_Db_PivotUser_UserLastCall $user_last_call):array {

		return [
			"call_key"    => $user_last_call->call_key,
			"is_finished" => $user_last_call->is_finished,
			"user_id"     => $user_last_call->user_id,
		];
	}

	/**
	 * пытаемся пометить разговорную линию занятой
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function tryMarkLineAsBusy():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");
		$call_key     = $this->post(\Formatter::TYPE_STRING, "call_key");
		$company_id   = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_User_Scenario_Socket::tryMarkCallLineAsBusy($user_id_list, $call_key, $company_id);
		} catch (cs_OneOfUsersHaveActiveCall $e) {

			return $this->error(2, "one of user have active call", [
				"busy_line_user_id_list" => $e->getUserListWithBusyLine(),
			]);
		}

		return $this->ok();
	}

}