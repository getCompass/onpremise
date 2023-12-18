<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * крон для исполнения задач
 */
class Type_Phphooker_Worker {

	// выполняем задачу
	// @long
	public function doTask(int $task_type, array $params):bool {

		// развилка по типу задачи
		switch ($task_type) {

			// обновление пользовательских данных при добавлении сообщения в тред
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD:

				$result = true;

				foreach ($params["user_list"] as $v) {

					$temp   = $this->updateUserDataOnMessageAdd($v, $params["thread_map"], $params["message"]);
					$result = $result && $temp;
				}

				return $result;

			case Type_Phphooker_Main::TASK_TYPE_UNFOLLOW_THREAD_LIST:

				return self::doUnfollowThreadList($params["thread_map_list"], $params["user_id"]);

			case Type_Phphooker_Main::TASK_TYPE_CLEAR_FOLLOW_USER:

				return self::doClearFollowUser($params["user_id"], $params["thread_map_list"]);

			// обновление пользовательских данных при упоминании пользователя после редактирования сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_EDIT:

				$result = true;

				foreach ($params["new_mentioned_user_list"] as $v) {

					$temp   = $this->_updateUserDataForMentionedOnMessageEdit($v, $params["thread_map"]);
					$result = $result && $temp;
				}

				return $result;

			// обновление пользовательских данных упомянутых после удаления сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_DELETE:

				$result = true;

				foreach ($params["user_id_list"] as $v) {

					$temp   = $this->_updateUserDataForMentionedOnMessageDelete($v, $params["thread_map"]);
					$result = $result && $temp;
				}

				return $result;

			// обновление пользовательских данных упомянутых после скрытии сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_HIDE:

				$result = true;

				foreach ($params["user_id_list"] as $v) {

					$temp   = $this->_updateUserDataForMentionedOnMessageHide($v, $params["thread_map"]);
					$result = $result && $temp;
				}

				return $result;

			// обновляем пользовательские данные, если количество непрочитанных меньше нуля
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_INBOX_FOR_UNREAD_LESS_ZERO:
				return $this->_updateUserInboxForUnreadCountLessZero($params["user_id"]);

			// обновляем пользовательское меню тредов, если количество непрочитанных меньше нуля
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_THREAD_MENU_FOR_UNREAD_LESS_ZERO:
				return $this->_updateUserThreadMenuForUnreadCountLessZero($params["user_id"], $params["thread_map_list"]);

			default:
				throw new ParseFatalException("Unhandled task_type [$task_type] in " . __METHOD__);
		}
	}

	// -------------------------------------------------------
	// ЛОГИКА ВЫПОЛНЕНИЯ ЗАДАЧ
	// -------------------------------------------------------

	// обновить записи пользователя при добавлении сообщения в тред
	// @long
	public function updateUserDataOnMessageAdd(int $user_id, string $thread_map, array $message):bool {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);
		if (!isset($thread_menu_row["user_id"]) || $thread_menu_row["is_follow"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return false;
		}

		$class = Type_Thread_Message_Main::getHandler($message);

		$message_map = $class::getMessageMap($message);

		// получаем индекс сообщения
		$current_message_thread_message_index = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message_map);

		// получаем индекс последнего прочитанного сообщения
		$last_read_thread_message_index = $this->_getLastReadThreadMessageIndex($thread_menu_row["last_read_message_map"]);

		$set = $this->_makeSet();

		// проверяем, нужно ли обновить число непрочитанных
		if ($this->_isNeedUpdateUnreadCount($thread_menu_row, $current_message_thread_message_index, $last_read_thread_message_index, $message, $user_id)) {

			if ($class::isIncrementUnreadCount($message, $user_id)) {

				$set = $this->_incrementUserInboxData($set, $thread_menu_row["unread_count"], $user_id);
			} else {

				$set["last_read_message_map"] = $message_map;
				if ($thread_menu_row["unread_count"] > 0) {

					$set["unread_count"] = 0;
					$this->_decrementThreadUserInbox($thread_menu_row["unread_count"], $user_id);
				}
			}
		}

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$thread_map], false);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// подготавливаем запись для обновления
		// пушим событие о том, что в тред меню поменялся элемент
		$prepared_thread_menu = Type_Thread_Utils::prepareThreadMenuForFormat(array_merge($thread_menu_row, $set));
		Gateway_Bus_Sender::threadMenuItemUpdated($user_id, $prepared_thread_menu);

		return true;
	}

	// формируем массив для обновления
	#[ArrayShape(["updated_at" => "int", "is_hidden" => "int"])]
	protected function _makeSet():array {

		return [
			"updated_at" => time(),
			"is_hidden"  => 0,
		];
	}

	// проверяем, нужно ли обновить число непрочитанных
	protected function _isNeedUpdateUnreadCount(array $thread_menu_row,
								  int   $current_message_thread_message_index, int $last_read_thread_message_index,
								  array $message, int $user_id):bool {

		$class = Type_Thread_Message_Main::getHandler($message);

		// если это системное сообщение о получении сущности карточки, то полагаемся на отправителя
		// (отправителю сообщения в тред инкрементим, получателю НЕ инкрементим)
		if ($class::isSystemReceivedEmployeeCardEntityMessage($message)) {
			return $message["sender_user_id"] == $user_id;
		}

		// если системное сообщение (но не о получении сообщения-сущности карточки) от меня,
		// то не нужно увеличивать количество непрочитанных сообщений
		if ($class::isSystemMessage($message) && $message["sender_user_id"] == $user_id) {
			return false;
		}

		// если сообщение уже было прочитано (либо мы его отправитель) - не стоит инкрементитить unread_count
		// это происходит потому что ws-ивент отправляется быстрее, чем задача в phphooker и тред не в муте
		if ($last_read_thread_message_index < $current_message_thread_message_index && $thread_menu_row["is_muted"] == 0) {
			return true;
		}

		if ($class::isUserMention($message, $user_id)) {
			return true;
		}

		return false;
	}

	// получаем индекс последнего прочитанного сообщения
	protected function _getLastReadThreadMessageIndex(string $last_read_message_map):int {

		// получаем индекс последнего прочитанного сообщения
		if (mb_strlen($last_read_message_map) > 0) {
			$last_read_message_index = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($last_read_message_map);
		} else {
			$last_read_message_index = 0;
		}

		return $last_read_message_index;
	}

	// декрементим данные в таблице user_inbox
	protected function _decrementThreadUserInbox(int $unread_count, int $user_id):void {

		// если декрементить нечего
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

	// отписываем пользователя от тредов
	public static function doUnfollowThreadList(array $thread_map_list, int $user_id):bool {

		// достаем список меню тредов
		$thread_menu_list = Type_Thread_Menu::getList($user_id, $thread_map_list, true);

		// достаем записи подписчиков для тредов
		$follower_list = Type_Thread_Followers::getList($thread_map_list, true);

		$need_unfollow_thread_map_list = [];

		foreach ($thread_menu_list as $thread_menu_row) {

			$thread_map = $thread_menu_row["thread_map"];

			// проверяем, что пользователь уже отписан
			if (!Type_Thread_Followers::isFollowUser($user_id, $follower_list[$thread_map]) && $thread_menu_row["is_follow"] == 0) {
				continue;
			}

			$need_unfollow_thread_map_list[] = $thread_map;
		}

		// отписываем
		foreach ($need_unfollow_thread_map_list as $v) {
			Domain_Thread_Action_Follower_Unfollow::do($user_id, $v, true);
		}

		return true;
	}

	// актуализируем список follower для пользователя
	public static function doClearFollowUser(int $user_id, array $thread_map_list):bool {

		foreach ($thread_map_list as $v) {
			Type_Thread_Followers::doClearFollowUser($user_id, $v);
		}

		return true;
	}

	/**
	 * обновляем пользовательские данные при упоминании при изменении текста сообщения
	 *
	 * @throws \returnException
	 * @long
	 */
	protected function _updateUserDataForMentionedOnMessageEdit(int $user_id, string $thread_map, bool $is_need_increment = true):bool {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);
		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return false;
		}

		// если пользователь не подписчик для треда, то ничего не меняем
		if ($thread_menu_row["is_follow"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		$set = [];

		if ($is_need_increment) {

			// если у пользователя не было непрочитанных, то инкрементим количество непрочитанных
			if ($thread_menu_row["unread_count"] == 0) {
				$set = $this->_incrementUserInboxData($set, $thread_menu_row["unread_count"], $user_id);
			}
		} else {

			// декрементим, в случае если были непрочитанные для пользователя в этом треде
			if ($thread_menu_row["unread_count"] > 0) {

				$set["unread_count"] = "unread_count - 1";
				$this->_decrementThreadUserInbox($thread_menu_row["unread_count"], $user_id);
			}
		}

		// если не нужно обновлять user_thread_menu
		if (count($set) < 1) {

			// commit, а не rollback ибо могли обновить user_inbox выше
			Gateway_Db_CompanyThread_Main::commitTransaction();
			return true;
		}

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();
		return true;
	}

	/**
	 * обновляем пользовательские данные упомянутых при удалении сообщения
	 *
	 * @throws \returnException
	 * @long
	 */
	protected function _updateUserDataForMentionedOnMessageDelete(int $user_id, string $thread_map):bool {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);
		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return false;
		}

		// если пользователь не подписчик для треда, то ничего не меняем
		if ($thread_menu_row["is_follow"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		if ($thread_menu_row["is_mentioned"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		// декрементим количество упоминаний в треде
		$mention_count = $thread_menu_row["mention_count"] - 1;

		$set["mention_count"] = $mention_count;

		// если упоминаний больше нет, то обнуляем флаг упоминания для пользователя
		if ($mention_count == 0) {

			$set["is_mentioned"] = 0;

			Gateway_Db_CompanyThread_UserInbox::set($user_id, [
				"thread_mention_count" => "thread_mention_count - 1",
				"updated_at"           => time(),
			]);
		}

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();
		return true;
	}

	/**
	 * обновляем пользовательские данные упомянутых при скрытии сообщения
	 *
	 * @throws \returnException
	 * @long
	 */
	protected function _updateUserDataForMentionedOnMessageHide(int $user_id, string $thread_map):bool {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);
		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return false;
		}

		// если пользователь не подписчик для треда, то ничего не меняем
		if ($thread_menu_row["is_follow"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		if ($thread_menu_row["is_mentioned"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		// декрементим количество упоминаний в треде
		$mention_count = $thread_menu_row["mention_count"] - 1;

		$set["mention_count"] = $mention_count;

		// если упоминаний больше нет, то обнуляем флаг упоминания для пользователя
		if ($mention_count == 0) {

			$set["is_mentioned"] = 0;

			Gateway_Db_CompanyThread_UserInbox::set($user_id, [
				"thread_mention_count" => "thread_mention_count - 1",
				"updated_at"           => time(),
			]);
		}

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();
		return true;
	}

	/**
	 * обновляем пользовательские данные, если количество непрочитанных меньше нуля
	 */
	protected function _updateUserInboxForUnreadCountLessZero(int $user_id):bool {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$total_unread_counters = Gateway_Db_CompanyThread_UserThreadMenu::getTotalUnreadCounters($user_id);

		if (!isset($total_unread_counters["message_unread_count"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return true;
		}

		$messages_unread_count = $total_unread_counters["message_unread_count"] ?? 0;
		$threads_unread_count  = $total_unread_counters["thread_unread_count"] ?? 0;

		if ($messages_unread_count < $threads_unread_count) {
			$threads_unread_count = $messages_unread_count;
		}

		Gateway_Db_CompanyThread_UserInbox::set($user_id, [
			"message_unread_count" => (int) $messages_unread_count,
			"thread_unread_count"  => (int) $threads_unread_count,
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();
		return true;
	}

	/**
	 * обновляем пользовательское меню тредов, если количество непрочитанных меньше нуля
	 */
	protected function _updateUserThreadMenuForUnreadCountLessZero(int $user_id, array $thread_map_list):bool {

		// обнуляем количество непрочитанных в меню тредов
		Gateway_Db_CompanyThread_UserThreadMenu::setByUserIdAndThreadMapList($user_id, $thread_map_list, [
			"unread_count" => 0,
		]);

		// актуализируем данные в user_inbox
		$this->_updateUserInboxForUnreadCountLessZero($user_id);

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// инкрементим данные в таблице company_thread.user_inbox
	protected function _incrementUserInboxData(array $set, int $unread_count, int $user_id):array {

		$delta = self::_getDelta();

		// отдельно фиксируем число непрочитанных
		// поскольку в сет будет лежать строковое sql выражение
		$set["unread_count"] = $unread_count + $delta;

		self::_updateUserInboxData($user_id, $delta, $unread_count);

		return $set;
	}

	/**
	 * обновляем данные таблицы company_thread.user_inbox
	 */
	protected static function _updateUserInboxData(int $user_id, int $delta, int $unread_count):void {

		// получаем количество непрочитанных для пользователя
		$user_inbox_row = Domain_Thread_Entity_UserInbox::getInboxForUpdateByUserId($user_id);

		$user_inbox_set = [
			"message_unread_count" => $user_inbox_row["message_unread_count"] + $delta,
			"updated_at"           => time(),
		];

		if ($unread_count > 0) {

			Gateway_Db_CompanyThread_UserInbox::set($user_id, $user_inbox_set);
			return;
		}

		$user_inbox_set["thread_unread_count"] = $user_inbox_row["thread_unread_count"] + 1;

		Gateway_Db_CompanyThread_UserInbox::set($user_id, $user_inbox_set);
	}

	/**
	 * Возвращает число изменений для счетчика непрочитанных.
	 * Такая стремная функция, чтобы не возникло случайного рассинхрона, поскольку инкремент вычисляется в двух местах.
	 *
	 */
	protected static function _getDelta():int {

		return 1;
	}
}
