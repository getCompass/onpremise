<?php

namespace Compass\Company;

/**
 * Системные сценарии для socket методов
 */
class Domain_System_Scenario_Socket {

	/**
	 * Произвести гибернацию компании
	 *
	 * @param bool $is_force_hibernate
	 *
	 * @return void
	 */
	public static function hibernate(bool $is_force_hibernate):void {

		// is_force_hibernate доступен только на тестовых и STAGE
		if (!isTestServer() && !isStageServer()) {
			$is_force_hibernate = false;
		}

		if (!Domain_System_Entity_Hibernation::isNeedHibernate()) {

			if (!$is_force_hibernate) {

				// компанию не нужно усыплять
				throw new Domain_System_Exception_CompanyHasActive();
			}
		}

		// публикуем анонс, что компания скоро уснет
		$raw_data = \Service\AnnouncementTemplate\TemplateService::createOfType(
			\Service\AnnouncementTemplate\AnnouncementType::COMPANY_IS_IN_HIBERNATION_MODE, []);

		Domain_Announcement_Action_Publish::run($raw_data);
	}

	/**
	 * Разбудить компанию
	 *
	 * @long
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function awake(int    $hibernation_immunity_till, int $last_wakeup_at, array $user_id_is_deleted_list,
					     array  $user_info_is_deleted_list, array $remind_bot_info, array $support_bot_info,
					     string $respect_conversation_avatar_file_key, array $user_info_update_list):void {

		Domain_Announcement_Action_Disable::run(\Service\AnnouncementTemplate\AnnouncementType::COMPANY_IS_IN_HIBERNATION_MODE);

		Domain_Company_Entity_Dynamic::set(Domain_Company_Entity_Dynamic::HIBERNATION_IMMUNITY_TILL, $hibernation_immunity_till);
		Domain_Company_Entity_Dynamic::set(Domain_Company_Entity_Dynamic::LAST_WAKEUP_AT, $last_wakeup_at);

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($user_id_is_deleted_list);

		// находящихся в компании пользователей помечаем удалёнными в компании
		$company_user_id_is_deleted_list = array_column($user_info_list, "user_id");
		Domain_Member_Action_SetUserListIsDeleted::do($company_user_id_is_deleted_list, $user_info_is_deleted_list);

		// заявки найма на постмодерации удалённых пользователей помечаем отклонёнными
		$join_request_list = Gateway_Db_CompanyData_HiringRequest::getByCandidateUserIdList($user_id_is_deleted_list,
			[Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION], count($user_id_is_deleted_list));
		foreach ($join_request_list as $join_request) {

			if (!isset($user_info_is_deleted_list[$join_request->candidate_user_id])) {
				continue;
			}

			$candidate_info = $user_info_is_deleted_list[$join_request->candidate_user_id];
			Domain_HiringRequest_Action_Revoke::do(
				$join_request, $candidate_info["full_name"], $candidate_info["avatar_file_key"], $candidate_info["avatar_color_id"]);
		}

		// актуализируем бота Напоминаний для пробуждённой компании
		if (count($remind_bot_info) > 0) {
			self::_actualizeSystemBot($remind_bot_info, Type_User_Main::NPC_TYPE_SYSTEM_BOT_REMIND);
		}

		// актуализируем бота Отдела поддержки для пробуждённой компании
		if (count($support_bot_info) > 0) {
			self::_actualizeSystemBot($support_bot_info, Type_User_Main::NPC_TYPE_SYSTEM_BOT_SUPPORT);
		}

		// создаем чат Спасибо если нужно
		if (mb_strlen($respect_conversation_avatar_file_key) > 0) {
			self::_createRespectConversation($respect_conversation_avatar_file_key);
		}

		// пушим событие если необходимо обновление информации о пользователях
		if (count($user_info_update_list) > 0) {
			Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_UpdateUserInfoList::create($user_info_update_list), true);
		}

		// выполняем оставшиеся асинхронные действия при пробуждении компании
		Gateway_Event_Dispatcher::dispatch(Type_Event_Company_OnWakeUp::create(), true);
	}

	/**
	 * актуализируем бота Напоминаний для пробуждённой компании
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _actualizeSystemBot(array $bot_info, int $npc_type):void {

		// сначала пробуем найти бота Напоминаний
		try {

			// ищем бота в участниках компании
			$bot_info_as_member = Gateway_Bus_CompanyCache::getMember($bot_info["user_id"]);

			// если бот имеет npc_type, соответствующий ему, то ничего не делаем
			if ($bot_info_as_member->npc_type == $npc_type) {
				return;
			}

			// меняем npc_type на актуальный (срабатывает один раз при первом пробуждении)
			$set = [
				"npc_type"   => $npc_type,
				"updated_at" => time(),
			];
			Gateway_Db_CompanyData_MemberList::set($bot_info_as_member->user_id, $set);

			Gateway_Bus_CompanyCache::clearMemberCacheByUserId($bot_info_as_member->user_id);

			return; // бот найден - завершаем на этом
		} catch (\cs_RowIsEmpty) {
			// всё ок, такое может быть - в этом случае добавляем его
		}

		// если бот отсутствует в компании, то добавляем его
		Domain_User_Action_AddBot::do(
			$bot_info["user_id"],
			$bot_info["npc_type"],
			"",
			$bot_info["full_name"],
			$bot_info["avatar_file_key"],
			""
		);
	}

	/**
	 * Создаем чат Спасибо и добавляем участников
	 *
	 * @param string $respect_conversation_avatar_file_key
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _createRespectConversation(string $respect_conversation_avatar_file_key):void {

		// получаем админа из под которого создадим диалог
		$member_list = Gateway_Db_CompanyData_MemberList::getListByRoles([\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR], 1);

		// создаем только если в пространстве есть админ
		if (isset($member_list[0])) {

			// создаем диалог
			$is_created = Gateway_Socket_Conversation::createRespectConversation($member_list[0]->user_id, $respect_conversation_avatar_file_key);

			// если создали то добавляем пользователей
			if ($is_created == 1) {
				Gateway_Socket_Conversation::addMembersToRespectConversation();
			}
		}
	}

	/**
	 * Выполняет очистку компании.
	 * Первый этап:
	 *      — чистка горячего кэша
	 *      — убийство всех сессий
	 */
	public static function purgeCompanyStepOne():void {

		Domain_System_Action_PurgeCompanyStepOne::run();
	}

	/**
	 * Выполняет очистку компании.
	 * Второй этап:
	 *      — очистка баз компании
	 *      — очистка модулей компании
	 */
	public static function purgeCompanyStepTwo():void {

		Domain_System_Action_PurgeCompanyStepTwo::run();
	}

	/**
	 * Проверяем готовность компании
	 */
	public static function checkReadyCompany():void {

		Domain_System_Entity_System::checkCleared();
	}

	/**
	 * Очистить все таблицы
	 *
	 * @throws \parseException
	 */
	public static function clearTables():void {

		// не убирать! только для тестовых серверов
		assertTestServer();

		Domain_System_Entity_System::purgeDatabase();
	}

	/**
	 * Получить список пользователей, которые кикнуты
	 */
	public static function getKickedUserList():array {

		return Gateway_Db_CompanyData_MemberList::getKickedUserIdList();
	}

	/**
	 * Уведомить о релокации компании
	 *
	 * @param int $will_start_at
	 *
	 * @return void
	 */
	public static function relocateNotice(int $company_id, int $will_start_at):void {

		// публикуем анонс, что компания скоро уснет
		$raw_data = \Service\AnnouncementTemplate\TemplateService::createOfType(
			\Service\AnnouncementTemplate\AnnouncementType::COMPANY_TECHNICAL_WORKS_NOTICE, [
			"will_start_at" => $will_start_at,
		]);

		Domain_Announcement_Action_Publish::run($raw_data, $company_id);
	}

	/**
	 * Начать релокацию компании
	 *
	 * @param int $will_be_available_at
	 *
	 * @return void
	 */
	public static function relocateStart(int $company_id, int $will_be_available_at):void {

		// публикуем анонс, что компания скоро уснет
		$raw_data = \Service\AnnouncementTemplate\TemplateService::createOfType(
			\Service\AnnouncementTemplate\AnnouncementType::COMPANY_TECHNICAL_WORKS_IN_PROGRESS, [
			"started_at"           => time(),
			"will_be_available_at" => $will_be_available_at,
		]);

		Domain_Announcement_Action_Publish::run($raw_data, $company_id);
		Domain_Announcement_Action_Disable::run(\Service\AnnouncementTemplate\AnnouncementType::COMPANY_TECHNICAL_WORKS_NOTICE, [], $company_id);
	}

	/**
	 * Закончить релокацию компании
	 *
	 * @return void
	 */
	public static function relocateEnd(int $company_id):void {

		Domain_Announcement_Action_Disable::run(\Service\AnnouncementTemplate\AnnouncementType::COMPANY_TECHNICAL_WORKS_IN_PROGRESS, [], $company_id);
	}

	/**
	 * Добавляем оператора службы поддержки
	 *
	 * @return array
	 */
	public static function addIntercomOperator(int $user_id, int $npc_type, string $full_name, string $avatar_file_key):array {

		try {

			$operator_member = Gateway_Bus_CompanyCache::getMember($user_id);
			$role            = $operator_member->role;
			$permissions     = $operator_member->permissions;
		} catch (\cs_RowIsEmpty) {

			[$role, $permissions] = Domain_User_Action_AddOperator::do(
				$user_id,
				$npc_type,
				$full_name,
				$avatar_file_key
			);
		}

		return [$role, $permissions];
	}
}
