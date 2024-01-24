<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс содержит основной функционал для работы с различными типами тредов
 */
class Type_Thread_Main {

	// получаем тип треда на основе мета
	public static function getType(array $meta_row):int {

		if ($meta_row["is_private"] == 1 && $meta_row["is_mono"] == 0) {
			return THREAD_TYPE_PRIVATE;
		}

		if ($meta_row["is_private"] == 0 && $meta_row["is_mono"] == 1) {
			return THREAD_TYPE_PUBLIC;
		}

		if ($meta_row["is_private"] == 0 && $meta_row["is_mono"] == 0) {
			return THREAD_TYPE_OPEN;
		}

		throw new ParseFatalException("Cannot get thread type — unsupported flags combination. is_private={$meta_row["is_private"]}, is_mono={$meta_row["is_mono"]}");
	}

	/*
	 * получить список пользователей, которым необходимо отправить сообщение о новом сообщении в треде
	 *
	 * возвращает массив следующей структуры:
	 * array
	 * (
	 *    [user_id] => array
	 *     (
	 *           "need_push" => 0
	 *     )
	 * )
	 */
	public static function getReceiverUserList(array $meta_row, array $follower_row, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, array $user_mute_info, array $not_send_ws_event_user_list = []):array {

		// получаем тип треда
		$thread_type = self::getType($meta_row);

		switch ($thread_type) {

			case THREAD_TYPE_PRIVATE:

				// проверяем что родительская сущность - сообщение из диалога
				$parent_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
				if (!in_array($parent_type, [PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE, PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST])) {

					throw new ParseFatalException("Unsupported parent_type type {$parent_type}");
				}
				return self::_makePrivateThreadReceiverUserList($meta_row, $follower_row, $source_parent_rel_dynamic, $user_mute_info, $not_send_ws_event_user_list);

			case THREAD_TYPE_OPEN:
			case THREAD_TYPE_PUBLIC:
				return [];

			default:
				throw new ParseFatalException("unhandled thread type in method: " . __METHOD__);
		}
	}

	// получаем receiver_user_list для приватного треда
	protected static function _makePrivateThreadReceiverUserList(array $meta_row, array $follower_row, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, array $user_mute_info, array $not_send_ws_event_user_list):array {

		// пробегаемся по участникам треда и формируем список получателей
		$output = [];
		foreach ($meta_row["users"] as $k => $v) {

			// если пользователю не нужно отправлять ws-событие
			if (in_array($k, $not_send_ws_event_user_list)) {
				continue;
			}

			// если диалог был очищен позже, чем написано сообщение к которому прикреплен тред
			$message_created_at = Type_Thread_ParentRel::getCreatedAt($meta_row["parent_rel"]);
			if (Type_Thread_SourceParentDynamic::getClearInfoUntil($source_parent_rel_dynamic, $k) > $message_created_at) {
				continue;
			}

			$output[$k] = [
				"need_push" => (int) self::_isUserNeedPush($k, $follower_row, $source_parent_rel_dynamic, $user_mute_info) ? 1 : 0,
			];
		}

		return $output;
	}

	// нужно ли отправлять юзеру пуш
	protected static function _isUserNeedPush(int $user_id, array $follower_row, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, array $user_mute_info):bool {

		// если юзер замутил родительскую сущность
		if (Type_Thread_SourceParentDynamic::isUserMuteMeta($source_parent_rel_dynamic, $user_id)) {
			return false;
		}

		// если юзер замутил тред - пуш не нужен
		if (Type_Thread_Dynamic::isMuted($user_id, $user_mute_info)) {
			return false;
		}

		// если юзер не подписан на тред - пуш не нужен
		if (!Type_Thread_Followers::isFollowUser($user_id, $follower_row)) {
			return false;
		}

		return true;
	}

	// нужно ли обновлять thread_menu участников для переданного треда
	public static function isNeedUpdateThreadMenu(array $thread_meta_row):bool {

		// получаем тип треда
		$thread_type = self::getType($thread_meta_row);

		if ($thread_type == THREAD_TYPE_PRIVATE) {
			return true;
		}

		return false;
	}
}
