<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Базовый класс для добавления пользователя в компанию
 */
class Domain_User_Action_AddUser {

	/**
	 * Выполняем действие добавления пользователя в участники компании
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @long
	 */
	public static function do(
		int          $user_id,
		int          $role,
		int          $permissions,
		int          $entry_type,
		string       $mbti_type,
		string       $full_name,
		string       $avatar_file_key,
		int          $avatar_color_id,
		string       $comment,
		string       $locale,
		string|false $pin_code = false,
		int          $inviter_user_id = 0,
		int          $hiring_request_id = 0,
		int          $npc_type = Type_User_Main::NPC_TYPE_HUMAN,
		int          $approved_by_user_id = 0,
		bool         $is_trial_activated = false,
		bool         $is_need_create_intercom_conversation = false,
		string       $ip = "",
		string       $user_agent = "",
		bool         $is_creator = false,
		int          $avg_screen_time = 0,
		int          $total_action_count = 0,
		int          $avg_message_answer_time = 0,
	):string {

		try {
			$is_user_already_was_in_company = true;
			Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			$is_user_already_was_in_company = false;
		}

		// добавляем участника в компанию
		self::_add($user_id, $role, $npc_type, $permissions, $mbti_type, $full_name, $avatar_file_key, $avatar_color_id, $comment,
			$locale, $is_creator, $pin_code, $avg_screen_time, $total_action_count, $avg_message_answer_time);

		// создаем запись в dynamic-данных для карточки
		Type_User_Card_DynamicData::add($user_id);
		$token_hash = generateUUID();

		// добавляем для пользователя токен для пушей
		$token = new Struct_Db_CompanyData_MemberNotification(
			$user_id, 0, time(), 0, $token_hash, [], Domain_Notifications_Entity_UserNotification_Extra::initExtra(),
		);
		Gateway_Db_CompanyData_MemberNotificationList::insertOrUpdate($token);

		// добавляем в дефолтные группы, если нужно
		if ($entry_type === \CompassApp\Domain\Member\Entity\Entry::ENTRY_CREATOR_TYPE
			|| $entry_type === \CompassApp\Domain\Member\Entity\Entry::ENTRY_WITHOUT_TYPE
			|| $entry_type === \CompassApp\Domain\Member\Entity\Entry::ENTRY_INVITE_LINK_TYPE) {

			$is_owner = $role == Member::ROLE_ADMINISTRATOR
				&& Permission::hasPermissionList($permissions, Permission::OWNER_PERMISSION_LIST);

			// добавляем пользователя в дефолтные группы компании
			Gateway_Socket_Conversation::addToDefaultGroups($user_id, $is_owner, $role, $locale);
		}

		// считаем сколько пользователей и гостей
		[$space_resident_user_id_list, $guest_user_id_list] = Domain_Member_Action_GetAll::do();
		Domain_User_Action_Config_SetMemberCount::do(count($space_resident_user_id_list));
		Domain_User_Action_Config_SetGuestCount::do(count($guest_user_id_list));

		// выставляем статус активного пользователя в таблицах go_rating
		if ($is_user_already_was_in_company) {
			Gateway_Bus_Company_Rating::enableUserInRating($user_id);
		}
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		// добавить пользователя в наблюдение к User_Observer
		Type_User_Observer::addUserForObserver($user_id);

		// шлем эвент о вступлении пользователя в компанию
		self::_sendUserJoinedCompanyEvent($user_id, $inviter_user_id, $npc_type, $role, $permissions, $entry_type, $hiring_request_id, $locale, $approved_by_user_id,
			$is_user_already_was_in_company, $is_need_create_intercom_conversation, $ip, $user_agent);

		// если активировали триал - оставляем уведомление об этом
		if ($is_trial_activated) {

			$company_name = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$extra        = new Domain_Member_Entity_Notification_Extra(
				$hiring_request_id, $full_name, $company_name, $avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do(0, Domain_Member_Entity_Menu::MEMBER_COUNT_TRIAL_PERIOD, $extra);
		}

		return $token_hash;
	}

	/**
	 * Добавляем пользователя в участники компании
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @long
	 */
	protected static function _add(int $user_id, int $role, int $npc_type, int $permissions, string $mbti_type, string $full_name, string $avatar_file_key,
						 int $avatar_color_id, string $comment, string $locale, bool $is_creator, string|false $pin_code = false,
						 int $avg_screen_time = 0, int $total_action_count = 0, int $avg_message_answer_time = 0):int {

		// получаем первоначальные данные вступаемого участника
		[$short_description, $badge_color_id, $badge_content] = Domain_Member_Entity_EmployeeCard::getJoiningInitialData($role, $is_creator, $locale);

		Gateway_Db_CompanyData_Main::beginTransaction();

		// инициализируем extra
		$extra = \CompassApp\Domain\Member\Entity\Extra::initExtra();
		$extra = \CompassApp\Domain\Member\Entity\Extra::setBadgeInExtra($extra, $badge_color_id, $badge_content);
		$extra = \CompassApp\Domain\Member\Entity\Extra::setAliasAvgScreenTime($extra, $avg_screen_time);
		$extra = \CompassApp\Domain\Member\Entity\Extra::setAliasTotalActionCount($extra, $total_action_count);
		$extra = \CompassApp\Domain\Member\Entity\Extra::setAliasAvgMessageAnswerTime($extra, $avg_message_answer_time);
		$extra = \CompassApp\Domain\Member\Entity\Extra::setAvatarColorId($extra, $avatar_color_id);

		// добавляем пользователя
		Gateway_Db_CompanyData_MemberList::insertOrUpdate(
			$user_id, $role, $npc_type, $permissions, $mbti_type, $full_name, $short_description, $avatar_file_key, $comment, $extra
		);

		Gateway_Db_CompanyData_Main::commitTransaction();

		$hash_version  = 0;
		$pin_code_hash = "";

		Gateway_Db_CompanyMember_Main::beginTransaction();

		$time = time();
		Gateway_Db_CompanyMember_SecurityList::insertOrUpdate($user_id, 0, $time, 0, 0, $hash_version, $pin_code_hash);
		Gateway_Db_CompanyMember_Main::commitTransaction();

		return $time;
	}

	/**
	 * шлем эвент о вступлении пользователя в компанию
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _sendUserJoinedCompanyEvent(int $user_id, int $inviter_user_id, int $npc_type, int $role, int $permissions, int $entry_type, int $hiring_request_id, string $locale,
										int $approved_by_user_id, bool $is_user_already_was_in_company, bool $is_need_create_intercom_conversation,
										string $ip, string $user_agent):void {

		$hiring_request = [];

		if ($hiring_request_id > 0) {

			// получаем заявку
			try {

				$temp           = Domain_HiringRequest_Entity_Request::get($hiring_request_id);
				$locale         = Domain_HiringRequest_Entity_Request::getLocale($temp->extra);
				$hiring_request = self::_formatHiringRequest($temp);

				// это пользователь, который будет считаться пригласившим в компанию
				// если тот, кто создал заявку был уволен, то будет выбран какой-то из владельцев
				$inviter_user_id = self::_getInviterUserId($inviter_user_id, $temp);
			} catch (cs_HireRequestNotExist) {
			}
		}

		// ставим задачу на обновление индекса
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_UserJoinedCompany::create(
			$user_id, $hiring_request, $entry_type, $inviter_user_id, $locale,
			$approved_by_user_id, $is_user_already_was_in_company, $is_need_create_intercom_conversation, $ip, $user_agent
		), true);

		// отправляем ивент в premise-модуль о вступлении в команду участника
		Domain_Premise_Entity_Event_SpaceNewMember::create($user_id, $npc_type, $role, $permissions);
	}

	/**
	 * Получаем ид пригласившего пользователя.
	 * Если в заявке указан пригласивший и он работает в компании на текущий момент, то берем оттуда,
	 * иначе получаем самого старого руководителя или владельца (сначала перебирает всех руководителей).
	 *
	 * @param int                                      $inviter_user_id
	 * @param Struct_Db_CompanyData_HiringRequest|null $hiring_request
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getInviterUserId(int $inviter_user_id, Struct_Db_CompanyData_HiringRequest|null $hiring_request):int {

		$hiring_request_creator_user_id = 0;

		if (!is_null($hiring_request)) {

			$hiring_request_creator_user_id = $hiring_request->hired_by_user_id;

			try {

				Domain_Member_Entity_Main::assertIsMember($hiring_request_creator_user_id);
				return $hiring_request->hired_by_user_id;
			} catch (\cs_UserIsNotMember) {

				// более не участник, не паникуем
			}
		}

		if ($inviter_user_id > 0 && $inviter_user_id != $hiring_request_creator_user_id) {

			try {

				Domain_Member_Entity_Main::assertIsMember($inviter_user_id);
				return $inviter_user_id;
			} catch (\cs_UserIsNotMember) {

				// более не участник, не паникуем
			}
		}

		try {

			$role_list   = [Member::ROLE_ADMINISTRATOR];
			$permissions = Permission::addPermissionListToMask(Permission::DEFAULT, Permission::OWNER_PERMISSION_LIST);
			$member      = Gateway_Db_CompanyData_MemberList::getOldestMember($role_list, $permissions);

			return $member->user_id;
		} catch (\cs_RowIsEmpty) {

			// нет овнеров, не паникуем
		}

		return 0;
	}

	/**
	 * Получаем заявку на найм
	 *
	 * @param Struct_Db_CompanyData_HiringRequest|null $hiring_request
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _formatHiringRequest(Struct_Db_CompanyData_HiringRequest|null $hiring_request):array {

		if (is_null($hiring_request)) {
			return [];
		}

		$member_info = Gateway_Bus_CompanyCache::getMember($hiring_request->candidate_user_id);
		$user_info   = new Struct_User_Info(
			$member_info->user_id,
			$member_info->full_name,
			$member_info->avatar_file_key,
			\CompassApp\Domain\Member\Entity\Extra::getAvatarColorId($member_info->extra),
		);

		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);
		return (array) Apiv1_Format::hiringRequest($formatted_hiring_request);
	}

}
