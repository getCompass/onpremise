<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;
use JetBrains\PhpStorm\ArrayShape;

/**
 * контроллер для сокет методов класса groups
 */
class Socket_Groups extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"createCompanyDefaultGroups",
		"getCompanyDefaultGroups",
		"getCompanyHiringGroups",
		"createCompanyExtendedEmployeeCardGroups",
		"addUserbotToGroup",
		"removeUserbotFromGroup",
		"getUserbotGroupInfoList",
		"addToDefaultGroups",
		"createRespectConversation",
		"addMembersToRespectConversation",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * создаем дефолтные группы компании
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_DecryptHasFailed
	 */
	public function createCompanyDefaultGroups():array {

		$default_file_key_list = $this->post(\Formatter::TYPE_ARRAY, "default_file_key_list");
		$locale                = $this->post(\Formatter::TYPE_STRING, "locale", Locale::LOCALE_ENGLISH);

		$default_file_key_list = new Struct_File_Default(
			$default_file_key_list["hiring_conversation_avatar_file_key"],
			$default_file_key_list["notes_conversation_avatar_file_key"],
			$default_file_key_list["support_conversation_avatar_file_key"],
			$default_file_key_list["respect_conversation_avatar_file_key"]
		);

		[
			$notes_conversation_avatar_file_map,
			$support_conversation_avatar_file_map,
			$respect_conversation_avatar_file_map,
		] = self::_getConversationAvatarFileMap($default_file_key_list);

		// создаем дефолтные группы компании
		Domain_Group_Action_CompanyDefaultCreate::do($this->user_id, $locale, false, $respect_conversation_avatar_file_map);

		// создаем группы найма компании
		Domain_Group_Action_CompanyHiringCreate::do($this->user_id, $default_file_key_list, $locale);

		// кладем аватарку чата заметки и службы поддержки в конфиг компании
		Domain_Conversation_Action_Config_Add::do($notes_conversation_avatar_file_map, Domain_Conversation_Entity_Config::NOTES_AVATAR_FILE_KEY_NAME);
		Domain_Conversation_Action_Config_Add::do($support_conversation_avatar_file_map, Domain_Conversation_Entity_Config::SUPPORT_AVATAR_FILE_KEY_NAME);

		return $this->ok();
	}

	/**
	 * Добавить в дефолтные группы компании
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public function addToDefaultGroups():array {

		$is_owner   = $this->post(\Formatter::TYPE_INT, "is_owner") === 1;
		$space_role = $this->post(\Formatter::TYPE_INT, "role");
		$locale     = $this->post(\Formatter::TYPE_STRING, "locale");

		// если пользователь создатель компании то он owner всех дефолтных групп
		$conversation_role = $is_owner === true ? Type_Conversation_Meta_Users::ROLE_OWNER : Type_Conversation_Meta_Users::ROLE_DEFAULT;

		// создаём для пользователя его личный чат заметки
		Domain_Conversation_Action_NotesCreate::do($this->user_id, $locale);

		if ($space_role != Member::ROLE_GUEST) {

			// добавляем пользователя в дефолтные группы компании
			Domain_Group_Action_CompanyDefaultJoin::do($this->user_id, $conversation_role, $is_owner);
		}

		return $this->ok();
	}

	/**
	 * получаем file_map для аватара
	 *
	 * @param Struct_File_Default $default_file_key_list
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected static function _getConversationAvatarFileMap(Struct_File_Default $default_file_key_list):array {

		if ($default_file_key_list->notes_conversation_avatar_file_key !== "") {
			$notes_conversation_avatar_file_map = \CompassApp\Pack\File::tryDecrypt($default_file_key_list->notes_conversation_avatar_file_key);
		} else {
			$notes_conversation_avatar_file_map = "";
		}

		if ($default_file_key_list->support_conversation_avatar_file_key !== "") {
			$support_conversation_avatar_file_map = \CompassApp\Pack\File::tryDecrypt($default_file_key_list->support_conversation_avatar_file_key);
		} else {
			$support_conversation_avatar_file_map = "";
		}

		if ($default_file_key_list->respect_conversation_avatar_file_key !== "") {
			$respect_conversation_avatar_file_map = \CompassApp\Pack\File::tryDecrypt($default_file_key_list->respect_conversation_avatar_file_key);
		} else {
			$respect_conversation_avatar_file_map = "";
		}

		return [$notes_conversation_avatar_file_map, $support_conversation_avatar_file_map, $respect_conversation_avatar_file_map];
	}

	/**
	 * создаем дефолтные группы компании
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function createCompanyExtendedEmployeeCardGroups():array {

		$locale = $this->post(\Formatter::TYPE_STRING, "locale");

		// создаем дефолтные группы компании
		Domain_Group_Action_CompanyDefaultCreate::do($this->user_id, $locale, true);

		return $this->ok();
	}

	/**
	 * возвращаем список дефолтных групп
	 *
	 * @return array
	 */
	#[ArrayShape(["status" => "string", "response" => "object"])]
	public function getCompanyDefaultGroups():array {

		return $this->ok(Domain_Group_Scenario_Socket::getCompanyDefaultGroups());
	}

	/**
	 * возвращаем список групп наймаы
	 *
	 * @return array
	 */
	#[ArrayShape(["status" => "string", "response" => "object"])]
	public function getCompanyHiringGroups():array {

		return $this->ok(Domain_Group_Scenario_Socket::getCompanyHiringGroups());
	}

	/**
	 * добавляем ботов в группу
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addUserbotToGroup():array {

		$userbot_id_list_by_user_id = $this->post(\Formatter::TYPE_ARRAY, "userbot_id_list_by_user_id");
		$first_add_userbot_id_list  = $this->post(\Formatter::TYPE_ARRAY, "first_add_userbot_id_list");
		$conversation_key           = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map           = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {
			Domain_Group_Scenario_Socket::addUserbotToGroup($this->user_id, $userbot_id_list_by_user_id, $first_add_userbot_id_list, $conversation_map);
		} catch (Domain_Conversation_Exception_NotGroup) {
			return $this->error(2418003, "conversation is not group");
		} catch (Domain_Conversation_Exception_User_IsNotMember) {
			return $this->error(2418002, "user is not conversation member");
		}

		return $this->ok();
	}

	/**
	 * убираем бота из группы
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 */
	public function removeUserbotFromGroup():array {

		$userbot_id_by_user_id = $this->post(\Formatter::TYPE_ARRAY, "userbot_id_by_user_id");
		$conversation_key      = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map      = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {
			Domain_Group_Scenario_Socket::removeUserbotFromGroup($userbot_id_by_user_id, $conversation_map);
		} catch (Domain_Conversation_Exception_NotGroup) {
			return $this->error(2418003, "conversation is not group");
		}

		return $this->ok();
	}

	/**
	 * получаем информацию по группам ботов
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getUserbotGroupInfoList():array {

		$conversation_map_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_map_list");

		$group_info_list = Domain_Group_Scenario_Socket::getUserbotGroupInfoList($conversation_map_list);

		return $this->ok([
			"group_info_list" => (array) $group_info_list,
		]);
	}

	/**
	 * Создаем чат "Спасибо", если не создан
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function createRespectConversation():array {

		$creator_user_id                      = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$respect_conversation_avatar_file_key = $this->post(\Formatter::TYPE_STRING, "respect_conversation_avatar_file_key");
		$is_force_update                      = $this->post(\Formatter::TYPE_INT, "is_force_update") === 1;

		$is_created = Domain_Group_Scenario_Socket::createRespectConversation($creator_user_id, $respect_conversation_avatar_file_key, $is_force_update);

		return $this->ok([
			"is_created" => (int) $is_created,
		]);
	}

	/**
	 * Добавляем всех участников пространства в чат "Спасибо"
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public function addMembersToRespectConversation():array {

		Domain_Group_Scenario_Socket::addMembersToRespectConversation();

		return $this->ok();
	}
}