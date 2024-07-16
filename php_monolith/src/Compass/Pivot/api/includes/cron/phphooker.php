<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\ExceptionUtils;
use BaseFrame\Server\ServerProvider;
use cs_RowIsEmpty;

/**
 * крон для исполнения задач
 */
class Cron_Phphooker extends \Cron_Default {

	// макс кол-во ошибок
	protected const _MAX_ERROR_COUNT = 3;

	// база / таблица
	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "phphooker_queue";

	protected const _NEED_WORK_INTERVAL = 60; // интервал для продюсера
	protected const _PRODUCER_LIMIT     = 20; // лимит записей за раз

	protected string $queue_prefix = "_" . CURRENT_MODULE;
	protected int    $memory_limit = 50;
	protected int    $sleep_time   = 0;

	public function work():void {

		// получаем задачи из базы
		$list = $this->_getList();

		// проверяем может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// формируем in
		$in = $this->_makeIn($list);

		// обновляем задачи в базе
		$this->_updateTaskList($in);
		$this->say($in);

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);

		$this->sleep(0);
	}

	// функция для получения задачи из базы
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;
		$query  = "SELECT * FROM `?p` WHERE `need_work` < ?i LIMIT ?i OFFSET ?i";

		return ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, time(), self::_PRODUCER_LIMIT, $offset);
	}

	// формируем массив для обновления задач
	protected function _makeIn(array $list):array {

		// формируем in[]
		$in = [];
		foreach ($list as $row) {

			$in[] = $row["task_id"];
			$this->say($row["task_id"]);
		}

		return $in;
	}

	// функция для обновления записи с задачей в базе
	protected function _updateTaskList(array $in):void {

		// обновляем need_work задачи и увеличиваем error_count
		$set = [
			"need_work"   => time() + self::_NEED_WORK_INTERVAL,
			"error_count" => "error_count + 1",
		];

		ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE `task_id` IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $in, self::_PRODUCER_LIMIT);
	}

	// функция для отправки задачи в doWork
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $item) {

			$item["params"] = fromJson($item["params"]);

			// проверяем задачу на количество ошибок
			if ($item["error_count"] >= self::_MAX_ERROR_COUNT) {

				$this->_deleteQueue($item["task_id"]);
				continue;
			}

			$item["task_time_start"] = time() - 1;

			// отправляем задачу на doWork
			$this->doQueue($item);
		}
	}

	/**
	 * функция для выполнения задач
	 *
	 * @throws \parseException
	 */
	public function doWork(array $item):void {

		// проверяем задачу полученную из реббита
		if ($item["task_time_start"] + self::_NEED_WORK_INTERVAL < time()) {
			return;
		}
		unset($item["task_time_start"]);

		// выполняем задачу локально
		$result = $this->_doTask($item["task_id"], $item["task_type"], $item["params"]);
		if ($result !== true) {
			return;
		}

		// удаляем задачу из очереди
		$this->_deleteQueue($item["task_id"]);
	}

	/**
	 * Выполнить задачу
	 *
	 * @param int   $task_id
	 * @param int   $task_type
	 * @param array $params
	 *
	 * @return bool
	 * @long
	 */
	protected function _doTask(int $task_id, int $task_type, array $params):bool {

		try {

			// развилка по типу задачи
			return match ($task_type) {

				Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_COMPANY_INFO        => $this->_doUpdateUserCompanyInfo($params["user_id"], $params["client_launch_uuid"]),
				Type_Phphooker_Main::TASK_TYPE_LOGOUT_USER                     => $this->_doLogoutUser($params["user_id"], $params["session_uniq_list"]),
				Type_Phphooker_Main::TASK_TYPE_KICK_USER_FROM_COMPANY          => $this->_doKickUserFromCompany($params["user_id"], $params["user_role"], $params["company_id"]),
				Type_Phphooker_Main::TASK_TYPE_DELETE_COMPANY                  => $this->_doActionsOnCompanyDelete($params["deleted_by_user_id"], $params["company_id"]),
				Type_Phphooker_Main::TASK_TYPE_DELETE_PROFILE                  => $this->_doActionsOnProfileDelete($params["deleted_user_id"]),
				Type_Phphooker_Main::TASK_TYPE_COUNT_COMPANY                   => $this->_doCountCompany(),
				Type_Phphooker_Main::TASK_TYPE_SMS_RESEND_NOTICE               => $this->_onSmsResent($params),
				Type_Phphooker_Main::TASK_TYPE_INCORRECT_INVITE_LINK           => $this->_onTryValidateIncorrectLink($params),
				Type_Phphooker_Main::TASK_TYPE_ON_AUTH_STORY_EXPIRE            => $this->_onAuthStoryExpire($params["auth_map"]),
				Type_Phphooker_Main::TASK_TYPE_ON_CONFIRMATION_STORY_EXPIRE          => $this->_onTwoFaStoryExpire($params["two_fa_map"]),
				Type_Phphooker_Main::TASK_TYPE_ON_PHONE_CHANGE_STORY_EXPIRE    => $this->_onPhoneChangeStoryExpire($params["user_id"], $params["phone_change_map"]),
				Type_Phphooker_Main::TASK_TYPE_ON_PHONE_ADD_STORY_EXPIRE       => $this->_onPhoneAddStoryExpire($params["user_id"], $params["phone_add_map"]),
				Type_Phphooker_Main::TASK_TYPE_SEND_ACCOUNT_STATUS_LOG         => $this->_onSendAccountStatusLog($params["user_id"], $params["action"]),
				Type_Phphooker_Main::TASK_TYPE_SEND_SPACE_STATUS_LOG           => $this->_onSendSpaceStatusLog($params["company_id"], $params["action"]),
				Type_Phphooker_Main::TASK_TYPE_SEND_BITRIX_ON_USER_REGISTERED  => $this->_sendBitrixOnUserRegistered($task_id, $params["user_id"], $params["force_stage_id"]),
				Type_Phphooker_Main::TASK_TYPE_SEND_BITRIX_ON_USER_CHANGE_INFO => $this->_sendBitrixOnUserChangeData($task_id, $params["user_id"], $params["changed_data"]),
				Type_Phphooker_Main::TASK_TYPE_SEND_BITRIX_USER_CAMPAIGN_DATA  => $this->_sendBitrixUserCampaignData($task_id, $params["user_id"]),
				Type_Phphooker_Main::TASK_TYPE_ACCEPT_FIRST_JOIN_LINK          => true, // удалить через 1 релиз
				Type_Phphooker_Main::TASK_TYPE_ON_USER_LEFT_SPACE_EARLY        => $this->_onUserLeftSpaceEarly($params["user_id"], $params["company_id"], $params["entry_id"]),
				Type_Phphooker_Main::TASK_TYPE_USER_ENTERING_FIRST_SPACE       => $this->_onUserJoinFirstSpace($params["user_id"], $params["company_id"], $params["entry_id"]),
				default                                                        => throw new ParseFatalException("Unhandled task_type [{$task_type}] in " . __METHOD__),
			};
		} catch (\Exception $e) {

			// пишем лог в файл
			$exception_message = ExceptionUtils::makeMessage($e, HTTP_CODE_500);
			ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			return false;
		}
	}

	// -------------------------------------------------------
	// ЛОГИКА ВЫПОЛНЕНИЯ ЗАДАЧ
	// -------------------------------------------------------

	/**
	 * обновление пользовательских данных в компаниях
	 *
	 * @param int    $user_id
	 * @param string $client_launch_uuid
	 *
	 * @return bool
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	protected function _doUpdateUserCompanyInfo(int $user_id, string $client_launch_uuid):bool {

		try {
			$user_data = Gateway_Db_PivotUser_UserList::getOne($user_id);
		} catch (cs_RowIsEmpty) {
			return true;
		}

		Domain_Company_Entity_Company::updateUserCompanyInfo($user_data, $client_launch_uuid);

		return true;
	}

	/**
	 * разлогин сессий пользователя в компании
	 *
	 * @param int   $user_id
	 * @param array $session_uniq_list
	 *
	 * @return bool
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _doLogoutUser(int $user_id, array $session_uniq_list):bool {

		$user_company_session_token_list = Domain_Company_Entity_UserCompanySessionToken::setInactiveAndGetBySessionUniqList($user_id, $session_uniq_list);

		Domain_Company_Entity_Company::logoutUserSessionList($user_id, $user_company_session_token_list);

		return true;
	}

	/**
	 * Задача исключения пользователя из компании.
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException|cs_CompanyUserIsNotFound
	 */
	protected function _doKickUserFromCompany(int $user_id, int $user_role, int $company_id):bool {

		Domain_User_Action_KickUserFromCompany::do($user_id, $user_role, $company_id);
		return true;
	}

	/**
	 * Произвести действия при удалении компании
	 *
	 * @param int $deleted_by_user_id
	 * @param int $company_id
	 *
	 * @return bool
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \queryException
	 */
	protected function _doActionsOnCompanyDelete(int $deleted_by_user_id, int $company_id):bool {

		Domain_User_Action_Company_RemoveAllUsersFromCompany::do($deleted_by_user_id, $company_id);

		// ставим задачу на очистку порта для компании
		Domain_Company_Entity_ServiceTask::schedule(Domain_Company_Entity_ServiceTask::TASK_TYPE_DELETE_COMPANY, 0, $company_id);

		// оповещаем CRM об удалении команды
		Domain_Crm_Entity_Event_SpaceDeleted::create($company_id);

		return true;
	}

	/**
	 * Произвести действия при удалении аккаунта пользователя
	 *
	 * @long Добавили обработку изменения аккаунта
	 *
	 * @param int $deleted_user_id
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_HiringRequestNotPostmoderation
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _doActionsOnProfileDelete(int $deleted_user_id):bool {

		$user_info = Gateway_Db_PivotUser_UserList::getOne($deleted_user_id);

		// получаем компании пользователя из лобби
		$all_lobby_company_list = Gateway_Db_PivotUser_CompanyLobbyList::getCompanyListWithMinOrder($deleted_user_id, 0, 100);

		// очищаем девайсы и токены для пользователя
		Type_User_Notifications::deleteDevicesForUser($deleted_user_id);

		// исключаем пользователя из анонсов
		Gateway_Announcement_Main::invalidateUser($deleted_user_id);

		// выполняем действия по удалению в компаниях пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($deleted_user_id);
		foreach ($user_company_list as $user_company) {

			try {

				$company = Domain_Company_Entity_Company::get($user_company->company_id);

				// пропускаем если компания неактивная
				if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
					continue;
				}

				$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
				Gateway_Socket_Company::deleteUser($deleted_user_id, $company->company_id, $company->domino_id, $private_key);
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
				// !!! если вдруг компания неактивна, то продолжаем дальнейшее выполнение на пивоте
			}
		}

		// для всех компаний лобби где осталась заявка на наём в статусе "Ожидания"
		foreach ($all_lobby_company_list as $company_lobby) {

			// если статус на постмодерации
			if (Domain_Company_Entity_User_Lobby::isStatusPostModeration($company_lobby->status)) {

				$company = Domain_Company_Entity_Company::get($company_lobby->company_id);

				// помечаем заявку на наём отклоненной пользователем
				$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

				// пропускаем если компания неактивная
				if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
					continue;
				}

				try {

					$user_info = Struct_User_Info::createStruct($user_info);
					Gateway_Socket_Company::revokeHiringRequest(
						$deleted_user_id, $company_lobby->entry_id, $company->company_id, $company->domino_id, $private_key, $user_info);
				} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
					// !!! если вдруг компания неактивна, то продолжаем дальнейшее выполнение на пивоте
				}
			}
		}

		return true;
	}

	/**
	 * Подсчитаем количество компаний
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected function _doCountCompany():bool {

		$domino_list = Gateway_Db_PivotCompanyService_DominoRegistry::getAll();
		$limit       = 10000;
		foreach ($domino_list as $domino) {

			$offset                    = 0;
			$company_full              = 0;
			$company_count_status_list = [
				Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE     => 0,
				Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED => 0,
			];

			// делаем через пагинацию, чтобы не было утечек когда большое количество компаний на домино
			do {

				$company_id_list = Gateway_Db_PivotCompanyService_CompanyRegistry::getAllCompanyIdList($domino->domino_id, $limit + 1, $offset);
				$has_next        = count($company_id_list) < $limit + 1 ? 0 : 1;
				$company_id_list = array_slice($company_id_list, 0, $limit);
				$offset          += $limit;

				$company_count_status_list = (array) Gateway_Db_PivotCompany_CompanyList::getStatusCountList($company_id_list, $company_count_status_list);
				foreach ($company_count_status_list as $count) {
					$company_full += $count;
				}
			} while ($has_next == 1);

			$company_active     = $company_count_status_list[Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE];
			$company_hibernated = $company_count_status_list[Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED];
			$this->_saveStatisticOnCountCompany($domino->domino_id, $company_full, $company_active, $company_hibernated);
		}

		return true;
	}

	// сохраняем статистику когда посчитали количество компаний на домино
	protected function _saveStatisticOnCountCompany(string $domino_id, int $company_full, int $company_active, int $company_hibernated):void {

		Type_System_Analytic::save(0, $domino_id, Type_System_Analytic::TYPE_COMPANY_FULL, ["value" => $company_full]);
		Type_System_Analytic::save(0, $domino_id, Type_System_Analytic::TYPE_COMPANY_ACTIVE, ["value" => $company_active]);
		Type_System_Analytic::save(0, $domino_id, Type_System_Analytic::TYPE_COMPANY_HIBERNATION, ["value" => $company_hibernated]);
	}

	/**
	 * Выполняет отправку уведомление о повторном запросе смс-сообщения.
	 */
	protected function _onSmsResent(array $param):bool {

		Domain_User_Entity_Alert::onSmsResent((int) $param["user_id"],
			$param["phone_number"],
			(int) $param["remain_attempt_count"],
			$param["action"],
			$param["country_name"] ?? null,
			$param["sms_id"] ?? null
		);
		return true;
	}

	/**
	 * Отправляет уведомление на ввод некорретной пригласительной ссылки после регистрации
	 */
	protected function _onTryValidateIncorrectLink(array $param):bool {

		Domain_User_Entity_Alert::onTryValidateIncorrectLink((int) $param["user_id"], $param["link"]);
		return true;
	}

	/**
	 * Срабатывает сразу после протухания попытки залогиниться/зарегистрировать
	 */
	protected function _onAuthStoryExpire(string $auth_map):bool {

		Domain_User_Scenario_Phphooker::onAuthStoryExpire($auth_map);
		return true;
	}

	/**
	 * Срабатывает сразу после протухания попытки two_fa
	 */
	protected function _onTwoFaStoryExpire(string $two_fa_map):bool {

		Domain_User_Scenario_Phphooker::onTwoFaStoryExpire($two_fa_map);
		return true;
	}

	/**
	 * Срабатывает сразу после протухания попытки смены номера телефона
	 */
	protected function _onPhoneChangeStoryExpire(int $user_id, string $phone_change_story):bool {

		Domain_User_Scenario_Phphooker::onPhoneChangeStoryExpire($user_id, $phone_change_story);
		return true;
	}

	/**
	 * Срабатывает сразу после истекания попытки добавления номера телефона
	 */
	protected function _onPhoneAddStoryExpire(int $user_id, string $phone_add_story):bool {

		Domain_User_Scenario_Phphooker::onPhoneAddStoryExpire($user_id, $phone_add_story);
		return true;
	}

	/**
	 * Отправляем лог о текущем статусе аккаунта пользователя
	 */
	protected function _onSendAccountStatusLog(int $user_id, int $action):bool {

		Domain_User_Scenario_Phphooker::onSendAccountStatusLog($user_id, $action);
		return true;
	}

	/**
	 * Отправляем лог о текущем статусе аккаунта пространства
	 */
	protected function _onSendSpaceStatusLog(int $company_id, int $action):bool {

		Domain_User_Scenario_Phphooker::onSendSpaceStatusLog($company_id, $action);
		return true;
	}

	/**
	 * Отправляем в битрикс информацию о новом пользователе
	 */
	protected function _sendBitrixOnUserRegistered(int $task_id, int $user_id, string|null $force_stage_id):bool {

		if (ServerProvider::isOnPremise()) {
			return true;
		}

		try {
			Domain_Bitrix_Action_OnUserRegistered::do($user_id, $force_stage_id);
		} catch (\Exception) {

			// если что-то пошло не так, то репортим ошибку
			Domain_Bitrix_Entity_Main::reportFailedUserInfoTask($task_id, $user_id);

			return false;
		}

		// если задачу выполнили успешно, то удаляем ошибку в выполнении задачи
		// делаем так всегда, поскольку не можем знать была ли ошибка ранее
		Domain_Bitrix_Entity_Main::solveFailedUserInfoTask($task_id);

		return true;
	}

	/**
	 * Обновляем в битриксе данные о пользователе после их изменений
	 */
	protected function _sendBitrixOnUserChangeData(int $task_id, int $user_id, array $changed_data):bool {

		if (ServerProvider::isOnPremise()) {
			return true;
		}

		try {
			Domain_Bitrix_Action_OnUserChangeData::do($user_id, $changed_data);
		} catch (\Exception) {

			// если что-то пошло не так, то репортим ошибку
			Domain_Bitrix_Entity_Main::reportFailedUserInfoTask($task_id, $user_id);

			return false;
		}

		// если задачу выполнили успешно, то удаляем ошибку в выполнении задачи
		// делаем так всегда, поскольку не можем знать была ли ошибка ранее
		Domain_Bitrix_Entity_Main::solveFailedUserInfoTask($task_id);

		return true;
	}

	/**
	 * Получим и сохраним в Bitrix данные по рекламной кампании, с которой пользователь пришел в приложение
	 *
	 * @return bool
	 */
	protected function _sendBitrixUserCampaignData(int $task_id, int $user_id):bool {

		if (ServerProvider::isOnPremise()) {
			return true;
		}

		try {
			// получаем данные по рекламной кампании
			[$link, $source_id, $is_direct_reg] = Domain_User_Entity_Attribution::getUserCampaignRelData($user_id);

			// сохраняем данные
			Domain_Bitrix_Action_OnUserChangeData::do($user_id, [
				Domain_Bitrix_Action_OnUserChangeData::CHANGED_UTM_TAG   => $link,
				Domain_Bitrix_Action_OnUserChangeData::CHANGED_SOURCE_ID => $source_id,
				Domain_Bitrix_Action_OnUserChangeData::CHANGED_REG_TYPE  => Domain_Bitrix_Entity_Main::convertIsDirectRegToBitrixValueFormat($is_direct_reg),
			]);
		} catch (\Exception) {

			// если что-то пошло не так, то репортим ошибку
			Domain_Bitrix_Entity_Main::reportFailedUserInfoTask($task_id, $user_id);

			return false;
		}

		// если задачу выполнили успешно, то удаляем ошибку в выполнении задачи
		// делаем так всегда, поскольку не можем знать была ли ошибка ранее
		Domain_Bitrix_Entity_Main::solveFailedUserInfoTask($task_id);

		return true;
	}

	/**
	 * Пользователь покинул пространство слишком рано
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	protected function _onUserLeftSpaceEarly(int $user_id, int $company_id, int $entry_id):bool {

		if (ServerProvider::isOnPremise()) {
			return true;
		}

		// получаем информацию о пользователе
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// проверяем, нужно ли собирать аналитику по пользователю
		if (!Domain_User_Entity_Attribution_JoinSpaceAnalytics::shouldCollectAnalytics($user_info)) {
			return true;
		}

		// получаем информацию о посещении, которое привело пользователя к регистрации в приложении
		[$link, $source_id, $is_direct_reg] = Domain_User_Entity_Attribution::getUserCampaignRelData($user_id);

		// если ссылка пустая или не содержит /join/, то ничего дальше не делаем
		if ($link == "" || !inHtml($link, "join")) {
			return true;
		}

		// получаем ссылку по которой пользователь попал в команду, которую покидает
		try {
			$company_join_link_user_rel = Gateway_Db_PivotData_CompanyJoinLinkUserRel::getByEntryUserCompany($entry_id, $user_id, $company_id);
		} catch (\cs_RowIsEmpty) {

			// если ничего не нашли, то завершаем
			return true;
		}

		// если в содержимом ссылки содержится уникальная часть ссылки-приглашения, которую принял пользователь
		if (inHtml($link, $company_join_link_user_rel->join_link_uniq)) {

			// обновим результат в аналитике
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserLeftSpaceAfterAcceptMatchedInvite($user_id);
		}

		return true;
	}

	/**
	 * Пользователь вступил в первое пространство
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @long большая логика с различными сценариями
	 */
	protected function _onUserJoinFirstSpace(int $user_id, int $company_id, int $entry_id):bool {

		if (ServerProvider::isOnPremise()) {
			return true;
		}

		// получаем информацию о пользователе
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// проверяем, нужно ли собирать аналитику по пользователю
		if (!Domain_User_Entity_Attribution_JoinSpaceAnalytics::shouldCollectAnalytics($user_info)) {
			return true;
		}

		// получаем информацию о команде
		$company = Domain_Company_Entity_Company::get($company_id);

		// получаем информацию о посещении, которое привело пользователя к регистрации в приложении
		[$link, $source_id, $is_direct_reg] = Domain_User_Entity_Attribution::getUserCampaignRelData($user_id);

		// если пользователь создатель команды
		if ($company->created_by_user_id === $user_id) {

			// если пользователь пришел по посещению join-страницы, но в итоге создал первую команду – значит он ее заигнорировал
			if (inHtml($link, "join")) {
				Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserIgnoreMatchedJoinLink($user_id);
			} else {

				// иначе фиксируем что пользователь создал свою команду
				Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserCreateFirstSpace($user_id);
			}

			return true;
		}

		// дальше остается логика когда пользователь вступил в команду:

		// если ссылка пустая или не содержит /join/, то фиксируем, что пользователь вступил в команду
		if ($link == "" || !inHtml($link, "join")) {

			Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserJoinFirstSpace($user_id);
			return true;
		}

		// получаем ссылку по которой пользователь попал в команду
		try {
			$company_join_link_user_rel = Gateway_Db_PivotData_CompanyJoinLinkUserRel::getByEntryUserCompany($entry_id, $user_id, $company_id);
		} catch (\cs_RowIsEmpty) {

			// если ничего не нашли, то завершаем
			return true;
		}

		// если в содержимом ссылки-посещения содержится уникальная часть ссылки-приглашения, которую принял пользователь
		if (inHtml($link, $company_join_link_user_rel->join_link_uniq)) {

			// фиксируем что пользователь принял предложенное приглашение
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserAcceptMatchedJoinLink($user_id);
		} else {

			// иначе фиксируем что пользователь проигнорировал предложение
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserIgnoreMatchedJoinLink($user_id);
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Возвращает экземпляр Rabbit для указанного ключа.
	 */
	protected static function _getBusInstance(string $bus_key):\Rabbit {

		return ShardingGateway::rabbit($bus_key);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}

	// удаляет задачу из очереди
	protected function _deleteQueue(int $task_id):void {

		ShardingGateway::database(self::_DB_KEY)
			->delete("DELETE FROM `?p` WHERE `task_id` = ?i LIMIT ?i", self::_TABLE_KEY, $task_id, 1);
	}

}
