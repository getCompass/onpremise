<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для очистки данных компании
 */
class Domain_Conversation_Entity_ConversationMeta {

	/**
	 * подготавливаем сущность conversation_meta для фронтенда
	 *
	 * @param int                                               $user_id
	 * @param Struct_Db_CompanyConversation_ConversationMeta    $meta_row
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic
	 *
	 * @return array
	 */
	#[ArrayShape(["conversation_map" => "mixed", "created_at" => "mixed", "total_action_count" => "int", "messages_updated_at" => "int", "reactions_updated_at" => "int", "threads_updated_at" => "int", "messages_updated_version" => "int", "reactions_updated_version" => "int", "threads_updated_version" => "int", "type" => "mixed", "users" => "array", "talking_hash" => "string", "data" => "array"])]
	public static function prepareForFrontend(int $user_id, Struct_Db_CompanyConversation_ConversationMeta $meta_row, Struct_Db_CompanyConversation_ConversationDynamic $dynamic):array {

		// получаем юзеров отсортированных по дате вступления
		$user_id_list = Type_Conversation_Meta_Users::getUserIdListSortedByJoinTime($meta_row->users);

		$data = match ($meta_row->type) {
			CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT, CONVERSATION_TYPE_SINGLE_DEFAULT =>
			self::_getSingleConversationFields($user_id, $meta_row),
			CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_RESPECT, CONVERSATION_TYPE_GROUP_GENERAL, CONVERSATION_TYPE_GROUP_HIRING,
			CONVERSATION_TYPE_SINGLE_NOTES, CONVERSATION_TYPE_GROUP_SUPPORT =>
			self::_getGroupConversationFields($user_id, $meta_row),
		};

		$dynamic = Domain_Conversation_Action_FixDynamicUpdatedVersion::do($dynamic);

		// в зависимости от типа диалога добавляем необходимые поля
		return [
			"conversation_map"          => $meta_row->conversation_map,
			"created_at"                => $meta_row->created_at,
			"total_action_count"        => $dynamic->total_action_count,
			"messages_updated_at"       => $dynamic->messages_updated_at,
			"reactions_updated_at"      => $dynamic->reactions_updated_at,
			"threads_updated_at"        => $dynamic->threads_updated_at,
			"messages_updated_version"  => $dynamic->messages_updated_version,
			"reactions_updated_version" => $dynamic->reactions_updated_version,
			"threads_updated_version"   => $dynamic->threads_updated_version,
			"type"                      => $meta_row->type,
			"users"                     => $user_id_list,
			"talking_hash"              => Type_Conversation_Utils::getTalkingHash($user_id_list),
			"data"                      => $data,
		];
	}

	// добавляем allow_status к ответу в зависимости от типа этого диалога
	#[ArrayShape(["allow_status" => "int", "opponent_user_id" => "int|null"])]
	protected static function _getSingleConversationFields(int $user_id, Struct_Db_CompanyConversation_ConversationMeta $meta_row):array {

		$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($user_id, $meta_row->users);
		return [
			"allow_status"     => Type_Conversation_Utils::getAllowStatus($meta_row->allow_status, $meta_row->extra, $opponent_user_id),
			"opponent_user_id" => $opponent_user_id,
		];
	}

	/**
	 * добавляем доп поля
	 */
	#[ArrayShape(["name" => "mixed", "member_count" => "int", "avatar_file_map" => "mixed", "owner_user_list" => "array", "role" => "int", "group_options" => "array", "subtype" => "mixed"])]
	protected static function _getGroupConversationFields(int $user_id, Struct_Db_CompanyConversation_ConversationMeta $meta_row):array {

		$owner_user_list = [];
		$member_count    = 0;

		foreach ($meta_row->users as $k => $v) {

			if (Type_Conversation_Meta_Users::isMember($k, $meta_row->users)) {
				$member_count++;
			}

			if (Type_Conversation_Meta_Users::isOwnerMember($k, $meta_row->users)) {
				$owner_user_list[$k] = $v;
			}
		}

		// сортируем администраторов по времени обновления
		$owner_user_id_list = Type_Conversation_Meta_Users::getUserIdListSortedByUpdateTime($owner_user_list);

		// получаем опции группового диалога
		$group_options = self::_getGroupOptions($meta_row->extra);

		// если наш создатель есть в списке участников и он овнер, то возвращаем его
		$owner_user_id = array_key_exists($meta_row->creator_user_id, $meta_row->users)
		&& Type_Conversation_Meta_Users::isOwnerMember($meta_row->creator_user_id, $meta_row->users)

			? $meta_row->creator_user_id
			: 0;

		return self::_makeGroupFields($user_id, $owner_user_id, $member_count, $owner_user_id_list, $meta_row, $group_options);
	}

	/**
	 * получаем опции группового диалога
	 *
	 * @param array $extra
	 *
	 * @return int[]
	 */
	protected static function _getGroupOptions(array $extra):array {

		return [
			"is_show_history_for_new_members"                 => (int) Type_Conversation_Meta_Extra::isShowHistoryForNewMembers($extra) ? 1 : 0,
			"is_can_commit_worked_hours"                      => (int) Type_Conversation_Meta_Extra::isCanCommitWorkedHours($extra) ? 1 : 0,
			"need_system_message_on_dismissal"                => (int) Type_Conversation_Meta_Extra::isNeedSystemMessageOnDismissal($extra) ? 1 : 0,
			"is_need_show_system_message_on_invite_and_join"  => (int) Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($extra) ? 1 : 0,
			"is_need_show_system_message_on_leave_and_kicked" => (int) Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($extra) ? 1 : 0,
			"is_need_show_system_deleted_message"             => (int) Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($extra) ? 1 : 0,
		];
	}

	// задаем поля для группы
	protected static function _makeGroupFields(int $user_id, int $owner_user_id, int $member_count, array $owner_user_list, Struct_Db_CompanyConversation_ConversationMeta $meta_row, array $group_options):array {

		$role = Type_Conversation_Meta_Users::getRole($user_id, $meta_row->users);
		if (in_array($user_id, $owner_user_list)) {
			$role = Type_Conversation_Meta_Users::ROLE_ADMIN;
		}

		return [
			"name"            => $meta_row->conversation_name,
			"description"     => Type_Conversation_Meta_Extra::getDescription($meta_row->extra),
			"avatar_file_map" => $meta_row->avatar_file_map,
			"subtype"         => $meta_row->type,
			"member_count"    => $member_count,
			"owner_user_list" => $owner_user_list,
			"owner_user_id"   => $owner_user_id,
			"role"            => $role,
			"group_options"   => $group_options,
		];
	}
}