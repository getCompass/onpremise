<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyIsHibernatedException;
use BaseFrame\Exception\Request\CompanyIsRelocatingException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарии пользователя для API
 *
 * Class Domain_User_Scenario_Api
 */
class Domain_User_Scenario_Api {

	/**
	 * Сценарий авторизации в компании
	 *
	 * @param int    $session_user_id
	 * @param int    $user_id
	 * @param string $pin_code
	 * @param string $user_company_session_token
	 * @param string $pin_key
	 *
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \busException
	 * @throws cs_CompanyIsDeleted
	 * @throws cs_CompanyIsNotExist
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidUserCompanySessionToken
	 * @throws cs_IsNotEqualsPinCode
	 * @throws cs_PlatformNotFound
	 * @throws \cs_SessionNotFound
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function tryLoginInCompany(int $session_user_id, int $user_id, string $user_company_session_token):void {

		Domain_Company_Entity_Validator::assertCompanyExist();
		Domain_Company_Entity_Dynamic::assertCompanyIsNotDeleted();
		Domain_User_Entity_Validator::assertValidUserCompanySessionToken($user_company_session_token);
		Domain_User_Entity_Validator::assertValidUserId($user_id);

		// проверяем что пользователь участник компании
		Domain_Member_Entity_Main::assertIsMember($user_id);

		// проверяем, возможно пользователь уже залогинен в компании
		try {
			Domain_User_Entity_Validator::assertNotLoggedIn($session_user_id);
		} catch (cs_UserAlreadyLoggedIn) {

			// в этом случае также дополнительно добавляем устройство в список тех, которые могут получать пуши (кроме электрона)
			if (Type_Api_Platform::getPlatform() !== Type_Api_Platform::PLATFORM_ELECTRON) {
				Domain_User_Action_Notifications_AddDevice::do($user_id, getDeviceId());
			}
			throw new cs_UserAlreadyLoggedIn("user already logged in company");
		}

		// проверяем блокировку
		Type_Antispam_User::assertKeyIsNotBlocked($user_id, Type_Antispam_User::PIN_CODE_LIMIT);

		// логиним сессию пользователю
		Type_Session_Main::doLoginSession($user_id, $user_company_session_token);

		// добавляем устройство в список тех, которые могут получать пуши, если это не электрон(им пуши не нужны)
		if (Type_Api_Platform::getPlatform() !== Type_Api_Platform::PLATFORM_ELECTRON) {
			Domain_User_Action_Notifications_AddDevice::do($user_id, getDeviceId());
		}
	}

	/**
	 * Получаем список пользователей по ролям
	 *
	 * @param array $roles
	 *
	 * @return array
	 */
	public static function getUserRoleList(array $roles):array {

		// получаем список пользователей
		$user_role_list = Domain_User_Action_Member_GetUserRoleList::do($roles);

		foreach ($user_role_list as $key => $user_role) {

			if ($user_role->role === Member::ROLE_ADMINISTRATOR
				&& !Permission::hasPermissionList($user_role->permissions, Permission::OWNER_PERMISSION_LIST)) {

				$user_role->role      = Member::ROLE_MEMBER;
				$user_role_list[$key] = $user_role;
			}
		}

		return $user_role_list;
	}

	/**
	 * Сценарий метода doStart
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws cs_AnswerCommand
	 * @throws cs_PlatformNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotLoggedIn
	 * @throws \queryException
	 */
	public static function doStart(int $user_id, int $role, int $permissions):array {

		$device_id = getDeviceId();
		$platform  = Type_Api_Platform::getPlatform();

		try {

			Domain_User_Entity_Validator::assertLoggedIn($user_id);
			$ws_connection_info = Gateway_Bus_Sender::setToken($user_id, $device_id, $platform);
		} catch (cs_TalkingBadResponse) {
			throw new ParamException("Failed to connect");
		}

		// получаем настройки уведомлений для пользователя
		$notification_preferences = Domain_Notifications_Action_GetPreferences::do();
		$member_permission_list   = Domain_Company_Scenario_Api::getMemberPermissions();
		$config                   = self::_getConfig();

		// узнаем, есть ли в пространстве принятая заявка на найм
		$has_confirmed_join_request = (int) self::_hasConfirmedJoinRequest($config["member_count"], $role, $permissions);

		$access = Domain_User_Entity_Access::get();

		// добавляем экранное время
		if ($user_id > 0) {

			[$local_date, $local_time, $_] = getLocalClientTime();
			Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);
		}

		return [$config, $ws_connection_info, $notification_preferences, $member_permission_list, $access, $has_confirmed_join_request];
	}

	// получаем переменные для конфига пользователя
	protected static function _getConfig():array {

		$config = [];

		$config_list = Domain_Company_Entity_Config::getList([
			Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY,
			Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY,
			Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS,
			Domain_Company_Entity_Config::ADD_TO_GENERAL_CHAT_ON_HIRING,
			Domain_Company_Entity_Config::MEMBER_COUNT,
		]);

		// достаём значения
		$config["is_push_body_display"]                 = $config_list[Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY]["value"];
		$config["is_extended_employee_card_enabled"]    = $config_list[Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY]["value"];
		$config["is_general_chat_notification_enabled"] = $config_list[Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS]["value"];
		$config["is_add_to_general_chat_on_hiring"]     = $config_list[Domain_Company_Entity_Config::ADD_TO_GENERAL_CHAT_ON_HIRING]["value"];
		$config["member_count"]                         = $config_list[Domain_Company_Entity_Config::MEMBER_COUNT]["value"];

		return $config;
	}

	/**
	 * Есть ли в пространстве ранее принятая заявка на вступление
	 *
	 * @param int $member_count
	 * @param int $role
	 * @param int $permissions
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	protected static function _hasConfirmedJoinRequest(int $member_count, int $role, int $permissions):bool {

		// если участников меньше 2 и участник может приглашать участников, проверяем, принимал ли кто-то заявку на вступление
		// таким образом обрабатываем кейс, когда участник добавился в компанию и сразу вышел
		if ($member_count < 2 && Permission::canInviteMember($role, $permissions)) {

			$hiring_request_list = Domain_HiringRequest_Entity_Request::getConfirmedAndDismissedList(1);
			return (count($hiring_request_list) > 0);
		}

		return true;
	}

	/**
	 * Разлогиниваемся из компании
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws paramException
	 * @throws \busException
	 * @throws cs_PlatformNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParamException|\paramException
	 */
	public static function doLogout(int $user_id):void {

		// ID устройства пользователя
		// может быть пустым, если это socket запрос по исключению пользователя из команды
		$device_id = getDeviceId();

		$cloud_session_uniq = false;
		try {

			Domain_User_Entity_Validator::assertLoggedIn($user_id);
			$cloud_session_uniq = Type_Session_Main::doLogoutSession($user_id);

			// если имеется device_id, то удаляем устройство из списка тех, которые могут получать пуши, если это не электрон(им пуши не нужны)
			if ($device_id !== "" && Type_Api_Platform::getPlatform() != Type_Api_Platform::PLATFORM_ELECTRON) {
				Domain_User_Action_Notifications_DeleteDevice::do($user_id, $device_id );
			}
		} catch (cs_UserNotLoggedIn | \cs_RowIsEmpty | \cs_SessionNotFound) {
			// просто ничего не делаем
		}

		if ($cloud_session_uniq === false) {
			return;
		}

		// отправляем ивент о том, что пользователь разлогинился из компании
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_UserLogoutCompany::create($user_id, $cloud_session_uniq), true);

		$routine_key = Gateway_Bus_Sender::getWaitRoutineKeyForUser($user_id);

		// в зависимости от наличия/отсутствия device_id делаем то или иное действие
		if ($device_id !== "") {

			// закрываем ws по device_id
			Gateway_Bus_Sender::closeConnectionsByDeviceIdWithWait($user_id, $device_id, $routine_key);
		} else {

			// закрываем ws по user_id
			Gateway_Bus_Sender::closeConnectionsByUserIdWithWait($user_id, $routine_key);
		}

		// обновляем бадж с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);
	}

	/**
	 * Получаем список идентификаторов пользователей, сгруппированные по их статусу в системе
	 *
	 * @param int   $user_id
	 * @param array $user_id_list
	 *
	 * @return array[]
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_UserIdListEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUserIdListWithStatusInSystem(int $user_id, array $user_id_list):array {

		// проверяем user_list
		Domain_User_Entity_Validator::assertValidUserIdList($user_id_list);

		// убираем нашего пользователя из списка
		$user_id_list = array_diff($user_id_list, [$user_id]);

		// достаем информацию по полученным пользователям
		$user_info_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);

		$disabled_user_id_list        = [];
		$account_deleted_user_id_list = [];
		$allowed_user_id_list         = [];
		foreach ($user_info_list as $user_item) {

			// если покинул компанию
			if (Member::isDisabledProfile($user_item->role)) {

				$disabled_user_id_list[] = (int) $user_item->user_id;
				continue;
			}

			// если удалил аккаунт
			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_item->extra)) {

				$account_deleted_user_id_list[] = (int) $user_item->user_id;
				continue;
			}

			// в ином случае в список доступных
			$allowed_user_id_list[] = (int) $user_item->user_id;
		}

		return [$disabled_user_id_list, $account_deleted_user_id_list, $allowed_user_id_list];
	}

	/**
	 * Покидаем компанию (самоувольняемся)
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \blockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_ActionForCompanyBlocked
	 * @throws cs_AnswerCommand
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws cs_CompanyUserIsOnlyOwner
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_PlatformNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws cs_TwoFaIsInvalid
	 * @throws cs_TwoFaIsNotActive
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function leaveCompany(int $user_id, int $user_role, string|false $two_fa_key, int $method_version):void {

		// проверяем то, что пользователю доступно покидание компании
		Domain_User_Action_Member_CheckIsAllowedLeaveCompany::do($user_id, $method_version);

		if (ServerProvider::isOnPremise()) {
			Domain_User_Entity_Validator::assertNotRootUserId($user_id);
		}

		// генерируем/проверяем 2fa токен
		Domain_TwoFa_Entity_TwoFa::handle($user_id, Domain_TwoFa_Entity_TwoFa::TWO_FA_SELF_DISMISSAL_TYPE, $two_fa_key);

		if ($method_version == 1) {

			// увольняем пользователя
			self::_dismissUser($user_id);

			// завершаем выполнение
			return;
		}

		// покидаем компанию
		self::doLeaveCompany($user_id, $user_role);
	}

	/**
	 * Покидаем компанию
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyIsHibernatedException
	 * @throws CompanyIsRelocatingException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_PlatformNotFound
	 */
	public static function doLeaveCompany(int $user_id, int $user_role, bool $is_delete_user = false):void {

		// получаем полноценных пользователей пространства
		$member_list                = Gateway_Db_CompanyData_MemberList::getAllActiveMemberWithNpcFilter();
		$space_resident_member_list = array_filter($member_list,
			static fn(\CompassApp\Domain\Member\Struct\Main $member) => in_array($member->role, Member::SPACE_RESIDENT_ROLE_LIST));

		// если в компании остались другие полноценные участники или покидает компанию гость,
		// то просто увольняем его; иначе - удаляем компанию
		if (count($space_resident_member_list) > 1 || $user_role == Member::ROLE_GUEST) {

			// увольняем пользователя
			self::_dismissUser($user_id);
		} else {

			// разлогиниваем
			self::doLogout($user_id);

			// удаляем компанию
			Gateway_Socket_Pivot::deleteCompany($user_id);
		}

		Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_UserLeftCompany::create($user_id), true);

		// отправляем ивент в premise-модуль
		Domain_Premise_Entity_Event_SpaceLeftMember::create($user_id);
	}

	/**
	 * увольняем пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_PlatformNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _dismissUser(int $user_id):void {

		// получаем старую заявку на увольнение, если она есть
		try {

			$old_dismissal_request = Domain_DismissalRequest_Entity_Request::getByDismissalUserId($user_id);
			Domain_DismissalRequest_Action_Approve::do($user_id, $old_dismissal_request);
		} catch (cs_DismissalRequestNotExist) {
			// нет заявки на обычное увольниение - это норма, продолжаем самоувольнение
		}

		// добавляем заявку
		$dismissal_request = Domain_DismissalRequest_Entity_Request::addForSelf($user_id);

		// переведем заявку найма в статус что сотрудника уволили
		Domain_HiringRequest_Action_Dismissed::do($user_id, $dismissal_request);

		// запускаем процесс увольнения
		Domain_DismissalRequest_Scenario_Api::createTaskDismiss($user_id, $dismissal_request, Member::LEAVE_REASON_LEAVE);

		// разлогиниваем пользователя
		self::doLogout($user_id);

		// отправляем соообщение заявки о самоувольнении в диалог
		[$hiring_conversation_map, $dismissal_request_message_map, $dismissal_request_thread_map] =
			Gateway_Socket_Conversation::addDismissalRequestMessage($user_id, $dismissal_request->dismissal_request_id, $dismissal_request->dismissal_user_id);

		// сохраняем в заявке мапу сообщения из чата и треда к заявке
		$dismissal_request = Domain_DismissalRequest_Action_SetMessageMap::do($dismissal_request->dismissal_request_id, $dismissal_request_message_map);
		$dismissal_request = Domain_DismissalRequest_Action_SetThreadMap::do($dismissal_request->dismissal_request_id, $dismissal_request_thread_map);

		// отправляем ивент с процессом создания заяки на самоувольнение
		Gateway_Event_Dispatcher::dispatch(Type_Event_DismissalRequest_SelfCreated::create((array) $dismissal_request), true);

		// отмечаем в intercom, что пользователь покинул пространство
		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MEMBER_COUNT);
		Gateway_Socket_Intercom::userLeaved($user_id, $config["value"] ?? 0);
	}
}
