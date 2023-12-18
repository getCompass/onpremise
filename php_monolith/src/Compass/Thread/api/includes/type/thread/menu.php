<?php

namespace Compass\Thread;

/**
 * класс для работы с сущностью thread_menu
 */
class Type_Thread_Menu {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// получить все сущности thread_menu
	public static function getMenu(int $user_id, int $count, int $offset):array {

		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getMenu($user_id, $count, $offset);

		$thread_menu_list = self::_updateUnreadCountIfLessZero($user_id, $thread_menu_list);

		return $thread_menu_list;
	}

	// получаем конкретные записи из меню, игнорируя скрытые
	public static function getMenuItems(int $user_id, array $thread_map_list):array {

		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getMenuItems($user_id, $thread_map_list);

		$thread_menu_list = self::_updateUnreadCountIfLessZero($user_id, $thread_menu_list);

		return $thread_menu_list;
	}

	// получить все непрочитанные сущности thread_menu
	public static function getUnreadMenu(int $user_id, int $count, int $offset):array {

		return Gateway_Db_CompanyThread_UserThreadMenu::getUnreadMenu($user_id, $count, $offset);
	}

	/**
	 * Получаем все треды по флагу "избранный тред"
	 */
	public static function getFavoriteMenu(int $user_id, int $count, int $offset, int $favorite_filter):array {

		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getFavoriteMenu($user_id, $count, $offset, $favorite_filter);

		$thread_menu_list = self::_updateUnreadCountIfLessZero($user_id, $thread_menu_list);

		return $thread_menu_list;
	}

	// получить одну запись из thread_menu
	public static function get(int $user_id, string $thread_map):array {

		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getOne($user_id, $thread_map);

		$thread_menu_list = self::_updateUnreadCountIfLessZero($user_id, [$thread_menu_row]);

		return $thread_menu_list[0] ?? [];
	}

	// получить несколько записей из thread_menu
	public static function getList(int $user_id, array $thread_map_list, bool $is_assoc = false):array {

		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getList($user_id, $thread_map_list);

		$thread_menu_list = self::_updateUnreadCountIfLessZero($user_id, $thread_menu_list);

		if (!$is_assoc) {
			return $thread_menu_list;
		}

		$assoc_thread_menu_list = [];
		foreach ($thread_menu_list as $thread_menu_row) {

			$assoc_thread_menu_list[$thread_menu_row["thread_map"]] = $thread_menu_row;
		}

		return $assoc_thread_menu_list;
	}

	/**
	 * Метод отписывает пользователя от thread
	 */
	public static function setUnfollow(int $user_id, string $thread_map, bool $is_need_hide):void {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);

		// если запись не существует
		if (!isset($thread_menu_row["user_id"])) {

			// откатываем транзакцию и выходим
			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		self::_doUpdateTotalUnreadAndMentionCount($thread_menu_row["unread_count"], $user_id);

		// обновляем запись
		$set = [
			"is_hidden"             => $is_need_hide ? 1 : 0,
			"is_favorite"           => ($is_need_hide && $thread_menu_row["is_favorite"] == 1) ? 0 : $thread_menu_row["is_favorite"],
			"is_follow"             => 0,
			"is_mentioned"          => 0,
			"unread_count"          => 0,
			"mention_count"         => 0,
			"last_read_message_map" => "",
		];
		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// отправляем ws событие об удалении треда из избранных
		if ($is_need_hide === true && $thread_menu_row["is_favorite"] == 1) {
			Gateway_Bus_Sender::threadIsFavoriteChanged($user_id, $thread_map, false);
		}
	}

	// отписываем от тредов по map родителя
	public static function setUnfollowByMetaMap(int $user_id, string $source_parent_map):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем список тредов по map родителя и user_id
		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getListByMetaMap($user_id, $source_parent_map);

		// если записей не существует
		if (count($thread_menu_list) === 0) {

			// откатываем транзакцию и выходим
			Gateway_Db_CompanyThread_Main::rollback();
			return $thread_menu_list;
		}

		// обновляем записи
		Gateway_Db_CompanyThread_UserThreadMenu::setByMetaMapAndUserId($user_id, $source_parent_map, count($thread_menu_list), [
			"is_hidden"             => 0,
			"is_follow"             => 0,
			"unread_count"          => 0,
			"last_read_message_map" => "",
		]);

		// декрементим общее кол-во непрочитанных
		[$unread_count, $thread_mention_count] = self::_getUnreadAndMentionCount($thread_menu_list);
		self::_doUpdateTotalUnreadAndMentionCount($unread_count, $user_id);

		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $thread_menu_list;
	}

	// обнуляем unread_count для пользователя по map родителя
	public static function nullifyUnreadCountByMetaMap(int $user_id, string $source_parent_map):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем список тредов по map родителя и user_id
		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getListByMetaMap($user_id, $source_parent_map);

		// если записей не существует
		if (count($thread_menu_list) === 0) {

			// откатываем транзакцию и выходим
			Gateway_Db_CompanyThread_Main::rollback();
			return [];
		}

		// обновляем записи
		Gateway_Db_CompanyThread_UserThreadMenu::setByMetaMapAndUserId($user_id, $source_parent_map, count($thread_menu_list), [
			"is_mentioned"  => 0,
			"unread_count"  => 0,
			"mention_count" => 0,
		]);

		// декрементим общее кол-во непрочитанных и упоминаний
		[$unread_count, $thread_mention_count] = self::_getUnreadAndMentionCount($thread_menu_list);
		self::_doUpdateTotalUnreadAndMentionCount($unread_count, $user_id);

		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $thread_menu_list;
	}

	/**
	 * отписываем от тредов по map родителя
	 *
	 * @long
	 */
	public static function setUnfollowIfRoleChangeToEmployee(int $user_id, string $source_parent_map):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем список тредов по map родителя и user_id
		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getListByMetaMap($user_id, $source_parent_map);

		// если записей не существует
		if (count($thread_menu_list) === 0) {

			// откатываем транзакцию и выходим
			Gateway_Db_CompanyThread_Main::rollback();
			return [];
		}

		// определяем, для каких тредов пользователь является создателем
		$thread_map_list  = array_column($thread_menu_list, "thread_map");
		$thread_meta_list = Type_Thread_Meta::getAllWhereCreator($user_id, $thread_map_list);

		// собираем только те итемы меню тредов, где пользователь НЕ является создателем заявки
		$parent_rel_by_thread_map = array_column($thread_meta_list, "parent_rel", "thread_map");
		$thread_menu_list         = array_filter($thread_menu_list, function(array $thread_menu) use ($user_id, $parent_rel_by_thread_map) {

			$thread_map = $thread_menu["thread_map"];

			// в случае с заявками, нужны только те треды, где пользователь НЕ является их создателем
			$parent_type = Type_Thread_ParentRel::getType($parent_rel_by_thread_map[$thread_map]);
			if (in_array($parent_type, [PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST])) {
				return $user_id != $parent_rel_by_thread_map[$thread_map]["creator_user_id"];
			}
			return true;
		});

		// обновляем записи для собранного списка
		$thread_map_list = array_column($thread_menu_list, "thread_map");
		Gateway_Db_CompanyThread_UserThreadMenu::setByUserIdAndThreadMapList($user_id, $thread_map_list, [
			"is_hidden"             => 1,
			"is_follow"             => 0,
			"is_favorite"           => 0,
			"unread_count"          => 0,
			"last_read_message_map" => "",
		]);

		// декрементим общее кол-во непрочитанных и упоминаний
		[$unread_count, $thread_mention_count] = self::_getUnreadAndMentionCount($thread_menu_list);
		self::_doUpdateTotalUnreadAndMentionCount($unread_count, $user_id);

		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $thread_menu_list;
	}

	// получаем кол-во непрочитанных и упоминаний
	public static function _getUnreadAndMentionCount(array $thread_menu_list):array {

		$thread_unread_count  = 0;
		$thread_mention_count = 0;

		foreach ($thread_menu_list as $thread_menu_row) {

			$thread_unread_count  += $thread_menu_row["unread_count"];
			$thread_mention_count += $thread_menu_row["is_mentioned"] == 1 ? 1 : 0;
		}

		return [$thread_unread_count, $thread_mention_count];
	}

	// отправляем задачу на отписывание пользователя от тредов
	public static function sendTaskIfUnfollowThreadList(int $user_id, array $unfollow_thread_list):void {

		$chunked_thread_map_list = self::_getChunkedThreadMapList($unfollow_thread_list, 100);

		if (count($chunked_thread_map_list) > 1) {
			self::_doWriteThreadHookerWork(count($chunked_thread_map_list), count($unfollow_thread_list));
		}

		// отправляем задачи на отписку от тредов
		foreach ($chunked_thread_map_list as $thread_map_list) {
			Type_Phphooker_Main::doUnfollowThreadList($thread_map_list, $user_id);
		}
	}

	// формируем массив мапов из списка
	protected static function _getChunkedThreadMapList(array $thread_list, int $size):array {

		$thread_map_list = [];

		foreach ($thread_list as $thread_row) {
			$thread_map_list[] = $thread_row["thread_map"];
		}

		return array_chunk($thread_map_list, $size);
	}

	// записывает лог о больших задачах
	protected static function _doWriteThreadHookerWork(int $count, int $total_count):void {

		// записываем лог
		$log = [
			"message"       => "Описывание от большого кол-ва тредов",
			"tasks"         => $count,
			"total_threads" => $total_count,
		];
		Type_System_Admin::log("thread_unfollow", $log, true);
	}

	// метод подписывает пользователя на thread
	public static function setFollow(int $user_id, string $thread_map, array $parent_rel):void {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getOne($user_id, $thread_map);

		// если запись не существует
		if (!isset($thread_menu_row["user_id"])) {

			// привязываем к треду
			self::_doFirstAttachUser($user_id, $thread_map, $parent_rel);
			Gateway_Db_CompanyThread_Main::commitTransaction();
			return;
		}

		// обновляем запись о треде в thread_menu пользователя
		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, [
			"is_hidden"             => 0,
			"is_follow"             => 1,
			"unread_count"          => 0,
			"updated_at"            => time(),
			"last_read_message_map" => "",
		]);
		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	// первая привязка юзера к треду
	protected static function _doFirstAttachUser(int $user_id, string $thread_map, array $parent_rel):void {

		// создаем запись о треде в thread_menu пользователя
		$message_map        = Type_Thread_ParentRel::getMap($parent_rel);
		$source_parent_map  = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$source_parent_type = Type_Thread_ParentRel::getType($parent_rel);

		Gateway_Db_CompanyThread_UserThreadMenu::insert($user_id, $thread_map, $source_parent_map, $source_parent_type, $parent_rel);

		// создаем запись в dynamic
		Gateway_Db_CompanyThread_UserInbox::insert($user_id);
	}

	// метод подписывает пользователей на thread
	public static function setFollowUserList(array $user_id_list, string $thread_map, array $parent_rel):void {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_list = Gateway_Db_CompanyThread_UserThreadMenu::getAllByUserIdList($user_id_list, $thread_map);

		// получаем записи которые надо проапдейтить
		$need_update_user_id_list = [];
		foreach ($thread_menu_list as $thread_menu_row) {
			$need_update_user_id_list[] = $thread_menu_row["user_id"];
		}
		$need_insert_user_id_list = array_diff($user_id_list, $need_update_user_id_list);

		// обновляем записи о треде в thread_menu пользователя
		if (count($need_update_user_id_list) > 0) {

			Gateway_Db_CompanyThread_UserThreadMenu::setListWhereUserUnfollow($need_update_user_id_list, $thread_map, [
				"is_hidden"             => 0,
				"is_follow"             => 1,
				"is_mentioned"          => 0,
				"unread_count"          => 0,
				"mention_count"         => 0,
				"updated_at"            => time(),
				"last_read_message_map" => "",
			]);
		}

		// создаем записи о треде в thread_menu пользователя
		if (count($need_insert_user_id_list) > 0) {
			self::_doFirstAttachUserList($need_insert_user_id_list, $thread_map, $parent_rel);
		}

		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	// первая привязка юзера к треду
	protected static function _doFirstAttachUserList(array $user_id_list, string $thread_map, array $parent_rel):void {

		// создаем запись о треде в thread_menu пользователя
		$message_map = Type_Thread_ParentRel::getMap($parent_rel);

		$source_parent_map = "";

		$source_parent_type = Type_Thread_ParentRel::getType($parent_rel);
		if (Type_Thread_Utils::isConversationMessageParent($source_parent_type)) {
			$source_parent_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		}

		Gateway_Db_CompanyThread_UserThreadMenu::insertList($user_id_list, $thread_map, $source_parent_map, $source_parent_type, $parent_rel);

		// создаем запись в dynamic
		Gateway_Db_CompanyThread_UserInbox::insertList($user_id_list);
	}

	// устанавливаем новый статус is_muted для сущности thread_menu
	public static function setIsMuted(int $user_id, string $thread_map, bool $is_muted):void {

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, [
			"is_muted" => $is_muted ? 1 : 0,
		]);
	}

	/**
	 * получаем все треды, в которых имеются непрочитанные сообщения
	 *
	 */
	public static function getAllUnreadList(int $user_id):array {

		// устанавливаем лимит для получений записей
		$limit = 50;

		$thread_menu_list = [];

		// получаем все треды, которые отмечены непрочитанными
		$offset = 0;
		do {

			$chunk_thread_menu_list = self::getUnreadMenu($user_id, $limit, $offset);
			$offset                 += $limit;

			// мерджим полученную часть тред_меню с тем, что получили ранее
			$thread_menu_list = array_merge($thread_menu_list, $chunk_thread_menu_list);
		} while (count($chunk_thread_menu_list) > 0);

		return $thread_menu_list;
	}

	/**
	 * пометить треды прочитанными
	 *
	 */
	public static function setThreadsAsRead(int $user_id, array $thread_map_list):void {

		Gateway_Db_CompanyThread_UserThreadMenu::setByUserIdAndThreadMapList($user_id, $thread_map_list, [
			"is_mentioned"          => 0,
			"unread_count"          => 0,
			"mention_count"         => 0,
			"last_read_message_map" => "",
		]);
	}

	/**
	 * обнуляем количество непрочитанных сообщений
	 *
	 */
	public static function nullifyTotalUnread(int $user_id):void {

		Gateway_Db_CompanyThread_UserInbox::set($user_id, [
			"message_unread_count" => 0,
			"thread_unread_count"  => 0,
			"thread_mention_count" => 0,
			"updated_at"           => time(),
		]);
	}

	/**
	 * Получаем число тредов в избранном
	 */
	public static function getFavoriteCount(int $user_id):int {

		return Gateway_Db_CompanyThread_UserThreadMenu::getFavoriteCount($user_id);
	}

	/**
	 * Устанавливаем новый статус if_favorite для сущности thread_menu
	 */
	public static function setIsFavorite(int $user_id, string $thread_map, bool $is_favorite):void {

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, [
			"is_favorite" => $is_favorite ? 1 : 0,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// если есть не прочитанные сообщения
	protected static function _doUpdateTotalUnreadAndMentionCount(int $unread_count, int $user_id):void {

		// если нет непрочитанных
		if ($unread_count < 1) {
			return;
		}

		// получаем количество непрочитанных для пользователя
		$user_inbox_row = Domain_Thread_Entity_UserInbox::getInboxForUpdateByUserId($user_id);

		$set = [
			"message_unread_count" => $user_inbox_row["message_unread_count"] - $unread_count,
			"thread_unread_count"  => $user_inbox_row["thread_unread_count"] - 1,
			"updated_at"           => time(),
		];

		Gateway_Db_CompanyThread_UserInbox::set($user_id, $set);
	}

	/**
	 * обновляем треды, если имеются непрочитанные меньше нуля
	 */
	protected static function _updateUnreadCountIfLessZero(int $user_id, array $thread_menu_list):array {

		$need_nullify_thread_map_list = [];

		foreach ($thread_menu_list as $index => $thread_menu_row) {

			// если количество непрочитанных меньше нуля
			if (isset($thread_menu_row["unread_count"]) && $thread_menu_row["unread_count"] < 0) {

				$thread_menu_row["unread_count"] = 0;
				$thread_menu_list[$index]        = $thread_menu_row;

				// собираем треды для дальнейшего обнуления до нуля
				$need_nullify_thread_map_list[] = $thread_menu_row["thread_map"];
			}
		}

		if (count($need_nullify_thread_map_list) > 0) {

			Type_Phphooker_Main::updateUserThreadMenuForUnreadLessZero($user_id, $need_nullify_thread_map_list);
		}

		return $thread_menu_list;
	}
}