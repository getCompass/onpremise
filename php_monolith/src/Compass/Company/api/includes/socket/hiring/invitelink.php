<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Exception\ActionNotAllowed;

/**
 * Класс сокет-методов для работы с заявками найма или увольнения
 */
class Socket_Hiring_InviteLink extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"accept",
		"getInfo",
		"getCreatorUserId",
		"deleteAllByUser",
	];

	/**
	 * Принимаем инвайт
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyNotServedException
	 * @throws ActionNotAllowed
	 * @throws cs_JoinLinkNotExist
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public function accept():array {

		$invite_link_uniq             = $this->post(\Formatter::TYPE_STRING, "invite_link_uniq");
		$comment                      = $this->post(\Formatter::TYPE_STRING, "comment", "");
		$full_name                    = $this->post(\Formatter::TYPE_STRING, "full_name");
		$avatar_file_key              = $this->post(\Formatter::TYPE_STRING, "avatar_file_key");
		$avatar_color_id              = $this->post(\Formatter::TYPE_STRING, "avatar_color_id");
		$is_force_exit_task_not_exist = $this->post(\Formatter::TYPE_INT, "is_force_exit_task_not_exist") == 1;
		$locale                       = $this->post(\Formatter::TYPE_STRING, "locale");
		$avg_screen_time              = $this->post(\Formatter::TYPE_INT, "avg_screen_time", 0);
		$total_action_count           = $this->post(\Formatter::TYPE_INT, "total_action_count", 0);
		$avg_message_answer_time      = $this->post(\Formatter::TYPE_INT, "avg_message_answer_time", 0);

		if ($is_force_exit_task_not_exist && !isTestServer()) {
			throw new ParamException("only for test-server");
		}

		try {

			[$is_postmoderation, $entry_option, $user_space_role, $user_space_permissions, $status, $inviter_user_id, $entry_id, $token] =
				Domain_JoinLink_Scenario_Socket::acceptInvite(
				$this->user_id,
				$invite_link_uniq,
				$comment,
				$full_name,
				$avatar_file_key,
				$avatar_color_id,
				$locale,
				$is_force_exit_task_not_exist,
				$avg_screen_time,
				$total_action_count,
				$avg_message_answer_time,
			);
		} catch (cs_CompanyUserIsEmployee|cs_InviteLinkNotActive|cs_InviteLinkNotExist|cs_InviteLinkAlreadyUsed|cs_InviteLinkIdExpired|cs_CompanyIsDeleted) {
			return $this->error(404, "invite is not active");
		} catch (cs_Text_IsTooLong) {
			return $this->error(406, "invited user comment is too long");
		} catch (cs_MemberExitTaskInProgress) {
			return $this->error(408, "invited user has not finished exit the company yet");
		}

		return $this->ok([
			"is_postmoderation"      => (int) $is_postmoderation,
			"entry_option"           => (int) $entry_option,
			"user_space_role"        => (int) $user_space_role,
			"user_space_permissions" => (int) $user_space_permissions,
			"inviter_user_id"        => (string) $inviter_user_id,
			"entry_id"               => (int) $entry_id,
			"status"                 => (int) $status,
			"token"                  => (string) $token,
		]);
	}

	/**
	 * Проверяем что инвайт существует и получем инфу о нем
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyNotServedException
	 * @throws ActionNotAllowed
	 * @throws \cs_RowIsEmpty
	 * @throws cs_JoinLinkNotExist
	 */
	public function getInfo():array {

		$invite_link_uniq = $this->post(\Formatter::TYPE_STRING, "invite_link_uniq");

		try {

			[
				$entry_option,
				$is_postmoderation,
				$inviter_user_id,
				$is_exit_status_in_progress,
				$was_member,
				$candidate_role,
			] = Domain_JoinLink_Scenario_Socket::getInviteLinkInfo($invite_link_uniq, $this->user_id);
		} catch (cs_IncorrectInviteLinkUniq|cs_InviteLinkNotActive|cs_CompanyUserIsEmployee|cs_InviteLinkNotExist|cs_InviteLinkIdExpired|cs_CompanyIsDeleted) {
			return $this->error(404, "invite-link is not active");
		}

		return $this->ok([
			"entry_option"               => (int) $entry_option,
			"is_postmoderation"          => (int) $is_postmoderation,
			"inviter_user_id"            => (int) $inviter_user_id,
			"is_exit_status_in_progress" => (int) $is_exit_status_in_progress,
			"was_member"                 => (int) $was_member,
			"candidate_role"             => (int) $candidate_role,
		]);
	}

	/**
	 * Возвращаем ID пользователя создателя ссылки приглашения
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getCreatorUserId():array {

		$invite_link_uniq = $this->post(\Formatter::TYPE_STRING, "invite_link_uniq");

		try {

			$creator_user_id = Domain_JoinLink_Scenario_Socket::getCreatorUserId($invite_link_uniq);
		} catch (cs_IncorrectInviteLinkUniq|cs_JoinLinkNotExist|cs_CompanyIsDeleted) {
			return $this->error(404, "invite-link is not active");
		}

		return $this->ok([
			"creator_user_id" => (int) $creator_user_id,
		]);
	}

	/**
	 * Отзываем все ссылки пользователей
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function deleteAllByUser():array {

		Domain_User_Action_UserInviteLinkActive_DeleteAllByUser::do($this->user_id);

		return $this->ok();
	}
}
