<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Сценарии по ссылкам-инвайтам socket
 */
class Domain_JoinLink_Scenario_Socket {

	/**
	 * Используем инвайт инвайт
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_CompanyIsDeleted
	 * @throws cs_IncorrectInviteLinkUniq
	 * @throws cs_InviteLinkIdExpired
	 * @throws cs_InviteLinkNotActive
	 * @throws cs_JoinLinkNotExist
	 * @throws \cs_RowIsEmpty
	 */
	public static function getInviteLinkInfo(string $invite_link_uniq, int $candidate_user_id):array {

		// смотрим, что компания не удалена
		Domain_Company_Entity_Dynamic::assertCompanyIsNotDeleted();

		try {

			$invite_link = Domain_JoinLink_Action_Get::do($invite_link_uniq);
		} catch (cs_InviteLinkNotExist) {
			throw new cs_IncorrectInviteLinkUniq("invite link doesn't exists");
		}

		// проверяем что пользователь имеет права на создание ссылок
		$user = Domain_User_Action_Member_GetShort::do($invite_link->creator_user_id);

		Permission::assertCanInviteMember($user->role, $user->permissions);

		// если ссылку нельзя использовать, то бросаем исключение
		Domain_JoinLink_Entity_Main::assertCanUse($invite_link);

		$was_member     = false;
		$candidate_role = 0;

		if ($candidate_user_id !== 0) {

			try {

				$candidate_user_info = Gateway_Bus_CompanyCache::getMember($candidate_user_id);
				$candidate_role      = $candidate_user_info->role;
				$was_member          = true;
			} catch (\cs_RowIsEmpty) {
				// ничего не делаем
			}
		}

		// получаем статус увольнения кандидата (уволился, вообще не увольнялся, на этапе увольнения)
		$is_exit_status_in_progress = Domain_User_Entity_TaskExit::isExitStatusInProgress($candidate_user_id);

		// требуется ли модерация заявки?
		$is_postmoderation = Domain_JoinLink_Entity_Main::isPostModerationEnabled($invite_link->entry_option);

		return [$invite_link->entry_option, $is_postmoderation, $invite_link->creator_user_id, $is_exit_status_in_progress, $was_member, $candidate_role];
	}

	/**
	 * Возвращаем ID пользователя создателя ссылки приглашения
	 *
	 * @param string $invite_link_uniq
	 *
	 * @return int
	 * @throws cs_IncorrectInviteLinkUniq
	 * @throws cs_JoinLinkNotExist
	 * @throws cs_CompanyIsDeleted
	 */
	public static function getCreatorUserId(string $invite_link_uniq):int {

		// смотрим, что компания не удалена
		Domain_Company_Entity_Dynamic::assertCompanyIsNotDeleted();

		try {

			$invite_link = Domain_JoinLink_Action_Get::do($invite_link_uniq);
		} catch (cs_InviteLinkNotExist) {
			throw new cs_IncorrectInviteLinkUniq("invite link doesn't exists");
		}

		return $invite_link->creator_user_id;
	}

	/**
	 * Принимаем инвайт
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_CompanyIsDeleted
	 * @throws cs_InviteLinkIdExpired
	 * @throws cs_InviteLinkNotActive
	 * @throws cs_JoinLinkNotExist
	 * @throws cs_MemberExitTaskInProgress
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function acceptInvite(int    $user_id, string $invite_link_uniq, string $comment, string $full_name, string $avatar_file_key, int $avatar_color_id,
							string $locale, bool $is_force_exit_task_not_exist, int $avg_screen_time, int $total_action_count, int $avg_message_answer_time,
							bool   $force_postmoderation, array $ldap_account_data):array {

		// смотрим, что компания не удалена
		Domain_Company_Entity_Dynamic::assertCompanyIsNotDeleted();

		// получаем информацию о ссылке
		$join_link = Domain_JoinLink_Action_Get::do($invite_link_uniq);

		// проверяем что пользователь имеет права на создание ссылок
		$user = Domain_User_Action_Member_GetShort::do($join_link->creator_user_id);
		Permission::assertCanInviteMember($user->role, $user->permissions);

		// если ссылку нельзя использовать, то бросаем исключение
		Domain_JoinLink_Entity_Main::assertCanUse($join_link);

		// получаем статус увольнения кандидата (уволился, вообще не увольнялся, на этапе увольнения)
		// если пользователь еще на этапе увольнения из компании, то стопим дальнейшее выполнение
		if (Domain_User_Entity_TaskExit::isExitStatusInProgress($user_id) && !$is_force_exit_task_not_exist) {
			throw new cs_MemberExitTaskInProgress();
		}

		// устанавливаем постмодерацию, если принудительно требуют этого
		$join_link->entry_option = $force_postmoderation ? Domain_JoinLink_Entity_Main::ENTRY_OPTION_NEED_POSTMODERATION : $join_link->entry_option;

		/** @var Struct_Db_CompanyData_JoinLink $join_link */
		[$join_link, $is_trial_activated, $status, $entry_id, $entry_type, $hiring_request_id] =
			Domain_JoinLink_Action_Accept::do($user_id, $full_name, $avatar_file_key, $join_link, $comment, $locale);

		// требуется ли модерация
		$is_postmoderation = Domain_JoinLink_Entity_Main::isPostModerationEnabled($join_link->entry_option);

		// по умолчанию ставим будто участник не вступил в пространство
		$role        = \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT;
		$permissions = Permission::DEFAULT;

		// если заявка без модерации
		$token = "";
		if (!$is_postmoderation) {

			// получаем роль и разрешения с которой пользователь попадет в пространство
			$role = Domain_JoinLink_Entity_Main::resolveRole($join_link->entry_option);

			// добавляем пользователя в компанию
			$token = Domain_User_Action_AddUser::do(
				$user_id,
				$role,
				$permissions,
				$entry_type,
				"",
				$full_name,
				$avatar_file_key,
				$avatar_color_id,
				"",
				$locale,
				false,
				$join_link->creator_user_id,
				$hiring_request_id,
				is_trial_activated: (bool) $is_trial_activated,
				avg_screen_time: $avg_screen_time,
				total_action_count: $total_action_count,
				avg_message_answer_time: $avg_message_answer_time,
				ldap_account_data: $ldap_account_data,
			);

			// создаем диалог с создателем заявки
			Gateway_Event_Dispatcher::dispatch(
				Type_Event_Conversation_AddSingleList::create($user_id, [$join_link->creator_user_id], false, false),
				true
			);
		}

		return [$is_postmoderation, $join_link->entry_option, $role, $permissions, $status, $join_link->creator_user_id, $entry_id, $token];
	}

	/**
	 * Создаем ссылку инвайт
	 *
	 * @param int $user_id
	 * @param int $lives_day_count
	 * @param int $can_use_count
	 *
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_ExceededCountActiveInvite
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function add(int $user_id, int $lives_day_count, int $can_use_count):Struct_Db_CompanyData_JoinLink {

		// проверяем что пользователь имеет права на создание ссылок
		$user = Domain_User_Action_Member_GetShort::do($user_id);
		Permission::assertCanInviteMember($user->role, $user->permissions);

		// создаем ссылку
		return Domain_JoinLink_Action_Create_Regular::do($user_id, $lives_day_count, false, $can_use_count, 0);
	}

	/**
	 * получаем данные по ссылке для участника компании
	 *
	 */
	public static function getJoinLinkInfoForMember(string $join_link_uniq, int $member_id):array {

		// смотрим, что компания не удалена
		Domain_Company_Entity_Dynamic::assertCompanyIsNotDeleted();

		try {
			$join_link = Domain_JoinLink_Action_Get::do($join_link_uniq);
		} catch (cs_InviteLinkNotExist) {
			throw new cs_IncorrectInviteLinkUniq("invite link doesn't exists");
		}

		$user_info = Gateway_Bus_CompanyCache::getMember($member_id);

		// получаем статус увольнения пользователя (уволился, вообще не увольнялся, на этапе увольнения)
		$is_exit_status_in_progress = Domain_User_Entity_TaskExit::isExitStatusInProgress($member_id);

		// требуется ли модерация заявки?
		$is_postmoderation = Domain_JoinLink_Entity_Main::isPostModerationEnabled($join_link->entry_option);

		return [$join_link->entry_option, $is_postmoderation, $join_link->creator_user_id, $is_exit_status_in_progress, true, $user_info->role];
	}

	/**
	 * получаем (если надо, то создаем) ссылку-приглашения для автоматического вступления
	 *
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param string $auto_join_type
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_ExceededCountActiveInvite
	 * @throws cs_JoinLinkNotExist
	 */
	public static function getForAutoJoin(int $user_id, int $creator_user_id, string $auto_join_type):array {

		// конвертируем строку с типом во внутреннее значение
		$type = Domain_JoinLink_Entity_AutoJoin::convertStringifyType($auto_join_type);

		try {

			// пытаемся достать ссылку
			$join_link = Domain_JoinLink_Entity_AutoJoin::get($type);

			// если ссылку нельзя использовать, то бросаем исключение
			Domain_JoinLink_Entity_Main::assertCanUse($join_link);
		} catch (cs_InviteLinkNotExist|cs_InviteLinkIdExpired|cs_InviteLinkNotActive|cs_JoinLinkNotExist) {

			// не смогли достать – создадим новую
			$join_link = Domain_JoinLink_Entity_AutoJoin::create($creator_user_id, $type);
		}

		// получаем статус увольнения кандидата (уволился, вообще не увольнялся, на этапе увольнения)
		$is_exit_status_in_progress = Domain_User_Entity_TaskExit::isExitStatusInProgress($user_id);

		// требуется ли модерация заявки?
		$is_postmoderation = Domain_JoinLink_Entity_Main::isPostModerationEnabled($join_link->entry_option);

		return [$join_link->join_link_uniq, $join_link->entry_option, $is_postmoderation, $join_link->creator_user_id, $is_exit_status_in_progress, false, 0];
	}
}
