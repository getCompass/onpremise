<?php

namespace Compass\Company;

use \BaseFrame\Exception\Request\CaseException;
use \BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс, отвечающий за апи ссылок-приглашений в компанию
 */
class Apiv2_Hiring_JoinLink extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"add",
		"delete",
		"edit",
		"getActiveList",
		"getInactiveList",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Метод для генерации ссылки собственником/руководителем
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws cs_IncorrectType
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public function add():array {

		$type              = $this->post(\Formatter::TYPE_STRING, "type");
		$lives_hour_count  = $this->post(\Formatter::TYPE_INT, "lives_hour_count", false);
		$lives_day_count   = $this->post(\Formatter::TYPE_INT, "lives_day_count", false);
		$can_use_count     = $this->post(\Formatter::TYPE_INT, "can_use_count", false);
		$is_postmoderation = $this->post(\Formatter::TYPE_INT, "is_postmoderation", false) == 1;
		$entry_option      = $this->post(\Formatter::TYPE_INT, "entry_option", false);

		try {

			$join_link = Domain_JoinLink_Scenario_Api::add($this->user_id, $this->role, $this->permissions, $type, $lives_day_count, $lives_hour_count,
				$can_use_count, $is_postmoderation, $entry_option, $this->method_version);
		} catch (cs_IncorrectLivesDayCount|cs_IncorrectLivesHourCount) {
			throw new ParamException("invalid lives_day_count: {$lives_day_count}");
		} catch (cs_IncorrectType) {
			throw new ParamException("invalid type: {$type}");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2208001, "User has no rights to work with join-links");
		} catch (cs_ExceededCountActiveInvite) {
			throw new CaseException(2208002, "Too many active invites");
		} catch (cs_IncorrectCanUseCount) {
			throw new ParamException("invalid can_use_count");
		} catch (Domain_JoinLink_Exception_IncorrectEntryOption $e) {
			throw new ParamException($e->getMessage());
		}

		// приводим ссылку к формату
		$invite_link_formatted = Apiv2_Format::inviteLink($join_link, Domain_JoinLink_Entity_Main::getLink($join_link), method_version: $this->method_version);
		$join_link_formatted   = Apiv2_Format::joinLink($join_link, Domain_JoinLink_Entity_Main::getLink($join_link), method_version: $this->method_version);

		// только для массовых пока нужен ивент
		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];
		Gateway_Bus_Sender::inviteLinkCreated($invite_link_formatted, $join_link_formatted, $talking_user_list, $this->user_id);
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_JOIN_LINK);

		return $this->ok([
			"join_link" => (object) $join_link_formatted,
		]);
	}

	/**
	 * Метод для удаления существующей ссылки собственником/руководителем
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws CaseException
	 */
	public function delete():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");

		try {
			Domain_JoinLink_Scenario_Api::delete($this->user_id, $this->role, $this->permissions, $join_link_uniq);
		} catch (cs_IncorrectJoinLinkUniq|cs_JoinLinkNotExist) {
			throw new ParamException("invalid join_link_uniq: {$join_link_uniq}");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2208001, "User has no rights to work with join-links");
		} catch (cs_IncorrectType) {
			throw new ParamException("invalid type");
		}

		return $this->ok();
	}

	/**
	 * Метод для редактирования ссылки собственником/руководителем
	 *
	 * @long
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws cs_IncorrectType
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function edit():array {

		$join_link_uniq    = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");
		$lives_day_count   = $this->post(\Formatter::TYPE_INT, "lives_day_count", false);
		$lives_hour_count  = $this->post(\Formatter::TYPE_INT, "lives_hour_count", false);
		$can_use_count     = $this->post(\Formatter::TYPE_INT, "can_use_count", false);
		$is_postmoderation = $this->post(\Formatter::TYPE_INT, "is_postmoderation", false);
		$entry_option      = $this->post(\Formatter::TYPE_INT, "entry_option", false);
		$entry_option      = $entry_option === false ? null : $entry_option;

		if ($is_postmoderation === false) {
			$is_postmoderation = null;
		} else {
			$is_postmoderation = $is_postmoderation == 1;
		}

		try {

			[$join_link, $entry_user_id_list] = Domain_JoinLink_Scenario_Api::edit(
				$this->user_id, $this->role, $this->permissions, $join_link_uniq, $lives_day_count, $lives_hour_count, $can_use_count,
				$is_postmoderation, $entry_option, $this->method_version
			);
		} catch (cs_JoinLinkNotExist) {
			throw new ParamException("link not found");
		} catch (cs_IncorrectLivesDayCount|cs_IncorrectLivesHourCount) {
			throw new ParamException("invalid lives_day_count: {$lives_day_count}");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2208001, "User has no rights to work with join-links");
		} catch (cs_InvalidStatusForEditInvite) {
			throw new CaseException(2208003, "Join link is not active");
		} catch (cs_JoinLinkDeleted) {
			throw new CaseException(2208004, "Join link is deleted");
		} catch (cs_IncorrectCanUseCount) {
			throw new ParamException("invalid can_use_count");
		} catch (cs_InvalidParamForEditInvite) {
			throw new ParamException("invalid param for edit invite");
		} catch (cs_IncorrectType) {
			throw new ParamException("invalid type");
		} catch (Domain_JoinLink_Exception_IncorrectEntryOption $e) {
			throw new ParamException($e->getMessage());
		}

		$this->action->users($entry_user_id_list);

		return $this->ok([
			"join_link" => (object) Apiv2_Format::joinLink($join_link, Domain_JoinLink_Entity_Main::getLink($join_link), $entry_user_id_list, $this->method_version),
		]);
	}

	/**
	 * Метод для получения активных ссылок
	 *
	 * @throws ParamException
	 * @throws cs_IncorrectType
	 * @throws CaseException
	 * @long
	 */
	public function getActiveList():array {

		$type = $this->post(\Formatter::TYPE_STRING, "type", Domain_JoinLink_Action_GetFilteredTypeList::MASS_FILTER_TYPE);

		try {
			[$join_link_list, $entry_user_id_list_by_uniq] = Domain_JoinLink_Scenario_Api::getActiveList(
				$type, $this->user_id, $this->role, $this->permissions, $this->method_version
			);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2208001, "User has no rights to work with join-links");
		} catch (cs_IncorrectType) {
			throw new ParamException("invalid type");
		}

		$join_link_formatted_list = [];
		$action_user_id_list      = [];

		/** @var Struct_Db_CompanyData_JoinLink $join_link */
		foreach ($join_link_list as $join_link) {

			$entry_user_id_list = $entry_user_id_list_by_uniq[$join_link->join_link_uniq] ?? [];

			$link                       = Domain_JoinLink_Entity_Main::getLink($join_link);
			$join_link_formatted_list[] = Apiv2_Format::joinLink($join_link, $link, $entry_user_id_list, $this->method_version);

			$action_user_id_list[] = $join_link->creator_user_id;

			if (count($entry_user_id_list) > 0) {
				$action_user_id_list = array_merge($action_user_id_list, $entry_user_id_list);
			}
		}

		$this->action->users(array_unique($action_user_id_list));

		return $this->ok([
			"join_link_list" => (array) $join_link_formatted_list,
		]);
	}

	/**
	 * Метод для получения неактивных ссылок
	 *
	 * @throws ParamException
	 * @throws cs_IncorrectType
	 * @throws CaseException
	 * @long
	 */
	public function getInactiveList():array {

		$count  = $this->post(\Formatter::TYPE_INT, "count", false);
		$offset = $this->post(\Formatter::TYPE_INT, "offset", false);

		try {

			[$join_link_list, $has_next, $entry_user_id_list_by_uniq] = Domain_JoinLink_Scenario_Api::getInactiveList(
				$this->role, $this->permissions, $count, $offset, $this->method_version
			);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2208001, "User has no rights to work with join-links");
		}

		$join_link_formatted_list = [];
		$action_user_id_list      = [];

		/** @var Struct_Db_CompanyData_JoinLink $join_link */
		foreach ($join_link_list as $join_link) {

			$entry_user_id_list = $entry_user_id_list_by_uniq[$join_link->join_link_uniq] ?? [];

			$link                       = Domain_JoinLink_Entity_Main::getLink($join_link);
			$join_link_formatted_list[] = Apiv2_Format::joinLink($join_link, $link, $entry_user_id_list, $this->method_version);

			$action_user_id_list[] = $join_link->creator_user_id;

			if (count($entry_user_id_list) > 0) {
				$action_user_id_list = array_merge($action_user_id_list, $entry_user_id_list);
			}
		}

		$this->action->users(array_unique($action_user_id_list));

		return $this->ok([
			"join_link_list" => (array) $join_link_formatted_list,
			"has_next"       => (int) $has_next,
		]);
	}
}