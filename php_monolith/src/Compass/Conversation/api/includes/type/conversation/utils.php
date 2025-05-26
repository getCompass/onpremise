<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * вспомогательные функции для диалогов
 */
class Type_Conversation_Utils {

	public const ALLOW_STATUS_OK                  = 1;  // в диалог можно писать, все ок
	public const ALLOW_STATUS_MEMBER_IS_DISABLED  = 14; // в диалог нельзя писать, один из участников заблокирован в системе
	public const ALLOW_STATUS_MEMBER_IS_DELETED   = 15; // в диалог нельзя писать, один из участников удалил аккаунт в системе
	public const ALLOW_STATUS_USERBOT_IS_DISABLED = 20; // в диалог нельзя писать, пользовательский бот выключен
	public const ALLOW_STATUS_USERBOT_IS_DELETED  = 21; // в диалог нельзя писать, пользовательский бот удалён

	// время, в течении которого валидна подпись
	protected const _SIGNATURE_EXPIRE = 60 * 2;

	// подготавливает строку из left_menu и meta к передаче в Apiv1_Format
	public static function prepareConversationForFormat(array $meta_row, array $left_menu_row = []):array {

		// собираем основную информацию для сущности
		$output = self::_getDefaultConversationFields($meta_row, $left_menu_row);

		// в зависимости от типа диалога добавляем необходимые поля
		switch ($meta_row["type"]) {

			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:
			case CONVERSATION_TYPE_SINGLE_DEFAULT:

				$output["data"] = self::_getSingleConversationFields($left_menu_row, $meta_row);
				break;
			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_GENERAL:
			case CONVERSATION_TYPE_GROUP_HIRING:
			case CONVERSATION_TYPE_SINGLE_NOTES:
			case CONVERSATION_TYPE_GROUP_SUPPORT:

				$output["data"] = self::_getGroupConversationFields($left_menu_row, $meta_row);
				break;
		}

		// добавляем последнее сообщение к ответу, если надо
		$output = self::_addLastMessageInOutputIfNeeded($meta_row["type"], $output, $left_menu_row);

		return $output;
	}

	// задаем поля ответа для левого меню
	protected static function _getDefaultConversationFields(array $meta_row, array $left_menu_row = []):array {

		// получаем юзеров отсортированных по дате вступления
		$user_id_list = Type_Conversation_Meta_Users::getUserIdListSortedByJoinTime($meta_row["users"]);

		return [
			"conversation_map"      => $meta_row["conversation_map"],
			"is_have_notice"        => $left_menu_row["is_have_notice"] ?? 0,
			"is_favorite"           => $left_menu_row["is_favorite"] ?? 0,
			"is_mentioned"          => $left_menu_row["is_mentioned"] ?? 0,
			"is_muted"              => $left_menu_row["is_muted"] ?? 0,
			"is_hidden"             => $left_menu_row["is_hidden"] ?? 0,
			"muted_until"           => $left_menu_row["muted_until"] ?? 0,
			"is_unread"             => (int) (($left_menu_row["unread_count"] ?? 0) > 0),
			"unread_count"          => $left_menu_row["unread_count"] ?? 0,
			"version"               => $left_menu_row["version"] ?? 0,
			"created_at"            => $left_menu_row["created_at"] ?? $meta_row["created_at"],
			"updated_at"            => $left_menu_row["updated_at"] ?? 0,
			"type"                  => $meta_row["type"],
			"last_read_message_map" => $left_menu_row["last_read_message_map"] ?? "",
			"data"                  => [],
			"user_id_list"          => $user_id_list,
			"talking_hash"          => self::getTalkingHash($user_id_list),
		];
	}

	// получает хэш для typing в talking
	public static function getTalkingHash(array $user_id_list):string {

		sort($user_id_list);

		$txt = implode(",", $user_id_list);
		$txt = sha1(\SALT_SENDER_HASH) . $txt;
		return sha1($txt);
	}

	// добавляем allow_status к ответу в зависимости от типа этого диалога
	protected static function _getSingleConversationFields(array $left_menu_row, array $meta_row):array {

		return [
			"allow_status"     => self::getAllowStatus($meta_row["allow_status"], $meta_row["extra"], $left_menu_row["opponent_user_id"]),
			"opponent_user_id" => $left_menu_row["opponent_user_id"],
		];
	}

	/**
	 * добавляем member_count, owner_user_id если это группа
	 *
	 */
	protected static function _getGroupConversationFields(array $left_menu_row, array $meta_row):array {

		$owner_user_list = [];
		$member_count    = 0;
		$owner_user_id   = self::_getOwnerUserId($meta_row["users"], $meta_row["creator_user_id"]);

		foreach ($meta_row["users"] as $k => $v) {

			if (Type_Conversation_Meta_Users::isMember($k, $meta_row["users"])) {
				$member_count++;
			}

			if (Type_Conversation_Meta_Users::isOwnerMember($k, $meta_row["users"])) {
				$owner_user_list[$k] = $v;
			}
		}

		// сортируем администраторов по времени обновления
		$owner_user_id_list = Type_Conversation_Meta_Users::getUserIdListSortedByUpdateTime($owner_user_list);

		// получаем опции группового диалога
		$group_options = self::_getGroupOptions($meta_row["extra"]);

		return self::_makeGroupFields($member_count, $owner_user_id, $left_menu_row, $owner_user_id_list, $meta_row, $group_options);
	}

	// получаем владельца группы
	protected static function _getOwnerUserId(array $users, int $creator_user_id):int {

		// если наш создатель есть в списке участников и он овнер, то возвращаем его
		if (array_key_exists($creator_user_id, $users) && Type_Conversation_Meta_Users::isOwnerMember($creator_user_id, $users)) {
			return $creator_user_id;
		}

		// либо создатель, либо никто
		return 0;
	}

	/**
	 * получаем опции группового диалога
	 *
	 * @param array $extra
	 *
	 * @return int[]
	 * при изменении обязательно добавь изменения в apiv2 (если это необходимо)
	 */
	protected static function _getGroupOptions(array $extra):array {

		return [
			"is_show_history_for_new_members"                 => (int) Type_Conversation_Meta_Extra::isShowHistoryForNewMembers($extra) ? 1 : 0,
			"is_can_commit_worked_hours"                      => (int) Type_Conversation_Meta_Extra::isCanCommitWorkedHours($extra) ? 1 : 0,
			"need_system_message_on_dismissal"                => (int) Type_Conversation_Meta_Extra::isNeedSystemMessageOnDismissal($extra) ? 1 : 0,
			"is_need_show_system_message_on_invite_and_join"  => (int) Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($extra) ? 1 : 0,
			"is_need_show_system_message_on_leave_and_kicked" => (int) Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($extra) ? 1 : 0,
			"is_need_show_system_deleted_message"             => (int) Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($extra) ? 1 : 0,
			"is_reactions_enabled"                            => (int) Type_Conversation_Meta_Extra::isReactionsEnabled($extra) ? 1 : 0,
			"is_comments_enabled"                             => (int) Type_Conversation_Meta_Extra::isCommentsEnabled($extra) ? 1 : 0,
			"is_channel"                                      => (int) Type_Conversation_Meta_Extra::isChannel($extra) ? 1 : 0,
		];
	}

	// задаем поля для группы
	protected static function _makeGroupFields(int   $member_count, int $owner_user_id, array $left_menu_row,
								 array $owner_user_list, array $meta_row, array $group_options):array {

		return [
			"name"            => $meta_row["conversation_name"],
			"description"     => Type_Conversation_Meta_Extra::getDescription($meta_row["extra"]),
			"member_count"    => $member_count,
			"avatar_file_map" => $meta_row["avatar_file_map"],
			"admin_user_list" => [], // поле оставлено для того, чтобы не поломать старых клиентов
			"owner_user_list" => $owner_user_list,
			"owner_user_id"   => $owner_user_id,
			"role"            => $left_menu_row["role"] ?? Type_Conversation_Meta_Users::ROLE_DEFAULT,
			"group_options"   => $group_options,
			"subtype"         => $meta_row["type"],
			"is_channel"      => Type_Conversation_Meta_Extra::isChannel($meta_row["extra"]) ? 1 : 0,
		];
	}

	// подготавливает строку из left_menu к передаче в Apiv1_Format
	public static function prepareLeftMenuForFormat(array $left_menu_row):array {

		$output = self::_getDefaultLeftMenuFields($left_menu_row);

		// в зависимости от типа диалога добавляем необходимые поля
		switch ($left_menu_row["type"]) {

			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:
			case CONVERSATION_TYPE_SINGLE_DEFAULT:
				$output["data"] = self::_getSingleLeftMenuFields($left_menu_row);
				break;

			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_GENERAL:
			case CONVERSATION_TYPE_GROUP_SUPPORT:
				$output["data"] = self::_getGroupLeftMenuFields($left_menu_row);
				break;

			case CONVERSATION_TYPE_GROUP_HIRING:
				$output["data"] = self::_getHiringConversationLeftMenuFields($left_menu_row);
				break;

			case CONVERSATION_TYPE_SINGLE_NOTES:
				$output["data"] = self::_getNotesConversationLeftMenuFields($left_menu_row);
				break;
		}

		// добавляем последнее сообщение к ответу
		return self::_addLastMessageInOutputIfNeeded($left_menu_row["type"], $output, $left_menu_row);
	}

	// получаем стандартные поля для leftMenu
	protected static function _getDefaultLeftMenuFields(array $left_menu_row):array {

		return [
			"conversation_map"      => $left_menu_row["conversation_map"],
			"is_have_notice"        => $left_menu_row["is_have_notice"],
			"is_mentioned"          => $left_menu_row["is_mentioned"],
			"is_hidden"             => $left_menu_row["is_hidden"],
			"is_leaved"             => $left_menu_row["is_leaved"],
			"is_favorite"           => $left_menu_row["is_favorite"],
			"is_muted"              => $left_menu_row["is_muted"],
			"muted_until"           => $left_menu_row["muted_until"],
			"is_unread"             => (int) ($left_menu_row["unread_count"] > 0),
			"unread_count"          => $left_menu_row["unread_count"],
			"version"               => $left_menu_row["version"],
			"created_at"            => $left_menu_row["created_at"],
			"updated_at"            => $left_menu_row["updated_at"],
			"type"                  => $left_menu_row["type"],
			"last_read_message_map" => $left_menu_row["last_read_message_map"],
			"data"                  => [],
		];
	}

	// если single диалог
	protected static function _getSingleLeftMenuFields(array $left_menu_row):array {

		return [
			"opponent_user_id"   => $left_menu_row["opponent_user_id"],
			"allow_status_alias" => $left_menu_row["allow_status_alias"],
		];
	}

	// если group диалог
	protected static function _getGroupLeftMenuFields(array $left_menu_row):array {

		return [
			"avatar_file_map" => $left_menu_row["avatar_file_map"],
			"member_count"    => $left_menu_row["member_count"],
			"name"            => $left_menu_row["conversation_name"],
			"role"            => $left_menu_row["role"],
			"is_channel"      => $left_menu_row["is_channel_alias"],
		];
	}

	/**
	 * hiring диалог
	 *
	 */
	protected static function _getHiringConversationLeftMenuFields(array $left_menu_row):array {

		return [
			"name"            => $left_menu_row["conversation_name"],
			"member_count"    => $left_menu_row["member_count"],
			"avatar_file_map" => $left_menu_row["avatar_file_map"],
		];
	}

	/**
	 * notes диалог
	 *
	 */
	protected static function _getNotesConversationLeftMenuFields(array $left_menu_row):array {

		return [
			"name"            => $left_menu_row["conversation_name"],
			"avatar_file_map" => $left_menu_row["avatar_file_map"],
		];
	}

	// получает инфо пользователя и allow_status
	public static function getAllowStatus(int $allow_status, array $extra, int $opponent_user_id):int {

		if (Type_Conversation_Meta_Extra::isBot($extra, $opponent_user_id)) {
			return self::ALLOW_STATUS_OK;
		}

		$user_info_list = Gateway_Bus_CompanyCache::getMemberList([$opponent_user_id]);
		if (!isset($user_info_list[$opponent_user_id])) {
			return self::ALLOW_STATUS_MEMBER_IS_DISABLED;
		}
		return self::getAllowStatusByUserInfo($allow_status, $extra, $user_info_list[$opponent_user_id]);
	}

	// получает allow_status
	public static function getAllowStatusByUserInfo(int $allow_status, array $extra, \CompassApp\Domain\Member\Struct\Main $opponent_user_info):int {

		return match ($allow_status) {

			ALLOW_STATUS_GREEN_LIGHT      => self::ALLOW_STATUS_OK,
			ALLOW_STATUS_NEED_CHECK       => self::_doAllowStatusCheck($opponent_user_info),
			ALLOW_STATUS_MEMBER_DISABLED  => self::ALLOW_STATUS_MEMBER_IS_DISABLED,
			ALLOW_STATUS_MEMBER_DELETED   => self::ALLOW_STATUS_MEMBER_IS_DELETED,
			ALLOW_STATUS_USERBOT_DISABLED => self::ALLOW_STATUS_USERBOT_IS_DISABLED,
			ALLOW_STATUS_USERBOT_DELETED  => self::ALLOW_STATUS_USERBOT_IS_DELETED,
			default                       => throw new ParseFatalException("Undefined allow_status=$allow_status in " . toJson($extra)),
		};
	}

	// получаем статус блокировки
	protected static function _doAllowStatusCheck(\CompassApp\Domain\Member\Struct\Main $opponent_user_info):int {

		// если пользователь уволен
		if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($opponent_user_info->role)) {
			return Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED;
		}

		// если пользователь удалил аккаунт
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($opponent_user_info->extra)) {
			return Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED;
		}

		// если это бот, то получаем его статус
		if (Type_User_Main::isUserbot($opponent_user_info->npc_type)) {

			$status = Gateway_Socket_Company::getUserbotStatusByUserId($opponent_user_info->user_id);
			switch ($status) {

				case Domain_Userbot_Entity_Userbot::STATUS_DISABLE:
					return self::ALLOW_STATUS_USERBOT_IS_DISABLED;
				case Domain_Userbot_Entity_Userbot::STATUS_DELETE:
					return self::ALLOW_STATUS_USERBOT_IS_DELETED;
			}
		}

		return self::ALLOW_STATUS_OK;
	}

	// формируем conversation_data
	public static function makeConversationData(array $meta_row, array $left_menu_row):array {

		return [
			"meta_row"      => $meta_row,
			"left_menu_row" => $left_menu_row,
		];
	}

	// получить meta_row из conversation_data
	public static function getMetaRowFromConversationData(array $conversation_data):array {

		// если такое поле не задано
		if (!isset($conversation_data["meta_row"])) {
			throw new ParseFatalException(__METHOD__ . ": parameter not passed to conversation data");
		}

		return $conversation_data["meta_row"];
	}

	// получить left_menu_row из conversation_data
	public static function getLeftMenuRowFromConversationData(array $conversation_data):array {

		// если такое поле не задано
		if (!isset($conversation_data["left_menu_row"])) {
			throw new ParseFatalException(__METHOD__ . ": parameter not passed to conversation data");
		}

		return $conversation_data["left_menu_row"];
	}

	// получает подпись
	public static function getSignatureWithCustomSalt(array $user_list, int $time, string $salt):string {

		// делаем intval каждого элемента
		$temp = [];
		foreach ($user_list as $v) {
			$temp[] = intval($v);
		}
		$user_list = $temp;

		$user_list[] = $time;
		sort($user_list);

		$json = toJson($user_list);

		// зашифровываем данные
		$iv_length   = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv          = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$binary_data = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, $salt, 0, $iv);

		return md5($binary_data) . "_" . $time;
	}

	// проверить подпись
	public static function verifySignatureWithCustomSalt(array $user_list, string $signature, string $salt):bool {

		$temp = explode("_", $signature);

		// проверяем, корректная ли пришла подпись
		if (count($temp) != 2) {
			return false;
		}

		// проверяем время
		$time = intval($temp[1]);
		if (time() > $time + self::_SIGNATURE_EXPIRE) {
			return false;
		}

		// сверяем подпись
		if ($signature != self::getSignatureWithCustomSalt($user_list, $time, $salt)) {
			return false;
		}

		return true;
	}

	// получает дополнительный бит для подтипа события сообщения в диалоге
	// для групповых диалогов бит добавляет, а для сингловых нет
	public static function makeConversationMessagePushDataEventSubtype(int $conversation_type):int {

		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {
			return EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;
		}

		return 0;
	}

	/**
	 * имеется ли последнее сообщение в диалоге левого меню
	 *
	 */
	public static function isExistLastMessage(array $left_menu_row):bool {

		return isset($left_menu_row["last_message"]) && !Gateway_Db_CompanyConversation_UserLeftMenu::isHidden($left_menu_row["last_message"]) &&
			count($left_menu_row["last_message"]) > 0;
	}

	/**
	 * достаем users из last_message
	 *
	 */
	public static function getLastMessageUsers(array $last_message):array {

		$user_list = [];

		$sender_user_id   = Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageSenderUserId($last_message);
		$receiver_user_id = Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageAdditionalReceiverId($last_message);

		if ($sender_user_id > 0) {
			$user_list[] = $sender_user_id;
		}

		if ($receiver_user_id > 0) {
			$user_list[] = $receiver_user_id;
		}

		return $user_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавляем последнее сообщение к ответу
	protected static function _addLastMessageInOutputIfNeeded(int $type, array $output, array $left_menu_row):array {

		// если это тип диалога, для которого не нужно знать last_message
		if ($type == CONVERSATION_TYPE_PUBLIC_DEFAULT) {
			return $output;
		}

		// если имеется last_message
		if (self::isExistLastMessage($left_menu_row)) {

			// получаем message_map
			$message_map = Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageMap($left_menu_row["last_message"]);

			$output["last_message"] = [
				"message_map"              => $message_map,
				"file_map"                 => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageFileMap($left_menu_row["last_message"]),
				"file_name"                => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageFileName($left_menu_row["last_message"]),
				"call_map"                 => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageCallMap($left_menu_row["last_message"]),
				"invite_map"               => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageInviteMap($left_menu_row["last_message"]),
				"sender_id"                => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageSenderUserId($left_menu_row["last_message"]),
				"type"                     => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageType($left_menu_row["last_message"]),
				"text"                     => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageText($left_menu_row["last_message"]),
				"message_count"            => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageMessageCountIfRepostOrQuote($left_menu_row["last_message"]),
				"receiver_id"              => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageAdditionalReceiverId($left_menu_row["last_message"]),
				"additional_type"          => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageAdditionalType($left_menu_row["last_message"]),
				"conference_id"            => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageConferenceId($left_menu_row["last_message"]),
				"conference_accept_status" => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageConferenceStatus($left_menu_row["last_message"]),
				"message_index"            => \CompassApp\Pack\Message\Conversation::getBlockMessageIndex($message_map),
				"data"                     => [
					"conference_id"            => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageConferenceId($left_menu_row["last_message"]),
					"conference_accept_status" => Gateway_Db_CompanyConversation_UserLeftMenu::getLastMessageConferenceStatus($left_menu_row["last_message"]),
				],
			];
		}

		return $output;
	}
}