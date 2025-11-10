<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Системный класс
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"clearTables",
		"getKickedUserList",
		"getPivotSocketKey",
		"execCompanyUpdateScript",
		"purgeCompanyStepOne",
		"purgeCompanyStepTwo",
		"checkReadyCompany",
		"hibernate",
		"awake",
		"relocateNotice",
		"relocateStart",
		"relocateEnd",
		"getActionCount",
		"sendMessageWithFile",
		"addRemindBot",
		"addSystemBot",
		"updateRemindBot",
		"updateSystemBot",
		"addOperator",
		"getCompanyAnalytic",
	];

	/**
	 * Очистить все таблицы
	 *
	 * @throws \parseException
	 */
	public function clearTables():array {

		// не убирать! только для тестовых серверов
		assertTestServer();

		Domain_System_Scenario_Socket::clearTables();

		return $this->ok();
	}

	/**
	 * Получить список кикнутых (для скрипта)
	 */
	public function getKickedUserList():array {

		$user_list = Domain_System_Scenario_Socket::getKickedUserList();

		return $this->ok([
			"user_list" => (array) $user_list,
		]);
	}

	/**
	 * получаем ключ для доступа к pivot
	 */
	public function getPivotSocketKey():array {

		return $this->ok([
			"socket_key" => (string) COMPANY_TO_PIVOT_PRIVATE_KEY,
		]);
	}

	/**
	 * Вызывает выполнения скрипта в компании.
	 * Используется для фикса данных в бд при обновлении.
	 */
	public function execCompanyUpdateScript():array {

		$script_name   = $this->post(\Formatter::TYPE_STRING, "script_name");
		$script_data   = $this->post(\Formatter::TYPE_ARRAY, "script_data");
		$flag_mask     = $this->post(\Formatter::TYPE_INT, "flag_mask");
		$proxy_modules = $this->post(\Formatter::TYPE_ARRAY, "proxy_modules");

		try {
			[$script_log, $error_log] = Type_Script_Handler::exec($script_name, $script_data, $flag_mask, $proxy_modules);
		} catch (\Exception $e) {
			return $this->error($e->getCode(), $e->getMessage());
		}

		return $this->ok([
			"script_log" => (string) $script_log,
			"error_log"  => (string) $error_log,
		]);
	}

	/**
	 * Первый этап очистки компании.
	 */
	public function purgeCompanyStepOne():array {

		Domain_System_Scenario_Socket::purgeCompanyStepOne();
		return $this->ok();
	}

	/**
	 * Второй этап очистки компании.
	 */
	public function purgeCompanyStepTwo():array {

		Domain_System_Scenario_Socket::purgeCompanyStepTwo();
		return $this->ok();
	}

	/**
	 * проверяем готовность компании
	 */
	public function checkReadyCompany():array {

		try {

			Domain_System_Scenario_Socket::checkReadyCompany();
		} catch (cs_TableIsNotEmpty $e) {
			return $this->error(404, "company is not empty " . $e->getMessage());
		}
		return $this->ok();
	}

	/**
	 * Произвести гибернацию компании
	 *
	 * @return array
	 */
	public function hibernate():array {

		$is_force_hibernate = $this->post(\Formatter::TYPE_INT, "is_force_hibernate", 0);
		$is_force_hibernate = $is_force_hibernate === 1;

		try {

			Domain_System_Scenario_Socket::hibernate($is_force_hibernate);
		} catch (Domain_System_Exception_CompanyHasActive) {
			return $this->error(2412001, "Company is active");
		}

		return $this->ok();
	}

	/**
	 * Разбудить компанию
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
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function awake():array {

		$hibernation_immunity_till            = $this->post(\Formatter::TYPE_INT, "hibernation_immunity_till");
		$last_wakeup_at                       = $this->post(\Formatter::TYPE_INT, "last_wakeup_at");
		$user_id_is_deleted_list              = $this->post(\Formatter::TYPE_ARRAY, "user_id_is_deleted_list");
		$user_info_is_deleted_list            = $this->post(\Formatter::TYPE_ARRAY, "user_info_is_deleted_list");
		$remind_bot_info                      = $this->post(\Formatter::TYPE_ARRAY, "remind_bot_info", []);
		$support_bot_info                     = $this->post(\Formatter::TYPE_ARRAY, "support_bot_info", []);
		$respect_conversation_avatar_file_key = $this->post(\Formatter::TYPE_STRING, "respect_conversation_avatar_file_key", "");
		$user_info_update_list                = $this->post(\Formatter::TYPE_ARRAY, "user_info_update_list", []);

		Domain_System_Scenario_Socket::awake(
			$hibernation_immunity_till, $last_wakeup_at, $user_id_is_deleted_list, $user_info_is_deleted_list, $remind_bot_info,
			$support_bot_info, $respect_conversation_avatar_file_key, $user_info_update_list
		);

		return $this->ok();
	}

	/**
	 * Уведомляем, что скоро начнется релокация
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function relocateNotice():array {

		$will_start_at = $this->post(\Formatter::TYPE_INT, "will_start_at");
		$company_id    = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_System_Scenario_Socket::relocateNotice($company_id, $will_start_at);

		return $this->ok();
	}

	/**
	 * Уведомить, что началась релокация
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function relocateStart():array {

		$will_be_available_at = $this->post(\Formatter::TYPE_INT, "will_be_available_at");
		$company_id           = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_System_Scenario_Socket::relocateStart($company_id, $will_be_available_at);

		return $this->ok();
	}

	/**
	 * Уведомить, что релокация закончилась
	 *
	 * @return array
	 */
	public function relocateEnd():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_System_Scenario_Socket::relocateEnd($company_id);

		return $this->ok();
	}

	// получаем количество дейсвия за сегодня
	public function getActionCount():array {

		$from_date_at = $this->post("?i", "from_date_at", dayStart());
		$rating       = Gateway_Bus_Company_Rating::get("general", $from_date_at + DAY1, dayStart() + DAY1, 0, 1);

		return $this->ok([
			"count" => (int) $rating->count,
		]);
	}

	// отправляем сообщение с файлом
	public function sendMessageWithFile():array {

		$sender_id = $this->post(\Formatter::TYPE_STRING, "sender_id");
		$file_key  = $this->post(\Formatter::TYPE_STRING, "file_key");

		$conversation_map = Type_Pack_Conversation::doDecrypt(STAT_GRAPH_IMAGE_CONVERSATION_RECEIVER);
		Gateway_Socket_Conversation::sendMessageWithFile($sender_id, $file_key, $conversation_map);

		return $this->ok();
	}

	/**
	 * добавляем бота Напоминания в компанию
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function addRemindBot():array {

		$bot_info = $this->post(\Formatter::TYPE_ARRAY, "bot_info");

		try {
			Gateway_Bus_CompanyCache::getMember($bot_info["user_id"]);
		} catch (\cs_RowIsEmpty) {

			Domain_User_Action_AddBot::do(
				$bot_info["user_id"],
				$bot_info["npc_type"],
				"",
				$bot_info["full_name"],
				$bot_info["avatar_file_key"],
				""
			);
		}

		return $this->ok();
	}

	/**
	 * добавляем системного бота в компанию
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function addSystemBot():array {

		$bot_info = $this->post(\Formatter::TYPE_ARRAY, "bot_info");

		try {
			Gateway_Bus_CompanyCache::getMember($bot_info["user_id"]);
		} catch (\cs_RowIsEmpty) {

			Domain_User_Action_AddBot::do(
				$bot_info["user_id"],
				$bot_info["npc_type"],
				"",
				$bot_info["full_name"],
				$bot_info["avatar_file_key"],
				""
			);
		}

		return $this->ok();
	}

	/**
	 * обновляем бота Напоминаний в компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 */
	public function updateRemindBot():array {

		$bot_info = $this->post(\Formatter::TYPE_ARRAY, "bot_info");

		try {

			Gateway_Bus_CompanyCache::getMember($bot_info["user_id"]);

			$set = [
				"npc_type"   => $bot_info["npc_type"],
				"updated_at" => time(),
			];
			Gateway_Db_CompanyData_MemberList::set($bot_info["user_id"], $set);

			Gateway_Bus_CompanyCache::clearMemberCacheByUserId($bot_info["user_id"]);
		} catch (\cs_RowIsEmpty) {
		}

		return $this->ok();
	}

	/**
	 * обновляем бота Напоминаний в компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @deprecated выпилить после заливки фичи intercom 25.02
	 */
	public function updateSystemBot():array {

		$bot_info = $this->post(\Formatter::TYPE_ARRAY, "bot_info");

		try {

			Gateway_Bus_CompanyCache::getMember($bot_info["user_id"]);

			$set = [
				"npc_type"   => $bot_info["npc_type"],
				"updated_at" => time(),
			];
			Gateway_Db_CompanyData_MemberList::set($bot_info["user_id"], $set);

			Gateway_Bus_CompanyCache::clearMemberCacheByUserId($bot_info["user_id"]);
		} catch (\cs_RowIsEmpty) {
		}

		return $this->ok();
	}

	/**
	 * Добавляем оператора в компанию
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 */
	public function addOperator():array {

		$operator_info = $this->post(\Formatter::TYPE_ARRAY, "operator_info");

		[$role, $permissions] = Domain_System_Scenario_Socket::addIntercomOperator(
			$operator_info["user_id"],
			$operator_info["npc_type"],
			$operator_info["full_name"],
			$operator_info["avatar_file_key"]
		);

		return $this->ok([
			"role"        => (int) $role,
			"permissions" => (int) $permissions,
		]);
	}

	/**
	 * Получаем данные для аналитики
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getCompanyAnalytic():array {

		$user_list = Domain_User_Action_Member_GetUserRoleList::do([Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR]);

		$member_id_list        = [];
		$administrator_id_list = [];
		foreach ($user_list as $user) {

			if ($user->role == Member::ROLE_MEMBER) {

				$member_id_list[] = $user->user_id;
				continue;
			}

			if ($user->role == Member::ROLE_ADMINISTRATOR) {
				$administrator_id_list[] = $user->user_id;
			}
		}

		try {

			$last_wakeup_at          = \CompassApp\Gateway\Db\CompanyData\CompanyDynamic::getValue(ShardingGateway::class, "last_wakeup_at");
			$hibernation_delay_token = Gateway_Db_CompanyData_HibernationDelayTokenList::getLastActivity();

			// получаем последнюю активность компании
			$hibernation_delayed_time = \CompassApp\Company\HibernationHandler::instance()->hibernationDelayedTime();
			if ($last_wakeup_at > time() - DAY14) {
				$last_active_at = $hibernation_delay_token->hibernation_delayed_till - $hibernation_delayed_time;
			} else {
				$last_active_at = $hibernation_delay_token->hibernation_delayed_till - $hibernation_delayed_time;
			}
		} catch (\cs_RowIsEmpty) {
			$last_active_at = 0;
		}

		return $this->ok([
			"member_count"       => (int) count($user_list),
			"last_active_at"     => (int) $last_active_at,
			"user_id_members"    => (array) $member_id_list,
			"user_id_admin_list" => (array) $administrator_id_list,
		]);
	}
}
