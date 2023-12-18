<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс сокет-методов для работы с приглашениями в компанию
 */
class Socket_Hiring_JoinLink extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"add",
		"getInfoForMember",
	];

	/**
	 * Создаем ссылку приглашение в компанию от лица пользователя
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_IncorrectType
	 * @throws cs_PlatformNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function add():array {

		$creator_user_id = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$lives_day_count = $this->post(\Formatter::TYPE_INT, "lives_day_count", 2);
		$can_use_count   = $this->post(\Formatter::TYPE_INT, "can_use_count", 10000);

		try {

			$join_link = Domain_JoinLink_Scenario_Socket::add($creator_user_id, $lives_day_count, $can_use_count);
		} catch (cs_CompanyUserIsEmployee) {
			return $this->error(2208001, "User has no rights to work with join-links");
		} catch (cs_ExceededCountActiveInvite) {
			return $this->error(2208002, "Too many active invites");
		}

		// приводим ссылку к формату
		$join_link_formatted = Apiv2_Format::joinLink($join_link, Domain_JoinLink_Entity_Main::getLink($join_link));

		return $this->ok([
			"join_link" => (object) $join_link_formatted,
		]);
	}

	/**
	 * получаем данные по ссылке для участника компании
	 */
	public function getInfoForMember():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");

		try {

			[
				$entry_option,
				$is_postmoderation,
				$inviter_user_id,
				$is_exit_status_in_progress,
				$was_member,
				$role,
			] = Domain_JoinLink_Scenario_Socket::getJoinLinkInfoForMember($join_link_uniq, $this->user_id);
		} catch (\cs_RowIsEmpty|cs_IncorrectInviteLinkUniq|cs_InviteLinkNotExist|cs_CompanyIsDeleted) {
			return $this->error(404, "invite-link is not active");
		}

		return $this->ok([
			"entry_option"               => (int) $entry_option,
			"is_postmoderation"          => (int) $is_postmoderation,
			"inviter_user_id"            => (int) $inviter_user_id,
			"is_exit_status_in_progress" => (int) $is_exit_status_in_progress,
			"was_member"                 => (int) $was_member,
			"role"                       => (int) $role,
		]);
	}
}
