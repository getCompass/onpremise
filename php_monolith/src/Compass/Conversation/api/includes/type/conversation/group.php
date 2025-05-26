<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с сущностью группового диалога. Создание, привязка/отвязка пользователей и ботов.
 */
class Type_Conversation_Group extends Type_Conversation_Default {

	/**
	 * Создает групповой диалог с создателем внутри
	 *
	 * @throws ParseFatalException
	 */
	public static function add(int    $creator_user_id, string $group_name, int $group_type, bool $is_favorite = false, bool $is_mentioned = false,
					   string $avatar_file_map = "", string $description = "", bool $is_need_add_creator = true, bool $is_need_send_system_message = true,
					   bool   $is_channel = false):array {

		// формируем users добавляя туда создателя
		$users = [];
		if ($is_need_add_creator) {
			$users = Type_Conversation_Meta_Users::addMember($users, $creator_user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		}

		// инициируем extra для диалога и устанавливаем подтип диалога
		$extra = Type_Conversation_Meta_Extra::initExtra();
		$extra = Type_Conversation_Meta_Extra::setDescription($extra, $description);
		$extra = Type_Conversation_Meta_Extra::setFlagIsChannel($extra, $is_channel);

		// создаем новый conversation
		$meta_row = self::_createNewConversation(
			$group_type, ALLOW_STATUS_GREEN_LIGHT, $creator_user_id, $users, $extra, $group_name, $avatar_file_map,
		);

		if ($is_need_add_creator) {

			// создаем запись в левом меню создателя, записываем 0 т.к. allow_status_alias не существует для групповых диалогов
			self::_createUserCloudData(
				$creator_user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER,
				$group_type, 0, count($users), $group_name, $avatar_file_map, $is_favorite, $is_mentioned, is_channel: $is_channel
			);

			// пушим событие, что пользователь присоединился к группе
			Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create(
				$creator_user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER, time(), true
			));
		}

		// отправляем системное сообщение о создании группы, если необходимо
		if ($is_need_send_system_message) {
			self::_sendSystemMessageUserAddGroup($meta_row["conversation_map"], $meta_row, $creator_user_id, $group_name);
		}

		return $meta_row;
	}

	/**
	 * Создаем чат через миграцию
	 *
	 * @throws ParseFatalException
	 */
	public static function addByMigration(int $creator_user_id, string $group_name, string $description, int|null $conversation_type = null, array|null $extra = null, array|null $dynamic = null, string $avatar_file_map = "", bool $is_need_add_creator = false):array {

		// формируем users добавляя туда создателя
		$users = [];

		if ($is_need_add_creator) {
			$users = Type_Conversation_Meta_Users::addMember($users, $creator_user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		}

		$group_name  = Type_Api_Filter::sanitizeGroupName($group_name);
		$description = Type_Api_Filter::sanitizeGroupDescription($description);

		// инициируем extra для диалога и устанавливаем подтип диалога
		if (is_null($extra)) {

			$extra = Type_Conversation_Meta_Extra::initExtra();
			$extra = Type_Conversation_Meta_Extra::setDescription($extra, $description);
		}

		$meta_type = is_null($conversation_type) ? CONVERSATION_TYPE_GROUP_DEFAULT : $conversation_type;

		$meta_row = self::_createNewConversation(
			$meta_type, ALLOW_STATUS_GREEN_LIGHT, $creator_user_id, $users, $extra, $group_name, $avatar_file_map, dynamic: $dynamic
		);

		if ($is_need_add_creator) {

			$is_migration_muted = false;
			if (!is_null($dynamic)) {

				$user_mute_info     = $dynamic["user_mute_info"] ?? [];
				$is_migration_muted = Domain_Conversation_Entity_Dynamic::isMuted($user_mute_info, $creator_user_id, time());
			}

			self::_createUserCloudData(
				$creator_user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER,
				$meta_type, 0, count($users), $group_name, $avatar_file_map, is_migration_muted: $is_migration_muted
			);
		}

		return $meta_row;
	}

	/**
	 * отправляем системное сообщение о создании группы
	 */
	protected static function _sendSystemMessageUserAddGroup(string $group_conversation_map, array $group_meta_row, int $user_id, string $group_name):void {

		// формируем системное сообщение о создании группы
		$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserAddGroup($user_id, $group_name);

		// отправляем системное сообщение
		try {

			Helper_Conversations::addMessage(
				$group_conversation_map,
				$system_message,
				$group_meta_row["users"],
				$group_meta_row["type"],
				$group_meta_row["conversation_name"],
				$group_meta_row["extra"],
				false
			);
		} catch (cs_ConversationIsLocked) {
			// nothing
		}
	}

	// добавить пользователя в группу.
	public static function addUserToGroup(string $conversation_map, int $user_id, int $role, bool $is_favorite = false, bool $is_mentioned = false, string $userbot_id = "", bool $is_migration_muted = false):array {

		// открываем транзакцию на cluster_conversation и получаем запись на обновление, если пользователь уже состоит делаем rollback, иначе обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		if (Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();
			$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);
			return [Type_Conversation_Utils::makeConversationData($meta_row, $left_menu_row), true];
		}
		$meta_row = self::_updateConversationAtUserJoin($conversation_map, $user_id, $role, $meta_row, $userbot_id);

		// если запись в левом меню уже существует - обновляем уже имеющиеся записи, иначе делаем записи в таблицах базы cloud_user_conversation
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);
		if (isset($left_menu_row["user_id"])) {

			$conversation_data = self::_repeatAttachUser($user_id, $conversation_map, $role, $meta_row, $left_menu_row, $is_favorite);
			Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

			// пушим событие о добавлении пользователя в группу
			Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create($user_id, $conversation_map, $role, time(), true));

			return [$conversation_data, false];
		}
		$conversation_data = self::_firstAttachUser($user_id, $conversation_map, $role, $meta_row, $is_favorite, $is_mentioned, $is_migration_muted);
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// пушим событие о добавлении пользователя в группу
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create($user_id, $conversation_map, $role, time(), true));

		return [$conversation_data, false];
	}

	/**
	 * Добавляем пользователей в группу при миграции
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function addUserToGroupByMigration(string $conversation_map, int $user_id, int $role = Type_Conversation_Meta_Users::ROLE_DEFAULT):array {

		// открываем транзакцию на cluster_conversation и получаем запись на обновление, если пользователь уже состоит делаем rollback, иначе обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		if (Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();
			$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);
			return [Type_Conversation_Utils::makeConversationData($meta_row, $left_menu_row), true];
		}
		$meta_row = self::_updateConversationAtUserJoin($conversation_map, $user_id, $role, $meta_row);

		// если запись в левом меню уже существует - обновляем уже имеющиеся записи, иначе делаем записи в таблицах базы cloud_user_conversation
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);
		if (isset($left_menu_row["user_id"])) {

			$conversation_data = self::_repeatAttachUser($user_id, $conversation_map, $role, $meta_row, $left_menu_row, false);
			Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

			// пушим событие о добавлении пользователя в группу
			Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create($user_id, $conversation_map, $role, time(), true));

			return [$conversation_data, false];
		}
		$conversation_data = self::_firstAttachUser($user_id, $conversation_map, $role, $meta_row, false, false);
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// пушим событие о добавлении пользователя в группу
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create($user_id, $conversation_map, $role, time(), true));

		return [$conversation_data, false];
	}

	// обвновляем информацию о дилоге при вступление в него юзера
	protected static function _updateConversationAtUserJoin(string $conversation_map, int $user_id, int $role, array $meta_row, string $userbot_id = ""):array {

		$meta_row["users"][$user_id] = Type_Conversation_Meta_Users::initUserSchema($role);

		$set = [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		];

		if (!isEmptyString($userbot_id)) {

			$meta_row["extra"] = Type_Conversation_Meta_Extra::addUserbot($meta_row["extra"], $userbot_id);
			$set["extra"]      = $meta_row["extra"];
		}

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, $set);

		return $meta_row;
	}

	// обновляем записи в таблицах пользователя при очередном вступлении в диалог (cloud_user_conversation)
	protected static function _repeatAttachUser(int $user_id, string $conversation_map, int $role, array $meta_row, array $left_menu_row, bool $is_favorite):array {

		// обновляем запись в таблице left_menu
		$set_left_menu = self::_updateLeftMenuOnUserJoinToGroup($conversation_map, $user_id, $role, $meta_row, $is_favorite);

		// мержим изменения set_left_menu и значения полученного left_menu
		$left_menu_row = array_merge($left_menu_row, $set_left_menu);

		// формируем conversation_data
		return Type_Conversation_Utils::makeConversationData($meta_row, $left_menu_row);
	}

	/**
	 * обновляем запись в таблице left_menu при вступлении пользователь в группу
	 *
	 */
	protected static function _updateLeftMenuOnUserJoinToGroup(string $conversation_map, int $user_id, int $role, array $meta_row, bool $is_favorite):array {

		// считаем member_count
		$member_count = count($meta_row["users"]);

		$set_left_menu            = [
			"is_favorite"       => $is_favorite ? 1 : 0,
			"is_mentioned"      => 0,
			"role"              => $role,
			"is_hidden"         => 0,
			"is_leaved"         => 0,
			"is_have_notice"    => 0,
			"is_channel_alias"  => Type_Conversation_Meta_Extra::isChannel($meta_row["extra"]) ? 1 : 0,
			"type"              => $meta_row["type"],
			"updated_at"        => time(),
			"member_count"      => $member_count,
			"conversation_name" => $meta_row["conversation_name"],
			"avatar_file_map"   => $meta_row["avatar_file_map"],
		];
		$set_left_menu["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set_left_menu);

		return $set_left_menu;
	}

	// убрать пользователя из группы (+ обновить все пользовательские данные)
	public static function removeUserFromGroup(string $conversation_map, int $user_id, int $leave_reason, string $userbot_id = ""):array {

		// получаем запись на обновление
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();
		$before_meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		// выбрасываем ошибку если пользователь не является участником диалога или если не группа
		self::_throwWithRollbackIfUserIsNotMember($user_id, $before_meta_row);
		self::_throwWithRollbackIfConversationIsNotGroup($before_meta_row);

		// убираем пользователя из списка участников
		$after_meta_row          = $before_meta_row;
		$after_meta_row["users"] = Type_Conversation_Meta_Users::removeMember($before_meta_row["users"], $user_id);

		$set = [
			"users"      => $after_meta_row["users"],
			"updated_at" => time(),
		];

		if (!isEmptyString($userbot_id)) {

			$after_meta_row["extra"] = Type_Conversation_Meta_Extra::removeUserbot($after_meta_row["extra"], $userbot_id);
			$set["extra"]            = $after_meta_row["extra"];
		}

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, $set);
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// обновляем пользовательские таблицы
		self::_updateUserDataOnUserLeftGroup($user_id, $conversation_map, $leave_reason);

		return [$after_meta_row, $before_meta_row];
	}

	// выбрасываем ошибку если пользователь не является участником диалога
	protected static function _throwWithRollbackIfUserIsNotMember(int $user_id, array $meta_row):void {

		// пользователь является участником диалога?
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();
			throw new cs_UserIsNotMember();
		}
	}

	// изменить название группового диалога
	public static function setName(string $conversation_map, string $conversation_name):void {

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"conversation_name" => $conversation_name,
			"updated_at"        => time(),
		]);

		// пушим событие, что пользователь присоединился к группе
		Gateway_Event_Dispatcher::dispatch(Type_Event_Conversation_ConversationNameChanged::create(
			$conversation_map, $conversation_name
		));
	}

	// обновляем опции группового диалога
	public static function setOptions(string $conversation_map, array $extra):void {

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"updated_at" => time(),
			"extra"      => $extra,
		]);
	}

	// установить аватар для группового диалога
	public static function setAvatar(string $conversation_map, string $file_map):void {

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"avatar_file_map" => $file_map,
			"updated_at"      => time(),
		]);
	}

	/**
	 * установить основную информацию о группе
	 *
	 * @param string       $conversation_map
	 * @param array        $meta_row
	 * @param string|false $group_name
	 * @param string|false $file_map
	 * @param string|false $description
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function setBaseInfo(string $conversation_map, array $meta_row, string|false $group_name, string|false $file_map, string|false $description):array {

		$set = [
			"updated_at" => time(),
		];

		// собираем параметры для обновления
		if ($group_name !== false) {
			$set["conversation_name"] = $group_name;
		}
		if ($file_map !== false) {
			$set["avatar_file_map"] = $file_map;
		}

		if ($description !== false) {
			$set["extra"] = Type_Conversation_Meta_Extra::setDescription($meta_row["extra"], $description);
		}

		// если по итогу только поле "updated_at" имеется для обновления
		if (count($set) === 1) {
			throw new ParseFatalException("incorrect set params");
		}

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, $set);

		if (isset($set["conversation_name"])) {

			// пушим событие, что пользователь группа переименована
			Gateway_Event_Dispatcher::dispatch(Type_Event_Conversation_ConversationNameChanged::create(
				$conversation_map, $set["conversation_name"]
			));
		}

		return array_merge($meta_row, $set);
	}

	// установить роль участнику диалога
	// возвращает обновленный users
	public static function setRole(string $conversation_map, int $user_id, int $role):array {

		// получаем запись на обновление после открытия транзацкии
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		if (!isset($meta_row["users"][$user_id])) {

			Type_System_Admin::log("change_role_fail", "Не удалось установить роль пользователю {$user_id} в чате {$meta_row["conversation_name"]} {$meta_row["conversation_map"]}");

			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();
			return $meta_row["users"];
		}
		// устанавливаем новую роль участнику
		$meta_row["users"][$user_id] = Type_Conversation_Meta_Users::setUserRole($meta_row["users"][$user_id], $role);

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		]);

		// закрываем транзакцию на cluster_conversation
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// обновляем записи диалога в левом меню пользователя
		self::_setRoleToLeftMenu($conversation_map, $user_id, $role);

		return $meta_row["users"];
	}

	// обновляем роль в левом меню пользователя
	protected static function _setRoleToLeftMenu(string $conversation_map, int $user_id, int $role):void {

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, [
			"role" => $role,
		]);
	}

	// прикрепляем бота к групповому диалогу
	// возвращает обновленный users
	public static function addBotToGroup(string $conversation_map, int $bot_user_id):array {

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();

		// получаем запись на обновление
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		// добавляем бота в список участников и обновляем запись
		$meta_row["users"][$bot_user_id] = Type_Conversation_Meta_Users::initUserSchema(Type_Conversation_Meta_Users::ROLE_DEFAULT);
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
			"extra"      => Type_Conversation_Meta_Extra::addBot($meta_row["extra"], $bot_user_id),
		]);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// делаем записи в таблицах базы cloud_user_conversation
		self::_firstAttachUser($bot_user_id, $conversation_map, Type_Conversation_Meta_Users::ROLE_DEFAULT, $meta_row, false, false);

		// пересчитываем количество пользователей в диалоге
		Type_Phphooker_Main::updateMembersCount($conversation_map, $meta_row["users"]);

		return $meta_row["users"];
	}

	// открепляем бота от группового диалога
	public static function removeBotFromGroup(string $conversation_map, int $bot_user_id, string $leave_reason):array {

		// получаем запись на обновление
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		self::_throwWithRollbackIfConversationIsNotGroup($meta_row);

		// убираем пользователя из списка участников и обновляем запись
		unset($meta_row["users"][$bot_user_id]);
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
			"extra"      => Type_Conversation_Meta_Extra::removeBot($meta_row["extra"], $bot_user_id),
		]);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		// обновляем пользовательские таблицы и пересчитываем количество пользователей в диалоге
		self::_updateUserDataOnUserLeftGroup($bot_user_id, $conversation_map, $leave_reason);
		Type_Phphooker_Main::updateMembersCount($conversation_map, $meta_row["users"]);

		return $meta_row;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавляем записи в таблицах пользователя при первом вступлении в диалог (cloud_user_conversation)
	protected static function _firstAttachUser(int $user_id, string $conversation_map, int $role, array $meta_row, bool $is_favorite, bool $is_mentioned, bool $is_migration_muted = false):array {

		// создаем записи в таблице dynamic
		self::_insertUserDynamicOnUserJoinToGroup($user_id);

		// создаем запись в таблице left_menu
		$insert_left_menu = self::_insertLeftMenuOnUserJoinToGroup($conversation_map, $meta_row, $user_id, $role, $is_favorite, $is_mentioned, $is_migration_muted);

		// формируем conversation_data
		return Type_Conversation_Utils::makeConversationData($meta_row, $insert_left_menu);
	}

	// создаем запись в таблице dynamic
	protected static function _insertUserDynamicOnUserJoinToGroup(int $user_id):void {

		$insert = [
			"user_id"                   => $user_id,
			"message_unread_count"      => 0,
			"conversation_unread_count" => 0,
			"created_at"                => time(),
			"updated_at"                => 0,
		];
		Gateway_Db_CompanyConversation_UserInbox::insert($insert);
	}

	// создаем запись в таблице left_menu
	// @long т.к. большая структура
	protected static function _insertLeftMenuOnUserJoinToGroup(string $conversation_map, array $meta_row, int $user_id, int $role,
										     bool   $is_favorite, bool $is_mentioned, bool $is_migration_muted = false):array {

		$muted_until = $is_migration_muted ? time() : 0;

		$insert_left_menu = [
			"user_id"               => $user_id,
			"conversation_map"      => $conversation_map,
			"is_favorite"           => $is_favorite ? 1 : 0,
			"is_mentioned"          => $is_mentioned ? 1 : 0,
			"is_muted"              => $is_migration_muted ? 1 : 0,
			"is_have_notice"        => 0,
			"muted_until"           => $muted_until,
			"role"                  => $role,
			"is_hidden"             => 0,
			"is_leaved"             => 0,
			"is_channel_alias"      => Type_Conversation_Meta_Extra::isChannel($meta_row["extra"]) ? 1 : 0,
			"type"                  => $meta_row["type"],
			"unread_count"          => 0,
			"member_count"          => count($meta_row["users"]),
			"version"               => Domain_User_Entity_Conversation_LeftMenu::generateVersion(0), // previous_version = 0, т.к новая запись
			"clear_until"           => 0,
			"created_at"            => time(),
			"updated_at"            => time(),
			"opponent_user_id"      => 0,
			"conversation_name"     => $meta_row["conversation_name"],
			"avatar_file_map"       => $meta_row["avatar_file_map"],
			"last_read_message_map" => "",
			"last_message"          => [],
		];
		Gateway_Db_CompanyConversation_UserLeftMenu::insert($insert_left_menu);

		return $insert_left_menu;
	}

	// откатываемся и выкидываем исключение если диалог - не группа
	protected static function _throwWithRollbackIfConversationIsNotGroup(array $meta_row):void {

		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {

			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();
			throw new ParseFatalException("Trying to use method " . __METHOD__ . " on conversation which type != group");
		}
	}

	// обновляем таблицы юзера когда он покидает группу по какой-то причине
	protected static function _updateUserDataOnUserLeftGroup(int $user_id, string $conversation_map, int $leave_reason):void {

		// открываем транзакцию на cloud_user_conversation
		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// обновляем запись в left_menu
		self::_updateLeftMenuOnUserLeftGroup($user_id, $conversation_map, $leave_reason);

		// обновляем запись в dynamic
		self::_updateUserDynamicOnUserLeftGroup($user_id);

		// закрываем транзакцию на cloud_user_conversation
		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// обновляем запись в left_menu
	protected static function _updateLeftMenuOnUserLeftGroup(int $user_id, string $conversation_map, int $leave_reason):void {

		$set = [
			"is_hidden"             => 1,
			"is_leaved"             => 1,
			"leave_reason"          => $leave_reason,
			"role"                  => Type_Conversation_Meta_Users::ROLE_NOT_ATTACHED,
			"is_favorite"           => 0,
			"is_mentioned"          => 0,
			"mention_count"         => 0,
			"unread_count"          => 0,
			"muted_until"           => 0,
			"last_read_message_map" => "",
			"last_message"          => [],
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// обновляем запись в dynamic
	protected static function _updateUserDynamicOnUserLeftGroup(int $user_id):void {

		// получаем сумму непрочитанных сообщений
		$total_unread_count_row = Gateway_Db_CompanyConversation_UserLeftMenu::getTotalUnreadCounters($user_id);

		// обновляем значение total_unread_count
		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"message_unread_count"      => $total_unread_count_row["message_unread_count"] ?? 0,
			"conversation_unread_count" => $total_unread_count_row["conversation_unread_count"] ?? 0,
			"updated_at"                => time(),
		]);
	}
}