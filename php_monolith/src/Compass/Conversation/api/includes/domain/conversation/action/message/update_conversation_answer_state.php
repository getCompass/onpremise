<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Pack\Conversation;

/**
 * Действие для сохранения времени ответа
 */
class Domain_Conversation_Action_Message_UpdateConversationAnswerState {

	/**
	 * Выполняем при отправке сообщения
	 *
	 * @param string $conversation_map
	 * @param int    $conversation_type
	 * @param array  $message_list
	 * @param array  $users
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @long много проверок
	 */
	public static function doBySendMessage(string $conversation_map, int $conversation_type, array $message_list, array $users):void {

		// исключаем диалога с ботами/собой/поддержкой
		if (in_array($conversation_type, [
			CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT, CONVERSATION_TYPE_SINGLE_NOTES,
			CONVERSATION_TYPE_GROUP_SUPPORT, CONVERSATION_TYPE_GROUP_RESPECT,
		])) {
			return;
		}

		// если по этому типу сообщения не пишем стату
		$last_message = $message_list[count($message_list) - 1];
		$message_type = Type_Conversation_Message_Main::getHandler($last_message)::getType($last_message);
		if (!self::_isAllowedMessageType($message_type)) {
			return;
		}

		// если отправитель пустой (например системное сообщение)
		$sender_user_id = Type_Conversation_Message_Main::getHandler($last_message)::getSenderUserId($last_message);
		if ($sender_user_id < 1) {
			return;
		}

		// если отправитель не человек, то тоже не пишем
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$sender_user_id], false);
		if (!Type_User_Main::isHuman($user_info_list[$sender_user_id]->npc_type)) {
			return;
		}

		// получаем локальное время из заголовков
		[$local_date, $local_time, $local_timezone] = getLocalClientTime();
		if (mb_strlen($local_date) < 1 || mb_strlen($local_time) < 1 || mb_strlen($local_timezone) < 1) {

			$time           = time();
			$local_date     = date("d.m.Y", $time);
			$local_time     = date("H:i:s", $time);
			$local_timezone = date("O", $time);
		}

		// синглы обрабатываем отдельно
		if ($conversation_type === CONVERSATION_TYPE_SINGLE_DEFAULT) {

			$sent_at = Type_Conversation_Message_Main::getHandler($last_message)::getCreatedAt($last_message);
			self::_updateConversationAnswerState($conversation_map, $sender_user_id, array_keys($users), $sent_at, $local_date, $local_time, $local_timezone);
			return;
		}

		// групповые диалоги с меншенами отдельно
		self::_saveAnswerTimeInGroup($conversation_map, $message_list, $last_message, $local_date, $local_time, $local_timezone);
	}

	/**
	 * Нужно ли по типу сообщения писать время ответа
	 *
	 * @param int $message_type
	 *
	 * @return bool
	 */
	protected static function _isAllowedMessageType(int $message_type):bool {

		// если нет в массиве запрещенных
		return !in_array($message_type, [
			CONVERSATION_MESSAGE_TYPE_INVITE,
			CONVERSATION_MESSAGE_TYPE_SYSTEM,
			CONVERSATION_MESSAGE_TYPE_DELETED,
			CONVERSATION_MESSAGE_TYPE_CALL,
			CONVERSATION_MESSAGE_TYPE_RESPECT,
			CONVERSATION_MESSAGE_TYPE_SHARED_WIKI_PAGE,
			CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST,
			CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST,
		]);
	}

	/**
	 * Сохраняем время ответа в групповом диалоге
	 *
	 * @param string $conversation_map
	 * @param array  $message_list
	 * @param array  $last_message
	 * @param string $local_date
	 * @param string $local_time
	 * @param string $local_timezone
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _saveAnswerTimeInGroup(string $conversation_map, array $message_list, array $last_message, string $local_date, string $local_time, string $local_timezone):void {

		$sender_user_id = Type_Conversation_Message_Main::getHandler($last_message)::getSenderUserId($last_message);

		// группируем по времени отправки
		$receiver_user_id_list_grouped_by_sent_at = self::_groupReceiverUserIdListBySentAt($message_list);

		// если просто написали в группу, то как отправитель должны засчитаться (это мог быть ответ тому, кто нас упомянул, но мы отвечали без упоминания)
		if (count($receiver_user_id_list_grouped_by_sent_at) < 1) {

			$sent_at = Type_Conversation_Message_Main::getHandler($last_message)::getCreatedAt($last_message);
			self::_updateConversationAnswerState($conversation_map, $sender_user_id, [], $sent_at, $local_date, $local_time, $local_timezone);
			return;
		}

		// отправляем события для всех получателей
		foreach ($receiver_user_id_list_grouped_by_sent_at as $sent_at => $receiver_user_id_list) {
			self::_updateConversationAnswerState($conversation_map, $sender_user_id, $receiver_user_id_list, $sent_at, $local_date, $local_time, $local_timezone);
		}
	}

	/**
	 * Группируем получаетелей по времени отправки сообщений (1 timestamp == 1 запрос в go_rating)
	 *
	 * @param array $message_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _groupReceiverUserIdListBySentAt(array $message_list):array {

		$temp = [];
		foreach ($message_list as $message) {

			$mentioned_user_id_list = Type_Conversation_Message_Main::getHandler($message)::getMentionedUsers($message);
			$sent_at                = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);
			foreach ($mentioned_user_id_list as $user_id) {

				// если уже добавлено более старое сообщение, то скипаем
				if (isset($temp[$user_id]) && $temp[$user_id] >= $sent_at) {
					continue;
				}

				// добавляем в массив получателей
				$temp[$user_id] = $sent_at;
			}
		}

		// группируем по времени отправки
		$receiver_user_id_list_grouped_by_sent_at = [];
		foreach ($temp as $user_id => $sent_at) {

			if (!isset($receiver_user_id_list_grouped_by_sent_at[$sent_at])) {
				$receiver_user_id_list_grouped_by_sent_at[$sent_at] = [];
			}

			$receiver_user_id_list_grouped_by_sent_at[$sent_at][] = $user_id;
		}

		return $receiver_user_id_list_grouped_by_sent_at;
	}

	/**
	 * Выполняем при редактировании сообщения
	 *
	 * @param string $conversation_map
	 * @param int    $conversation_type
	 * @param int    $editor_user_id
	 * @param array  $new_mentioned_user_id_list
	 * @param int    $edited_at
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @long
	 */
	public static function doByEditMessage(string $conversation_map, int $conversation_type, int $editor_user_id, array $new_mentioned_user_id_list, int $edited_at):void {

		// если никого новых не упоминали в сообщении
		if (count($new_mentioned_user_id_list) < 1) {
			return;
		}

		// исключаем диалога с ботами/собой/поддержкой
		if (in_array($conversation_type, [
			CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT,
			CONVERSATION_TYPE_SINGLE_NOTES, CONVERSATION_TYPE_GROUP_SUPPORT, CONVERSATION_TYPE_GROUP_RESPECT,
		])) {
			return;
		}

		// если редактирующий пустой
		if ($editor_user_id < 1) {
			return;
		}

		// если редактировавший не человек, то тоже не пишем
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$editor_user_id], false);
		if (!Type_User_Main::isHuman($user_info_list[$editor_user_id]->npc_type)) {
			return;
		}

		// синглы не обрабатываем
		if ($conversation_type === CONVERSATION_TYPE_SINGLE_DEFAULT) {
			return;
		}
		// получаем локальное время из заголовков
		[$local_date, $local_time, $local_timezone] = getLocalClientTime();
		if (mb_strlen($local_date) < 1 || mb_strlen($local_time) < 1 || mb_strlen($local_timezone) < 1) {

			$time           = time();
			$local_date     = date("d.m.Y", $time);
			$local_time     = date("H:i:s", $time);
			$local_timezone = date("O", $time);
		}

		// групповые диалоги с меншенами отдельно
		self::_updateConversationAnswerStateForReceivers($conversation_map, $editor_user_id, $new_mentioned_user_id_list, $edited_at, $local_date, $local_time, $local_timezone);
	}

	/**
	 * Выполняем при установке реакции
	 *
	 * @param string $conversation_map
	 * @param int    $conversation_type
	 * @param int    $sender_user_id
	 * @param int    $added_at
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function doByAddReaction(string $conversation_map, int $conversation_type, int $sender_user_id, int $added_at):void {

		// исключаем диалога с ботами/собой/поддержкой
		if (in_array($conversation_type, [
			CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT, CONVERSATION_TYPE_SINGLE_NOTES,
			CONVERSATION_TYPE_GROUP_SUPPORT, CONVERSATION_TYPE_GROUP_RESPECT,
		])) {
			return;
		}

		// если отправитель не человек, то тоже не пишем
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$sender_user_id], false);
		if (!Type_User_Main::isHuman($user_info_list[$sender_user_id]->npc_type)) {
			return;
		}

		// получаем локальное время из заголовков
		[$local_date, $local_time, $local_timezone] = getLocalClientTime();
		if (mb_strlen($local_date) < 1 || mb_strlen($local_time) < 1 || mb_strlen($local_timezone) < 1) {

			$time           = time();
			$local_date     = date("d.m.Y", $time);
			$local_time     = date("H:i:s", $time);
			$local_timezone = date("O", $time);
		}
		self::_updateConversationAnswerState($conversation_map, $sender_user_id, [], $added_at, $local_date, $local_time, $local_timezone);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Добавляем время ответа
	 *
	 * @param string $conversation_map
	 * @param int    $sender_user_id
	 * @param array  $receiver_user_id_list
	 * @param int    $sent_at
	 * @param string $local_date
	 * @param string $local_time
	 * @param string $local_timezone
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @long
	 */
	protected static function _updateConversationAnswerState(string $conversation_map, int $sender_user_id, array $receiver_user_id_list, int $sent_at, string $local_date, string $local_time, string $local_timezone):void {

		// фильтруем финальный список получателей
		$receiver_user_id_list = self::_filterReceiverUserIdList($sender_user_id, $receiver_user_id_list);

		// преобразуем map -> key
		$conversation_key = Conversation::doEncrypt($conversation_map);

		// отправляем запрос
		[
			$space_id,
			$conversation_key,
			$answer_time,
			$created_at,
			$micro_conversation_start_at,
			$micro_conversation_end_at,
		] = Gateway_Bus_Rating_Main::updateConversationAnswerState(
			$conversation_key,
			$sender_user_id,
			$receiver_user_id_list,
			$sent_at,
			"{$local_date} {$local_time} {$local_timezone}"
		);

		if ($created_at < 1) {
			return;
		}

		$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($sender_user_id, false);
		$text              = sprintf("Время ответа: %s", self::_formatAnswerTime($answer_time));
		self::_writeLog($space_id, $sender_user_id, $conversation_key, $answer_time, $created_at,
			$micro_conversation_start_at, $micro_conversation_end_at);

		// если отправителя нет в списке тех, кому нужно слать ws, то выходим
		if ((!ServerProvider::isStage() && !ServerProvider::isTest() && !in_array($sender_user_id, NEED_SEND_ANSWER_TIME_WS_USER_ID_LIST))
			|| (ServerProvider::isOnPremise() && !ServerProvider::isCi())) {

			return;
		}

		// отправляем событие
		Gateway_Bus_Sender::answerDebugInfo([$talking_user_item], $conversation_key, $text);
	}

	/**
	 * Фильтруем финальный список получателей
	 *
	 * @param int   $sender_user_id
	 * @param array $receiver_user_id_list
	 *
	 * @return array
	 */
	protected static function _filterReceiverUserIdList(int $sender_user_id, array $receiver_user_id_list):array {

		$output = [];
		foreach ($receiver_user_id_list as $user_id) {

			// скипаем отправителя
			if ($sender_user_id === $user_id) {
				continue;
			}

			$output[] = $user_id;
		}

		return $output;
	}

	/**
	 * Форматируем время ответа
	 *
	 * @param int $answer_time
	 *
	 * @return string
	 */
	protected static function _formatAnswerTime(int $answer_time):string {

		$hours   = floor($answer_time / 3600);
		$minutes = floor($answer_time / 60) % 60;

		$formatted_answer_time = "";

		// если время в часах
		if ($hours > 0) {
			$formatted_answer_time .= "{$hours} " . plural($hours, "час", "часа", "часов");
		}

		// если есть минуты
		if ($minutes > 0) {

			// если вдруг формат: 1 час 5 минут
			if ($formatted_answer_time !== "") {
				$formatted_answer_time .= " ";
			}
			$formatted_answer_time .= "{$minutes} " . plural($minutes, "минута", "минуты", "минут");
		}

		// если время ответа меньше минуты
		if (mb_strlen($formatted_answer_time) < 1) {
			$formatted_answer_time = "{$answer_time} " . plural($answer_time, "секунда", "секунды", "секунд");
		}

		return $formatted_answer_time;
	}

	/**
	 * Пишем лог
	 *
	 * @param int    $space_id
	 * @param int    $sender_user_id
	 * @param string $conversation_key
	 * @param int    $answer_time
	 * @param int    $created_at
	 * @param int    $micro_conversation_start_at
	 * @param int    $micro_conversation_end_at
	 *
	 * @return void
	 */
	protected static function _writeLog(
		int    $space_id,
		int    $sender_user_id,
		string $conversation_key,
		int    $answer_time,
		int    $created_at,
		int    $micro_conversation_start_at,
		int    $micro_conversation_end_at
	):void {

		Type_System_Admin::log("answer_time_debug_info", [
			"space_id"                    => $space_id,
			"sender_user_id"              => $sender_user_id,
			"conversation_key"            => $conversation_key,
			"answer_time"                 => $answer_time,
			"created_at"                  => $created_at,
			"micro_conversation_start_at" => $micro_conversation_start_at,
			"micro_conversation_end_at"   => $micro_conversation_end_at,
		], false);
	}

	/**
	 * Добавляем время ответа
	 *
	 * @param string $conversation_map
	 * @param int    $sender_user_id
	 * @param array  $receiver_user_id_list
	 * @param int    $sent_at
	 * @param string $local_date
	 * @param string $local_time
	 * @param string $local_timezone
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _updateConversationAnswerStateForReceivers(string $conversation_map, int $sender_user_id, array $receiver_user_id_list, int $sent_at, string $local_date, string $local_time, string $local_timezone):void {

		// фильтруем финальный список получателей
		$receiver_user_id_list = self::_filterReceiverUserIdList($sender_user_id, $receiver_user_id_list);

		// преобразуем map -> key
		$conversation_key = Conversation::doEncrypt($conversation_map);

		// отправляем запрос
		Gateway_Bus_Rating_Main::updateConversationAnswerStateForReceivers(
			$conversation_key,
			$sender_user_id,
			$receiver_user_id_list,
			$sent_at,
			"{$local_date} {$local_time} {$local_timezone}"
		);
	}
}