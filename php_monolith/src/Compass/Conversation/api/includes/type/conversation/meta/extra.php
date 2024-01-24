<?php

namespace Compass\Conversation;

/**
 * класс для работы со структурой поля extra
 */
class Type_Conversation_Meta_Extra {

	// версия упаковщика
	protected const _EXTRA_VERSION = 7;

	// схема extra
	protected const _EXTRA_SCHEMA = [

		1 => [
			"bot_id_list"                     => [],
			"blocked_by"                      => [], // структура user_id = time()
			"is_show_history_for_new_members" => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"      => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
		],
		2 => [
			"bot_id_list"                      => [],
			"blocked_by"                       => [], // структура user_id = time()
			"is_show_history_for_new_members"  => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"       => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal" => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
		],
		3 => [
			"bot_id_list"                      => [],
			"blocked_by"                       => [], // структура user_id = time()
			"is_show_history_for_new_members"  => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"       => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal" => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
			"clear_until_for_all"              => 0,  // время очистки диалога у всех пользователей
		],
		4 => [
			"bot_id_list"                                     => [],
			"blocked_by"                                      => [], // структура user_id = time()
			"is_show_history_for_new_members"                 => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"                      => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal"                => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
			"clear_until_for_all"                             => 0,  // время очистки диалога у всех пользователей
			"is_need_show_system_message_on_invite_and_join"  => 1,  // флаг отключения системных сообщений о приглашении и вступлении в групповой диалог
			"is_need_show_system_message_on_leave_and_kicked" => 1,  // флаг отключения системных сообщений о покидании группового диалога
		],
		5 => [
			"bot_id_list"                                     => [],
			"blocked_by"                                      => [], // структура user_id = time()
			"is_show_history_for_new_members"                 => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"                      => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal"                => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
			"clear_until_for_all"                             => 0,  // время очистки диалога у всех пользователей
			"is_need_show_system_message_on_invite_and_join"  => 1,  // флаг отключения системных сообщений о приглашении и вступлении в групповой диалог
			"is_need_show_system_message_on_leave_and_kicked" => 1,  // флаг отключения системных сообщений о покидании группового диалога
			"userbot_id_list"                                 => [], // список с userbot_id пользовательских ботов
		],
		6 => [
			"bot_id_list"                                     => [],
			"blocked_by"                                      => [], // структура user_id = time()
			"is_show_history_for_new_members"                 => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"                      => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal"                => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
			"clear_until_for_all"                             => 0,  // время очистки диалога у всех пользователей
			"is_need_show_system_message_on_invite_and_join"  => 1,  // флаг отключения системных сообщений о приглашении и вступлении в групповой диалог
			"is_need_show_system_message_on_leave_and_kicked" => 1,  // флаг отключения системных сообщений о покидании группового диалога
			"userbot_id_list"                                 => [], // список с userbot_id пользовательских ботов
			"description"						  => "", // описание группы
		],
		7 => [
			"bot_id_list"                                     => [],
			"blocked_by"                                      => [], // структура user_id = time()
			"is_show_history_for_new_members"                 => 1,  // флаг 0/1, нужно ли показывать историю группового диалога при вступлении в него новых участников
			"is_can_commit_worked_hours"                      => 0,  // флаг 0/1, можно ли зафиксировать отработанные часы из этого диалога
			"need_system_message_on_dismissal"                => 1,  // флаг 0/1, нужно ли показывать системное сообщение, что пользователь покинул компанию
			"clear_until_for_all"                             => 0,  // время очистки диалога у всех пользователей
			"is_need_show_system_message_on_invite_and_join"  => 1,  // флаг отключения системных сообщений о приглашении и вступлении в групповой диалог
			"is_need_show_system_message_on_leave_and_kicked" => 1,  // флаг отключения системных сообщений о покидании группового диалога
			"is_need_show_system_deleted_message"             => 1,  // флаг отключения системных удалённых сообщений
			"userbot_id_list"                                 => [], // список с userbot_id пользовательских ботов
			"description"                                     => "", // описание группы
		],
	];

	// добавить пользователя в массив blocked_by
	public static function addToBlockedBy(array $extra, int $user_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["blocked_by"][$user_id] = time();

		return $extra;
	}

	// убрать пользователя из массива blocked_by
	public static function removeFromBlockedBy(array $extra, int $user_id):array {

		$extra = self::_getExtra($extra);

		unset($extra["extra"]["blocked_by"][$user_id]);

		return $extra;
	}

	// выясняет заблокирован ли диалог оппонентом текущего пользователя
	public static function isBlockedBy(array $extra, int $opponent_user_id):bool {

		$extra = self::_getExtra($extra);

		return isset($extra["extra"]["blocked_by"][$opponent_user_id]);
	}

	// выясняет заблокирован ли диалог обоими пользователями
	public static function isBlockedByEachOther(array $extra, int $user_id, int $opponent_user_id):bool {

		$extra = self::_getExtra($extra);

		if (!isset($extra["extra"]["blocked_by"][$opponent_user_id])) {
			return false;
		}

		if (!isset($extra["extra"]["blocked_by"][$user_id])) {
			return false;
		}

		return true;
	}

	// состоит ли бот в extra
	public static function isBot(array $extra, int $user_id):bool {

		$extra = self::_getExtra($extra);

		return in_array($user_id, $extra["extra"]["bot_id_list"]);
	}

	// состоит ли бот в extra
	public static function isUserbot(array $extra, string $userbot_id):bool {

		$extra = self::_getExtra($extra);

		return in_array($userbot_id, $extra["extra"]["userbot_id_list"]);
	}

	// содержит ли бота в extra
	public static function isContainUserbot(array $extra):bool {

		$extra = self::_getExtra($extra);

		return count($extra["extra"]["userbot_id_list"]) > 0;
	}

	// добавить бота в диалог
	public static function addBot(array $extra, int $bot_user_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["bot_id_list"][] = $bot_user_id;

		return $extra;
	}

	// добавить бота в диалог
	public static function addUserbot(array $extra, string $userbot_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["userbot_id_list"][] = $userbot_id;

		return $extra;
	}

	// убрать бота из диалога
	public static function removeBot(array $extra, int $bot_user_id):array {

		$extra = self::_getExtra($extra);

		array_splice($extra["extra"]["bot_id_list"], array_search($bot_user_id, $extra["extra"]["bot_id_list"]), 1);

		return $extra;
	}

	// убрать бота из диалога
	public static function removeUserbot(array $extra, string $userbot_id):array {

		$extra = self::_getExtra($extra);

		array_splice($extra["extra"]["userbot_id_list"], array_search($userbot_id, $extra["extra"]["userbot_id_list"]), 1);

		return $extra;
	}

	// получить список пользовательских ботов
	public static function getUserbotList(array $extra):array {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["userbot_id_list"];
	}

	// создать новую структуру для extra
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	// устанавливаем опцию — нужно ли чистить групповой диалог при вступлении в него нового участника
	public static function setFlagShowHistoryForNewMembers(array $extra, bool $value):array {

		$extra                                             = self::_getExtra($extra);
		$extra["extra"]["is_show_history_for_new_members"] = $value ? 1 : 0;

		return $extra;
	}

	// возвращаем опцию — нужно ли показывать историю группового диалога при вступлении в него новых участников
	public static function isShowHistoryForNewMembers(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_show_history_for_new_members"] == 1;
	}

	// устанавливаем опцию — можно ли зафиксировать отработанные часы из этого диалога
	public static function setFlagCanCommitWorkedHours(array $extra, bool $value):array {

		$extra                                        = self::_getExtra($extra);
		$extra["extra"]["is_can_commit_worked_hours"] = $value ? 1 : 0;

		return $extra;
	}

	// возвращаем опцию — можно ли зафиксировать отработанные часы из этого диалога
	public static function isCanCommitWorkedHours(array $extra):bool {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_can_commit_worked_hours"] == 1;
	}

	// устанавливаем опцию — нужно ли показывать системные сообщения о том, что пользователь покинул компанию
	public static function setFlagNeedSystemMessageOnDismissal(array $extra, bool $value):array {

		$extra                                              = self::_getExtra($extra);
		$extra["extra"]["need_system_message_on_dismissal"] = $value ? 1 : 0;

		return $extra;
	}

	// устанавливаем опцию — нужно ли отключить системные сообщения о том, что пользователь приглашен и вступил в группу
	public static function setFlagNeedShowSystemMessageOnInviteAndJoin(array $extra, bool $value):array {

		$extra                                                            = self::_getExtra($extra);
		$extra["extra"]["is_need_show_system_message_on_invite_and_join"] = $value ? 1 : 0;

		return $extra;
	}

	// устанавливаем опцию — нужно ли отключить системные сообщения о том, что пользователь покинул группу
	public static function setFlagNeedShowSystemMessageOnLeaveAndKicked(array $extra, bool $value):array {

		$extra                                                             = self::_getExtra($extra);
		$extra["extra"]["is_need_show_system_message_on_leave_and_kicked"] = $value ? 1 : 0;

		return $extra;
	}

	// устанавливаем опцию — нужно ли отключить системные сообщения о том, что сообщение удалено
	public static function setFlagNeedShowSystemDeletedMessage(array $extra, bool $value):array {

		$extra                                                 = self::_getExtra($extra);
		$extra["extra"]["is_need_show_system_deleted_message"] = $value ? 1 : 0;

		return $extra;
	}

	// возвращаем опцию — нужно ли показывать системные сообщения о том, что пользователь покинул компанию
	public static function isNeedSystemMessageOnDismissal(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["need_system_message_on_dismissal"] == 1;
	}

	// отключено ли системное сообщения о том, что пользователь приглашен и вступил в группу
	public static function isNeedShowSystemMessageOnInviteAndJoin(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_need_show_system_message_on_invite_and_join"] == 1;
	}

	// состояние флага системного сообщения о том, что пользователь вышел/кикнут из группы
	public static function isNeedShowSystemMessageOnLeaveAndKicked(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_need_show_system_message_on_leave_and_kicked"] == 1;
	}

	// отключено ли системное сообщения о том, что сообщение удалено
	public static function isNeedShowSystemDeletedMessage(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_need_show_system_deleted_message"] == 1;
	}

	// устанавливаем время очистки диалога у всех
	public static function setConversationClearUntilForAll(array $extra, int $conversation_clear_until_all):array {

		$extra                                 = self::_getExtra($extra);
		$extra["extra"]["clear_until_for_all"] = $conversation_clear_until_all;

		return $extra;
	}

	// получаем время очистки диалога у всех
	public static function getConversationClearUntilForAll(array $extra):int {

		$extra = self::_getExtra($extra);

		if (isset($extra["extra"]["clear_until_for_all"])) {
			return $extra["extra"]["clear_until_for_all"];
		}

		return 0;
	}

	/**
	 * Возвращаем описание чата
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getDescription(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["description"] ?? "";
	}

	/**
	 * Устанавливаем описание чата
	 *
	 * @param array  $extra
	 * @param string $description
	 *
	 * @return array
	 */
	public static function setDescription(array $extra, string $description):array {

		$extra = self::_getExtra($extra);
		$extra["extra"]["description"] = $description;

		return $extra;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить актуальную структуру для extra
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}