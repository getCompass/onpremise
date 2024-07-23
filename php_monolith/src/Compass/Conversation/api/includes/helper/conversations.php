<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Struct\Short as MemberStructShort;
use CompassApp\Domain\Member\Struct\Main as MemberStructMain;
use CompassApp\Pack\File;
use CompassApp\Pack\Message;

/**
 * хелпер для всего, что связано с диалогами
 *
 */
class Helper_Conversations {

	protected const _CLIENT_MESSAGE_ID_CACHE_EXPIRE = 60 * 60;  // время в секундах через сколько истечен кэш хранения client_message_id
	protected const _MAX_SELECTED_MESSAGES_COUNT    = 150;      // число выбранных сообщений для репоста/цитаты

	public const MESSAGE_TYPE_EMPTY          = 0;  // тип пустого сообщения
	public const MAX_MESSAGES_COUNT_IN_CHUNK = 15; // число сообщений в одном родительском

	// mute диалога
	public static function doMute(int $user_id, string $conversation_map, int $is_muted, int $interval_minutes):int {

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем валидность действия
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::MUTE_CONVERSATION);

		// проверили что пользователь участник диалога
		self::_throwIfUserIsNotConversationMember($user_id, $meta_row["users"], "conversations", "row203");

		$time_at        = time(); // записываем текущее время
		$max_time_limit = $time_at + DAY1 * 99 + (DAY1 - 1); // максимальное значение таймера

		// выполняем действия для mute
		$new_muted_until = Domain_Conversation_Entity_Dynamic::setMuted($conversation_map, $user_id, $is_muted, $interval_minutes, $max_time_limit, $time_at);

		// обновляем muted_until в left_menu пользователя
		self::_setLeftMenuMuted($user_id, $conversation_map, $is_muted, $new_muted_until);

		// отправляем события пользователю
		Gateway_Bus_Sender::conversationMutedChanged($user_id, $conversation_map, $is_muted, $new_muted_until);

		// если достигли лимита времени отключения уведомлений диалога
		// и клиент желает получать об этом ошибку
		if ($new_muted_until === $max_time_limit) {
			throw new cs_Conversation_NotificationsDisableTimeLimited();
		}

		return $new_muted_until;
	}

	// устанавливаем новый статус is_muted и muted_until для сущности left_menu
	protected static function _setLeftMenuMuted(int $user_id, string $conversation_map, int $is_muted, int $new_muted_until):void {

		Type_Conversation_LeftMenu::doMute($user_id, $conversation_map, $is_muted, $new_muted_until);
	}

	// unmute диалога
	public static function doUnmute(int $user_id, string $conversation_map):void {

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверили что пользователь участник диалога
		self::_throwIfUserIsNotConversationMember($user_id, $meta_row["users"], "conversations", "row203");

		// выполняем дествия для mute
		Domain_Conversation_Entity_Dynamic::setUnmuted($conversation_map, $user_id);

		Type_Conversation_LeftMenu::doUnmute($user_id, $conversation_map);

		// отправляем событие пользователю
		Gateway_Bus_Sender::conversationMutedChanged($user_id, $conversation_map, 0, 0);
	}

	// очищаем диалог для пользователя
	public static function clearMessages(int $user_id, string $conversation_map, array $left_menu_row, bool $is_cleared_user, int $clear_until, bool $is_need_silent = false):void {

		// проверяем валидность действия
		Type_Conversation_Action::assertAction((int) $left_menu_row["type"], Type_Conversation_Action::CLEAR_CONVERSATION);

		// обнуляем unread_count, last_message и устанавливаем clear_until в left_menu
		Type_Conversation_LeftMenu::setCleared($user_id, $conversation_map, $clear_until);

		// пересчитываем total_unread_count
		Type_Conversation_LeftMenu::recountTotalUnread($user_id);

		// обновляем время очистки диалога
		$dynamic = self::_setClearUntil($user_id, $conversation_map, $clear_until, $is_cleared_user);

		// обновляем время очистки диалога для модуля php_thread
		Gateway_Socket_Thread::clearConversationForUserIdList($conversation_map, $clear_until, [$user_id]);

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// отправляем событие участнику
		if (!$is_need_silent) {
			Gateway_Bus_Sender::conversationClearMessages([$user_id], $conversation_map, $dynamic->messages_updated_version);
		}

		// отправляем сообщение на очистку диалога для пользователя
		Domain_Search_Entity_Conversation_Task_Clear::queue($conversation_map, [$user_id]);
	}

	// обновляет время очистки диалога для модуля php_conversation
	protected static function _setClearUntil(int $user_id, string $conversation_map, int $clear_until, bool $is_cleared_user = true):Struct_Db_CompanyConversation_ConversationDynamic {

		if ($is_cleared_user) {
			Domain_Conversation_Entity_Dynamic::setClearUntil($conversation_map, $user_id, $clear_until);
		}

		return Domain_Conversation_Entity_Dynamic::setClearUntilConversationForUserIdList($conversation_map, [$user_id], $clear_until, false);
	}

	// возращаем ранее очищеные сообщения диалога для пользователя
	public static function unclearMessages(int $user_id, string $conversation_map, array $left_menu_row, array $dynamic_row):void {

		// проверяем валидность действия
		Type_Conversation_Action::assertAction((int) $left_menu_row["type"], Type_Conversation_Action::UNCLEAR_CONVERSATION);

		// устанавливаем clear_until left_menu
		$clear_until = isset($dynamic_row["user_clear_info"][$user_id]) ? $dynamic_row["user_clear_info"][$user_id]["clear_until"] : 0;
		Type_Conversation_LeftMenu::setCleared($user_id, $conversation_map, $clear_until);

		// возращаем ранее очищеные сообщения
		Domain_Conversation_Entity_Dynamic::setUnclearUntilConversation($conversation_map, $user_id);
	}

	// восстанавливаем диалоги с пользователем которого разблокировали
	public static function updateConversationListAfterUnblock(int $user_id):void {

		// получаем количество записей по id пользователя и устанавливаем лимит получения записей за раз
		$count = Gateway_Db_CompanyConversation_UserLeftMenu::getCountLeftMenu($user_id);
		$limit = 50;

		// проходим по всем записям
		for ($i = 0; $i < ceil($count / $limit); $i++) {

			// получаем записи по id пользователя
			$offset         = $i * $limit;
			$left_menu_list = Gateway_Db_CompanyConversation_UserLeftMenu::getByOffset($user_id, $limit, $offset);

			// бежим по всем диалогам юзера
			foreach ($left_menu_list as $left_menu_row) {

				if (!Type_Conversation_Meta::isSubtypeOfSingle($left_menu_row["type"])) {
					continue;
				}

				// выставляем allow_status = need_check
				$meta_row = Type_Conversation_Meta::get($left_menu_row["conversation_map"]);

				// такой диалог нужно проверить с уникальной логикой, поскольку пользователь покидал пространство
				$allow_status = ALLOW_STATUS_NEED_CHECK;
				$set          = [
					"allow_status" => $allow_status,
					"updated_at"   => time(),
				];
				Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($left_menu_row["conversation_map"], $set);

				// обновляем поле allow_status_alias для самого пользователя
				$allow_status_alias = Type_Conversation_Utils::getAllowStatus($allow_status, $meta_row["extra"], $left_menu_row["opponent_user_id"]);

				self::_updateConversationListAfterUnblockForUser($user_id, $left_menu_row, $allow_status_alias);
				self::_updateConversationListAfterUnblockForUser($left_menu_row["opponent_user_id"], $left_menu_row, $allow_status_alias);
			}
		}
	}

	// востанавливаем диалоги у пользователя
	protected static function _updateConversationListAfterUnblockForUser(int $user_id, array $left_menu_row, int $allow_status_alias):void {

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $left_menu_row["conversation_map"], ["allow_status_alias" => $allow_status_alias]);
	}

	// добавить сообщение произвольной структуры в диалог
	public static function addMessage(string $conversation_map, array $message, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, bool $is_silent = true, array $not_send_ws_event_user_list = []):array {

		$message_list = self::addMessageList(
			$conversation_map,
			[$message],
			$users,
			$conversation_type,
			$conversation_name,
			$conversation_extra,
			true,
			$is_silent,
			$not_send_ws_event_user_list);

		return reset($message_list);
	}

	// добавляет пул произвольных сообщений в базу
	// под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения
	public static function addMessageList(string $conversation_map, array $raw_message_list, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, bool $is_need_attach_preview = true, bool $is_silent = true, array $not_send_ws_event_user_list = []):array {

		// удаляем дубликаты если они есть в одной пачке сообщений
		$raw_message_list = self::_removeDuplicateIfExistsWithinRawMessageList($raw_message_list);

		// добавляем сообщения в кэш
		foreach ($raw_message_list as $v) {
			self::_addDuplicateMessageCache($conversation_map, $v);
		}

		// получаем запись из dynamic и проверяем что диалог не закрыт на добавление сообщений
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		self::_throwIfConversationIsLocked($dynamic_row);

		// добавляем сообщение в диалог
		[$message_list, $dynamic_row] = Type_Conversation_Message_Block::addMessageList($conversation_map, $raw_message_list, $dynamic_row);

		// выполняем обработку для каждого сообщения
		self::_doActionsForEachMessage($message_list, $users, $is_need_attach_preview);

		Domain_Conversation_Entity_Dynamic::updateMessagesUpdatedAt($conversation_map);

		// если необходимо совершаем действия различного рода с участниками диалога, в который было отправлено сообщение
		if ($is_silent) {

			self::_doActionWithUsersOnMessageAdd(
				$users,
				$message_list,
				$dynamic_row,
				$conversation_extra,
				$conversation_type,
				$conversation_name,
				$not_send_ws_event_user_list);
		}

		// сохраняем время ответа
		Domain_Conversation_Action_Message_UpdateConversationAnswerState::doBySendMessage($conversation_map, $conversation_type, $message_list, $users);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incMessageSent($conversation_map, $message_list);

		return $message_list;
	}

	/**
	 * Добавляет пул сообщений в базу
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Message_DuplicateClientMessageId
	 */
	public static function addMessageListByMigration(string $conversation_map, array $raw_message_list, array $users, bool $is_need_attach_preview = true, bool $is_need_index = false):array {

		// удаляем дубликаты если они есть в одной пачке сообщений
		$raw_message_list = self::_removeDuplicateIfExistsWithinRawMessageList($raw_message_list);

		// добавляем сообщения в кэш
		foreach ($raw_message_list as $v) {
			self::_addDuplicateMessageCache($conversation_map, $v);
		}

		// получаем запись из dynamic и проверяем что диалог не закрыт на добавление сообщений
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		self::_throwIfConversationIsLocked($dynamic_row);

		// добавляем сообщение в диалог
		[$message_list, $dynamic_row] = Type_Conversation_Message_Block::addMessageList($conversation_map, $raw_message_list, $dynamic_row);

		// выполняем обработку для каждого сообщения
		self::_doActionsForEachMessage($message_list, $users, $is_need_attach_preview, $is_need_index);

		Domain_Conversation_Entity_Dynamic::updateMessagesUpdatedAt($conversation_map);

		return $message_list;
	}

	/**
	 * удаляем дубликаты если они есть в одной пачке сообщений
	 *
	 */
	protected static function _removeDuplicateIfExistsWithinRawMessageList(array $raw_message_list):array {

		$temp = array_unique(array_column($raw_message_list, "client_message_id"));
		return array_intersect_key($raw_message_list, $temp);
	}

	/**
	 * использует client_message_id первого сообщения в качестве ключа
	 * добавляет сообщение в mCache, чтобы избежать повторной отправки
	 *
	 * @throws \parseException|cs_Message_DuplicateClientMessageId
	 */
	protected static function _addDuplicateMessageCache(string $conversation_map, array $message):void {

		// получаем client_message_id и записываем в кэш
		$client_message_id = Type_Conversation_Message_Main::getHandler($message)::getClientMessageId($message);

		try {
			ShardingGateway::cache()->add(self::_getKey($conversation_map, $client_message_id), $message, self::_CLIENT_MESSAGE_ID_CACHE_EXPIRE);
		} catch (\cs_MemcacheRowIfExist) {
			throw new cs_Message_DuplicateClientMessageId();
		}
	}

	// ставим задачи на парсинг строки
	// тут происходят несвязные вещи, но зато обходим список в один проход
	protected static function _doActionsForEachMessage(array $message_list, array $users, bool $is_need_attach_preview, bool $is_need_index = true):void {

		// определяем нужно ли парсить превью
		$is_preview_parse = $is_need_attach_preview && count($message_list) === 1;

		foreach ($message_list as $v) {

			$message_map    = Type_Conversation_Message_Main::getHandler($v)::getMessageMap($v);
			$sender_user_id = Type_Conversation_Message_Main::getHandler($v)::getSenderUserId($v);
			$message_text   = Type_Conversation_Message_Main::getHandler($v)::getText($v);

			// прикрепляем превью если нужно
			Type_Preview_Producer::addTaskIfLinkExistInMessage($sender_user_id, $message_text, $message_map, $users, $is_preview_parse);

			// делаем всякие штуки для разных типов сообщений
			self::_doActionAfterMessageAddByType($v);

			// инкрментим статистику по типу сообщений
			self::_incStatAfterMessageAdd($sender_user_id, $v);
		}

		$is_need_index && Domain_Search_Entity_ConversationMessage_Task_Index::queueList($message_list, array_keys($users), Locale::getLocale());
	}

	// совершаем действия различного рода в зависимости от типа добавленного сообщения
	// @long - switch
	protected static function _doActionAfterMessageAddByType(array $message):void {

		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			case CONVERSATION_MESSAGE_TYPE_FILE:

				self::_onAddMessageFile($message);
				break;
			case CONVERSATION_MESSAGE_TYPE_QUOTE:

				self::_onAddMessageQuote($message);
				break;
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				self::_onAddMessageMassQuote($message);
				break;
			case CONVERSATION_MESSAGE_TYPE_REPOST:

				$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageList($message);
				self::_onAddMessageRepost($message, $reposted_message_list);
				break;
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageListFromThread($message);
				self::_onAddMessageRepost($message, $reposted_message_list);
				break;
		}
	}

	// совершаем действия после добавления сообщения с файлом
	protected static function _onAddMessageFile(array $message):void {

		// заносим информацию о файле в таблицу с файлами диалога
		$file_map         = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
		$file_uid         = Type_Conversation_Message_Main::getHandler($message)::getFileUuid($message);
		$sender_user_id   = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$message_map      = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$insert           = Domain_Conversation_Entity_File_Main::createStructForFileFromConversation($conversation_map, $file_map, $file_uid, time(), $message_map, $sender_user_id);
		Domain_Conversation_Entity_File_Main::addFile($insert);
	}

	// совершаем действия после добавления сообщения с цитатой
	protected static function _onAddMessageQuote(array $message):void {

		// если процитированное сообщение не содержит файла
		$quoted_message = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessage($message);
		if (!Type_Conversation_Message_Main::getHandler($quoted_message)::isFile($quoted_message)) {
			return;
		}

		// получаем conversation_map
		$message_map      = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// получаем отправителя цитаты
		$sender_user_id = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);

		// добавляем файл в таблицу файлов диалога
		$file_map = Type_Conversation_Message_Main::getHandler($quoted_message)::getFileMap($quoted_message);
		$file_uid = Type_Conversation_Message_Main::getHandler($quoted_message)::getFileUuid($quoted_message);
		$insert   = Domain_Conversation_Entity_File_Main::createStructForFileFromConversation($conversation_map, $file_map, $file_uid, time(), $message_map, $sender_user_id);
		Domain_Conversation_Entity_File_Main::addFile($insert);
	}

	// совершаем действия после добавления сообщения с несколькими цитатами
	protected static function _onAddMessageMassQuote(array $message):void {

		$quoted_message_list = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessageList($message);
		$message_map         = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$conversation_map    = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// переворачиваем список процитированных сообщений сообщений, чтобы не нарушить порядок файлов
		$quoted_message_list = array_reverse($quoted_message_list);

		// проходим по всем сообщениям в цитате и заносим все файлы, что содержатся в них
		$insert_list = self::_getInsertListIfMessageIsQuoteOrRepost($quoted_message_list, $conversation_map, $message_map);

		if (count($insert_list) == 0) {
			return;
		}

		Domain_Conversation_Entity_File_Main::addFileList($insert_list);
	}

	// совершаем действия после добавления сообщения с репостом
	protected static function _onAddMessageRepost(array $message, array $reposted_message_list):void {

		$message_map      = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// переворачиваем список репостнутых сообщений, чтобы не нарушить порядок файлов
		$reposted_message_list = array_reverse($reposted_message_list);

		// проходим по всем сообщениям в репосте и заносим все файлы, что содержатся в них
		$insert_list = self::_getInsertListIfMessageIsQuoteOrRepost($reposted_message_list, $conversation_map, $message_map);

		if (count($insert_list) == 0) {
			return;
		}

		Domain_Conversation_Entity_File_Main::addFileList($insert_list);
	}

	// получаем массив для вставки если сообщение имеет тип цитата или репост
	protected static function _getInsertListIfMessageIsQuoteOrRepost(array $message_list, string $conversation_map, string $message_map, array $insert_list = []):array {

		foreach ($message_list as $v) {
			$insert_list = self::_getInsertListIfMessageHasFile($v, $conversation_map, $message_map, $insert_list);
		}

		return $insert_list;
	}

	// получаем массив для вставки файла если сообщение имеет файл
	protected static function _getInsertListIfMessageHasFile(array $message, string $conversation_map, string $message_map, array $insert_list):array {

		// если это цитата или репост, то дополнительно проходимся по процитированным/репостнутым сообщениям
		if (Type_Conversation_Message_Main::getHandler($message)::isQuote($message)) {

			$quoted_message_list = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessageList($message);
			return self::_getInsertListIfMessageIsQuoteOrRepost($quoted_message_list, $conversation_map, $message_map, $insert_list);
		}
		if (Type_Conversation_Message_Main::getHandler($message)::isRepost($message)) {

			$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageList($message);
			return self::_getInsertListIfMessageIsQuoteOrRepost($reposted_message_list, $conversation_map, $message_map, $insert_list);
		}

		// получаем map файла, если сообщение имеет тип файл или файл репостнутый из треда
		// если файла нет, то ничего не меняем
		$file_map = self::_getFileMapIfMessageIsFile($message);
		if ($file_map === false) {
			return $insert_list;
		}

		$file_uuid      = Type_Conversation_Message_Main::getHandler($message)::getFileUuid($message);
		$sender_user_id = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$insert_list[]  = Domain_Conversation_Entity_File_Main::createStructForFileFromConversation($conversation_map, $file_map, $file_uuid, time(), $message_map, $sender_user_id);
		return $insert_list;
	}

	/**
	 * получаем file_map если сообщение имеет тип file
	 *
	 * @return false|string
	 *
	 * @throws \parseException
	 * @mixed - file_map может быть как false, так и string
	 */
	protected static function _getFileMapIfMessageIsFile(array $message):bool|string {

		// получаем map файла, если сообщение имеет тип файл
		if (Type_Conversation_Message_Main::getHandler($message)::isFile($message) ||
			Type_Conversation_Message_Main::getHandler($message)::isFileFromThreadRepost($message)) {
			return Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
		}

		return false;
	}

	// совершаем действия различного рода с участниками диалога, в который было отправлено сообщение
	protected static function _doActionWithUsersOnMessageAdd(array $users, array $message_list, array $dynamic_row, array $conversation_extra, int $conversation_type, string $conversation_name, array $not_send_ws_event_user_list):void {

		$last_message   = end($message_list);
		$user_mute_info = $dynamic_row["user_mute_info"];

		// если сообщения для фиксации рабочего времени, то отправку уведомлений и обновление левого меню делать не нужно
		if (Type_Conversation_Message_Main::getHandler($last_message)::isContainAdditionalWorkedHours($last_message)) {
			return;
		}

		$need_update_left_menu_user_list = [];
		$all_talking_user_list           = [];

		// получаем данные по последнему сообщению
		foreach ($message_list as $v) {

			// обрабатываем сообщения для каждого пользователя
			$aggregated = self::_aggregateUserListsOnMessageAdd(
				$v, $users, $conversation_extra, $user_mute_info, $conversation_type, $not_send_ws_event_user_list, $all_talking_user_list
			);

			// складываем talking_user для отправки ws о списке сообщений
			$all_talking_user_list = $aggregated["all_talking_user_list"];

			// вставляем в массив с заменой
			$need_update_left_menu_user_list = array_replace($need_update_left_menu_user_list, $aggregated["need_update_left_menu_user_list"]);

			// отправляем уведомление на каждое сообщение
			self::_notifyUserOnMessageAdded($v, $aggregated["talking_user_list"], $conversation_type, $conversation_name, $dynamic_row);
		}

		// отправляем ws о добавлении списка сообщений
		Domain_Conversation_Action_Message_SendWsOnMessageListReceived::do(
			$message_list, $conversation_type, $dynamic_row["messages_updated_version"], array_values($all_talking_user_list)
		);

		// обновляем левое меню один раз на весь лист
		self::_sendTaskUpdateLeftMenuIfNeeded($last_message, $need_update_left_menu_user_list, count($message_list));

		// отправляем в очередь ботам задачу
		$userbot_id_list = Type_Conversation_Meta_Extra::getUserbotList($conversation_extra);
		self::_addMessagesToUserbotQueue($userbot_id_list, $message_list, $conversation_type);

		// отправляем сообщения в intercom
		self::_addMessagesToIntercomQueue($message_list, $conversation_type);
	}

	/**
	 * собираем списки пользователей, для которых нужны специфичные действия
	 *
	 * @param array $message
	 * @param array $users
	 * @param array $conversation_extra
	 * @param array $user_mute_info
	 * @param int   $conversation_type
	 * @param array $not_send_ws_event_user_list
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 */
	protected static function _aggregateUserListsOnMessageAdd(array $message, array $users, array $conversation_extra, array $user_mute_info, int $conversation_type, array $not_send_ws_event_user_list, array $all_talking_user_list):array {

		$output = self::_makeOutputForAggregateUserListsOnMessageAdd();

		$created_at     = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);
		$sender_user_id = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$message_type   = Type_Conversation_Message_Main::getHandler($message)::getType($message);

		foreach ($users as $user_id => $user_item) {

			if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
				continue;
			}

			// если пользователю не нужно отправлять ws-событие
			if (in_array($user_id, $not_send_ws_event_user_list)) {
				continue;
			}

			// проверяем, был ли пользователь упомянут в сообщении, добавляем его к списку упомянутых пользователей
			$is_mention = Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id);
			if ($is_mention) {
				$output["mentioned_user_list"][] = $user_id;
			}

			// уведомление + левое меню
			$talking_user_item = self::_makeTalkingUserItem(
				$user_id, $user_mute_info, $sender_user_id, $conversation_type, $message_type, $created_at, $is_mention
			);

			$output["talking_user_list"][] = $talking_user_item;

			if (!isset($all_talking_user_list[$user_id])) {
				$all_talking_user_list[$user_id] = $talking_user_item;
			}

			$output["all_talking_user_list"] = $all_talking_user_list;

			// добавляем элемент пользователя в массив, если ему нужно обновить левое меню
			$output["need_update_left_menu_user_list"] = self::_appendNeedUpdateLeftMenuUserListIfNeeded(
				$output["need_update_left_menu_user_list"],
				$user_id,
				$user_item,
				$conversation_type,
				$conversation_extra);
		}

		return $output;
	}

	// формируем output
	protected static function _makeOutputForAggregateUserListsOnMessageAdd():array {

		return [
			"talking_user_list"               => [],
			"need_update_left_menu_user_list" => [],
			"bot_list"                        => [],
			"mentioned_user_list"             => [],
			"all_talking_user_list"           => [],
		];
	}

	// формируем talking_user_item и добавляем в список получателей WS и push уведомлений
	protected static function _makeTalkingUserItem(int $user_id, array $user_mute_info, int $sender_user_id, int $conversation_type, int $message_type, int $created_at, bool $is_mention):array {

		// проверяем если в муте
		$is_need_push = !Domain_Conversation_Entity_Dynamic::isMuted($user_mute_info, $user_id, $created_at);

		// нужен ли принудительный пуш для пользователя
		$is_need_user_force_push = $is_mention;

		// если пользователь отправитель сообщения, сообщение системное или сообщение типа звонок - то пуш не нужен
		if (($user_id == $sender_user_id && $message_type != CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST)
			|| $message_type == CONVERSATION_MESSAGE_TYPE_SYSTEM
			|| $message_type == CONVERSATION_MESSAGE_TYPE_CALL
			|| $message_type == CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE
			|| $conversation_type == CONVERSATION_TYPE_PUBLIC_DEFAULT) {
			$is_need_push = 0;
		}

		// если сообщение-Напоминание, то пуш нужен
		if ($message_type == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND) {
			$is_need_push = 1;
		}

		return Gateway_Bus_Sender::makeTalkingUserItem($user_id, $is_need_push, $is_need_user_force_push);
	}

	// добавляем элемент пользователя в массив, если ему нужно обновить левое меню
	protected static function _appendNeedUpdateLeftMenuUserListIfNeeded(array $output, int $user_id, array $user_item, int $conversation_type, array $conversation_extra):array {

		// если это тип диалога, для которого левое меню не обязательно для обновления
		// то оставляем массив безизменным
		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {
			return $output;
		}

		// если пользователь бот, то обновлять ему не нужно
		if (Type_Conversation_Meta_Extra::isBot($conversation_extra, $user_id)) {
			return $output;
		}

		$output[$user_id] = $user_item;
		return $output;
	}

	// отправляем задачу в phphooker на обновление left_menu записей участников
	protected static function _sendTaskUpdateLeftMenuIfNeeded(array $message, array $need_update_left_menu_user_list, int $messages_count = 1):void {

		if (!Type_Conversation_Message_Main::getHandler($message)::isNeedUpdateLeftMenu($message)) {
			return;
		}

		// получаем conversation_map
		$message_map      = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		Type_Phphooker_Main::updateUserDataOnMessageAdd($conversation_map, $message, $need_update_left_menu_user_list, $messages_count);
	}

	/**
	 * добавляем сообщение в очередь на отправку
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @long - множество действий
	 */
	protected static function _addMessagesToUserbotQueue(array $userbot_id_list, array $message_list, int $conversation_type):void {

		if (count($userbot_id_list) == 0) {
			return;
		}

		$message_text_list = [];
		foreach ($message_list as $message) {

			// если это не текстовое сообщение, то пропускаем
			if (!Type_Conversation_Message_Main::getHandler($message)::isText($message)) {
				continue;
			}

			// если первый символ строки начинается со слэша
			$message_text = Type_Conversation_Message_Main::getHandler($message)::getText($message);
			if (Domain_Userbot_Entity_Userbot::isFormatCommand($message_text)) {

				$message_map                     = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
				$message_text_list[$message_map] = $message_text;
			}
		}

		if (count($message_text_list) == 0) {
			return;
		}

		$first_message = reset($message_list);

		// получаем conversation_map (для single-диалогов пустой)
		$conversation_map = "";
		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {

			$message_map      = Type_Conversation_Message_Main::getHandler($first_message)::getMessageMap($first_message);
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		}

		// получаем отправителя
		$sender_id = Type_Conversation_Message_Main::getHandler($first_message)::getSenderUserId($first_message);

		// отправляем задачу выполнить команду, если таковая имелась
		$event = Type_Event_Userbot_OnMessageReceived::create($userbot_id_list, $sender_id, $message_text_list, $conversation_map);
		Gateway_Event_Dispatcher::dispatch($event);
	}

	/**
	 * Добавляем сообщения в очередь на отправку в intercom
	 *
	 * @param array $message_list
	 * @param int   $conversation_type
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @long - множество действий
	 */
	protected static function _addMessagesToIntercomQueue(array $message_list, int $conversation_type):void {

		// отправляем только из чата службы поддержки
		if (!Type_Conversation_Meta::isGroupSupportConversationType($conversation_type)) {
			return;
		}

		// если отправитель не человек - не отправляем, иначе вечный цикл, что оператор сам себе шлет сообщения
		$first_message  = reset($message_list);
		$sender_user_id = Type_Conversation_Message_Main::getHandler($first_message)::getSenderUserId($first_message);
		if ($sender_user_id < 1) {
			return;
		}

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$sender_user_id], false);
		if (!Type_User_Main::isHuman($user_info_list[$sender_user_id]->npc_type)) {
			return;
		}

		$to_intercom_message_list  = [];
		$file_map_message_map_list = [];
		foreach ($message_list as $message) {

			// если это текстовое сообщение
			if (Type_Conversation_Message_Main::getHandler($message)::isText($message)) {

				$message_map                            = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
				$to_intercom_message_list[$message_map] = [
					"text"           => Type_Conversation_Message_Main::getHandler($message)::getText($message),
					"type"           => "text",
					"sender_user_id" => Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
					"message_key"    => Message::doEncrypt($message_map),
				];
				continue;
			}

			// если это файловое сообщение
			if (Type_Conversation_Message_Main::getHandler($message)::isFile($message)) {

				$file_map                               = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
				$message_map                            = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
				$file_map_message_map_list[$file_map]   = $message_map;
				$to_intercom_message_list[$message_map] = [
					"text"           => "",
					"type"           => "file",
					"sender_user_id" => Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
					"message_key"    => Message::doEncrypt($message_map),
				];
				continue;
			}
		}

		if (count($to_intercom_message_list) < 1) {
			return;
		}

		// если есть файловые сообщения
		if (count($file_map_message_map_list) > 0) {

			$file_info_list = Gateway_Socket_FileBalancer::getFileList(array_keys($file_map_message_map_list));
			foreach ($file_info_list as $item) {

				$message_map                                    = $file_map_message_map_list[$item["file_map"]];
				$to_intercom_message_list[$message_map]["text"] = $item["url"];
			}
		}

		// получаем conversation_key
		$first_message    = reset($message_list);
		$message_map      = Type_Conversation_Message_Main::getHandler($first_message)::getMessageMap($first_message);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		$sender_id = Type_Conversation_Message_Main::getHandler($first_message)::getSenderUserId($first_message);
		Type_User_ActionAnalytics::send($sender_id, Type_User_ActionAnalytics::WRITE_TO_SUPPORT);

		// отправляем в очередь на отправку в intercom
		Gateway_Socket_Intercom::addMessageListToQueue(
			\CompassApp\Pack\Conversation::doEncrypt($conversation_map),
			getIp(),
			\BaseFrame\System\UserAgent::getUserAgent(),
			array_values($to_intercom_message_list)
		);
	}

	// отправляем WS событие онлайн и push уведомления офлайн пользователям через go_talking_handler микросервис
	protected static function _notifyUserOnMessageAdded(array $message, array $talking_user_list, int $conversation_type, string $conversation_name, array $dynamic_row):void {

		// если диалог Найма, то отправка ws-события не нужна
		if (Type_Conversation_Meta::isHiringConversation($conversation_type)) {
			return;
		}

		// формируем объект пуш уведомления, если того требует логика
		$push_data = Domain_Conversation_Action_Message_GetPushData::do($message, $conversation_type, $conversation_name);

		$temp              = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message);
		$formatted_message = Apiv1_Format::conversationMessage($temp);
		$ws_users          = Type_Conversation_Message_Main::getHandler($message)::getUsers($message);

		if (Type_Conversation_Message_Main::getHandler($message)::isSystemBotText($message)) {
			$formatted_message = Apiv1_Format::prepareConversationTextMessageForNewClient($formatted_message);
		} elseif (Type_Conversation_Message_Main::getHandler($message)::isSystemBotFile($message)) {
			$formatted_message = Apiv1_Format::prepareConversationFileMessageForNewClient($formatted_message);
		}

		Gateway_Bus_Sender::conversationMessageReceived($talking_user_list, $formatted_message, $dynamic_row["messages_updated_version"], $push_data, $ws_users);
	}

	// инкрментим статистику
	protected static function _incStatAfterMessageAdd(int $user_id, array $message):void {

		switch (Type_Conversation_Message_Main::getHandler($message)::getType($message)) {

			case CONVERSATION_MESSAGE_TYPE_FILE:

				$file_map = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
				if (File::getFileSource($file_map) == FILE_SOURCE_MESSAGE_VOICE) {

					Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::VOICE, $user_id);
					Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_VOICE);
					return;
				}

				Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::FILE, $user_id);
				Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_FILE);
				break;

			default:
		}
	}

	/**
	 * Подготовить сообщения для репоста
	 *
	 * @param int    $user_id
	 * @param string $donor_conversation_map
	 * @param array  $message_map_list
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Message_IsFromDifferentConversation
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_MessageList_IsEmpty
	 */
	public static function prepareMessageListForRepost(int $user_id, string $donor_conversation_map, array $message_map_list):array {

		// проверяем, что пришли сообщения из одного чата
		foreach ($message_map_list as $message_map) {

			if (\CompassApp\Pack\Message\Conversation::getConversationMap($message_map) !== $donor_conversation_map) {
				throw new Domain_Conversation_Exception_Message_IsFromDifferentConversation("message map list is not from one conversation");
			}
		}

		$block_list = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		// подготавливаем выбранные сообщения к репосту
		$reposted_message_list = self::_getPreparedRepostedMessageListV2($message_map_list, $block_list, $user_id);

		// если список сообщений для репоста оказался пуст, то выдаем exception cs_MessageList_IsEmpty
		self::_throwIfEmptyMessageList($reposted_message_list);

		$prepared_message_list = [];

		// готовим сообщения для репоста
		foreach ($reposted_message_list as $reposted_message) {

			// апгрейдим список сообщений для репоста (например для звонков получаем их номер; продолжительность)
			$reposted_message = self::_upgradeCallMessageList($user_id, $reposted_message);

			// подготавливаем сообщения для репоста
			$prepared_message_list[] = $reposted_message;
		}

		return $prepared_message_list;
	}

	// репост сообщений в диалог
	public static function addRepostV2(int $user_id, string $text, string $client_message_id, array $message_map_list, string $conversation_map, array $receiver_meta_row, array $mention_user_id_list, string $platform):array {

		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);
		$block_list             = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		$repost_message_list = [];

		// подготавливаем выбранные сообщения к репосту
		$reposted_message_list = self::_getPreparedRepostedMessageListV2($message_map_list, $block_list, $user_id);

		// если список сообщений для репоста оказался пуст, то выдаем exception cs_MessageList_IsEmpty
		self::_throwIfEmptyMessageList($reposted_message_list);

		// репостим сообщения
		foreach ($reposted_message_list as $k => $reposted_message) {

			// апгрейдим список сообщений для репоста (например для звонков получаем их номер; продолжительность)
			$reposted_message = self::_upgradeCallMessageList($user_id, $reposted_message);

			// текст должен быть только у первого сообщения - у остальных убираем
			if ($k != 0) {
				$text = "";
			}

			// подготавливаем сообщения типа репост
			$message          = Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost(
				$user_id, $text, $client_message_id . "_" . "$k", $reposted_message, $platform
			);
			$prepared_message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

			// добавляем репост в диалог и выполняем все необходимые действия после репоста
			$repost_message = self::_addMessage($prepared_message, $conversation_map, $receiver_meta_row);
			self::doAfterRepost($user_id, [$repost_message], $donor_conversation_map, $receiver_meta_row, "row178");
			$repost_message_list[] = $repost_message;
		}

		return $repost_message_list;
	}

	// получить список подготовленных для репоста сообщений
	protected static function _getPreparedRepostedMessageListV2(array $message_map_list, array $block_list, int $user_id):array {

		// получаем сообщения
		$reposted_message_list = [];
		$message_count         = 0;
		foreach ($message_map_list as $v) {

			try {
				$reposted_message = self::_getRepostedMessage($block_list, $v, $user_id);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// подготавливаем сообщение к репосту
			$reposted_message_list = Type_Message_Utils::prepareMessageForRepostOrQuoteV2($reposted_message, $reposted_message_list);

			// инкрементим количество выбранных для репоста сообщений
			$message_count++;

			// для репоста/цитаты подсчитываем также сообщения в репостнутых/процитированных
			$message_count = self::_incMessageListCountIfRepostOrQuote($message_count, $reposted_message);

			// если достигли лимита сообщений для репоста - выдаём exception cs_Message_Limit
			self::_throwIfExceededSelectedMessageLimit($message_count);
		}

		// чанкуем сообщения для репоста
		$chunk_reposted_message_list = array_chunk($reposted_message_list, self::MAX_MESSAGES_COUNT_IN_CHUNK);

		// подготавливаем чанки с репостами
		$prepare_chunk_data_message_list = self::_prepareChunkRepostedAndQuotedMessageList($chunk_reposted_message_list);

		// подготавливаем списки сообщений с репостами
		return self::_prepareRepostOrQuoteMessageList($prepare_chunk_data_message_list);
	}

	// репост сообщений в диалог
	public static function addRepost(int $user_id, string $client_message_id, string $text, array $message_map_list, string $conversation_map, array $receiver_meta_row, bool $is_add_repost_quote, array $mention_user_id_list, string $platform):array {

		// если старая версия
		if (!$is_add_repost_quote) {

			return self::addRepostOldVersion(
				$user_id,
				$client_message_id,
				$text,
				$message_map_list,
				$conversation_map,
				$receiver_meta_row,
				$mention_user_id_list,
				$platform);
		}

		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);
		$block_list             = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		// подготавливаем выбранные сообщения к репосту
		$reposted_message_list = self::_getPreparedRepostedMessageList($message_map_list, $block_list, $user_id);

		// если список сообщений для репоста оказался пуст, то выдаем exception cs_MessageList_IsEmpty
		self::_throwIfEmptyMessageList($reposted_message_list);

		// апгрейдим список сообщений для репоста (например для звонков получаем их номер; продолжительность)
		$reposted_message_list = self::_upgradeCallMessageList($user_id, $reposted_message_list);

		// сортируем сообщения по message_index
		$reposted_message_list = self::_doSortMessageListByMessageIndex($reposted_message_list);

		// подготавливаем сообщения типа репост
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost($user_id, $text, $client_message_id, $reposted_message_list, $platform);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		// выполняем репост
		return self::_doRepost($message, $user_id, $receiver_meta_row, $conversation_map, $donor_conversation_map);
	}

	// получить список подготовленных для репоста сообщений
	protected static function _getPreparedRepostedMessageList(array $message_map_list, array $block_list, int $user_id):array {

		$prepared_reposted_message_list = [];
		$message_count                  = 0;
		foreach ($message_map_list as $v) {

			try {
				$reposted_message = self::_getRepostedMessage($block_list, $v, $user_id);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// подготавливаем сообщение к репосту
			$reposted_message = Type_Message_Utils::prepareMessageForRepostQuoteRemind($reposted_message);

			// добавляем сообщение в список того, что будем репостить
			$prepared_reposted_message_list = self::_addToMessageListByIndex($prepared_reposted_message_list, $reposted_message);

			// инкрементим количество выбранных для репоста сообщений
			$message_count++;

			// для репоста/цитаты подсчитываем также сообщения в репостнутых/процитированных
			$message_count = self::_incMessageListCountIfRepostOrQuote($message_count, $reposted_message);

			// если достигли лимита сообщений для репоста - выдаём exception cs_Message_Limit
			self::_throwIfExceededSelectedMessageLimit($message_count);
		}

		return $prepared_reposted_message_list;
	}

	// выбрасываем исключение, если сообщение нельзя репостнуть
	protected static function _throwIfNotAllowToRepost(array $reposted_message, int $user_id):void {

		if (Type_Conversation_Message_Main::getHandler($reposted_message)::isMessageDeleted($reposted_message)) {
			throw new cs_Message_IsDeleted();
		}

		if (!Type_Conversation_Message_Main::getHandler($reposted_message)::isAllowToRepost($reposted_message, $user_id)) {
			throw new ParamException("you have not permissions to repost this message");
		}
	}

	// выполняем репост сообщений
	protected static function _doRepost(array $message, int $user_id, array $receiver_meta_row, string $conversation_map, string $donor_conversation_map):array {

		// добавляем репост в диалог
		$repost_message = self::_addMessage($message, $conversation_map, $receiver_meta_row);

		// выполняем все необходимые действия после репоста
		self::doAfterRepost($user_id, [$repost_message], $donor_conversation_map, $receiver_meta_row, "row178");

		return [$repost_message];
	}

	// заносим информацию о репосте в таблицу с историей репостов
	public static function doAfterRepost(int $user_id, array $repost_message_list, string $donor_conversation_map, array $receiver_meta_row, string $stat_row = null):void {

		$message_map_list = [];
		foreach ($repost_message_list as $v) {

			$message_map        = Type_Conversation_Message_Main::getHandler($v)::getMessageMap($v);
			$message_map_list[] = $message_map;
		}

		Type_Conversation_RepostRel::addList($donor_conversation_map, $receiver_meta_row["conversation_map"], $message_map_list, $user_id);

		if (!is_null($stat_row)) {
			Gateway_Bus_Statholder::inc("messages", $stat_row);
		}
	}

	// репост сообщений в диалог (старая версия, не позволяющая репостить репосты)
	public static function addRepostOldVersion(int $user_id, string $client_message_id, string $text, array $message_map_list, string $conversation_map, array $receiver_meta_row, array $mention_user_id_list, string $platform):array {

		// получаем conversation_map диалога отправителя
		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);

		// получаем список сообщений для репоста
		// вся его работа показана (и переделана) выше
		$reposted_message_list = self::_getPreparedRepostedMessageListLegacy($user_id, $message_map_list, $donor_conversation_map);

		// разбиваем массив на блоки по 15 сообщений
		$chunked_reposted_message_list = array_chunk($reposted_message_list, self::MAX_MESSAGES_COUNT_IN_CHUNK);

		// проходимся по все блокам, отправляем по одному
		$message_prepare_list = [];
		foreach ($chunked_reposted_message_list as $k => $v) {

			// чтобы текст был только у первого сообщения
			if ($k != 0) {
				$text = "";
			}

			// формируем свой $client_message_id для каждого сообщения на которое разбиваем
			$chunked_client_message_id = $client_message_id . "_" . $k;

			// подготавливаем сообщения типа репост
			$message                = Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost(
				$user_id,
				$text,
				$chunked_client_message_id,
				$v,
				$platform);
			$message_prepare_list[] = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}
		$repost_message_list = self::_addMessageListLocalOrBroadcast($message_prepare_list, $conversation_map, $receiver_meta_row);

		// выполняем все необходимые действия после репоста
		self::doAfterRepost($user_id, $repost_message_list, $donor_conversation_map, $receiver_meta_row, "row178");

		return $repost_message_list;
	}

	// получаем отсортированный список репостнутых сообщений
	protected static function _getPreparedRepostedMessageListLegacy(int $user_id, array $message_map_list, string $donor_conversation_map):array {

		$block_list            = self::_getBlockListRow($message_map_list, $donor_conversation_map);
		$reposted_message_list = [];

		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($v);
			if (!isset($block_list[$block_id])) {
				throw new ParamException("message block not exist");
			}

			$reposted_message = Domain_Conversation_Entity_Message_Block_Message::get($v, $block_list[$block_id]);
			self::_throwIfNotAllowToRepostLegacy($reposted_message, $user_id);

			if (Type_Conversation_Message_Main::getHandler($reposted_message)::isFile($reposted_message)) {
				$reposted_message = Type_Conversation_Message_Main::getHandler($reposted_message)::setNewFileUid($reposted_message);
			}

			$message_index                         = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($reposted_message["message_map"]);
			$reposted_message_list[$message_index] = $reposted_message;
		}

		ksort($reposted_message_list);
		return array_values($reposted_message_list);
	}

	// метод для отправки сообщения в диалог
	protected static function _addMessageListLocalOrBroadcast(array $message_list, string $conversation_map, array $meta_row):array {

		// добавляем сообщения в диалог локально
		$repost_message_list = [];
		foreach ($message_list as $v) {

			$repost_message_list[] = self::addMessage(
				$conversation_map,
				$v,
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]);
		}

		return $repost_message_list;
	}

	// репост сообщений в диалог
	public static function addRepostLegacy(int $user_id, string $client_message_id, string $text, array $message_map_list, string $conversation_map, array $receiver_meta_row, array $mention_user_id_list, string $platform):array {

		// получаем conversation_map диалога отправителя
		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);

		// получаем список сообщений для репоста
		$reposted_message_list = self::_getRepostedMessageListLegacy($user_id, $message_map_list, $donor_conversation_map);

		// апгрейдим список сообщений для репоста (например для звонков получаем их номер; продолжительность)
		$reposted_message_list = self::_upgradeCallMessageList($user_id, $reposted_message_list);

		// репостим сообщения в диалог
		$repost_message = self::_addRepostLegacy(
			$user_id,
			$conversation_map,
			$receiver_meta_row,
			$text,
			$client_message_id,
			$reposted_message_list,
			$mention_user_id_list,
			$platform);
		self::_doAfterRepostLegacy($user_id, $repost_message, $donor_conversation_map, $receiver_meta_row);
		return $repost_message;
	}

	// получаем отсортированный список репостнутых сообщений
	protected static function _getRepostedMessageListLegacy(int $user_id, array $message_map_list, string $donor_conversation_map):array {

		$donor_dynamic_row     = self::_tryGetDynamicRowIfNotLockedLegacy($donor_conversation_map);
		$reposted_message_list = [];
		foreach ($message_map_list as $v) {

			$block_row        = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($donor_conversation_map, $v, $donor_dynamic_row);
			$reposted_message = Domain_Conversation_Entity_Message_Block_Message::get($v, $block_row);
			self::_throwIfMessageIsDeleted($reposted_message);
			self::_throwIfNotAllowToRepostLegacy($reposted_message, $user_id);

			if (Type_Conversation_Message_Main::getHandler($reposted_message)::isFile($reposted_message)) {
				$reposted_message = Type_Conversation_Message_Main::getHandler($reposted_message)::setNewFileUid($reposted_message);
			}

			$message_index                         = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($reposted_message["message_map"]);
			$reposted_message_list[$message_index] = $reposted_message;
		}

		ksort($reposted_message_list);
		return array_values($reposted_message_list);
	}

	// получаем динамик диалога если он не заблокирован
	protected static function _tryGetDynamicRowIfNotLockedLegacy(string $conversation_map):array {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		if ($dynamic_row["is_locked"] == 1) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		}

		return $dynamic_row;
	}

	// бросаем исключение если сообщение было удалено
	protected static function _throwIfMessageIsDeleted(array $message):void {

		if (Type_Conversation_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}
	}

	// метод для отправки сообщения в диалог
	protected static function _addRepostLegacy(int $user_id, string $conversation_map, array $meta_row, string $text, string $client_message_id, array $reposted_message_list, array $mention_user_id_list, string $platform):array {

		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost($user_id, $text, $client_message_id, $reposted_message_list, $platform);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		return self::addMessage($conversation_map, $message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]);
	}

	// заносим информацию о репосте в таблицу с историей репостов
	protected static function _doAfterRepostLegacy(int $user_id, array $repost_message, string $donor_conversation_map, array $receiver_meta_row):void {

		$message_map = Type_Conversation_Message_Main::getHandler($repost_message)::getMessageMap($repost_message);
		Type_Conversation_RepostRel::add($donor_conversation_map, $receiver_meta_row["conversation_map"], $message_map, $user_id);
		Gateway_Bus_Statholder::inc("messages", "row178");
	}

	/**
	 * Отмечает сообщение прочитанным.
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param int    $user_id
	 * @param array  $meta_row
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsNotAllowToMarkAsRead
	 */
	public static function markMessageAsRead(string $conversation_map, string $message_map, int $user_id, array $meta_row, string $local_date, string $local_time):array {

		// получаем dynamic диалога и проверяем что диалог не закрыт
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		if ($dynamic_row["is_locked"] == 1) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		}

		// отмечаем прочитанным
		$message = Type_Conversation_Message_Block::markMessageAsRead($message_map, $user_id);

		// готовим сообщение
		$prepared_message = self::_doPrepareMessage($conversation_map, $message);

		// отправляем вски
		self::_onMessageMarkedAsRead($user_id, $message, $prepared_message, $meta_row);

		// добавляем пользователю экранное время
		Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);

		return $prepared_message;
	}

	/**
	 * Выполняет действия после сохранения сообщения как прочитанной.
	 *
	 * @throws \parseException
	 */
	protected static function _onMessageMarkedAsRead(int $user_id, array $message, array $prepared_message, array $meta_row):void {

		// обявляем данные
		$talking_user_item = null;

		// перебираем пользователей диалога в поисках нужных данных для вски
		foreach ($meta_row["users"] as $k => $v) {

			if (intval($k) === $user_id && !Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
				$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
			}
		}

		// если по каким-то причинам пользователь не нашелся в мета записи
		// или сообщение для него недоступно по причине скрытости
		if (is_null($talking_user_item)) {
			return;
		}

		// отправляем ws-ивент о прочтении сообщения
		Gateway_Bus_Sender::conversationMessageMarkedAsRead([$talking_user_item], $prepared_message);
	}

	// редактируем текст сообщения
	public static function editMessageText(string $conversation_map, string $message_map, int $user_id, string $new_text, array $meta_row, array $mention_user_id_list, bool $is_force_edit = false):array {

		// получаем dynamic диалога и проверяем что диалог не закрыт
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		if ($dynamic_row["is_locked"] == 1) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		}

		// получаем роль пользователя в диалоге. Для single диалога эта роль всегда будет default
		$user_role = Type_Conversation_Meta_Users::getRole($user_id, $meta_row["users"]);

		/** @var Struct_Db_CompanyConversation_ConversationDynamic $dynamic */
		[
			$edited_message,
			$diff_mentioned_user_id_list,
			$dynamic,
		] = Type_Conversation_Message_Block::editMessageText($message_map, $user_id, $user_role, $new_text, $mention_user_id_list, $is_force_edit);

		$new_mentioned_user_id_list = [];
		foreach ($diff_mentioned_user_id_list as $mentioned_user_id) {

			// если упомянутый отправитель или отредактировавший то им не нужны уведомления
			if ($mentioned_user_id == $user_id || $mentioned_user_id == Type_Conversation_Message_Main::getHandler($edited_message)::getSenderUserId(
					$edited_message)
			) {
				continue;
			}
			$new_mentioned_user_id_list[] = $mentioned_user_id;
		}

		$prepared_message = self::_onMessageEdited(
			$conversation_map,
			$message_map,
			$meta_row,
			$edited_message,
			$new_mentioned_user_id_list,
			$mention_user_id_list,
			$diff_mentioned_user_id_list,
			$dynamic->messages_updated_version);

		// обрабатываем ссылки в тексте, если они есть
		Type_Preview_Producer::addTaskIfLinkExistInMessage(
			$user_id, $new_text, $edited_message["message_map"], $meta_row["users"]);

		// если нужно отмечаем время получения сообщения (если добавили меншен)
		$edited_at = floor(Type_Conversation_Message_Main::getHandler($edited_message)::getLastMessageTextEditedAt($edited_message) / 1000);
		Domain_Conversation_Action_Message_UpdateConversationAnswerState::doByEditMessage($conversation_map, $meta_row["type"], $user_id,
			$new_mentioned_user_id_list, $edited_at);

		// отправляем сообщение на индексацию обновленного текста
		Domain_Search_Entity_ConversationMessage_Task_Reindex::queueList([$edited_message], Locale::getLocale());

		return $prepared_message;
	}

	/**
	 * выполняется при редактировании сообщения
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param array  $meta_row
	 * @param array  $message
	 * @param array  $new_mentioned_user_id_list
	 * @param array  $mention_user_id_list
	 * @param array  $diff_mentioned_user_id_list
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 */
	protected static function _onMessageEdited(string $conversation_map, string $message_map, array $meta_row, array $message, array $new_mentioned_user_id_list, array $mention_user_id_list, array $diff_mentioned_user_id_list, int $messages_updated_version):array {

		$need_update_users = [];
		foreach ($meta_row["users"] as $user_id => $v) {

			if (!Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
				$need_update_users[$user_id] = $v;
			}
		}

		// отправляем задачу на актуализацию left_menu в phphooker
		Type_Phphooker_Main::updateLeftMenuForUserOnMessageEdit($conversation_map, $message, $need_update_users, $new_mentioned_user_id_list);
		Gateway_Bus_Statholder::inc("messages", "row883");

		// формируем пуш-дату
		$push_data = self::_makeConversationMessagePushData($message, $meta_row);

		$talking_user_list = [];
		foreach ($need_update_users as $k => $v) {

			// уведомление + левое меню
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, false, in_array($k, $new_mentioned_user_id_list));
		}

		// если к сообщению прикреплен тред, то удаляем родительское сообщение из кэша
		try {

			Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);
			Type_Phphooker_Main::sendClearParentMessageCache($message_map);
		} catch (\cs_RowIsEmpty) {
			//ничего не делаем
		}

		$prepared_message = self::_doPrepareMessage($conversation_map, $message);

		// отправляем ws-ивент о редактировании сообщения
		Gateway_Bus_Sender::conversationMessageEdited(
			$talking_user_list,
			$message_map,
			$message,
			$prepared_message,
			$conversation_map,
			$mention_user_id_list,
			$diff_mentioned_user_id_list,
			$push_data,
			$messages_updated_version
		);

		return $prepared_message;
	}

	// формирует информацию для рассылки пушей
	protected static function _makeConversationMessagePushData(array $message, array $conversation_meta):array {

		// получим информацию из кэша
		$full_name     = "";
		$user_npc_type = 0;

		if (Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message) > 0) {

			$user_info     = Gateway_Bus_CompanyCache::getMember(Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message));
			$full_name     = $user_info->full_name;
			$user_npc_type = $user_info->npc_type;
		}
		$handler = Type_Conversation_Message_Main::getHandler($message);

		return Gateway_Bus_Pusher::makeConversationMessagePushData(
			\CompassApp\Pack\Message\Conversation::getConversationMap($message["message_map"]),
			$message["message_map"],
			$handler::getPushTitle($message, $conversation_meta["type"], $conversation_meta["conversation_name"], $full_name),
			$handler::getPushBody($message, $conversation_meta["type"], $full_name),
			$handler::getPushBodyLocale($message, $conversation_meta["type"], $full_name, $user_npc_type),
			$handler::getEventType($message),
			Type_Conversation_Utils::makeConversationMessagePushDataEventSubtype($conversation_meta["type"]),
			Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
			$user_npc_type,
			$conversation_meta["type"],
			Type_Conversation_Message_Main::getHandler($message)::getType($message));
	}

	// подготавливаем объект сообщения под форматирование
	protected static function _doPrepareMessage(string $conversation_map, array $message):array {

		// получаем block_id сообщения
		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message["message_map"]);

		[$reaction_user_list, $reaction_last_edited_at] = self::prepareReaction($message["message_map"], $conversation_map, $block_id);

		// получаем тред по сообщению
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message["message_map"]);
		$thread_rel       = Type_Conversation_ThreadRel::getThreadRelByMessageMap($conversation_map, $message["message_map"]);

		// подготавливаем сообщение к форматированию
		return Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy(
			$message, 0, $thread_rel, $reaction_user_list, $reaction_last_edited_at
		);
	}

	// подготавливаем объект сообщения под форматирование
	public static function prepareReaction(string $message_map, string $conversation_map, int $block_id):array {

		try {

			$reaction_count_block_row = Gateway_Db_CompanyConversation_MessageBlockReactionList::getOne($conversation_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [[], 0];
		}

		$reaction_user_list      = [];
		$reaction_last_edited_at = 0;

		$message_reaction_list = $reaction_count_block_row->getMessageReactionList($message_map);
		if (count($message_reaction_list) < 1) {
			return [[], 0];
		}

		foreach ($message_reaction_list as $reaction_name => $user_list) {

			asort($user_list);
			$reaction_user_list[$reaction_name] = array_keys($user_list);
			foreach ($user_list as $updated_at_ms) {
				$reaction_last_edited_at = max($reaction_last_edited_at, $updated_at_ms);
			}
		}

		// подготавливаем сообщение к форматированию
		return [$reaction_user_list, $reaction_last_edited_at];
	}

	/**
	 * проверить логику, может ли $user_id отправить сообщение в single диалог собеседнику
	 *
	 * @param string $conversation_map
	 * @param array  $meta_row
	 * @param int    $user_id
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws ParseFatalException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 */
	public static function checkIsAllowed(string $conversation_map, array $meta_row, int $user_id):void {

		// если все ок - просто выходим
		if ($meta_row["allow_status"] == ALLOW_STATUS_GREEN_LIGHT) {
			return;
		}

		// делаем основные проверки для диалога
		self::_throwIfConversationIsGroup($meta_row["type"]);

		// бросаем исключение если allow_status уже != 1
		self::_throwExceptionIfIsAllowedAlreadySet($meta_row["allow_status"]);

		// если все в порядке выходим
		if ($meta_row["allow_status"] != ALLOW_STATUS_NEED_CHECK) {
			return;
		}

		$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($user_id, $meta_row["users"]);
		if (Type_Conversation_Meta::isBot($meta_row["extra"], $user_id) || Type_Conversation_Meta::isBot($meta_row["extra"], $opponent_user_id)) {
			return;
		}

		// получаем информацию о пользователях
		$users_info_list = self::_getSingleConversationsOpponentsInfo($user_id, $opponent_user_id);
		[$user_info, $opponent_user_info] = [$users_info_list[$user_id], $users_info_list[$opponent_user_id]];

		// получаем информацию о пользователе если он npc
		self::_assertUserAbleToCreateSingle($user_info->npc_type);

		// проверяем, является ли оппонент пользовательским ботом
		if (Type_User_Main::isUserbot($opponent_user_info->npc_type)) {
			self::_doIsAllowedUserbotStatusCheck($conversation_map, $opponent_user_id, $opponent_user_info, $meta_row);
		}

		// проверяем allowed_status
		self::_doIsAllowedStatusDisabledCheck($conversation_map, $user_id, $opponent_user_info, $meta_row);
		self::_doIsAllowedStatusDeletedCheck($conversation_map, $user_id, $opponent_user_info, $meta_row);

		// выбрасываем исключение, если пользвоатель инициатор проверки – гость
		self::throwIfGuestAttemptToInitialConversation($user_info->npc_type, $user_id, $opponent_user_id, $users_info_list);

		// обновляем статус и если нужно привязываем пользователя
		$meta_row["allow_status"] = ALLOW_STATUS_GREEN_LIGHT;
		Type_Conversation_Single::setIsAllowedInMetaAndLeftMenu($conversation_map, $meta_row["allow_status"], $user_id, $opponent_user_id, $meta_row["extra"]);

		// привязываем пользователя если он не привязан
		self::_attachIfUserIsNotAttached($opponent_user_id, $user_id, $conversation_map, $meta_row);
		Gateway_Bus_Statholder::inc("messages", "row544");
	}

	// проверяем что диалог не является группой
	protected static function _throwIfConversationIsGroup(int $type):void {

		// тип диалога групповой
		if (Type_Conversation_Meta::isSubtypeOfGroup($type)) {
			throw new ParseFatalException("conversation with type group in method " . __METHOD__ . " genius :shrug:");
		}
	}

	// проверяем что диалог не заблокирован
	protected static function _throwExceptionIfIsAllowedAlreadySet(int $allow_status):void {

		// один из участников диалога заблокирован в системе (is_disabled = 1)
		if ($allow_status == ALLOW_STATUS_MEMBER_DISABLED) {
			throw new cs_Conversation_MemberIsDisabled();
		}

		// пользователь удалил аккаунт
		if ($allow_status == ALLOW_STATUS_MEMBER_DELETED) {
			throw new Domain_Conversation_Exception_User_IsAccountDeleted("user delete account");
		}

		// если пользовательский бот отключён
		if ($allow_status == ALLOW_STATUS_USERBOT_DISABLED) {
			throw new cs_Conversation_UserbotIsDisabled();
		}

		// если пользовательский бот удалён
		if ($allow_status == ALLOW_STATUS_USERBOT_DELETED) {
			throw new cs_Conversation_UserbotIsDeleted();
		}
	}

	/**
	 * Получаем информацию о пользователях собеседниках single диалога
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 *
	 * @return MemberStructMain[]
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 */
	protected static function _getSingleConversationsOpponentsInfo(int $user_id, int $opponent_user_id):array {

		return Gateway_Bus_CompanyCache::getMemberList([$user_id, $opponent_user_id]);
	}

	/**
	 * проверяем возможность создавать сингл-диалоги
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertUserAbleToCreateSingle(int $user_npc_type):void {

		if (!Type_User_Action::isAbleToPerform($user_npc_type, Type_User_Action::CREATE_SINGLE)) {
			throw new ParseFatalException("user have no permission to this action");
		}
	}

	/**
	 * проверяем статус нашего собеседника-бота
	 *
	 * @throws \returnException
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 */
	protected static function _doIsAllowedUserbotStatusCheck(string $conversation_map, int $user_id, MemberStructMain $opponent_user_info, array $meta_row):void {

		$status = Gateway_Socket_Company::getUserbotStatusByUserId($opponent_user_info->user_id);

		// если бот активен, то заканчиваем проверку
		if ($status == Domain_Userbot_Entity_Userbot::STATUS_ENABLE) {
			return;
		}

		// если выключен или удалён
		$is_allowed = $status == Domain_Userbot_Entity_Userbot::STATUS_DISABLE ? ALLOW_STATUS_USERBOT_DISABLED : ALLOW_STATUS_USERBOT_DELETED;
		Type_Conversation_Single::setIsAllowedInMetaAndLeftMenu(
			$conversation_map,
			$is_allowed,
			$user_id,
			$opponent_user_info->user_id,
			$meta_row["extra"]);

		switch ($status) {

			case Domain_Userbot_Entity_Userbot::STATUS_DISABLE:
				throw new cs_Conversation_UserbotIsDisabled();
			case Domain_Userbot_Entity_Userbot::STATUS_DELETE:
				throw new cs_Conversation_UserbotIsDeleted();
		}
	}

	// проверяем наш собеседник заблочен или нет
	protected static function _doIsAllowedStatusDisabledCheck(string $conversation_map, int $user_id, MemberStructMain $opponent_user_info, array $meta_row):void {

		// если собеседник задизеймблен
		if (!Member::isDisabledProfile($opponent_user_info->role)) {
			return;
		}

		$meta_row["allow_status"] = ALLOW_STATUS_MEMBER_DISABLED;
		Type_Conversation_Single::setIsAllowedInMetaAndLeftMenu(
			$conversation_map,
			$meta_row["allow_status"],
			$user_id,
			$opponent_user_info->user_id,
			$meta_row["extra"]);

		throw new cs_Conversation_MemberIsDisabled();
	}

	// проверяем удалил ли собеседник аккаунт
	protected static function _doIsAllowedStatusDeletedCheck(string $conversation_map, int $user_id, MemberStructMain $opponent_user_info, array $meta_row):void {

		// если собеседник удалил аккаунт
		if (!\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($opponent_user_info->extra)) {
			return;
		}

		$meta_row["allow_status"] = ALLOW_STATUS_MEMBER_DELETED;
		Type_Conversation_Single::setIsAllowedInMetaAndLeftMenu(
			$conversation_map,
			$meta_row["allow_status"],
			$user_id,
			$opponent_user_info->user_id,
			$meta_row["extra"]);

		throw new Domain_Conversation_Exception_User_IsAccountDeleted("user delete account");
	}

	// создаем или обновляем юзер дату
	protected static function _attachIfUserIsNotAttached(int $user_id, int $opponent_user_id, string $conversation_map, array $meta_row):void {

		if (!Type_Conversation_Meta_Users::isNotAttachedMember($user_id, $meta_row["users"])) {
			return;
		}

		Type_Conversation_Single::attachUser($conversation_map, $user_id, $opponent_user_id, $meta_row, false);
	}

	// получаем список реакции и их количество
	public static function getMessageReactionCountList(string $message_map):array {

		$conversation_map = self::_tryGetConversationMap([$message_map]);
		$dynamic_row      = self::_tryGetDynamicRowIfNotLocked($conversation_map);

		// получаем id блока сообщения
		$block_id = self::_getBlockId($message_map, $dynamic_row);

		// в зависимости от активности блока, в котором находится сообщение
		return self::_getReactionCountListIfHot($message_map, $conversation_map, $dynamic_row, $block_id);
	}

	// получаем список поставленных реакции и их количество, если сообщение еще горячее
	protected static function _getReactionCountListIfHot(string $message_map, string $conversation_map, array $dynamic_row, int $block_id):array {

		// проверяем, что сообщение не удалено
		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row, true);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);
		self::_throwIfMessageIsDelete($message);

		try {

			$message_block_reaction_row = Gateway_Db_CompanyConversation_MessageBlockReactionList::getOne($conversation_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		// проверяем есть ли реакции для нашего сообщения
		$reaction_data = $message_block_reaction_row->getMessageReactionList($message_map);
		if (count($reaction_data) < 1) {
			return [];
		}

		// достаем список реакций и их количество
		return $reaction_data;
	}

	// получить объект сообщения
	public static function getMessageData(int $user_id, string $message_map, bool $is_need_reactions = false):array {

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$dynamic_row      = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// получаем содержимое блоков и достаем сообщение
		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// получаем thread_map
		$thread_rel = Type_Conversation_ThreadRel::getThreadRelByMessageMap($conversation_map, $message_map);

		self::_throwIfMessageIsHidden($message, $user_id);
		self::_throwIfMessageIsClear($message, $dynamic_row, $user_id);

		// подготавливаем массив $message_data к ответу
		return self::_prepareOutput($message, $is_need_reactions, $thread_rel, $user_id);
	}

	// подготавливаем output для ответа в getMessage
	protected static function _prepareOutput(array $message, bool $is_need_reactions, array $thread_rel, int $user_id):array {

		// прикрепляем тред, если он есть
		if (isset($thread_rel[$message["message_map"]])) {

			$message["child_thread"]["thread_map"] = $thread_rel[$message["message_map"]]["thread_map"];

			$is_hidden = 0;
			if (in_array($user_id, $thread_rel[$message["message_map"]]["thread_hidden_user_list"])) {
				$is_hidden = 1;
			}
			if ($thread_rel[$message["message_map"]]["is_thread_hidden_for_all_users"] === 1) {
				$is_hidden = 1;
			}

			$message["child_thread"]["is_hidden"] = (int) $is_hidden;
		}

		$output = [
			"message"              => $message,
			"thread_rel"           => $thread_rel,
			"reaction_user_list"   => [],
			"last_reaction_edited" => 0,
		];

		if ($is_need_reactions) {
			$output = Type_Conversation_Reaction_Main::attachReactionList($output, $message);
		}

		return $output;
	}

	// хелпер для удаления нескольких сообщения
	public static function deleteMessageList(int   $user_id, string $conversation_map, int $conversation_type,
							     array $message_map_list, array $meta_row,
							     bool  $is_force_delete = false):array {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		self::_throwIfConversationIsLocked($dynamic_row);

		$message_map_list_grouped_by_block_id = self::_groupMessageMapListByBlockId($message_map_list);
		$participant_role                     = Type_Conversation_Meta_Users::getRole($user_id, $meta_row["users"]);

		// удаляем все сообщения
		/** @var Struct_Db_CompanyConversation_ConversationDynamic $dynamic */
		[$message_list_grouped_by_message_map, $dynamic] = self::_doDeleteMessageList(
			$conversation_map,
			$conversation_type,
			$message_map_list_grouped_by_block_id,
			$user_id,
			$meta_row,
			$participant_role,
			$is_force_delete);

		// отправляем ws ивент и лог об успешном удалении сообщений
		self::_sendEventAboutDeletedMessageList($conversation_map, $message_map_list, $meta_row["users"], $dynamic->messages_updated_version);

		// формируем список сообщений для возврата
		$message_list = [];
		foreach ($message_list_grouped_by_message_map as $v) {
			$message_list[] = $v;
		}

		// удаляем сообщение для всех пользователей
		Domain_Search_Entity_ConversationMessage_Task_Delete::queueList(array_column($message_list, "message_map"));
		return $message_list;
	}

	// группируем сообщения по блоку
	protected static function _groupMessageMapListByBlockId(array $message_map_list):array {

		$message_map_list_grouped_by_block_id = [];

		// группируем сообщения по id блока
		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($v);

			$message_map_list_grouped_by_block_id[$block_id][] = $v;
		}

		return $message_map_list_grouped_by_block_id;
	}

	/**
	 * удаляем сообщения
	 *
	 * @param string $conversation_map
	 * @param int    $conversation_type
	 * @param array  $message_map_list_grouped_by_block_id
	 * @param int    $user_id
	 * @param array  $users
	 * @param int    $user_role
	 * @param bool   $is_force_delete
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 */
	protected static function _doDeleteMessageList(string $conversation_map, int $conversation_type, array $message_map_list_grouped_by_block_id,
								     int    $user_id, array $meta_row, int $user_role, bool $is_force_delete = false):array {

		// проходимся по всем сообщениям сгруппированным по block_id и формируем массивы
		$block_row_grouped_by_block_id       = [];
		$message_list_grouped_by_message_map = [];
		$thread_map_list                     = [];
		$message_map_list_for_thread_delete  = [];
		$list_of_all_message_map             = [];
		foreach ($message_map_list_grouped_by_block_id as $block_id => $message_map_list) {

			$block_row = self::_doDeleteMessageListDependedByConversationType(
				$user_id,
				$conversation_map,
				$conversation_type,
				$block_id,
				$message_map_list,
				$user_role,
				$is_force_delete);

			$block_row_grouped_by_block_id[$block_id] = $block_row;

			foreach ($message_map_list as $message_map) {

				$deleted_message                                   = Domain_Conversation_Entity_Message_Block_Message::get(
					$message_map,
					$block_row_grouped_by_block_id[$block_id]);
				$message_list_grouped_by_message_map[$message_map] = $deleted_message;
				$list_of_all_message_map[]                         = Type_Conversation_Message_Main::getHandler($deleted_message)::getMessageMap(
					$deleted_message);
			}
		}

		// получаем треды из списка релейшенов по списку message map
		$thread_relation_list = Gateway_Db_CompanyConversation_MessageThreadRel::getThreadListByMessageMapList($conversation_map, $list_of_all_message_map);
		foreach ($thread_relation_list as $thread_relation_row) {

			$message_map_list_for_thread_delete[] = $thread_relation_row["message_map"];
			$thread_map_list[]                    = $thread_relation_row["thread_map"];
		}

		return self::_afterDeleteMessageList(
			$user_id,
			$conversation_map,
			$conversation_type,
			$message_list_grouped_by_message_map,
			$message_map_list_for_thread_delete,
			$thread_map_list,
			$meta_row);
	}

	// удаляем сообщения диалога в зависимости от его типа
	protected static function _doDeleteMessageListDependedByConversationType(int   $user_id, string $conversation_map, int $conversation_type, int $block_id,
													 array $message_map_list, int $user_role, bool $is_force_delete = false):array {

		// если это диалог публичный
		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {
			return Type_Conversation_Message_Block::setSystemDeletedMessageListInPublicConversation($conversation_type, $user_id, $user_role, $message_map_list);
		}

		// для всех остальных случаев делаем по дефолту
		return Type_Conversation_Message_Block::deleteMessageList(
			$message_map_list,
			$conversation_map,
			$conversation_type,
			$block_id,
			$user_id,
			$user_role,
			$is_force_delete);
	}

	/**
	 * после удаления сообщений удаляем файлы и треды которые были к ним прикреплены, а так же обновляем левое меню
	 *
	 * @long
	 * @return array
	 */
	protected static function _afterDeleteMessageList(int $user_id, string $conversation_map, int $conversation_type, array $message_list_grouped_by_message_map, array $message_map_list, array $thread_map_list, array $meta_row):array {

		// отправляем задачу на удаление тредов
		Domain_Conversation_Action_Message_Thread_DeleteList::do($thread_map_list, $message_map_list);

		// удаляем файлы
		$dynamic = Type_Conversation_Message_Block::onDeleteMessageListWithFile($conversation_map, $message_list_grouped_by_message_map);

		// удаляем превью
		Domain_Conversation_Entity_Preview_Main::setDeletedByConversationMessageList(array_keys($message_list_grouped_by_message_map));

		// актуализируем левое меню, если это диалог не типа public
		if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {

			$deleted_last_message = self::_getLastMessageForMessageDelete($message_list_grouped_by_message_map);

			// если удалены сообщения в групповом диалоге в котором отключено отображение удаленных сообщений
			if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type) && !Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($meta_row["extra"])) {
				Type_Phphooker_Main::updateLastMessageOnDeleteIfDisabledShowDeleteMessage($conversation_map, $deleted_last_message, $meta_row["users"]);
			} else {
				Type_Phphooker_Main::updateLastMessageOnMessageUpdate($conversation_map, $deleted_last_message, $meta_row["users"]);
			}
		}

		// если удалены сообщения типа респект, требовательность, рабочие часы, то удаляем их и в карточке
		self::_deleteInCardIfDeletedOfCardEntity($user_id, $message_list_grouped_by_message_map, $conversation_type);

		return [$message_list_grouped_by_message_map, $dynamic];
	}

	// получаем последнее сообщение из списка сообщений
	protected static function _getLastMessageForMessageDelete(array $message_list_grouped_by_message_map):array {

		// сортируем по block message index от большего к меньшему
		usort($message_list_grouped_by_message_map, static function(array $a, array $b) {

			$a = \CompassApp\Pack\Message\Conversation::getBlockMessageIndex(Type_Conversation_Message_Main::getHandler($a)::getMessageMap($a));
			$b = \CompassApp\Pack\Message\Conversation::getBlockMessageIndex(Type_Conversation_Message_Main::getHandler($b)::getMessageMap($b));
			return $a - $b;
		});

		return end($message_list_grouped_by_message_map);
	}

	// удаляем в карточке, если удалили сообщение сущности карточки
	protected static function _deleteInCardIfDeletedOfCardEntity(int $user_id, array $message_list_grouped_by_message_map, int $conversation_type):void {

		// если это диалог типа public
		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {

			// если это сообщения из диалога "Личный heroes" пытаемся удалить объект с зафиксированным временем
			self::_onDeleteWorkedHoursMessages($user_id, $message_list_grouped_by_message_map);
			return;
		}

		// проверяем, удалены сообщения типа Респект или Требовательность?
		// пробуем собрать список удаленных сообщение по типу сущности
		$message_list_grouped_by_entity = [];
		foreach ($message_list_grouped_by_message_map as $v) {

			if (Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalRespect($v)) {
				$message_list_grouped_by_entity[Type_Company_Default::RESPECT][] = $v;
			}

			if (Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalExactingness($v)) {
				$message_list_grouped_by_entity[Type_Company_Default::EXACTINGNESS][] = $v;
			}

			if (Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalAchievement($v)) {
				$message_list_grouped_by_entity[Type_Company_Default::ACHIEVEMENT][] = $v;
			}
		}

		// для каждого удаленного сообщения каждого типа собираем список id сущностей и удаляем их в карточке
		foreach ($message_list_grouped_by_entity as $entity_type => $message_list) {

			$card_entity_id_list_by_receiver_id = self::_getEntityDataListFromDeleteMessages($entity_type, $message_list);

			// выполняем сокет-запрос для удаления сущностей из карточки
			Gateway_Socket_Company::deleteCardEntityOnEmployeeCard($entity_type, $card_entity_id_list_by_receiver_id);
		}
	}

	// если удалили сообщения из диалога "Личный heroes", то пытаемся удалить объект с зафиксированным временем
	protected static function _onDeleteWorkedHoursMessages(int $user_id, array $message_list_grouped_by_message_map):void {

		// группируем сообщения по worked_hours_id
		$message_list_grouped_by_worked_hours_id = [];
		foreach ($message_list_grouped_by_message_map as $message_map => $message) {

			$handler_class = Type_Conversation_Message_Main::getHandler($message);

			// если это не worked_hours, то дальше
			if (!$handler_class::isContainAdditionalWorkedHours($message)) {
				continue;
			}

			// получаем worked_hours_id и группируем
			$worked_hours_id                                             = $handler_class::getAdditionalWorkedHoursId($message);
			$message_list_grouped_by_worked_hours_id[$worked_hours_id][] = $message_map;
		}

		// если по итогу совсем нет сообщений
		if (count($message_list_grouped_by_worked_hours_id) < 1) {
			return;
		}

		// пытаемся удалить объекты
		foreach ($message_list_grouped_by_worked_hours_id as $worked_hours_id => $message_map_list) {
			Type_Conversation_Public_WorkedHours::tryDelete($user_id, $worked_hours_id, $message_map_list);
		}
	}

	// получаем данные сущностей карточки из удаленных сообщений
	protected static function _getEntityDataListFromDeleteMessages(string $entity_type, array $message_list):array {

		$entity_id_list_by_receiver = [];
		foreach ($message_list as $v) {

			if ($entity_type == "exactingness" && Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalExactingness($v)) {

				$receiver_user_id                                = Type_Conversation_Message_Main::getHandler($v)::getAdditionalExactingnessReceiver($v);
				$exactingness_id                                 = Type_Conversation_Message_Main::getHandler($v)::getAdditionalExactingnessId($v);
				$entity_id_list_by_receiver[$receiver_user_id][] = $exactingness_id;
			}

			if ($entity_type == "respect" && Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalRespect($v)) {

				$receiver_user_id                                = Type_Conversation_Message_Main::getHandler($v)::getAdditionalRespectReceiver($v);
				$respect_id                                      = Type_Conversation_Message_Main::getHandler($v)::getAdditionalRespectId($v);
				$entity_id_list_by_receiver[$receiver_user_id][] = $respect_id;
			}

			if ($entity_type == "achievement" && Type_Conversation_Message_Main::getHandler($v)::isContainAdditionalAchievement($v)) {

				$receiver_user_id                                = Type_Conversation_Message_Main::getHandler($v)::getAdditionalAchievementReceiver($v);
				$achievement_id                                  = Type_Conversation_Message_Main::getHandler($v)::getAdditionalAchievementId($v);
				$entity_id_list_by_receiver[$receiver_user_id][] = $achievement_id;
			}
		}

		return $entity_id_list_by_receiver;
	}

	// отправляем ws-ивент об удалении списка сообщений
	protected static function _sendEventAboutDeletedMessageList(string $conversation_map, array $message_map_list, array $users, int $messages_updated_version):void {

		$talking_user_list = [];
		foreach ($users as $k => $v) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, false);
		}

		Gateway_Bus_Sender::conversationMessageListDeleted($talking_user_list, $message_map_list, $conversation_map, $messages_updated_version);
	}

	/**
	 * получаем ошибку для апи из checkIsAllowed для статусов
	 * cs_Conversation_MemberIsDisabled
	 *
	 * @param      $e
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	public static function getCheckIsAllowedError($e, bool $is_new_error = false):array {

		if ($is_new_error) {
			return self::_returnNewError($e);
		}

		return self::_returnOldError($e);
	}

	/**
	 * возвращаем новые ошибки
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected static function _returnNewError($e):array {

		if ($e instanceof cs_Conversation_MemberIsDisabled) {

			return [
				"error_code" => 532,
				"message"    => "You can't write to this conversation because your opponent is blocked in our system",
			];
		}
		if ($e instanceof Domain_Conversation_Exception_User_IsAccountDeleted) {

			return [
				"error_code" => 2118001,
				"message"    => "You can't write to this conversation because your opponent delete account",
			];
		}
		if ($e instanceof cs_Conversation_UserbotIsDisabled) {

			return [
				"error_code" => 2134001,
				"message"    => "You can't write to this conversation because userbot is disabled",
			];
		}
		if ($e instanceof cs_Conversation_UserbotIsDeleted) {

			return [
				"error_code" => 2134002,
				"message"    => "You can't write to this conversation because userbot is deleted",
			];
		}

		throw new ParseFatalException("Received unknown error in e");
	}

	/**
	 * возвращаем новые ошибки для socket-методов
	 *
	 * @param $e
	 *
	 * @return array
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	public static function getCheckIsAllowedErrorForSocket($e):array {

		if ($e instanceof cs_Conversation_MemberIsDisabled) {

			return [
				"error_code" => 10021,
				"message"    => "You can't write to this conversation because your opponent is blocked in our system",
			];
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	/**
	 * возвращаем старые ошибки
	 *
	 * @param $e
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 * @long
	 */
	protected static function _returnOldError($e):array {

		if ($e instanceof cs_Conversation_MemberIsDisabled) {

			return [
				"error_code" => 532,
				"message"    => "You can't write to this conversation because your opponent is blocked in our system",
			];
		}
		if ($e instanceof Domain_Conversation_Exception_User_IsAccountDeleted) {

			return [
				"error_code" => 2118001,
				"message"    => "You can't write to this conversation because your opponent delete account",
			];
		}
		if ($e instanceof cs_Conversation_UserbotIsDisabled) {

			return [
				"error_code" => 2134001,
				"message"    => "You can't write to this conversation because userbot is disabled",
			];
		}
		if ($e instanceof cs_Conversation_UserbotIsDeleted) {

			return [
				"error_code" => 2134002,
				"message"    => "You can't write to this conversation because userbot is deleted",
			];
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	// цитирование сообщений в диалог V2
	public static function addQuoteV2(int $user_id, string $text, string $client_message_id, array $message_map_list, string $conversation_map, array $receiver_meta_row, array $mention_user_id_list, string $platform):array {

		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);
		$block_list             = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		$quoted_message_list = [];

		// подготавливаем выбранные сообщения к цитированию
		$quote_message_list = self::_getPreparedQuotedMessageListV2($message_map_list, $block_list);

		// если список сообщений для цитаты оказался пуст, то выдаем exception cs_MessageList_IsEmpty
		self::_throwIfEmptyMessageList($quote_message_list);

		// цитируем сообщения
		foreach ($quote_message_list as $k => $quote_message) {

			// апгрейдим список сообщений для цитаты (например для звонков получаем их номер; продолжительность)
			$quote_message = self::_upgradeCallMessageList($user_id, $quote_message);

			// текст должен быть только у последнего сообщения - у остальных убираем
			$message_text = $k == count($quote_message_list) - 1 ? $text : "";

			// формируем сообщение массовой цитаты
			$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeMassQuote(
				$user_id,
				$message_text,
				$client_message_id . "_" . "$k",
				$quote_message,
				$platform,
				true);

			// подготавливаем сообщения типа репост
			$prepared_message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

			// репостим сообщения
			$quoted_message_list[] = self::_addMessage($prepared_message, $conversation_map, $receiver_meta_row);
		}

		return $quoted_message_list;
	}

	// получить список подготовленных для цитирования сообщений
	protected static function _getPreparedQuotedMessageListV2(array $message_map_list, array $block_list):array {

		// получаем сообщения
		$quoted_message_list = [];
		foreach ($message_map_list as $v) {

			try {
				$quoted_message = self::_getQuotedMessage($block_list, $v);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// подготавливаем сообщение к цитированию
			$quoted_message_list = Type_Message_Utils::prepareMessageForRepostOrQuoteV2($quoted_message, $quoted_message_list);
		}

		// чанкуем сообщения для цитаты
		$chunk_quoted_message_list = array_chunk($quoted_message_list, self::MAX_MESSAGES_COUNT_IN_CHUNK);

		// подготавливаем чанки с цитатами
		$prepare_chunk_data_message_list = self::_prepareChunkRepostedAndQuotedMessageList($chunk_quoted_message_list);

		// подготавливаем списки сообщений с цитатами
		return self::_prepareRepostOrQuoteMessageList($prepare_chunk_data_message_list);
	}

	// цитируем сообщения
	public static function addQuote(int $user_id, string $client_message_id, string $text, string $conversation_map, array $message_map_list, array $receiver_meta_row, array $mention_user_id_list, string $platform):array {

		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);
		$block_list             = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		// подготавливаем сообщения для цитирования
		$message_list = self::_getPreparedQuotedMessageList($message_map_list, $block_list);

		// если список сообщений для цитирования оказался пуст
		self::_throwIfEmptyMessageList($message_list);

		// апгрейдим список сообщений (например для звонков получаем их номер; продолжительность)
		$message_list = self::_upgradeCallMessageList($user_id, $message_list);

		// сортируем сообщения для цитирования по message_index
		$message_list = self::_doSortMessageListByMessageIndex($message_list);

		// создаем новую структуру цитаты
		$quote_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeMassQuote($user_id, $text, $client_message_id, $message_list, $platform, true);
		$quote_message = Type_Conversation_Message_Main::getHandler($quote_message)::addMentionUserIdList($quote_message, $mention_user_id_list);

		// добавляем цитату в диалог
		return self::_addMessage($quote_message, $conversation_map, $receiver_meta_row);
	}

	// получить список подготовленных для цитирования сообщений
	protected static function _getPreparedQuotedMessageList(array $message_map_list, array $block_list):array {

		$prepared_quoted_message_list = [];
		$message_count                = 0;
		foreach ($message_map_list as $v) {

			try {
				$quoted_message = self::_getQuotedMessage($block_list, $v);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// подготавливаем сообщение к цитированию
			$quoted_message = Type_Message_Utils::prepareMessageForRepostQuoteRemind($quoted_message);

			// добавляем сообщение в список того, что будем цитировать
			$prepared_quoted_message_list = self::_addToMessageListByIndex($prepared_quoted_message_list, $quoted_message);

			// инкрементим количество выбранных для цитирования сообщений
			$message_count++;

			// для репоста/цитаты подсчитываем также сообщения в репостнутых/процитированных
			$message_count = self::_incMessageListCountIfRepostOrQuote($message_count, $quoted_message);

			// если достигли лимита выбранных для цитирования сообщений - кидаем exception cs_Message_Limit
			self::_throwIfExceededSelectedMessageLimit($message_count);
		}

		return $prepared_quoted_message_list;
	}

	// получаем сообщение для цитаты
	protected static function _getQuotedMessage(array $block_list, string $message_map):array {

		// достаем сообщение для цитаты из блоки сообщений диалога
		$quoted_message = self::_getMessageFromMessageBlock($message_map, $block_list);

		// проверяем, что сообщение позволяет его процитировать
		self::_throwIfQuoteMessageIsNotAllowed($quoted_message);

		return $quoted_message;
	}

	// проверяет, можно ли совершать цитирование сообщения
	protected static function _throwIfQuoteMessageIsNotAllowed(array $message):void {

		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		if (!Type_Conversation_Message_Main::getHandler($message)::isAllowToQuoteNew($message)) {

			Gateway_Bus_Statholder::inc("messages", "row147");
			throw new ParamException("you have not permissions to quote this message");
		}
	}

	// добавляет диалог в избранное и шлет событие клиенту
	public static function addToFavorite(int $user_id, array $left_menu_row):array {

		return self::_changeFavoriteStatus(true, $left_menu_row);
	}

	// удаляет диалог из избранного и шлет событие клиенту
	public static function removeFromFavorite(int $user_id, array $left_menu_row):array {

		return self::_changeFavoriteStatus(false, $left_menu_row);
	}

	// изменяем статус избранности
	protected static function _changeFavoriteStatus(bool $is_favorite, array $left_menu_row):array {

		$formatted_left_menu = Type_Conversation_LeftMenu::setIsFavorite($is_favorite, $left_menu_row);

		Gateway_Bus_Sender::conversationIsFavoriteChanged($is_favorite, $left_menu_row, $formatted_left_menu);

		return $formatted_left_menu;
	}

	// метод для получения списка упомянутых из текста
	public static function getMentionUserIdListFromText(array $meta_row, string $text):array {

		$matches = [];

		// ищем из текста все упоминания вот пример матча: ["@"|160593|"Имя"]
		preg_match_all("/\[\"@\"\|(\d{1,20})\|\".*\"]/mU", $text, $matches);

		// если не нашли никого отдаем пустоту
		if (!isset($matches[1]) || count($matches[1]) < 1) {
			return [];
		}

		$filtered_mention_user_id_list = [];

		// проходимся по всем упомянутым пользователям
		foreach ($matches[1] as $user_id) {

			// проверяем что указанный id в string не больше значения PHP_INT_MAX
			if (self::_isNumberStringMorePhpIntMax($user_id)) {
				continue;
			}

			if (!Type_Conversation_Meta_Users::isExistInUsers($user_id, $meta_row["users"])) {
				continue;
			}

			$filtered_mention_user_id_list[] = $user_id;
		}
		return array_unique($filtered_mention_user_id_list);
	}

	// проверяем что указанный id в string не больше значения PHP_INT_MAX
	protected static function _isNumberStringMorePhpIntMax(string $user_id):bool {

		if (mb_strlen($user_id) > 19) {
			return true;
		}

		if (mb_strlen($user_id) < 19) {
			return false;
		}

		$number = (int) mb_substr($user_id, 0, 18);
		if ($number < 922337203685477580) {
			return false;
		}

		$number = (int) mb_substr($user_id, 18);
		if ($number <= 7) {
			return false;
		}

		return true;
	}

	// получаем все сообщения для перессылки из одного диалога в другой
	// сообщения полностью подготовлены для перессылки
	public static function getMessagesForForwarding(int $user_id, array $message_map_list, bool $is_need_special_action = false):array {

		$donor_conversation_map = self::_tryGetConversationMap($message_map_list);
		$block_list             = self::_getBlockListRow($message_map_list, $donor_conversation_map);

		// подготавливаем выбранные сообщения к перессылки
		$prepared_message_list = self::_prepareMessageListForForwarding($user_id, $message_map_list, $block_list, $is_need_special_action);

		// если список сообщений для репоста оказался пуст, то выдаем exception cs_MessageList_IsEmpty
		self::_throwIfEmptyMessageList($prepared_message_list);

		// сортируем сообщения по message_index
		$forwarding_message_list = self::_doSortMessageListByMessageIndex($prepared_message_list);

		// апгрейдим список сообщений для требовательности (например для звонков получаем их номер; продолжительность)
		return self::_upgradeCallMessageList($user_id, $forwarding_message_list);
	}

	// подготавливаем выбранные сообщения к перессылки
	protected static function _prepareMessageListForForwarding(int $user_id, array $message_map_list, array $block_list, bool $is_need_special_actions):array {

		$prepared_message_list = [];
		$message_count         = 0;
		foreach ($message_map_list as $v) {

			// достаем сообщение из блока сообщений диалога
			$forwarded_message = self::_getMessageFromMessageBlock($v, $block_list);
			if (Type_Conversation_Message_Main::getHandler($forwarded_message)::isMessageDeleted($forwarded_message)) {
				continue;
			}

			// если к сообщению прикреплено превью, то избавляемся от него
			$forwarded_message = Type_Conversation_Message_Main::getHandler($forwarded_message)::removePreview($forwarded_message);

			// если сообщение такого типа нельзя репостить, то и перессылать тоже не будем, ибо выбирают сообщения
			// для фиксирования времени по той же логике что и при репосте
			if (!Type_Conversation_Message_Main::getHandler($forwarded_message)::isAllowToRepost($forwarded_message, $user_id)) {
				throw new ParamException("you have not permissions to forward this message");
			}

			// подготавливаем сообщение к перессылке
			$forwarded_message = Type_Message_Utils::prepareMessageForwarding($forwarded_message, $is_need_special_actions);

			// устанавливаем новый client_message_id, чтобы не уперлись в проблему с дублированием сообщений
			$forwarded_message = Type_Conversation_Message_Main::getHandler($forwarded_message)::setClientMessageId($forwarded_message, generateUUID());

			// добавляем сообщение в список того, что будем перессылать
			$prepared_message_list = self::_addToMessageListByIndex($prepared_message_list, $forwarded_message);

			// инкрементим количество выбранных для перессылки сообщений
			$message_count++;

			// для репоста/цитаты/перессылки подсчитываем также сообщения в репостнутых/процитированных
			$message_count = self::_incMessageListCountIfRepostOrQuote($message_count, $forwarded_message);

			// если достигли лимита сообщений для перессылки - выдаём exception cs_Message_Limit
			self::_throwIfExceededSelectedMessageLimit($message_count);
		}

		return $prepared_message_list;
	}

	// фиксируем сообщения с отработанными часами в чат "Личный Heroes"
	public static function doForwardMessageList(int $user_id, string $conversation_map, array $message_list, int $worked_hours_id, string $day_start_at_iso, int $worked_hours_created_at):array {

		// получаем meta диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// привязываем объект worked_hours ко всем сообщениям из списка
		foreach ($message_list as $k => $v) {

			$message_list[$k] = Type_Conversation_Message_Main::getHandler($v)::attachWorkedHours(
				$v,
				$worked_hours_id,
				$day_start_at_iso,
				$worked_hours_created_at);
		}

		// разбиваем добавляемые сообщения на чанки и отправляем
		$chunked_forwarding_message_list = array_chunk($message_list, Type_Conversation_Message_Block::MESSAGE_PER_BLOCK_LIMIT);
		$message_list                    = [];
		foreach ($chunked_forwarding_message_list as $chunk_with_forwarding_message_list) {

			$temp         = Helper_Conversations::addMessageList(
				$conversation_map,
				$chunk_with_forwarding_message_list,
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"],
				false);
			$message_list = array_merge($message_list, $temp);
		}

		// еще раз на всякий случай отсортируем сообщения
		$message_list = self::_doSortMessageList($message_list);

		// сохраняем все фиксированные сообщения
		$message_map_list = array_column($message_list, "message_map");
		Type_Conversation_Public_WorkedHours::doAppendFixedMessageMap($user_id, $worked_hours_id, $message_map_list);

		return $message_list;
	}

	// сортируем отправленные сообщения
	protected static function _doSortMessageList(array $message_list):array {

		$message_list_by_index = [];
		foreach ($message_list as $message) {

			$message_index                         = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message["message_map"]);
			$message_list_by_index[$message_index] = $message;
		}

		ksort($message_list_by_index);
		return array_values($message_list_by_index);
	}

	// пробуем проявить Требовательность
	public static function tryExacting(int $sender_user_id, array $message_list, string $conversation_map, array $meta_row, int $week_count, int $month_count, int $created_at = null):array {

		if (ServerProvider::isOnPremise()) {
			throw new \ParseException("action not allowed on on-premise environment");
		}

		// выдаем Требовательность - добавляем сообщения-требовательность в группу Требовательность
		$new_message_list = self::addMessageList(
			$conversation_map,
			$message_list,
			$meta_row["users"],
			$meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"],
			false);

		// собираем данные для обновления message_map добавленного сообщения в требовательности карточки
		$exactingness_data_list_by_user_id = [];
		foreach ($new_message_list as $v) {

			// достаем id требовательности & получателя требовательности
			$exactingness_id  = Type_Conversation_Message_Main::getHandler($v)::getAdditionalExactingnessId($v);
			$receiver_user_id = Type_Conversation_Message_Main::getHandler($v)::getAdditionalExactingnessReceiver($v);

			// сохраняем message_map сообщения-требовательности за нужной требовательностью в карточке
			$exactingness_data_list_by_user_id[$receiver_user_id][$exactingness_id] = $v["message_map"];

			// отправляем ивент о том, что пользователь получил требовательность
			Gateway_Event_Dispatcher::dispatch(Type_Event_Member_OnUserReceivedEmployeeCardEntity::create(
				"exactingness", $v["message_map"], $sender_user_id, $receiver_user_id, $week_count, $month_count));
		}

		// закрепляем message_map добавленных сообщений за каждой из требовательностей
		$created_at = is_null($created_at) ? time() : $created_at;
		Gateway_Socket_Company::setMessageMapListForExactingnessList($exactingness_data_list_by_user_id, $created_at);

		return $new_message_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем сообщение для репоста
	protected static function _getRepostedMessage(array $block_list, string $message_map, int $user_id):array {

		// достаем сообщение для репоста из блока сообщений диалога
		$reposted_message = self::_getMessageFromMessageBlock($message_map, $block_list);

		// проверяем, что сообщение позволяет его репостнуть
		self::_throwIfNotAllowToRepost($reposted_message, $user_id);

		return $reposted_message;
	}

	// добавляем сообщение в диалог
	protected static function _addMessage(array $message, string $conversation_map, array $meta_row):array {

		return self::addMessage($conversation_map, $message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]);
	}

	// получаем conversation map из списка сообщений
	protected static function _tryGetConversationMap(array $message_map_list):string {

		return \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
	}

	// получаем блоки с сообщениями
	protected static function _getBlockListRow(array $message_map_list, string $donor_conversation_map):array {

		$donor_dynamic_row = self::_tryGetDynamicRowIfNotLocked($donor_conversation_map);
		[$block_list] = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByMessageMapList($donor_conversation_map, $donor_dynamic_row, $message_map_list);

		return $block_list;
	}

	// получаем динамик диалога если он не заблокирован
	protected static function _tryGetDynamicRowIfNotLocked(string $conversation_map):array {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		if ($dynamic_row["is_locked"] == 1) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		}

		return $dynamic_row;
	}

	// достаем сообщение из блока сообщений
	protected static function _getMessageFromMessageBlock(string $message_map, array $block_list):array {

		// получаем id блока, откуда этот message_map
		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		self::_throwIfNotExistBlock($block_list, $block_id);

		// достаем сообщение из блока сообщений
		return Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_list[$block_id]);
	}

	// выбрасываем исключение если не найдется блок с сообщениями
	protected static function _throwIfNotExistBlock(array $block_list, int $block_id):void {

		if (!isset($block_list[$block_id])) {
			throw new ParseFatalException("message block not exist");
		}
	}

	// добавляем сообщение в список сообщений для репоста
	protected static function _addToMessageListByIndex(array $message_list, array $message):array {

		$message_index                = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message["message_map"]);
		$message_list[$message_index] = $message;

		return $message_list;
	}

	// подготавливаем чанки с репостами/цитатами
	public static function _prepareChunkRepostedAndQuotedMessageList(array $chunk_message_list):array {

		// компануем по первоначальным сообщениям составляющим чанку
		$prepare_chunk_data_message_list = [];
		foreach ($chunk_message_list as $k1 => $message_list) {

			// счетчик родительских сообщений
			$iterate_parent_message = 0;

			foreach ($message_list as $k2 => $message) {

				// пропускаем ранее добавленные сообщения пустышки
				if ($message["message"]["type"] === self::MESSAGE_TYPE_EMPTY) {
					continue;
				}

				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["type"]              = $message["type"];
				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["message_list"][$k2] = $message["message"];
				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["parent_message"]    = $message["parent_message"];

				// убираем комментарий у пачки сообщений, если они были под общим родителем, но оказались в разных чанках
				if ($k1 != 0) {

					$previous_message_list = $chunk_message_list[$k1 - 1];
					$previous_last_message = array_pop($previous_message_list);

					// если нужно, убираем текст
					if ($previous_last_message["parent_message"] == $message["parent_message"]) {
						$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["parent_message"]["data"]["text"] = "";
					}
				}

				if (!isset($message_list[$k2 + 1]["parent_message"])) {
					continue;
				}

				if ($message["parent_message"] != $message_list[$k2 + 1]["parent_message"]) {
					$iterate_parent_message++;
				}
			}
		}

		return $prepare_chunk_data_message_list;
	}

	// выполняем сортировку сообщений по message_index
	protected static function _doSortMessageListByMessageIndex(array $message_list):array {

		ksort($message_list);
		return array_values($message_list);
	}

	// проверяем, что диалог не закрыт
	protected static function _throwIfConversationIsLocked(array $dynamic_row):void {

		if ($dynamic_row["is_locked"] == 1) {
			throw new cs_ConversationIsLocked();
		}
	}

	// подготавливаем списки сообщений с репостами/цитатами
	public static function _prepareRepostOrQuoteMessageList(array $prepare_chunk_data_message_list):array {

		// подготавливаем сообщения
		$prepared_chunk_message_list = [];
		foreach ($prepare_chunk_data_message_list as $k1 => $chunk_data_message_list) {

			foreach ($chunk_data_message_list as $data_message_list) {

				$prepared_chunk_message_list = Type_Conversation_Message_Main::getHandler($data_message_list["parent_message"])::makeRepostedOrQuotedMessageList(
					$data_message_list,
					$prepared_chunk_message_list,
					$k1);
			}
		}

		return $prepared_chunk_message_list;
	}

	// проверяем, что пользователь является участником диалога
	protected static function _throwIfUserIsNotConversationMember(int $user_id, array $users, string $namespace, string $row):void {

		if (!Type_Conversation_Meta_Users::isMember($user_id, $users)) {

			Gateway_Bus_Statholder::inc($namespace, $row);
			throw new ParamException("User is not conversation member");
		}
	}

	// выбрасываем исключение, если сообщение нельзя репостнуть
	protected static function _throwIfNotAllowToRepostLegacy(array $reposted_message, int $user_id):void {

		if (!Type_Conversation_Message_Main::getHandler($reposted_message)::isAllowToRepost($reposted_message, $user_id)) {

			Gateway_Bus_Statholder::inc("messages", "row177");
			throw new ParamException("you have not permissions to repost this message");
		}
	}

	// проверяем, что сообщение не было скрыто пользователем
	protected static function _throwIfMessageIsHidden(array $message, int $user_id):void {

		// если сообщение было скрыто пользователем
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
			throw new cs_Message_UserHaveNotPermission();
		}
	}

	// проверяем, что сообщение не было почищено в диалоге
	protected static function _throwIfMessageIsClear(array $message, array $dynamic_row, int $user_id):void {

		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"], $user_id);

		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {
			throw new cs_Message_UserHaveNotPermission();
		}
	}

	// проверяем, что сообщение не удалено
	protected static function _throwIfMessageIsDelete(array $message):void {

		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}
	}

	// метод для получения ключа mCache
	protected static function _getKey(string $conversation_map, string $client_message_id):string {

		return __CLASS__ . "_" . $conversation_map . "_" . $client_message_id;
	}

	// получаем id блока и выполняем проверку
	protected static function _getBlockId(string $message_map, array $donor_dynamic_row):int {

		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		if (!Domain_Conversation_Entity_Message_Block_Main::isExist($donor_dynamic_row, $block_id)) {
			throw new ParseFatalException("this message block is not exist");
		}

		return $block_id;
	}

	// инкрементим количество выбранных сообщений, если репост или цитата
	protected static function _incMessageListCountIfRepostOrQuote(int $message_count, array $reposted_message):int {

		if (Type_Conversation_Message_Main::getHandler($reposted_message)::isRepost($reposted_message)) {

			$reposted_message_list = Type_Conversation_Message_Main::getHandler($reposted_message)::getRepostedMessageList($reposted_message);
			$message_count         += Type_Conversation_Message_Main::getHandler($reposted_message)::getRepostedMessageCount($reposted_message_list);
		}

		if (Type_Conversation_Message_Main::getHandler($reposted_message)::isQuote($reposted_message)) {

			$quoted_message_list = Type_Conversation_Message_Main::getHandler($reposted_message)::getQuotedMessageList($reposted_message);
			$message_count       += Type_Conversation_Message_Main::getHandler($reposted_message)::getQuotedMessageCount($quoted_message_list);
		}

		return $message_count;
	}

	// выбрасываем исключение, если превысили лимит выбранных сообщений
	protected static function _throwIfExceededSelectedMessageLimit(int $message_count):void {

		if ($message_count > self::_MAX_SELECTED_MESSAGES_COUNT) {
			throw new cs_Message_Limit();
		}
	}

	// выбрасываем исключение, если список сообщений оказался пуст
	protected static function _throwIfEmptyMessageList(array $message_list):void {

		if (count($message_list) == 0) {
			throw new cs_MessageList_IsEmpty();
		}
	}

	// апгрейдим сообщения со звонками, если таковы есть
	protected static function _upgradeCallMessageList(int $user_id, array $message_list):array {

		// получаем массив репостнотых call_map; если таковых нет, то ничего не делаем
		$call_map_list = self::_getCallMapListFromMessageList($message_list);

		if (count($call_map_list) < 1) {
			return $message_list;
		}

		$call_info_grouped_by_call_map = self::_getCallInfo($user_id, $call_map_list);

		// привязываем информацию о звонках к репостнутым сообщениям
		foreach ($message_list as $k => $v) {

			if (Type_Conversation_Message_Main::getHandler($v)::getType($v) != CONVERSATION_MESSAGE_TYPE_CALL) {
				continue;
			}

			// привязываем инфу по репостнотому звонку
			$call_map         = Type_Conversation_Message_Main::getHandler($v)::getCallMap($v);
			$temp             = $call_info_grouped_by_call_map[$call_map];
			$message_list[$k] = Type_Conversation_Message_Main::getHandler($v)::attachRepostedCallInfo($v, $temp["call_report_id"], $temp["duration"]);
		}
		return $message_list;
	}

	// получаем массив call_map звонков из списка репостнутых сообщений
	protected static function _getCallMapListFromMessageList(array $reposted_message_list):array {

		$output = [];
		foreach ($reposted_message_list as $v) {

			if (Type_Conversation_Message_Main::getHandler($v)::getType($v) != CONVERSATION_MESSAGE_TYPE_CALL) {
				continue;
			}

			$output[] = Type_Conversation_Message_Main::getHandler($v)::getCallMap($v);
		}

		return $output;
	}

	// совершаем socket-запросы в модуль php_speaker для получения информации по звонку
	protected static function _getCallInfo(int $user_id, array $call_map_list):array {

		$output = [];

		[$status, $response] = Gateway_Socket_Speaker::doCall("calls.getBatchingInfo", ["call_map_list" => $call_map_list], $user_id);
		if ($status != "ok" || !isset($response["call_info_list"])) {
			throw new ReturnFatalException(__METHOD__ . ": unexpected response from php_speaker calls.getBatchingInfo");
		}

		// пробегаемся по результату и группируем ответ по call_map
		foreach ($response["call_info_list"] as $v) {
			$output[$v["call_map"]] = $v;
		}

		return $output;
	}

	/**
	 * Выбрасываем исключение в случае если гость пытается инициировать диалог
	 *
	 * @param int                                    $initiator_npc_type
	 * @param int                                    $initiator_user_id
	 * @param int                                    $opponent_user_id
	 * @param MemberStructShort[]|MemberStructMain[] $users_info_list
	 *
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws ParseFatalException
	 */
	public static function throwIfGuestAttemptToInitialConversation(int $initiator_npc_type, int $initiator_user_id, int $opponent_user_id, array $users_info_list):void {

		// если инициатором диалога является бот
		if (Type_User_Action::isValidForAction($initiator_npc_type, Type_User_Action::ATTACH_TO_BOT_LIST)) {

			// завершаем проверку
			return;
		}

		// получаем информацию
		$initiator_user_info = $users_info_list[$initiator_user_id];
		$opponent_user_info  = $users_info_list[$opponent_user_id];

		// если инициатор не гость, то ничего не проверяем дальше
		if (Member::ROLE_GUEST !== $initiator_user_info->role) {
			return;
		}

		// дальше в любом случае будет exception – сейчас нужно определить черту собеседника, на основе которой в api вернем нужную ошибку
		$opponent_trait = Domain_Conversation_Exception_Guest_AttemptInitialConversation::OPPONENT_TRAIT_SPACE_RESIDENT;

		// если собеседник гость
		if (Member::ROLE_GUEST === $opponent_user_info->role) {
			$opponent_trait = Domain_Conversation_Exception_Guest_AttemptInitialConversation::OPPONENT_TRAIT_GUEST;
		}

		// если собеседник бот
		if (Type_User_Main::isUserbot($opponent_user_info->npc_type)) {
			$opponent_trait = Domain_Conversation_Exception_Guest_AttemptInitialConversation::OPPONENT_TRAIT_BOT;
		}

		throw new Domain_Conversation_Exception_Guest_AttemptInitialConversation($opponent_trait);
	}
}
