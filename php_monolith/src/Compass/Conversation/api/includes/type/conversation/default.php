<?php

namespace Compass\Conversation;

/**
 * основной класс для conversation который наследуют диалоги любых типов
 */
abstract class Type_Conversation_Default {

	// создает новый пустой диалог (company_conversation.meta + company_conversation.dynamic)
	protected static function _createNewConversation(int $type, int $allow_status, int $creator_user_id, array $users, array $extra, string $name = "", string $avatar_file_map = "", array|null $dynamic = null):array {

		// получаем shard_id
		$created_at = time();

		[$shard_id, $table_id] = \CompassApp\Pack\Conversation::getShardByTime($created_at);
		$meta_id = Type_Autoincrement_Main::getNextId(Type_Autoincrement_Main::CONVERSATION_META);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();

		// делаем insert в таблицу company_conversation.meta
		$meta_row = self::_insertClusterConversationMeta(
			$meta_id, $shard_id, $table_id, $created_at, $type, $allow_status, $creator_user_id, $users, $extra, $name, $avatar_file_map
		);

		// делаем insert в таблицу company_conversation.dynamic
		$dynamic = !is_null($dynamic) ? $dynamic : [];
		self::_insertCloudConversationDynamic($meta_row["conversation_map"], $created_at, $dynamic);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		return $meta_row;
	}

	// вставляем запись в company_conversation.meta
	protected static function _insertClusterConversationMeta(int $meta_id, int $shard_id, int $table_id, int $created_at, int $type, int $allow_status, int $creator_user_id, array $users, array $extra, string $name = "", string $avatar_file_map = ""):array {

		$insert = self::_makeClusterConversationMetaInsert(
			$meta_id, $shard_id, $created_at, $type, $allow_status, $creator_user_id, $users, $extra, $name, $avatar_file_map
		);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::insert($insert);

		// формируем conversation_map и подменяем meta_id на него
		$conversation_map           = \CompassApp\Pack\Conversation::doPack($shard_id, $table_id, $meta_id);
		$insert["conversation_map"] = $conversation_map;
		unset($insert["meta_id"]);
		unset($insert["year"]);

		Gateway_Db_SpaceSearch_EntitySearchIdRel::insert(Domain_Search_Const::TYPE_CONVERSATION, $conversation_map);

		return $insert;
	}

	// делаем insert для cluster_conversation.meta
	protected static function _makeClusterConversationMetaInsert(int $meta_id, int $year, int $created_at, int $type, int $allow_status, int $creator_user_id, array $users, array $extra, string $name = "", string $avatar_file_map = ""):array {

		return [
			"meta_id"           => $meta_id,
			"year"              => $year,
			"allow_status"      => $allow_status,
			"type"              => $type,
			"created_at"        => $created_at,
			"updated_at"        => 0,
			"creator_user_id"   => $creator_user_id,
			"avatar_file_map"   => $avatar_file_map,
			"conversation_name" => $name,
			"users"             => $users,
			"extra"             => $extra,
		];
	}

	// вставляем запись в company_conversation.dynamic
	protected static function _insertCloudConversationDynamic(string $conversation_map, int $created_at, array $dynamic):array {

		$insert = [
			"conversation_map"        => $conversation_map,
			"last_block_id"           => 0,
			"start_block_id"          => 0,
			"total_message_count"     => 0,
			"total_action_count"      => 0,
			"file_count"              => 0,
			"created_at"              => $created_at,
			"updated_at"              => 0,
			"user_mute_info"          => $dynamic["user_mute_info"] ?? [],
			"user_clear_info"         => $dynamic["user_clear_info"] ?? [],
			"conversation_clear_info" => $dynamic["conversation_clear_info"] ?? [],
		];
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::insert($insert);

		return $insert;
	}

	// -------------------------------------------------------
	// создание записей в таблицах юзера
	// -------------------------------------------------------

	// создаем записи в таблицах юзера
	protected static function _createUserCloudData(int $user_id, string $conversation_map, int $user_role, int $conversation_type, int $allow_status_alias, int $member_count, string $conversation_name = "", string $avatar_file_map = "", bool $is_favorite = false, bool $is_mentioned = false, int $opponent_user_id = 0, bool $is_hidden = false, bool $is_migration_muted = false):void {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		$created_at = time();
		self::_doUserLeftMenuInsert($user_id, $conversation_map, $user_role, $conversation_type, $allow_status_alias, $member_count, $created_at,
			$opponent_user_id, $conversation_name, $avatar_file_map, $is_favorite, $is_mentioned, $is_hidden, $is_migration_muted);

		// создаем запись в dynamic пользователя
		self::_doUserDynamicInsert($user_id, $created_at);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// создаем запись в left_menu
	private static function _doUserLeftMenuInsert(int $user_id, string $conversation_map, int $user_role, int $conversation_type, int $allow_status_alias, int $member_count, int $created_at, int $opponent_user_id, string $conversation_name, string $avatar_file_map, bool $is_favorite, bool $is_mentioned, bool $is_hidden, bool $is_migration_muted = fales):void {

		$left_menu_row = self::_makeInsertDataLeftMenuRow(
			$user_id, $conversation_map, $user_role,
			$conversation_type, $allow_status_alias,
			$member_count, $created_at, $opponent_user_id,
			$conversation_name,
			$avatar_file_map, $is_favorite, $is_mentioned, $is_hidden, $is_migration_muted
		);

		Gateway_Db_CompanyConversation_UserLeftMenu::insert($left_menu_row);
	}

	// формируем данные для вставки в левое меню
	// @long - большая структура
	protected static function _makeInsertDataLeftMenuRow(int $user_id, string $conversation_map, int $user_role, int $conversation_type, int $allow_status_alias, int $member_count, int $created_at, int $opponent_user_id, string $conversation_name, string $avatar_file_map, bool $is_favorite, bool $is_mentioned, bool $is_hidden, bool $is_migration_muted = false):array {

		$muted_until = $is_migration_muted ? time() : 0;

		return [
			"user_id"               => $user_id,
			"conversation_map"      => $conversation_map,
			"is_favorite"           => $is_favorite ? 1 : 0,
			"is_mentioned"          => $is_mentioned ? 1 : 0,
			"is_muted"              => $is_migration_muted ? 1 : 0,
			"muted_until"           => $muted_until,
			"is_hidden"             => $is_hidden ? 1 : 0,
			"allow_status_alias"    => $allow_status_alias,
			"is_leaved"             => 0,
			"role"                  => $user_role,
			"type"                  => $conversation_type,
			"unread_count"          => 0,
			"member_count"          => $member_count,
			"version"               => Domain_User_Entity_Conversation_LeftMenu::generateVersion(0), // previous_version = 0, т.к новая запись
			"clear_until"           => 0,
			"created_at"            => $created_at,
			"updated_at"            => $created_at,
			"opponent_user_id"      => $opponent_user_id,
			"conversation_name"     => $conversation_name,
			"avatar_file_map"       => $avatar_file_map,
			"last_read_message_map" => "",
			"last_message"          => [],
		];
	}

	// создаем запись в dynamic пользователя
	private static function _doUserDynamicInsert(int $user_id, int $created_at):void {

		$user_dynamic_row = [
			"user_id"                   => $user_id,
			"message_unread_count"      => 0,
			"conversation_unread_count" => 0,
			"created_at"                => $created_at,
			"updated_at"                => 0,
		];
		Gateway_Db_CompanyConversation_UserInbox::insert($user_dynamic_row);
	}
}