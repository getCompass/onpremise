<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\AccountDeleted;
use CompassApp\Domain\Member\Exception\IsLeft;
use CompassApp\Pack\Conversation;

/**
 * контроллер, отвечающий за группы диалогов
 */
class Apiv2_Conversations_Groups extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getShared",
		"addParticipant",
		"add",
		"edit",
		"copy",
		"copyWithUsers",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"addParticipant",
		"add",
		"edit",
		"copy",
		"copyWithUsers",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [
		Member::ROLE_GUEST => [
			"addParticipant",
			"add",
			"copy",
			"copyWithUsers",
		],
	];

	/**
	 * Метод для получения общих с пользователем групповых диалогов
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function getShared():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			$shared_group_list = Domain_Conversation_Scenario_Apiv2::getShared($this->user_id, $user_id);
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect user id");
		}

		$left_menu_list = [];
		foreach ($shared_group_list as $left_menu_row) {

			// подготавливаем и форматируем сущность left_menu
			$temp             = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
			$left_menu_list[] = (object) Apiv2_Format::leftMenu($temp);
		}

		return $this->ok([
			"left_menu_list" => (array) $left_menu_list,
		]);
	}

	/**
	 * Метод для добавления участника в группы
	 *
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \paramException
	 * @throws \parseException
	 * @long
	 */
	public function addParticipant():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$conversation_key_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_key_list");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_ADDPARTICIPANT);

		try {

			[$not_allowed_conversation_list, $not_group_conversation_list] = Domain_Conversation_Scenario_Apiv2::addParticipant(
				$this->user_id, $this->role, $this->permissions, $user_id, $conversation_key_list
			);
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			throw new ParamException("passed wrong conversation key");
		} catch (cs_IncorrectConversationMapList) {
			throw new ParamException("incorrect conversation map list");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect participant id");
		} catch (AccountDeleted) {
			throw new CaseException(2209007, "member deleted account");
		} catch (\cs_RowIsEmpty|IsLeft) {
			throw new CaseException(2209006, "member not found");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		foreach ($not_allowed_conversation_list as $k => $v) {
			$not_allowed_conversation_list[$k] = Conversation::doEncrypt($v);
		}

		foreach ($not_group_conversation_list as $k => $v) {
			$not_group_conversation_list[$k] = Conversation::doEncrypt($v);
		}

		return $this->ok([
			"not_allowed_conversation_list" => (array) $not_allowed_conversation_list,
			"not_group_conversation_list"   => (array) $not_group_conversation_list,
		]);
	}

	/**
	 * Создать группу
	 *
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public function add():array {

		$name            = $this->post(\Formatter::TYPE_STRING, "name");
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", "");
		$description     = $this->post(\Formatter::TYPE_STRING, "description", "");

		$avatar_file_map = "";

		if ($avatar_file_key !== "") {
			$avatar_file_map = \CompassApp\Pack\File::tryDecrypt($avatar_file_key);
		}

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_ADD, "groups", "row2");

		try {
			$prepared_conversation = Domain_Group_Scenario_Api::add($this->user_id, $name, $avatar_file_map, $description);
		} catch (Domain_Group_Exception_InvalidFileForAvatar) {
			throw new ParamException("invalid file for avatar");
		} catch (Domain_Group_Exception_InvalidName) {
			throw new ParamException("invalid name for group");
		} catch (Domain_Group_Exception_NameContainsEmoji) {
			throw new CaseException(2219001, "name contains emoji");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok([
			"conversation" => Apiv2_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * Изменить группу
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @long - конвертации ключей в мапы
	 */
	public function edit():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$name             = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_file_key  = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);
		$description      = $this->post(\Formatter::TYPE_STRING, "description", false);

		if ($name === false && $avatar_file_key === false && $description === false) {
			throw new ParamException("you should pass at least one param for change");
		}

		$avatar_file_map = $avatar_file_key === false ? false : "";

		if ($avatar_file_key !== false && $avatar_file_key !== "") {
			$avatar_file_map = \CompassApp\Pack\File::tryDecrypt($avatar_file_key);
		}

		$conversation_map = Conversation::tryDecrypt($conversation_key);

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_SETINFO);

		try {

			$prepared_conversation = Domain_Group_Scenario_Api::edit(
				$this->user_id, $conversation_map, $name, $avatar_file_map, $description);
		} catch (Domain_Group_Exception_InvalidFileForAvatar) {
			throw new ParamException("invalid file for avatar");
		} catch (Domain_Group_Exception_InvalidName) {
			throw new ParamException("invalid name for group");
		} catch (Domain_Group_Exception_NameContainsEmoji) {
			throw new CaseException(2219001, "name contains emoji");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok([
			"conversation" => Apiv2_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * Продублировать группу
	 *
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @long - конвертации ключей в мапы
	 */
	public function copy():array {

		$conversation_key      = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$name                  = $this->post(\Formatter::TYPE_STRING, "name");
		$avatar_file_key       = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", "");
		$description           = $this->post(\Formatter::TYPE_STRING, "description", "");
		$excluded_user_id_list = $this->post(\Formatter::TYPE_ARRAY, "excluded_user_id_list", []);

		$avatar_file_map = "";

		if ($avatar_file_key !== "") {
			$avatar_file_map = \CompassApp\Pack\File::tryDecrypt($avatar_file_key);
		}

		$conversation_map = Conversation::tryDecrypt($conversation_key);

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_ADD);

		try {

			$prepared_conversation = Domain_Group_Scenario_Api::copy(
				$this->user_id, $conversation_map, $name, $avatar_file_map, $description, $excluded_user_id_list);
		} catch (Domain_Group_Exception_InvalidFileForAvatar) {
			throw new ParamException("invalid file for avatar");
		} catch (Domain_Group_Exception_InvalidName) {
			throw new ParamException("invalid name for group");
		} catch (Domain_Group_Exception_NameContainsEmoji) {
			throw new CaseException(2219001, "name contains emoji");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok([
			"conversation" => Apiv2_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * Продублировать группу с добавлением пользователей
	 *
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @long - конвертации ключей в мапы
	 */
	public function copyWithUsers():array {

		$conversation_key      = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$name                  = $this->post(\Formatter::TYPE_STRING, "name");
		$avatar_file_key       = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", "");
		$description           = $this->post(\Formatter::TYPE_STRING, "description", "");
		$excluded_user_id_list = $this->post(\Formatter::TYPE_ARRAY, "excluded_user_id_list", []);

		$avatar_file_map = "";
		if ($avatar_file_key !== "") {
			$avatar_file_map = \CompassApp\Pack\File::tryDecrypt($avatar_file_key);
		}
		$conversation_map = Conversation::tryDecrypt($conversation_key);

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_ADD);

		try {

			$prepared_conversation = Domain_Group_Scenario_Api::copyWithUsers(
				$this->user_id, $conversation_map, $name, $avatar_file_map, $description, $excluded_user_id_list);
		} catch (Domain_Group_Exception_InvalidFileForAvatar) {
			throw new ParamException("invalid file for avatar");
		} catch (Domain_Group_Exception_InvalidName) {
			throw new ParamException("invalid name for group");
		} catch (Domain_Group_Exception_NameContainsEmoji) {
			throw new CaseException(2219001, "name contains emoji");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (cs_PlatformNotFound) {
			throw new ParamException("invalid platform");
		}

		return $this->ok([
			"conversation" => Apiv2_Format::conversation($prepared_conversation),
		]);
	}
}
