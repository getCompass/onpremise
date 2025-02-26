<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;
use Compass\Conversation\Domain_Search_Entity_ThreadMessage_Task_Index;
use CompassApp\Pack\File;
use JetBrains\PhpStorm\ArrayShape;

/**
 * добавляет пул сообщений, под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения
 */
class Domain_Thread_Action_Message_AddList {

	protected const _CLIENT_MESSAGE_ID_CACHE_EXPIRE = 60 * 60;  // время жизни кэша в секундах

	/**
	 * выполняем действие
	 *
	 * @param string $thread_map
	 * @param array  $meta_row
	 * @param array  $raw_message_list
	 * @param array  $mentioned_users
	 * @param array  $additional_data
	 * @param array  $not_send_ws_event_user_list
	 *
	 * @return array
	 * @throws BlockException
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 * @long
	 */
	#[ArrayShape(["meta_row" => "array", "message_list" => "array"])]
	public static function do(string $thread_map, array $meta_row, array $raw_message_list, array $mentioned_users = [], array $additional_data = [], array $not_send_ws_event_user_list = [], bool $is_silent = false):array {

		// ожидаем как минимум одно сообщение
		if (count($raw_message_list) == 0) {
			throw new Domain_Thread_Exception_Message_ListIsEmpty(__CLASS__ . ": empty message list");
		}

		// проверяем и ругаемся, если тред только для чтения
		self::_throwIfThreadIsReadonly($meta_row, $raw_message_list[0]);

		// проверяем что тред не locked / readonly
		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);

		// проверяем не закрыт ли тред сейчас
		if ($dynamic_obj->is_locked == 1) {
			throw new BlockException(__METHOD__ . " thread is locked");
		}

		// удаляем дубликаты если они есть в одной пачке сообщений
		$raw_message_list = self::_removeDuplicateIfExistsWithinRawMessageList($raw_message_list);

		// добавляем сообщения в кэш
		foreach ($raw_message_list as $v) {

			self::_addDuplicateMessageCache($thread_map, $v);
		}

		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);

		// если тред кто то скрывал, открываем его обратно
		$message_count = (int) $meta_row["message_count"];
		if (count($dynamic_obj->user_hide_list) > 0
			|| ($message_count == 0 && $meta_row["parent_rel"]["type"] == PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE)) {

			$dynamic_obj->user_hide_list = [];
			Gateway_Db_CompanyThread_ThreadDynamic::set($thread_map, ["user_hide_list" => $dynamic_obj->user_hide_list]);

			Gateway_Socket_Conversation::revealThread($thread_map, $parent_conversation_map);
		}

		// добавляем пользователей в мету
		if (count($mentioned_users) > 0) {
			Helper_Threads::attachUsersToThread($meta_row, $mentioned_users);
		}

		// добавляем сообщение в тред
		$data     = Type_Thread_Message_Block::addMessageList($thread_map, $raw_message_list, $dynamic_obj);
		$meta_row = $data["meta_row"];

		$first_message = reset($data["message_list"]);

		$user_id = Type_Thread_Message_Main::getHandler($first_message)::getSenderUserId($first_message);

		// получаем source_parent_rel dynamic
		[$source_parent_rel_dynamic, $is_hiring_conversation_type, $is_support_conversation_type, $location_type] = Type_Thread_SourceParentDynamic::get(
			$meta_row["source_parent_rel"]
		);
		if ($is_support_conversation_type) {
			throw new ParamException("action not allowed");
		}

		$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);

		// отправляем эвенты и задачу на обновление thread_menu подписанным на тред пользователям
		if (!$is_hiring_conversation_type && !$is_silent) {

			// получаем список подписанных и отписанных пользователей
			$follower_row = self::_getFollowersRow($user_id, $meta_row, $additional_data);

			// получаем пользователей, которым необходимо отправить сообщение с тредом
			$receiver_user_list = Type_Thread_Main::getReceiverUserList(
				$meta_row, $follower_row, $source_parent_rel_dynamic, $dynamic_obj->user_mute_info, $not_send_ws_event_user_list
			);

			self::_notifyThreadUsersOnMessageAdd(
				$meta_row,
				$follower_row,
				$receiver_user_list,
				$data["message_list"],
				$source_parent_rel_dynamic,
				$additional_data,
				$parent_conversation_map,
				$threads_updated_version);
			self::_sendThreadMenuUpdateTask($meta_row, $follower_row, $receiver_user_list, $data["message_list"]);
		}

		// выполняем обработку для каждого сообщения
		self::_doActionsForEachMessage($user_id, $meta_row, $data["message_list"]);

		// сохраняем время ответа если нужно
		if (!$is_hiring_conversation_type && !$is_silent) {

			Domain_Search_Entity_ThreadMessage_Task_Index::queueList($data["message_list"], array_keys($receiver_user_list));

			Domain_Thread_Action_Message_UpdateConversationAnswerState::doBySendMessage(
				$parent_conversation_map,
				$location_type,
				$data["message_list"],
				$receiver_user_list,
				$follower_row
			);
		}

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incMessageSent($parent_conversation_map, $data["message_list"]);

		return self::_makeAddMessageOutput($meta_row, $data["message_list"]);
	}

	/**
	 * ругаемся, если тред только для чтения
	 *
	 * @throws \parseException
	 * @throws cs_ThreadIsReadOnly
	 */
	protected static function _throwIfThreadIsReadonly(array $thread_meta_row, array $message):void {

		// проверяем, если отправляется сообщение и отправитель бот-Напоминание, то скипаем проверку
		if (Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message) == REMIND_BOT_USER_ID) {
			return;
		}

		// проверяем, является ли тред только для чтения
		if ($thread_meta_row["is_readonly"] == 1) {
			throw new cs_ThreadIsReadOnly();
		}
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
	protected static function _addDuplicateMessageCache(string $thread_map, array $message):void {

		// получаем client_message_id и записываем в кэш
		$client_message_id = Type_Thread_Message_Main::getHandler($message)::getClientMessageId($message);

		try {
			ShardingGateway::cache()->add(self::_getKey($thread_map, $client_message_id), $message, self::_CLIENT_MESSAGE_ID_CACHE_EXPIRE);
		} catch (\cs_MemcacheRowIfExist) {
			throw new cs_Message_DuplicateClientMessageId();
		}
	}

	// метод для получения ключа mCache
	protected static function _getKey(string $thread_map, string $client_message_id):string {

		return __CLASS__ . "_" . $thread_map . "_" . $client_message_id;
	}

	/**
	 * Метод возвращает массив из подписанных и отписанных пользоваталей
	 * Так же тут происходит логика подписки отправителя сообщения к треду,
	 * если пользователь есть в $additional_data["exclude_follow_user_id_list"],
	 * то его не подпишет на тред
	 *
	 * @param int   $user_id
	 * @param array $meta_row
	 * @param array $additional_data
	 *
	 * @return array
	 */
	protected static function _getFollowersRow(int $user_id, array $meta_row, array $additional_data):array {

		// если список пользователей которых не нужно подписывать не существует, подпишем пользователя и вернем список
		if (!isset($additional_data["exclude_follow_user_id_list"])) {
			return Domain_Thread_Action_Follower_Follow::do([$user_id], $meta_row["thread_map"], $meta_row["parent_rel"]);
		}

		// если пользователя нет в списке пользователей которых не нужно подписывать, подпишем его и вернем список
		if (!in_array($user_id, $additional_data["exclude_follow_user_id_list"])) {
			return Domain_Thread_Action_Follower_Follow::do([$user_id], $meta_row["thread_map"], $meta_row["parent_rel"]);
		}

		// если пользователь есть в списке, не будем подписывать и просто вернем список
		return Type_Thread_Followers::get($meta_row["thread_map"]);
	}

	// шлем ws событие всем кому нужно при добавлении сообщения
	protected static function _notifyThreadUsersOnMessageAdd(array $meta_row, array $follower_row, array $receiver_user_list, array $message_list, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, array $additional_data, string $parent_conversation_map, int $threads_updated_version):void {

		// список фолловеров
		$followers_users = Type_Thread_Followers::getFollowerUsersDiff($follower_row);

		// мета треда
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, 0, true);
		$thread_meta          = Apiv1_Format::threadMeta($prepared_thread_meta);

		$all_talking_user_list = [];

		foreach ($message_list as $v) {

			// формируем talking_user_list
			[$talking_user_list, $all_talking_user_list] = self::_getTalkingUserListFromReceiverUserList($receiver_user_list, $v, $all_talking_user_list);

			// получаем локацию сообщения
			$location_type = $source_parent_rel_dynamic->location_type;

			// если сообщение не системное, то готовим данные для пуша
			$push_data = Domain_Thread_Entity_Push::makePushData($v, $meta_row, $location_type, $additional_data);

			$temp              = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
			$formatted_message = Apiv1_Format::threadMessage($temp);
			$ws_users          = Type_Thread_Message_Main::getHandler($v)::getUsers($v);
			$ws_users          = array_unique(array_merge($ws_users, Type_Thread_Meta::getActionUsersList($meta_row)));

			// отправляем сообщение получателям
			Gateway_Bus_Sender::threadMessageReceived(
				$talking_user_list,
				$formatted_message,
				$push_data,
				$thread_meta,
				$ws_users,
				$followers_users,
				$location_type,
				$parent_conversation_map,
				$threads_updated_version
			);
		}

		// отправляем ws о списке сообщений получателям
		Domain_Thread_Action_Message_SendWsOnMessageListReceived::do(
			$meta_row, $follower_row, array_values($all_talking_user_list), $message_list, $source_parent_rel_dynamic, $parent_conversation_map, $threads_updated_version
		);
	}

	// получаем список юзеров которые должны получить сообщение
	protected static function _getTalkingUserListFromReceiverUserList(array $receiver_user_list, array $message, array $all_talking_user_list):array {

		$talking_user_list = [];
		foreach ($receiver_user_list as $user_id => $user) {

			// если пользователь по каким-то причинам не может получить это сообщение
			if (Type_Thread_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
				continue;
			}

			$is_need_push = Type_Thread_Message_Main::getHandler($message)::isNeedPush($message, $user_id, $user);
			$is_mention   = Type_Thread_Message_Main::getHandler($message)::isUserMention($message, $user_id);

			// добавляем пользователя в список, кому нужно отправить событие
			$talking_user_item   = Gateway_Bus_Sender::makeTalkingUserItem($user_id, $is_need_push, $is_mention);
			$talking_user_list[] = $talking_user_item;

			if (!isset($all_talking_user_list[$user_id])) {
				$all_talking_user_list[$user_id] = $talking_user_item;
			}
		}

		return [$talking_user_list, $all_talking_user_list];
	}

	// шлем задачу на обновление thread_menu всех фолловеров треда
	protected static function _sendThreadMenuUpdateTask(array $meta_row, array $follower_row, array $receiver_user_list, array $message_list):void {

		// если не нужно слать
		if (!Type_Thread_Main::isNeedUpdateThreadMenu($meta_row)) {
			return;
		}

		foreach ($message_list as $message) {

			// пропускаем данное сообщение
			if (Type_Thread_Message_Main::getLastVersionHandler()::isSystemMessageUserFollowedThread($message)) {
				continue;
			}

			// инициализируем массив пользователей, которым необходимо обновить данные
			$need_update_user_data_list = [];
			foreach ($receiver_user_list as $user_id => $_) {

				// если пользователь по каким-то причинам не может получить это сообщение
				if (Type_Thread_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
					continue;
				}
				// юзер подписан на тред?
				if (!Type_Thread_Followers::isFollowUser($user_id, $follower_row)) {
					continue;
				}
				$need_update_user_data_list[] = $user_id;
			}
			Type_Phphooker_Main::updateUserDataOnMessageAdd($meta_row["thread_map"], $message, $need_update_user_data_list);
		}
	}

	// задача на парсинг ссылок в каждом сообщении
	protected static function _doActionsForEachMessage(int $sender_user_id, array $meta_row, array $message_list):void {

		// не нужно парсить превьюшки на больших сообщениях
		$is_preview_parse = count($message_list) == 1;

		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);

		foreach ($message_list as $v) {

			$message_map  = Type_Thread_Message_Main::getHandler($v)::getMessageMap($v);
			$message_text = Type_Thread_Message_Main::getHandler($v)::getText($v);

			Type_Preview_Producer::addTaskIfLinkExist($sender_user_id, $message_text, $message_map, $meta_row["users"], $parent_conversation_map, $is_preview_parse);

			self::_addFileListToConversation($message_map, $v, $meta_row);

			// инкрментим статистику по типу сообщений
			self::_incStatAfterMessageAdd($sender_user_id, $v);
		}
	}

	// совершаем действия различного рода в зависимости от типа добавленного сообщения
	// @long - switch
	protected static function _addFileListToConversation(string $message_map, array $message, array $meta_row):void {

		if (Type_Thread_SourceParentRel::getType($meta_row["source_parent_rel"]) != SOURCE_PARENT_ENTITY_TYPE_CONVERSATION) {
			throw new ParamException("thread meta is not conversation entity");
		}

		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);

		switch ($message_type) {

			case THREAD_MESSAGE_TYPE_FILE:

				$need_add_file_list = self::_onAddMessageFile($message);
				break;

			case THREAD_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_MASS_QUOTE:

				$need_add_file_list = self::_onAddMessageMassQuote($message);
				break;
			case THREAD_MESSAGE_TYPE_REPOST:
			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:
				$need_add_file_list = self::_onAddMessageRepost($message);
				break;
			default:
				return;
		}

		if (count($need_add_file_list) == 0) {
			return;
		}

		$conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);

		// в зависимости от родительской сущности добавляем файл по разному
		$parent_entity_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		switch ($parent_entity_type) {

			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:

				$conversation_message_map = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);
				Gateway_Socket_Conversation::addFileListToConversation($conversation_map, $conversation_message_map, $message_map, $need_add_file_list);
				break;
			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:
				Gateway_Socket_Conversation::addFileListToHiringConversation($conversation_map, $message_map, $need_add_file_list, $meta_row["created_at"]);
		}
	}

	// совершаем действия после добавления сообщения с файлом
	protected static function _onAddMessageFile(array $message):array {

		// заносим информацию о файле в таблицу с файлами диалога
		$file_map       = Type_Thread_Message_Main::getHandler($message)::getFileMap($message);
		$file_uid       = Type_Thread_Message_Main::getHandler($message)::getFileUuid($message);
		$sender_user_id = Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message);

		$insert = [
			"file_map"       => $file_map,
			"file_uuid"      => $file_uid,
			"sender_user_id" => $sender_user_id,
		];

		return [$insert];
	}

	// совершаем действия после добавления сообщения с несколькими цитатами
	protected static function _onAddMessageMassQuote(array $message):array {

		$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);

		// переворачиваем список процитированных сообщений сообщений, чтобы не нарушить порядок файлов
		$quoted_message_list = array_reverse($quoted_message_list);

		// проходим по всем сообщениям в цитате и заносим все файлы, что содержатся в них
		return self::_getInsertListIfMessageIsQuoteOrRepost($quoted_message_list);
	}

	/**
	 * Обрабатываем добавленное сообщение-репост.
	 */
	protected static function _onAddMessageRepost(array $message):array {

		// получаем список пересланных сообщений
		$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

		// переворачиваем список процитированных сообщений сообщений, чтобы не нарушить порядок файлов
		$reposted_message_list = array_reverse($reposted_message_list);

		// проходим по всем сообщениям в цитате и заносим все файлы, что содержатся в них
		return self::_getInsertListIfMessageIsQuoteOrRepost($reposted_message_list);
	}

	// получаем массив для вставки если сообщение имеет тип цитата или репост
	protected static function _getInsertListIfMessageIsQuoteOrRepost(array $message_list, array $insert_list = []):array {

		foreach ($message_list as $v) {
			$insert_list = self::_getInsertListIfMessageHasFile($v, $insert_list);
		}

		return $insert_list;
	}

	// получаем массив для вставки файла если сообщение имеет файл
	protected static function _getInsertListIfMessageHasFile(array $message, array $insert_list):array {

		// если это цитата, то дополнительно проходимся по процитированным сообщениям
		if (Type_Thread_Message_Main::getHandler($message)::isQuote($message)) {

			$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);
			return self::_getInsertListIfMessageIsQuoteOrRepost($quoted_message_list, $insert_list);
		}

		// если это репост, то дополнительно проходимся по пересланным сообщениям
		if (Type_Thread_Message_Main::getHandler($message)::isRepost($message)) {

			$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);
			return self::_getInsertListIfMessageIsQuoteOrRepost($reposted_message_list, $insert_list);
		}

		// получаем map файла, если сообщение имеет тип файл или файл репостнутый из треда
		// если файла нет, то ничего не меняем
		$file_map = self::_getFileMapIfMessageIsFile($message);
		if ($file_map === false) {
			return $insert_list;
		}

		$file_uuid      = Type_Thread_Message_Main::getHandler($message)::getFileUuid($message);
		$sender_user_id = Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message);
		$insert_list[]  = [
			"file_map"       => $file_map,
			"file_uuid"      => $file_uuid,
			"sender_user_id" => $sender_user_id,
		];
		return $insert_list;
	}

	/**
	 * получаем file_map если сообщение имеет тип file
	 *
	 * @param array $message
	 *
	 * @return false|string
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @mixed - file_map может быть как false, так и string
	 */
	protected static function _getFileMapIfMessageIsFile(array $message):bool|string {

		$file_map = false;

		// получаем map файла, если сообщение имеет тип файл
		if (Type_Thread_Message_Main::getHandler($message)::isFile($message)) {
			$file_map = Type_Thread_Message_Main::getHandler($message)::getFileMap($message);
		}

		return $file_map;
	}

	/**
	 * инкрментим статистику
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	protected static function _incStatAfterMessageAdd(int $user_id, array $message):void {

		switch (Type_Thread_Message_Main::getHandler($message)::getType($message)) {

			case THREAD_MESSAGE_TYPE_FILE:

				$file_map = Type_Thread_Message_Main::getHandler($message)::getFileMap($message);
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

	// формируем ответ для Domain_Thread_Action_Message_Add::do
	#[ArrayShape(["meta_row" => "array", "message_list" => "array"])]
	protected static function _makeAddMessageOutput(array $meta_row, array $message_list):array {

		return [
			"meta_row"     => $meta_row,
			"message_list" => $message_list,
		];
	}
}