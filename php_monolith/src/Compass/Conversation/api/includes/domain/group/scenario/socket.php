<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарий сокета для взаимодействий с группами
 */
class Domain_Group_Scenario_Socket {

	/**
	 * возвращаем список дефолтных групп
	 *
	 */
	public static function getCompanyDefaultGroups():array {

		$groups = Domain_Group_Entity_Company::getDefaultGroupList();
		$keys   = [];
		foreach ($groups as $group) {
			$keys[] = \CompassApp\Pack\Conversation::doEncrypt(Domain_Conversation_Action_Config_Get::do($group)["value"]);
		}

		return $keys;
	}

	/**
	 * возвращаем список групп наймаы
	 *
	 */
	public static function getCompanyHiringGroups():array {

		$groups = Domain_Group_Entity_Company::HIRING_GROUP_LIST_ON_ADD_MEMBER;
		$keys   = [];
		foreach ($groups as $group) {
			$keys[] = \CompassApp\Pack\Conversation::doEncrypt(Domain_Conversation_Action_Config_Get::do($group)["value"]);
		}

		return $keys;
	}

	/**
	 * добавляем бота в группу
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws Domain_Conversation_Exception_NotGroup
	 * @throws Domain_Conversation_Exception_User_IsNotMember
	 */
	public static function addUserbotToGroup(int $developer_user_id, array $userbot_list_by_user_id, array $first_add_userbot_id_list, string $conversation_map):void {

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что диалог групповой
		if (!in_array($meta_row["type"], [CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_GENERAL])) {
			throw new Domain_Conversation_Exception_NotGroup("conversation must be group in " . __METHOD__);
		}

		// проверяем, что добавляющий бота в группу является участником диалога
		if (!Type_Conversation_Meta_Users::isMember($developer_user_id, $meta_row["users"])) {
			throw new Domain_Conversation_Exception_User_IsNotMember("user not member of conversation in " . __METHOD__);
		}

		// добавляем бота в группу
		foreach ($userbot_list_by_user_id as $userbot_user_id => $userbot_id) {

			// если бот уже часть группы
			if (Type_Conversation_Meta_Extra::isUserbot($meta_row["extra"], $userbot_id)) {
				continue;
			}

			// добавляем бота
			Domain_Group_Action_UserbotDoJoin::do($conversation_map, $userbot_user_id, $userbot_id, in_array($userbot_id, $first_add_userbot_id_list));
		}
	}

	/**
	 * убираем бота из группы
	 *
	 * @throws \paramException
	 * @throws Domain_Conversation_Exception_NotGroup
	 */
	public static function removeUserbotFromGroup(array $userbot_id_by_user_id, string $conversation_map):void {

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что диалог групповой
		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {
			throw new Domain_Conversation_Exception_NotGroup("conversation must be group in " . __METHOD__);
		}

		// убираем бота из группы
		foreach ($userbot_id_by_user_id as $user_id => $userbot_id) {
			Helper_Groups::doUserKick($meta_row, $user_id, true, $userbot_id);
		}
	}

	/**
	 * получаем данные групп ботов
	 */
	public static function getUserbotGroupInfoList(array $conversation_map_list):array {

		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		$output = [];
		foreach ($meta_list as $meta_row) {

			$output[] = [
				"conversation_key" => \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]),
				"name"             => $meta_row["conversation_name"],
				"avatar_file_map"  => $meta_row["avatar_file_map"],
			];
		}

		return $output;
	}

	/**
	 * Создаем чат "Спасибо", если не создан
	 * Если создан, но тип чата старый - меняем на корректный
	 * Если создан и тип чата корректный - ничего не делаем
	 *
	 * @param int    $creator_user_id
	 * @param string $respect_conversation_avatar_file_key
	 * @param bool   $is_force_update
	 *
	 * @return bool
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function createRespectConversation(int $creator_user_id, string $respect_conversation_avatar_file_key, bool $is_force_update):bool {

		// если пока не нужно создавать - выходим
		if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {
			return false;
		}

		$respect_conversation_avatar_file_map = \CompassApp\Pack\File::tryDecrypt($respect_conversation_avatar_file_key);

		$group_key_name = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
		$value          = Domain_Conversation_Action_Config_Get::do($group_key_name);
		if (isset($value["value"]) && mb_strlen($value["value"]) > 0) {

			// если тип не тот - меняем на нужный
			$meta_row = Type_Conversation_Meta::get($value["value"]);
			if ($meta_row["type"] != CONVERSATION_TYPE_GROUP_RESPECT || $is_force_update) {
				self::_changeRespectConversationType($meta_row["conversation_map"], $meta_row["users"]);
			}
			return false;
		}

		try {
			$group_name = Domain_Group_Entity_Company::getDefaultGroupNameByKey($group_key_name, Locale::LOCALE_RUSSIAN);
		} catch (LocaleTextNotFound) {
			throw new ParseFatalException("cant find group default name");
		}

		// создаем группу
		$meta_row = Type_Conversation_Group::add(
			$creator_user_id, $group_name, CONVERSATION_TYPE_GROUP_RESPECT, false, false,
			$respect_conversation_avatar_file_map, "", false, false
		);

		// добавляем ее ключ в конфиг
		Domain_Conversation_Action_Config_Add::do($meta_row["conversation_map"], $group_key_name);

		return true;
	}

	/**
	 * Меняем тип существующему диалогу
	 *
	 * @param string $conversation_map
	 * @param array  $users
	 *
	 * @return void
	 */
	protected static function _changeRespectConversationType(string $conversation_map, array $users):void {

		// получаем пользователей состоящих в диалоге
		$user_id_list = [];
		foreach ($users as $member_user_id => $_) {

			if (Type_Conversation_Meta_Users::isMember($member_user_id, $users)) {
				$user_id_list[] = $member_user_id;
			}
		}

		// обновляем тип пользователям в левом меню
		Gateway_Db_CompanyConversation_UserLeftMenu::setForUserIdList($user_id_list, $conversation_map, [
			"type" => CONVERSATION_TYPE_GROUP_RESPECT,
		]);

		// меняем тип в мете
		Gateway_Db_CompanyConversation_ConversationMeta::set($conversation_map, [
			"type" => CONVERSATION_TYPE_GROUP_RESPECT,
		]);
	}

	/**
	 * Добавляем пользователей в чат спасибо
	 *
	 * @return void
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public static function addMembersToRespectConversation():void {

		$group_key_name = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
		$value          = Domain_Conversation_Action_Config_Get::do($group_key_name);
		if (!isset($value["value"]) || mb_strlen($value["value"]) < 1) {
			throw new ParseFatalException("not found respect conversation");
		}

		// получаем информацию о группе
		$meta_row = Type_Conversation_Meta::get($value["value"]);

		// получаем пользователей по ролям
		$user_role_list = Gateway_Socket_Company::getUserRoleList([Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR]);

		// проходим по каждой роли и ее пользователям
		foreach ($user_role_list as $role => $user_id_list) {

			// выдаем роль в группе в зависимости от прав в пространстве
			$group_role = Type_Conversation_Meta_Users::ROLE_DEFAULT;
			if ($role === Member::ROLE_ADMINISTRATOR) {
				$group_role = Type_Conversation_Meta_Users::ROLE_ADMIN;
			}

			// проходим по каждому пользователю
			foreach ($user_id_list as $user_id) {

				// если еще не участник - добавляем
				if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
					Helper_Groups::doJoin($value["value"], $user_id, role: $group_role);
				}
			}
		}
	}
}