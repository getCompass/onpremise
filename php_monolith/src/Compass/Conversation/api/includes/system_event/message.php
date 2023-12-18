<?php

namespace Compass\Conversation;

/**
 * класс, который обрабатывает события из категории message
 * из очереди событий
 */
class SystemEvent_Message {

	// якоря в тексте сообщения системного бота для замены
	protected const _SUBSTITUTION_LIST_BY_MESSAGE_TYPE = [
		"text" => ["general_group_name", "heroes_group_name", "challenge_group_name"],
	];

	/**
	 * отправляет пачку сообщений от бота пользователю
	 *
	 * @param Struct_Event_Message_SendSystemMessageListToUser $event_data
	 *
	 * @return bool
	 */
	#[Type_Attribute_EventListener(Type_Event_Message_SendSystemMessageListToUser::EVENT_TYPE)]
	public static function sendMessageListFromSystemBotToUser(Struct_Event_Message_SendSystemMessageListToUser $event_data):bool {

		try {

			$bot_user_id    = $event_data->bot_user_id;
			$target_user_id = $event_data->receiver_user_id;

			// готовим сообщения
			$message_list = self::_prepareMessageList($bot_user_id, $event_data->message_list);

			try {
				self::_addMessageListToUser($bot_user_id, $target_user_id, $message_list);
			} catch (cs_Conversation_MemberIsDisabled | Domain_Conversation_Exception_User_IsAccountDeleted $e) {
				throw new cs_SystemEventException($e->getMessage());
			}
		} catch (\Exception) {
			return false;
		}

		return true;
	}

	/**
	 * отправляет пачку сообщений от бота в чат
	 *
	 * @param Struct_Event_Message_SendSystemMessageListToConversation $event_data
	 *
	 * @return bool
	 */
	#[Type_Attribute_EventListener(Type_Event_Message_SendSystemMessageListToConversation::EVENT_TYPE)]
	public static function sendMessageListFromSystemBotToGroup(Struct_Event_Message_SendSystemMessageListToConversation $event_data):bool {

		try {

			$bot_user_id      = $event_data->bot_user_id;
			$conversation_map = $event_data->conversation_map;

			// готовим сообщения
			$message_list = self::_prepareMessageList($bot_user_id, $event_data->message_list);

			try {
				self::_addMessageListToConversation($bot_user_id, $conversation_map, $message_list);
			} catch (cs_Conversation_MemberIsDisabled $e) {
				throw new cs_SystemEventException($e->getMessage());
			}
		} catch (\Exception) {
			return false;
		}

		return true;
	}

	# region protected

	/**
	 * Подготавливает сообщения перед отправкой.
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 * @long switch-case
	 */
	protected static function _prepareMessageList(int $bot_user_id, array $raw_message_list):array {

		$message_list = [];

		foreach ($raw_message_list as $v) {

			$substitution_list = self::_SUBSTITUTION_LIST_BY_MESSAGE_TYPE[$v["type"]] ?? [];

			switch ($v["type"]) {

				case "text":

					$message_list[] = self::_makeTextMessage($bot_user_id, $v, $substitution_list);
					break;

				case "file":

					$message_list[] = self::_makeFileMessage($bot_user_id, $v);
					break;

				case "invite":

					$message_list[] = self::_makeInviteMessage($bot_user_id, $v);
					break;

				case "editor_feedback_request":

					$message_list[] = self::_makeEditorFeedbackRequestMessage($bot_user_id, $v);
					break;

				case "editor_employee_anniversary":

					$message_list[] = self::_makeEditorEmployeeAnniversary($bot_user_id, $v);
					break;

				case "employee_anniversary":

					$message_list[] = self::_makeEmployeeAnniversary($bot_user_id, $v);
					break;

				case "editor_worksheet_rating":

					$message_list[] = self::_makeEditorWorksheetRatingMessage($bot_user_id, $v);
					break;

				case "company_employee_metric_statistic":

					$message_list[] = self::_makeCompanyEmployeeMetricStatistic($bot_user_id, $v);
					break;

				case "editor_employee_metric_notice":

					$message_list[] = self::_makeEditorEmployeeMetricNoticeMessage($bot_user_id, $v);
					break;

				case "work_time_auto_log_notice":

					$message_list[] = self::_makeWorkTimeAutoLogNotice($bot_user_id, $v);
					break;

				case "company_rating_message":

					$message_list[] = self::_makeCompanyRatingMessage($bot_user_id, $v);
					break;

				case "invite_to_company_inviter_single":

					$message_list[] = self::_makeInviteToCompanyInviterSingle($bot_user_id, $v);
					break;

				case "system_bot_messages_moved_notification":

					$message_list[] = self::_makeSystemBotMessagesMovedNotification($bot_user_id);
					break;

				default:
					throw new cs_SystemEventException("incorrect message type");
			}
		}

		return $message_list;
	}

	// подготавливает текстовое сообщение
	protected static function _makeTextMessage(int $bot_user_id, array $message_data, array $substitution_list):array {

		// готовим текст
		$message_text = self::_prepareText($message_data, $substitution_list);

		// создаем сообщение с текстом
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotText(
			$bot_user_id,
			$message_text,
			generateUUID()
		);
	}

	// подготавливает сообщение с файлом
	protected static function _makeFileMessage(int $bot_user_id, array $message_data):array {

		if (!isset($message_data["file_map"])) {
			throw new cs_SystemEventException("incorrect file map");
		}

		$file_map  = $message_data["file_map"];
		$file_name = $message_data["file_name"] ?? "";

		// проверяем на валидность
		\CompassApp\Pack\File::getFileSource($file_map);

		// создаем сообщение с файлом
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotFile(
			$bot_user_id,
			"",
			generateUUID(),
			$file_map,
			$file_name
		);
	}

	/**
	 * подготавливает сообщение с инвайтом
	 *
	 * @throws cs_SystemEventException
	 */
	protected static function _makeInviteMessage(int $bot_user_id, array $message_data):array {

		if (!isset($message_data["invite_map"])) {
			throw new cs_SystemEventException("incorrect invite_map");
		}

		$invite_map = $message_data["invite_map"];

		return Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotInvite(
			$bot_user_id,
			$invite_map,
		);
	}

	/**
	 * Создает сообщение типа «Уведомление о годовщине сотрудника в компании».
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeEditorEmployeeAnniversary(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["employee_user_id"]) || !isset($message_data["hired_at"])) {
			throw new cs_SystemEventException("incorrect employee data");
		}

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeEditorEmployeeAnniversary(
			$bot_user_id,
			$message_text,
			generateUUID(),
			$message_data["employee_user_id"],
			$message_data["hired_at"]
		);
	}

	/**
	 * Создает сообщение типа «Уведомление о годовщине сотрудника в компании».
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeEmployeeAnniversary(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["hired_at"])) {
			throw new cs_SystemEventException("incorrect employee data");
		}

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeEmployeeAnniversary(
			$bot_user_id,
			$message_text,
			generateUUID(),
			$message_data["hired_at"]
		);
	}

	/**
	 * Создает сообщение типа «Уведомление о запросе на обратную связь».
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeEditorFeedbackRequestMessage(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["employee_user_id"])) {
			throw new cs_SystemEventException("incorrect employee data");
		}

		// проверяем, что мап в норме
		if (!isset($message_data["feedback_request_id"]) || intval($message_data["feedback_request_id"]) != $message_data["feedback_request_id"]) {
			throw new cs_SystemEventException("incorrect feedback request data");
		}

		// проверяем сообщение
		if (!isset($message_data["period_id"]) || !isset($message_data["period_start_date"]) || !isset($message_data["period_end_date"])) {
			throw new cs_SystemEventException("incorrect period data");
		}

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeEditorFeedbackRequest(
			$bot_user_id, $message_text, generateUUID(), $message_data["feedback_request_id"], $message_data["employee_user_id"],
			$message_data["period_id"], $message_data["period_start_date"], $message_data["period_end_date"]
		);
	}

	/**
	 * Создает сообщение типа «Уведомление о рабочих часах за период времени».
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeEditorWorksheetRatingMessage(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["leader_user_work_item_list"]) || !isset($message_data["driven_user_work_item_list"])) {
			throw new cs_SystemEventException("incorrect employee list data");
		}

		$leader_user_work_item_list = self::_filterUserWorkItemList($message_data["leader_user_work_item_list"]);
		$driven_user_work_item_list = self::_filterUserWorkItemList($message_data["driven_user_work_item_list"]);

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeEditorWorksheetRating(
			$bot_user_id, $message_text, generateUUID(), $leader_user_work_item_list, $driven_user_work_item_list,
			$message_data["period_id"], $message_data["period_start_date"], $message_data["period_end_date"]
		);
	}

	/**
	 * Фильтрует список пользователь-рабочее время.
	 *
	 * @throws cs_SystemEventException
	 */
	protected static function _filterUserWorkItemList(array $user_work_item_list):array {

		$output = [];

		foreach ($user_work_item_list as $v) {

			if (!isset($v["user_id"]) || !isset($v["work_time"]) || (int) $v["user_id"] <= 0) {
				throw new cs_SystemEventException("incorrect employee work item");
			}

			// оставляем только нужные данные
			$output[] = [
				"user_id"   => $v["user_id"],
				"work_time" => $v["work_time"],
			];
		}

		return $output;
	}

	/**
	 * Создает сообщение типа «Уведомление о итоговых метриках сотрудников за период времени».
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 * @long
	 */
	protected static function _makeCompanyEmployeeMetricStatistic(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["metric_count_item_list"])) {
			throw new cs_SystemEventException("incorrect metric data");
		}

		// проверяем сообщение
		if (!isset($message_data["company_name"])) {
			throw new cs_SystemEventException("incorrect company name");
		}

		$metric_count_item_list = [];

		foreach ($message_data["metric_count_item_list"] as $v) {

			if (!isset($v["metric_type"]) || !isset($v["count"])) {
				throw new cs_SystemEventException("incorrect metric count item");
			}

			// оставляем только нужные данные
			$metric_count_item_list[] = [
				"metric_type" => $v["metric_type"],
				"count"       => $v["count"],
			];
		}

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeCompanyEmployeeMetricStatistic(
			$bot_user_id,
			$message_text,
			generateUUID(),
			$message_data["company_name"],
			$metric_count_item_list,
			0,
			$message_data["period_start_date"],
			$message_data["period_end_date"]
		);
	}

	/**
	 * Подготавливает сообщение-напоминание о необходимости дать ОС.
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeEditorEmployeeMetricNoticeMessage(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["employee_user_id"])) {
			throw new cs_SystemEventException("incorrect employee user id");
		}

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение с текстом
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeEditorEmployeeMetricNotice(
			$bot_user_id,
			$message_text,
			generateUUID(),
			$message_data["employee_user_id"]
		);
	}

	/**
	 * Подготавливает сообщение-уведомление о автоматически списанных часах.
	 *
	 * @throws cs_SystemEventException
	 * @throws \parseException
	 */
	protected static function _makeWorkTimeAutoLogNotice(int $bot_user_id, array $message_data):array {

		// проверяем сообщение
		if (!isset($message_data["work_time"])) {
			throw new cs_SystemEventException("passed incorrect work time");
		}

		$work_time = (int) $message_data["work_time"];

		// готовим текст
		$message_text = self::_prepareText($message_data);

		// создаем сообщение с текстом
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeWorkTimeAutoLogNotice(
			$bot_user_id,
			$message_text,
			generateUUID(),
			$work_time
		);
	}

	/**
	 * Подготавливает сообщение со статистикой за период времени.
	 *
	 * @throws cs_SystemEventException
	 */
	protected static function _makeCompanyRatingMessage(int $bot_user_id, array $message_data):array {

		$year         = intval($message_data["year"]);
		$week         = intval($message_data["week"]);
		$count        = intval($message_data["count"]);
		$company_name = $message_data["name"];

		if ($year == 0 || $week == 0 || $company_name == "") {
			throw new cs_SystemEventException("incorrect data for rating message");
		}

		return $message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotRating($bot_user_id, $year, $week, $count, $company_name, generateUUID());
	}

	/**
	 * Подготавливает сообщение с приглашение в сингл с пригласившим в компанию..
	 *
	 * @throws cs_SystemEventException
	 */
	protected static function _makeInviteToCompanyInviterSingle(int $bot_user_id, array $message_data):array {

		if (!isset($message_data["text"]) || !isset($message_data["company_inviter_user_id"])) {
			throw new cs_SystemEventException("incorrect data for invite to company inviter single message");
		}

		$text                    = Type_Api_Filter::sanitizeMessageText($message_data["text"]);
		$company_inviter_user_id = (int) $message_data["company_inviter_user_id"];

		return Type_Conversation_Message_Main::getLastVersionHandler()::makeInviteToCompanyInviterSingle(
			$bot_user_id, generateUUID(), $text, $company_inviter_user_id
		);
	}

	/**
	 * Подготавливает сообщение о смене типа чата оповещения
	 */
	protected static function _makeSystemBotMessagesMovedNotification(int $bot_user_id):array {

		return Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotMessagesMovedNotification(
			$bot_user_id, generateUUID()
		);
	}

	/**
	 * Подготавливает текст для сообщения.
	 * Обрезает, чистит и все такое.
	 *
	 * @param array  $message_data данные сообщения
	 * @param string $field        название поля с текстом в сообщении
	 *
	 * @throws \parseException
	 */
	protected static function _prepareText(array $message_data, array $substitution_list = [], string $field = "text"):string {

		$text = $message_data[$field] ?? "";
		$text = Type_Api_Filter::replaceEmojiWithShortName($text);

		// заменяем алиасы на текст, если таковые имеются
		$text = Type_Api_Filter::processSubstitutionsIfExist($text, $substitution_list);

		return Type_Api_Filter::sanitizeMessageText($text);
	}

	/**
	 * добавляем сообщение пользователю
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 */
	protected static function _addMessageListToUser(int $sender_id, int $receiver_id, array $message_list):void {

		// создаем диалог, если не получилось то выходим просто
		try {
			$meta_row = Helper_Single::createSingleWithSystemBotIfNotExist($sender_id, $receiver_id);
		} catch (cs_Conversation_MemberIsDisabled | cs_UserNotFound) {
			return;
		}

		// добавляем сообщение на текущий момент ничего не делаем
		Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			$message_list,
			$meta_row["users"],
			$meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		foreach ($message_list as $v) {

			$text     = $v["data"]["text"] ?? "no text";
			$file_map = $v["data"]["file_map"] ?? "no file";

			Type_System_Admin::log("system_bot_messages", "system message sent from $sender_id to $receiver_id \n$text $file_map)");
		}
	}

	/**
	 * добавляем сообщение в чат
	 *
	 * @throws \paramException
	 */
	protected static function _addMessageListToConversation(int $sender_id, string $conversation_map, array $message_list):void {

		// получаем мету чата
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// добавляем сообщение на текущий момент ничего не делаем
		Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			$message_list,
			$meta_row["users"],
			$meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		foreach ($message_list as $v) {

			$text     = $v["data"]["text"] ?? "no text";
			$file_map = $v["data"]["file_map"] ?? "no file";

			Type_System_Admin::log("system_bot_messages", "system message sent from $sender_id to chat: $conversation_map \n$text $file_map)");
		}
	}

	# endregion
}
