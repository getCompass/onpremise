<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CompanyIsHibernatedException;
use BaseFrame\Exception\Request\CompanyIsRelocatingException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарии пользователя для socket методов
 */
class Domain_User_Scenario_Socket {

	/**
	 * Сценарий получения списка id пользователей с ролями
	 *
	 * @param array $roles
	 *
	 * @return array
	 */
	public static function getUserRoleList(array $roles):array {

		return Domain_User_Action_Member_GetUserRoleList::do($roles);
	}

	/**
	 * Сценарий добавления создателя в компанию
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_MemberExitTaskInProgress
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @long
	 */
	public static function addCreator(
		int          $user_id,
		string|false $pin_code,
		string       $full_name,
		string       $avatar_file_key,
		int          $avatar_color_id,
		int          $npc_type,
		bool         $is_force_exit_task_not_exist,
		string       $locale,
		bool         $is_trial_activated,
		bool         $is_need_create_intercom_conversation,
		string       $ip,
		string       $user_agent,
		int          $avg_screen_time,
		int          $total_action_count,
		int          $avg_message_answer_time
	):array {

		// получаем статус увольнения кандидата (уволился, вообще не увольнялся, на этапе увольнения)
		// если пользователь еще на этапе увольнения из компании, то стопим дальнейшее выполнение
		if (Domain_User_Entity_TaskExit::isExitStatusInProgress($user_id) && !$is_force_exit_task_not_exist) {
			throw new cs_MemberExitTaskInProgress("user has not finished exit the company yet");
		}

		// получим всю информацию для создания пользователя
		[$entry_id, $entry_type, $role, $permissions, $inviter_user_id] = self::_getInfoForCreateUser(Member::ROLE_ADMINISTRATOR, $user_id);

		$token_hash = Domain_User_Action_AddUser::do(
			$user_id, $role, $permissions, $entry_type, "", $full_name,
			$avatar_file_key, $avatar_color_id, "", $locale, $pin_code, $inviter_user_id, npc_type: $npc_type, approved_by_user_id: $user_id,
			is_trial_activated: (bool) $is_trial_activated, is_need_create_intercom_conversation: $is_need_create_intercom_conversation,
			ip: $ip, user_agent: $user_agent, is_creator: true,
			avg_screen_time: $avg_screen_time, total_action_count: $total_action_count, avg_message_answer_time: $avg_message_answer_time);
		return [$entry_id, $token_hash, $role, $permissions];
	}

	/**
	 * Сценарий добавления пользователя с указанной ролью в компанию
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws cs_MemberExitTaskInProgress
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @long
	 */
	public static function addByRole(
		int          $user_id,
		int          $user_role,
		string|false $pin_code,
		string       $full_name,
		string       $avatar_file_key,
		int          $avatar_color_id,
		int          $npc_type,
		bool         $is_force_exit_task_not_exist,
		string       $locale,
		bool         $is_trial_activated,
		string       $ip,
		string       $user_agent,
		int          $avg_screen_time,
		int          $total_action_count,
		int          $avg_message_answer_time
	):array {

		// получаем статус увольнения кандидата (уволился, вообще не увольнялся, на этапе увольнения)
		// если пользователь еще на этапе увольнения из компании, то стопим дальнейшее выполнение
		if (Domain_User_Entity_TaskExit::isExitStatusInProgress($user_id) && !$is_force_exit_task_not_exist) {
			throw new cs_MemberExitTaskInProgress("user has not finished exit the company yet");
		}

		// получим всю информацию для создания пользователя
		[$entry_id, $entry_type, $role, $permissions, $inviter_user_id] = self::_getInfoForCreateUser($user_role, $user_id);

		[$creator_user_id, $is_creator] = $user_role == Member::ROLE_ADMINISTRATOR ? [$user_id, true] : [0, false];

		$token_hash = Domain_User_Action_AddUser::do(
			$user_id, $role, $permissions, $entry_type, "", $full_name,
			$avatar_file_key, $avatar_color_id, "", $locale, $pin_code, $inviter_user_id, npc_type: $npc_type, approved_by_user_id: $creator_user_id,
			is_trial_activated: (bool) $is_trial_activated, is_need_create_intercom_conversation: false,
			ip: $ip, user_agent: $user_agent, is_creator: $is_creator,
			avg_screen_time: $avg_screen_time, total_action_count: $total_action_count, avg_message_answer_time: $avg_message_answer_time);
		return [$entry_id, $token_hash, $role, $permissions];
	}

	/**
	 * Сценарий чтобы установить количество пользователей компании в конфиге
	 *
	 * @param int $member_count
	 *
	 * @throws \parseException
	 */
	public static function setUsersCount(int $member_count, int $guest_count):void {

		Domain_User_Action_Config_SetMemberCount::do($member_count);
		Domain_User_Action_Config_SetGuestCount::do($guest_count);
	}

	/**
	 * Сценарий чтобы получить количество участников компании в конфиге
	 *
	 * @return int
	 */
	public static function getMemberCount():int {

		[$user_list] = Domain_Member_Action_GetAll::do();
		return count($user_list);
	}

	/**
	 * Обновляем информацию пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function updateUserInfo(int $user_id, string $full_name, string $avatar_file_key, int $avatar_color_id, int $avg_screen_time, int $total_action_count,
							  int $avg_message_answer_time, int $profile_created_at, int $disabled_at, string $client_launch_uuid, int $is_deleted):void {

		Domain_Member_Action_SetPivotData::do($user_id, $full_name, $avatar_file_key, $avatar_color_id, $avg_screen_time, $total_action_count, $avg_message_answer_time
			, $profile_created_at, $disabled_at, $client_launch_uuid, $is_deleted);
	}

	/**
	 * разлогиниваем пользователя
	 *
	 * @param int   $user_id
	 * @param array $user_company_session_token_list
	 *
	 * @return void
	 * @throws \busException|\parseException
	 */
	public static function logoutUserSessionList(int $user_id, array $user_company_session_token_list):void {

		// удаляем сессии
		Gateway_Db_CompanyData_SessionActiveList::deleteByUserCompanySessionToken($user_company_session_token_list);
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_UserLogoutCompany::create($user_id, ""), true);

		// чистим кэш
		Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
	}

	/**
	 * разлогиниваем всех пользователей
	 *
	 * @return void
	 * @throws \busException|\parseException
	 */
	public static function logoutAll():void {

		Domain_System_Action_LogoutAll::do();
	}

	/**
	 * Исключаем пользователя из компании.
	 *
	 * @param int $user_id
	 * @param int $kicked_user_id
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function kick(int $user_id, int $kicked_user_id):void {

		Domain_Company_Action_Dismissal::do($kicked_user_id);

		$dismissal_request = Domain_DismissalRequest_Entity_Request::add($user_id, $kicked_user_id);

		Domain_User_Entity_TaskExit::add($kicked_user_id, $dismissal_request->dismissal_request_id);

		Gateway_Socket_Pivot::addScheduledCompanyTask(
			$dismissal_request->dismissal_request_id,
			Domain_User_Entity_TaskType::TYPE_EXIT,
			$user_id,
		);
	}

	/**
	 * Устанавливает статус премиума в сессии пользователя.
	 */
	public static function updatePremiumStatuses(array $raw_premium_company_data_list):void {

		// перегоняем в массив структур, чтобы точно все на месте было
		$premium_company_data_list = array_map(static fn(array $el) => new Struct_Premium_CompanyData(...$el), $raw_premium_company_data_list);

		// обновляем каждого пользователя по отдельности, пачкой никак это не провернуть
		foreach ($premium_company_data_list as $premium_company_data) {

			// просто пробрасываем дальше в действие
			Domain_User_Action_Premium_SetStatus::run(
				$premium_company_data->user_id,
				$premium_company_data->premium_active_till,
				$premium_company_data->need_block_if_premium_inactive
			);
		}
	}

	/**
	 * Проверить, может ли пользователь править настройки компании
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function checkCanEditSpaceSettings(int $user_id):bool {

		try {
			$user = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		return Permission::canEditSpaceSettings($user->role, $user->permissions);
	}

	/**
	 * Проверить, может ли пользователь привязать компанию в партнерке
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function checkCanAttachSpace(int $user_id):bool {

		try {
			$user = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		return Permission::canEditSpaceSettings($user->role, $user->permissions) && Permission::canDeleteSpace($user->role, $user->permissions);
	}

	/**
	 * Получим информацию для создания пользователя
	 */
	protected static function _getInfoForCreateUser(int $user_role, int $user_id):array {

		$permissions = Permission::DEFAULT;

		// !!! этим методом только создатель добавляется
		if ($user_role == Member::ROLE_ADMINISTRATOR) {

			[$entry_id, $entry_type] = Domain_User_Entity_Entry::addCreatorType($user_id);

			$role            = Member::ROLE_ADMINISTRATOR;
			$permissions     = Permission::addPermissionListToMask($permissions, Permission::OWNER_PERMISSION_LIST);
			$inviter_user_id = $user_id;
		} elseif ($user_role == Member::ROLE_GUEST) {

			[$entry_id, $entry_type] = Domain_User_Entity_Entry::addWithoutType($user_id);

			$role            = Member::ROLE_GUEST;
			$inviter_user_id = 0;
		} else {

			[$entry_id, $entry_type] = Domain_User_Entity_Entry::addWithoutType($user_id);

			$role            = Member::ROLE_MEMBER;
			$inviter_user_id = 0;
		}

		return [$entry_id, $entry_type, $role, $permissions, $inviter_user_id];
	}

	/**
	 * Был создан счет на оплату
	 */
	public static function onInvoiceCreated(int $created_by_user_id):void {

		Gateway_Bus_Sender::invoiceCreated($created_by_user_id);
	}

	/**
	 * Был оплачен счет
	 */
	public static function onInvoicePayed():void {

		Gateway_Bus_Sender::invoicePayed();
	}

	/**
	 * Счет был отменен
	 */
	public static function onInvoiceCanceled(int $invoice_id):void {

		Gateway_Bus_Sender::invoiceCanceled($invoice_id);
	}

	/**
	 * Действия при удалении аккаунта пользователя
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyIsHibernatedException
	 * @throws CompanyIsRelocatingException
	 * @throws ParamException
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
	public static function deleteUser(int $user_id):void {

		// удаляем все активные ссылки-инвайты пользователя
		Domain_User_Action_UserInviteLinkActive_DeleteAllByUser::do($user_id);

		// удаляем запрос на оплату премиума, если таковой был
		Domain_Premium_Action_PaymentRequest_Delete::do($user_id);

		// получаем роль
		$user_info = Gateway_Bus_CompanyCache::getMember($user_id);

		// удаляемся из компании
		Domain_User_Scenario_Api::doLeaveCompany($user_id, $user_info->role, true);
	}

	/**
	 * Проверить, является ли пользователь владельцем компании
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function checkIsOwner(int $user_id):bool {

		try {
			$user = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		return $user->role === Member::ROLE_ADMINISTRATOR;
	}

}
