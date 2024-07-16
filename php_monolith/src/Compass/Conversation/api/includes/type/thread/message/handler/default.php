<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use const Compass\Thread\THREAD_MESSAGE_TYPE_FILE;
use const Compass\Thread\THREAD_MESSAGE_TYPE_MASS_QUOTE;
use const Compass\Thread\THREAD_MESSAGE_TYPE_QUOTE;
use const Compass\Thread\THREAD_MESSAGE_TYPE_REPOST;
use const Compass\Thread\THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
use const Compass\Thread\THREAD_MESSAGE_TYPE_TEXT;

/**
 * базовый класс для работы со структурой сообщений всех версий
 * все взаимодействие с сообщением нужной версии происходит через
 * класс Type_Thread_Message_Main::getHandler()
 * где возвращается класс-обработчик для нужной версии сообщения
 * обращаться можно только к потомкам этого класса, например Type_Thread_Message_HandlerV1
 *
 * таким образом достигается полная работоспособность со структурами сообщений разных версий
 */
class Type_Thread_Message_Handler_Default {

	// используем один и тот же трейт
	// просто чтобы не плодить копипасту
	use Type_Conversation_Message_Handler_Indexation;

	protected const _VERSION = 0;

	protected const _ALLOW_TO_QUOTE    = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
	];
	protected const _ALLOW_TO_REACTION = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
	];
	protected const _ALLOW_TO_REPORT   = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
	];
	protected const _ALLOW_TO_REPOST   = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
	];

	// список с типами сообщения треда для файла
	protected const _THREAD_MESSAGE_FILE_TYPE_LIST = [
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
	];

	/** @var int[] массив типов сообщений, которые могут быть проиндексированы */
	public const _INDEXABLE_TYPE_LIST = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];

	/** @var int[] массив файловых типов сообщений, которые могут быть проиндексированы */
	public const _INDEXABLE_FILE_TYPE_LIST = [
		FILE_TYPE_DEFAULT,
		FILE_TYPE_IMAGE,
		FILE_TYPE_VIDEO,
		FILE_TYPE_AUDIO,
		FILE_TYPE_DOCUMENT,
		FILE_TYPE_ARCHIVE,
	];

	##########################################################
	# region создание сообщений разных типов
	##########################################################

	// метод изменяет поля в сообщении, которые отвечают за его идентификацию в рамках системы.
	// дело в том, что нам недоступен message_map и thread_message_index до тех пор, пока...
	// ... мы не обратились в базу и не выяснили их очередные номера, но при этом нужна неполная структура сообщения

	// для этого нужна универсальная функция, которая устанавливает структуре значение этих полей

	public static function prepareForInsert(array $message, string $message_map, int $thread_message_index):array {

		self::_checkVersion($message);

		$message["message_map"]          = $message_map;
		$message["thread_message_index"] = $thread_message_index;

		return $message;
	}

	// создать сообщение типа "текст"
	public static function makeText(int $sender_user_id, string $text, string $client_message_id):array {

		$message                 = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_TEXT, $sender_user_id, $client_message_id);
		$message["data"]["text"] = $text;

		return $message;
	}

	// создать сообщение типа "файл"
	public static function makeFile(int $sender_user_id, string $text, string $client_message_id, string $file_map, string $file_name = ""):array {

		$message                      = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_FILE, $sender_user_id, $client_message_id);
		$message["data"]["text"]      = $text;
		$message["data"]["file_map"]  = $file_map;
		$message["data"]["file_name"] = $file_name;
		$message["data"]["file_uid"]  = generateUUID();

		return $message;
	}

	// создать сообщение типа "цитата"
	public static function makeMassQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list):array {

		// получаем массив сообщений для цитирования
		foreach ($quoted_message_list as $v) {
			self::_throwIfQuoteMessageIsNotAllowed($v, $sender_user_id);
		}

		// получаем стандартную структуру для сообщений
		$message                 = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_MASS_QUOTE, $sender_user_id, $client_message_id);
		$message["data"]["text"] = $text;

		// добавляем дополнительные поля
		$quoted_message_list                    = self::_addExtraForMassQuote($quoted_message_list);
		$message["data"]["quoted_message_list"] = $quoted_message_list;

		// собираем массив action_user_list со всех сообщений
		$action_user_list = self::_getActionUserList($quoted_message_list);

		// мержим action_users_list с тем что уже был у сообщения
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_user_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// проверяем, можно ли процитировать переданно сообщение
	protected static function _throwIfQuoteMessageIsNotAllowed(array $message, int $sender_user_id):void {

		if (!Type_Thread_Message_Main::getHandler($message)::isAllowToQuote($message, $sender_user_id)) {
			throw new ParseFatalException("you have not permissions to quote this message");
		}
	}

	// добавляем дополнительные поля для цитаты
	protected static function _addExtraForMassQuote(array $message_list):array {

		// обновляем поле file_uid для каждого файла в списке сообщений
		$message_list = self::_setNewFileUidIfNeeded($message_list);

		return $message_list;
	}

	// обновляем поле file_uid для процитированных файлов
	protected static function _setNewFileUidIfNeeded(array $message_list):array {

		foreach ($message_list as $k => $v) {

			// если сообщение является файлом - меняем file_uid
			if (Type_Thread_Message_Main::getHandler($v)::isFile($v)) {
				$message_list[$k] = Type_Thread_Message_Main::getHandler($v)::setNewFileUid($v);
			}
		}

		return $message_list;
	}

	// создаем структуру сообщения диалога для треда
	// @long - switch..case по типу сообщения диалога
	public static function makeStructureForConversationMessage(array $message):array {

		// в зависимости от типа родителя треда
		switch ($message["type"]) {

			case CONVERSATION_MESSAGE_TYPE_TEXT:
			case CONVERSATION_MESSAGE_TYPE_RESPECT:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeThreadQuoteItemParentText(
					$message["sender_user_id"], $message["data"]["text"], $message["client_message_id"],
					$message["created_at"], $message["mention_user_id_list"]
				);
				break;

			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeThreadQuoteItemParentFile(
					$message["sender_user_id"], $message["data"]["text"], $message["client_message_id"], $message["created_at"],
					$message["data"]["file_map"], $message["data"]["file_name"] ?? "");
				break;

			case CONVERSATION_MESSAGE_TYPE_QUOTE:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeThreadQuoteItemParentQuote($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], [$message["data"]["quoted_message"]]);
				break;

			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeThreadQuoteItemParentQuote($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], $message["data"]["quoted_message_list"]);
				break;

			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeThreadQuoteItemParentRepost($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], $message["data"]["repost_message_list"]);
				break;

			case CONVERSATION_MESSAGE_TYPE_CALL:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()
					::makeThreadQuoteItemParentCall($message["sender_user_id"], $message["data"]["call_map"]);
				break;
			case CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE:

				$new_message = Type_Thread_Message_Main::getLastVersionHandler()
					::makeThreadQuoteItemParentMediaConference(
						$message["sender_user_id"], $message["data"]["conference_id"],
						$message["data"]["conference_accept_status"], $message["data"]["conference_link"]);
				break;

			default:
				throw new ParseFatalException("get unknown type of parent message");
		}

		$new_message["message_map"] = $message["message_map"];

		if (isset($message["child_thread"]["thread_map"])) {
			$new_message["extra"]["thread_map"] = $message["child_thread"]["thread_map"];
		}

		return $new_message;
	}

	// получаем action_user_list
	protected static function _getActionUserList(array $message_list):array {

		// получаем всех action users для каждого сообщения
		$message_action_user_list = [];
		foreach ($message_list as $v) {
			$message_action_user_list[] = Type_Thread_Message_Main::getHandler($v)::getUsers($v);
		}

		$action_user_list = [];
		foreach ($message_action_user_list as $v1) {

			// складываем user_id пользователя в массив в качестве ключа
			// если он будет повторятся, то просто перезапишется
			foreach ($v1 as $k2 => $v2) {
				$action_user_list[$v2] = true;
			}
		}

		// т.к сейчас в массиве все user_id являются ключами для элементов, получаем все ключи из массива.
		return array_keys($action_user_list);
	}

	// создать сообщение типа "цитата"
	public static function makeQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message):array {

		self::_throwIfNotAllowToQuote($quoted_message, $sender_user_id);

		$message                 = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_QUOTE, $sender_user_id, $client_message_id);
		$message["data"]["text"] = $text;

		// если сообщение было цитатой - превращаем его в текст, чтобы процитировать только текст цитаты
		if (Type_Thread_Message_Main::getHandler($quoted_message)::isQuote($quoted_message)) {
			$quoted_message = Type_Thread_Message_Main::getHandler($quoted_message)::prepareForQuote($quoted_message);
		}

		// если сообщение является файлом - меняем file_uid
		if (Type_Thread_Message_Main::getHandler($quoted_message)::isFile($quoted_message)) {
			$quoted_message = Type_Thread_Message_Main::getHandler($quoted_message)::setNewFileUid($quoted_message);
		}

		// прикрепляем к сообщению процитированное
		$message["data"]["quoted_message"] = $quoted_message;

		// мержим action_users_list с дочерним
		$quoted_message_action_users_list = Type_Thread_Message_Main::getHandler($quoted_message)::getUsers($quoted_message);
		$message["action_users_list"]     = array_merge($message["action_users_list"], $quoted_message_action_users_list);

		return $message;
	}

	// проверяем, что сообщение можно цитировать
	protected static function _throwIfNotAllowToQuote(array $quoted_message, int $sender_user_id):void {

		if (!Type_Thread_Message_Main::getHandler($quoted_message)::isAllowToQuote($quoted_message, $sender_user_id)) {
			throw new ParseFatalException("Trying to quote message, which is not available to quote for some reasons");
		}
	}

	// помечает сообщение удаленным
	public static function setDeleted(array $message):array {

		self::_checkVersion($message);

		$message["type"] = THREAD_MESSAGE_TYPE_DELETED;

		return $message;
	}

	// создать сообщение типа "текстовое сообщение родителя для цитаты в треде"
	public static function makeThreadQuoteItemParentText(int $sender_user_id, string $text, string $client_message_id, int $created_at, array $mention_user_id_list = []):array {

		$message                         = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_TEXT, $sender_user_id, $client_message_id);
		$message["data"]["text"]         = $text;
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		return $message;
	}

	// создать сообщение типа "файловое сообщение родителя для цитаты в треде"
	public static function makeThreadQuoteItemParentFile(int $sender_user_id, string $text, string $client_message_id, int $created_at, string $file_map, string $file_name, array $mention_user_id_list = []):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_FILE, $sender_user_id, $client_message_id);

		$message["data"]["text"]         = $text;
		$message["data"]["file_map"]     = $file_map;
		$message["data"]["file_name"]    = $file_name;
		$message["data"]["file_uid"]     = generateUUID();
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		return $message;
	}

	// создать сообщение типа "родитель-цитата для цитаты в треде"
	public static function makeThreadQuoteItemParentQuote(int $sender_user_id, string $text, string $client_message_id, int $created_at, array $quoted_message_list, array $mention_user_id_list = []):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE, $sender_user_id, $client_message_id);

		$message["data"]["text"]         = $text;
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		$quoted_message_list = self::_getPreparedMessageListIfQuoteOrRepost($quoted_message_list);

		// добавляем дополнительные поля
		$quoted_message_list                    = self::_addExtraForMassQuote($quoted_message_list);
		$message["data"]["quoted_message_list"] = $quoted_message_list;

		// собираем массив action_user_list со всех сообщений
		$action_user_list = self::_getActionUserList($quoted_message_list);

		// мержим action_users_list с тем что уже был у сообщения
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_user_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// создать сообщение типа "родитель-репост для цитаты в треде"
	public static function makeThreadQuoteItemParentRepost(int $sender_user_id, string $text, string $client_message_id, int $created_at, array $reposted_message_list, array $mention_user_id_list = []):array {

		$message                         = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, $sender_user_id, $client_message_id);
		$message["data"]["text"]         = $text;
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		$reposted_message_list = self::_getPreparedMessageListIfQuoteOrRepost($reposted_message_list);

		// добавляем дополнительные поля
		$reposted_message_list                    = self::_addExtraForMassQuote($reposted_message_list);
		$message["data"]["reposted_message_list"] = $reposted_message_list;

		// собираем массив action_user_list со всех сообщений
		$action_user_list = self::_getActionUserList($reposted_message_list);

		// мержим action_users_list с тем что уже был у сообщения
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_user_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// создать сообщение типа "родитель-звонок для цитаты в треде"
	public static function makeThreadQuoteItemParentCall(int $sender_user_id, string $call_map):array {

		$message                     = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_CALL, $sender_user_id);
		$message["data"]["call_map"] = $call_map;

		return $message;
	}

	// создать сообщение типа "родитель-конференция для цитаты в треде"
	public static function makeThreadQuoteItemParentMediaConference(int $sender_user_id, string $conference_id, string $conference_accept_status, string $conference_link):array {

		$message                          = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE, $sender_user_id);
		$message["data"]["conference_id"] = $conference_id;
		$message["data"]["conference_accept_status"]        = $conference_accept_status;
		$message["data"]["conference_link"]          = $conference_link;

		return $message;
	}

	# endregion
	##########################################################

	##########################################################
	# region получение данных из сообщения
	##########################################################

	// получает file_map файла, прикрепленного к сообщению
	public static function getFileMap(array $message):string {

		self::_checkVersion($message);

		// если тип сообщения треда не File
		if (!in_array($message["type"], self::_THREAD_MESSAGE_FILE_TYPE_LIST)) {
			throw new ParseFatalException("Trying to get file_map of message, which is not FILE_TYPE");
		}

		return $message["data"]["file_map"];
	}

	// получает file_name файла, прикрепленного к сообщению
	public static function getFileName(array $message):string {

		self::_checkVersion($message);

		// если тип сообщения треда не File
		if (!in_array($message["type"], self::_THREAD_MESSAGE_FILE_TYPE_LIST)) {
			throw new ParseFatalException("Trying to get file_name of message, which is not FILE_TYPE");
		}

		if (!isset($message["data"]["file_name"])) {
			return "";
		}

		return $message["data"]["file_name"];
	}

	// возвращает id отправителя сообщения
	public static function getSenderUserId(array $message):int {

		self::_checkVersion($message);

		return $message["sender_user_id"];
	}

	// получаем message_map
	public static function getMessageMap(array $message):string {

		self::_checkVersion($message);

		return $message["message_map"];
	}

	// получаем thread_message_index
	public static function getThreadMessageIndex(array $message):int {

		self::_checkVersion($message);

		return $message["thread_message_index"];
	}

	// получаем created_at сообщения
	public static function getCreatedAt(array $message):int {

		self::_checkVersion($message);

		return $message["created_at"];
	}

	/**
	 * получаем платформу, из под которой было отправлено сообщение
	 *
	 * @throws \parseException
	 */
	public static function getPlatform(array $message):string {

		self::_checkVersion($message);

		return $message["platform"] ?? Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM;
	}

	/**
	 * получаем список ссылок
	 * @throws \parseException
	 */
	public static function getLinkListIfExist(array $message):array {

		self::_checkVersion($message);

		return $message["link_list"] ?? [];
	}

	// получает event_type для пушера
	public static function getEventType(array $message):int {

		self::_checkVersion($message);

		return EVENT_TYPE_THREAD_MESSAGE_MASK;
	}

	// возвращает тип сообщения
	public static function getType(array $message):int {

		self::_checkVersion($message);

		return $message["type"];
	}

	// возвращает текст сообщения
	public static function getText(array $message):string {

		self::_checkVersion($message);

		return $message["data"]["text"] ?? "";
	}

	// получает call_map звонка, прикрепленного к сообщению
	public static function getCallMap(array $message):string {

		self::_checkVersion($message);

		// если сообщение не типа звонок
		if ($message["type"] != THREAD_MESSAGE_TYPE_CONVERSATION_CALL) {
			throw new ParseFatalException("Trying to get call_map of message, which is not TYPE_CALL");
		}

		return $message["data"]["call_map"];
	}

	// получает conference_id конференции, прикрепленного к сообщению
	public static function getConferenceId(array $message):string {

		self::_checkVersion($message);

		// если сообщение не типа звонок
		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			throw new ParseFatalException("Trying to get call_map of message, which is not TYPE_MEDIA_CONFERENCE");
		}

		return $message["data"]["conference_id"];
	}

	// получает статус конференции, прикрепленного к сообщению
	public static function getConferenceAcceptStatus(array $message):string {

		self::_checkVersion($message);

		// если сообщение не типа звонок
		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			throw new ParseFatalException("Trying to get call_map of message, which is not TYPE_MEDIA_CONFERENCE");
		}

		return $message["data"]["conference_accept_status"];
	}

	// получает ссылку на конференцию, прикрепленного к сообщению
	public static function getConferenceLink(array $message):string {

		self::_checkVersion($message);

		// если сообщение не типа звонок
		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			throw new ParseFatalException("Trying to get call_map of message, which is not TYPE_MEDIA_CONFERENCE");
		}

		return $message["data"]["conference_link"];
	}

	// получаем список упомянутых из сообщения
	public static function getMentionUserIdList(array $message):array {

		self::_checkVersion($message);
		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return arrayValuesInt($message["mention_user_id_list"]);
	}

	// возвращает client_message_id
	public static function getClientMessageId(array $message):string {

		self::_checkVersion($message);

		return $message["client_message_id"];
	}

	// получает время изменений последнего редактирования сообщения
	public static function getLastMessageTextEditedAt(array $message):string {

		self::_checkVersion($message);

		return $message["extra"]["last_message_text_edited_at"];
	}

	// получаем процитированные сообщения
	public static function getQuotedMessageList(array $message):array {

		self::_checkVersion($message);

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_QUOTE:

				if (isset($message["data"]["quoted_message"])) {
					return [$message["data"]["quoted_message"]];
				}

				return $message["data"]["quoted_message_list"];

			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND:
				return $message["data"]["quoted_message_list"];

			default:
				throw new ParseFatalException("Trying to get quoted_message_list of message, which is not QUOTE_TYPE");
		}
	}

	// получаем репостнутые сообщения
	public static function getRepostedMessageList(array $message):array {

		self::_checkVersion($message);

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:
			case THREAD_MESSAGE_TYPE_REPOST:
				return $message["data"]["reposted_message_list"];

			default:
				throw new ParseFatalException("Trying to get reposted_message_list of message, which is not REPOST_TYPE");
		}
	}

	// получаем репостнутые/процитированные сообщения
	public static function getRepostedOrQuotedMessageList(array $message):array {

		if (self::isRepost($message)) {
			return self::getRepostedMessageList($message);
		}

		if (self::isQuote($message) || self::isSystemBotRemindFromThread($message)) {
			return self::getQuotedMessageList($message);
		}

		throw new ParseFatalException("get message list from not repost or quote");
	}

	// получаем mention_user_id_list
	public static function getMentionedUsers(array $message):array {

		self::_checkVersion($message);

		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return $message["mention_user_id_list"];
	}

	// получает превью
	public static function getPreview(array $message):string {

		self::_checkVersion($message);

		return $message["preview_map"];
	}

	// возвращает список id пользователей, которые нужны клиенту для отображения сообщения
	public static function getUsers(array $message):array {

		self::_checkVersion($message);

		$user_id_list = [];

		// приводим
		foreach ($message["action_users_list"] as $v) {
			$user_id_list[] = (int) $v;
		}

		return $user_id_list;
	}

	# endregion
	##########################################################

	##########################################################
	# region изменение сообщений
	##########################################################

	// добавляет пользователя в массив скрывших сообщение
	public static function addToHiddenBy(array $message, int $user_id):array {

		self::_checkVersion($message);

		$message["user_rel"]["hidden_by"][] = $user_id;
		$message["user_rel"]["hidden_by"]   = array_unique($message["user_rel"]["hidden_by"]);

		return $message;
	}

	// изменяем текст сообщения
	public static function editMessageText(array $message, string $text, array $mention_user_id_list):array {

		$message["data"]["text"]                         = $text;
		$message["extra"]["is_edited_by_user"]           = 1;
		$message["extra"]["last_message_text_edited_at"] = intval(microtime(true) * 1000);
		$message["mention_user_id_list"]                 = $mention_user_id_list;
		$message["action_users_list"]                    = array_merge($message["action_users_list"], $mention_user_id_list);
		$message["action_users_list"]                    = array_unique($message["action_users_list"]);

		return $message;
	}

	// устанавливаем новый file_uid
	public static function setNewFileUid(array $message):array {

		$message["data"]["file_uid"] = generateUUID();

		return $message;
	}

	// подготавливает сообщение для цитирования (убирает цитируемое сообщение из цитаты, оставляет только текст, меняет тип сообщения на текст)
	public static function prepareForQuote(array $message):array {

		self::_checkVersion($message);

		unset($message["data"]["quoted_message"]);
		$message["type"] = THREAD_MESSAGE_TYPE_TEXT;

		return $message;
	}

	// помечает сообщение удаленным системой
	public static function setSystemDeleted(array $message):array {

		self::_checkVersion($message);

		$message["extra"]["is_deleted_by_system"] = 1;

		return $message;
	}

	// добавляет превью
	public static function addPreview(array $message, string $preview_map, int $preview_type):array {

		self::_checkVersion($message);

		$message["preview_map"]  = $preview_map;
		$message["preview_type"] = $preview_type;

		return $message;
	}

	// добавляет размеры изображения с превью
	public static function addPreviewImage(array $message, array $preview_image):array {

		self::_checkVersion($message);

		$message["preview_image"] = $preview_image;

		return $message;
	}

	// удаляет превью
	public static function removePreview(array $message):array {

		self::_checkVersion($message);

		unset($message["preview_map"]);

		return $message;
	}

	// удаляет список ссылок
	public static function removeLinkList(array $message):array {

		self::_checkVersion($message);

		unset($message["link_list"]);

		return $message;
	}

	// добавляет список ссылок
	public static function addLinkList(array $message, array $link_list):array {

		self::_checkVersion($message);

		$message["link_list"] = $link_list;

		return $message;
	}

	// добавляем к сообщению список упомянутых
	public static function addMentionUserIdList(array $message, array $mention_user_id_list):array {

		self::_checkVersion($message);

		// добавляем упомянутых
		$message["mention_user_id_list"] = $mention_user_id_list;

		// добавляем их в action users
		$message["action_users_list"] = array_merge($message["action_users_list"], $mention_user_id_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// меняем время создания сообщения
	public static function changeCreatedAt(array $message, int $created_at):array {

		$message["created_at"] = $created_at;

		return $message;
	}

	# endregion
	##########################################################

	##########################################################
	# region функции отвечающие на вопросы бизнес логики
	##########################################################

	// можно ли репортить сообщение
	public static function isAllowToReport(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если тип сообщения не позволяет его репортить
		if (!in_array($message["type"], self::_ALLOW_TO_REPORT)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	// можно ли репостнуть сообщение
	public static function isAllowToRepost(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если тип сообщения не позволяет его репостнуть
		if (!in_array($message["type"], self::_ALLOW_TO_REPOST)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	// можно ли ставить реакцию
	// не проверяем, что сообщение скрыто пользователем ставившим реакцию, чтобы при convert горячих реакций
	// выставлять их пользователями, скрывшими сообщение
	public static function isAllowToReaction(array $message):bool {

		// если тип сообщения не позволяет ставить реакцию
		if (!in_array($message["type"], self::_ALLOW_TO_REACTION)) {
			return false;
		}

		return true;
	}

	// нужно ли обновлять thread_menu для этого типа сообщения
	// пока что всегда TRUE, затем может быть какая-то логика :wink:
	public static function isNeedUpdateThreadMenu(array $message):bool {

		self::_checkVersion($message);

		return true;
	}

	// удалено ли сообщение системой
	public static function isMessageDeletedBySystem(array $message):bool {

		return isset($message["extra"]["is_deleted_by_system"]) && $message["extra"]["is_deleted_by_system"] == 1;
	}

	// удалено ли сообщение
	public static function isMessageDeleted(array $message):bool {

		return in_array($message["type"], [THREAD_MESSAGE_TYPE_DELETED]);
	}

	// скрыто ли сообщение для пользователя (никак не фигуририует в ws/api)
	public static function isMessageHiddenForUser(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если скрыто у пользователя
		if (self::isHiddenByUser($message, $user_id)) {
			return true;
		}

		// если сообщение удалено системой
		if (self::isMessageDeletedBySystem($message)) {
			return true;
		}

		return false;
	}

	// пользователь упомянут в сообщении?
	public static function isUserMention(array $message, int $user_id):bool {

		if (!isset($message["mention_user_id_list"])) {
			return false;
		}

		return in_array($user_id, $message["mention_user_id_list"]);
	}

	// скрыто ли сообщение пользователем
	public static function isHiddenByUser(array $message, int $user_id):bool {

		self::_checkVersion($message);

		return in_array($user_id, $message["user_rel"]["hidden_by"]);
	}

	// является ли сообщение цитатой
	public static function isQuote(array $message):bool {

		if ($message["type"] == THREAD_MESSAGE_TYPE_QUOTE) {
			return true;
		}

		if ($message["type"] == THREAD_MESSAGE_TYPE_MASS_QUOTE) {
			return true;
		}

		if ($message["type"] == THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE) {
			return true;
		}

		return false;
	}

	// является ли сообщение репостом
	public static function isRepost(array $message):bool {

		if ($message["type"] == THREAD_MESSAGE_TYPE_CONVERSATION_REPOST || $message["type"] == THREAD_MESSAGE_TYPE_REPOST) {
			return true;
		}

		return false;
	}

	// является ли сообщение файлом
	public static function isFile(array $message):bool {

		return in_array($message["type"], [THREAD_MESSAGE_TYPE_FILE, THREAD_MESSAGE_TYPE_CONVERSATION_FILE]);
	}

	// прикреплен ли список ссылок
	public static function isAttachedLinkList(array $message):bool {

		if (isset($message["link_list"])) {
			return true;
		}

		return false;
	}

	// прикреплено ли превью
	public static function isAttachedPreview(array $message):bool {

		if (isset($message["preview_map"])) {
			return true;
		}

		return false;
	}

	// можно ли редактировать с пустым текстом
	public static function isEditEmptyText(array $message):bool {

		// получаем тип сообщения
		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);

		// если цитата
		if ($message_type == THREAD_MESSAGE_TYPE_QUOTE) {
			return true;
		}

		// если массовая цитата
		if ($message_type == THREAD_MESSAGE_TYPE_MASS_QUOTE) {
			return true;
		}

		// если цитата родителя в тред из массовый цитаты
		if ($message_type == THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE) {
			return true;
		}

		return false;
	}

	// можно ли цитировать сообщение
	public static function isAllowToQuote(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если тип сообщения не позволяет его цитировать
		if (!in_array($message["type"], self::_ALLOW_TO_QUOTE)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить сообщение стандартной структуры
	protected static function _getDefaultStructure(int $type, int $sender_user_id, string $client_message_id = ""):array {

		$action_users_list = [];
		if ($sender_user_id > 0) {
			$action_users_list[] = $sender_user_id;
		}

		return [
			"message_map"          => null,
			"version"              => static::_VERSION,
			"type"                 => $type,
			"thread_message_index" => null,
			"sender_user_id"       => $sender_user_id,
			"client_message_id"    => $client_message_id,
			"created_at"           => time(),
			"updated_at"           => 0,
			"user_rel"             => [
				"hidden_by" => [],
			],
			"data"                 => [],
			"extra"                => [],
			"action_users_list"    => $action_users_list,
		];
	}

	// проверить что версия - ок
	protected static function _checkVersion(array $message):void {

		if (!isset($message["version"]) || $message["version"] != static::_VERSION) {
			throw new ParseFatalException(__CLASS__ . ": passed message with incorrect version parameter");
		}
	}

	// подготавливаем список сообщений для репоста/цитаты
	protected static function _getPreparedMessageListIfQuoteOrRepost(array $message_list):array {

		$index = 1;
		foreach ($message_list as $k => $v) {

			// создаем структуру для сообщения
			$prepared_message = self::makeStructureForConversationMessage($v);

			// добавляем thread_message_index
			$prepared_message["thread_message_index"] = $index;
			$index++;

			$message_list[$k] = $prepared_message;
		}

		return $message_list;
	}

	/**
	 * Возвращает тело сообщения для индексации.
	 */
	protected static function _getBodyText(array $message):string {

		return static::getText($message);
	}

	/**
	 * Возвращает текст вложенных сообщений для индексации.
	 */
	protected static function _getNestedText(array $message):string {

		if (static::hasNestedMessages($message)) {
			return implode("\n", static::_resolveNestedTexts($message));
		}

		return "";
	}

	/**
	 * Возвращает массив с текстами вложенных сообщений.
	 */
	protected static function _resolveNestedTexts(array $message):array {

		$output = [];

		foreach (static::getRepostedOrQuotedMessageList($message) as $nested_message) {

			$handler = Type_Thread_Message_Main::getHandler($nested_message);

			// сразу сохраняем текст самого сообщения
			$output[] = $handler::getText($nested_message);

			// репосты и цитаты разбираем рекурсивно
			if (static::hasNestedMessages($nested_message)) {
				array_push($output, ...static::_resolveNestedTexts($nested_message));
			}
		}

		return $output;
	}

	/**
	 * Проверяет, содержит ли сообщение вложенные сообщения.
	 */
	public static function hasNestedMessages(array $message):bool {

		$handler = Type_Thread_Message_Main::getHandler($message);
		return $handler::isRepost($message)
			|| $handler::isQuote($message)
			|| $handler::isSystemBotRemindFromThread($message);
	}

	/**
	 * является ли сообщение Напоминанием из треда?
	 *
	 * @return bool
	 */
	public static function isSystemBotRemindFromThread(array $message):bool {

		return $message["type"] == THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
	}

	/**
	 * является ли сообщение Напоминанием?
	 *
	 * @return bool
	 */
	public static function isSystemBotRemind(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
	}

	/**
	 * Конвертирует сообщение в структуру для добавления в индекс.
	 */
	public static function prepareFiles(array $message):array {

		return [
			static::_getBodyFile($message),
			static::_getNestedFiles($message),
		];
	}

	/**
	 * Возвращает файл самого сообщения.
	 */
	protected static function _getBodyFile(array $message):string|false {

		if (Type_Thread_Message_Main::getHandler($message)::isAnyFile($message)) {

			// получаем файл-мап из сообщения
			$file_map = Type_Thread_Message_Main::getHandler($message)::getFileMap($message);

			// если файл индексируется, то возвращаем его данные
			if (in_array(\CompassApp\Pack\File::getFileType($file_map), static::_INDEXABLE_FILE_TYPE_LIST)) {
				return $file_map;
			}
		}

		return false;
	}

	/**
	 * является ли сообщение каким-либо файлом?
	 *
	 * @return bool
	 */
	public static function isAnyFile(array $message):bool {

		// если это просто обычный файл
		if (self::isFile($message)) {
			return true;
		}

		// если это файл из репоста
		if (self::isFileFromThreadRepost($message)) {
			return true;
		}

		return false;
	}

	/**
	 * является ли сообщение репоста из треда файлом
	 *
	 * @return bool
	 */
	public static function isFileFromThreadRepost(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE;
	}

	/**
	 * Возвращает данные связанных файлов.
	 */
	protected static function _resolveNestedFiles(array $message):array {

		$output = [];

		foreach (Type_Thread_Message_Main::getHandler($message)::getRepostedOrQuotedMessageList($message) as $nested_message) {

			$handler = Type_Thread_Message_Main::getHandler($nested_message);

			if ($handler::isAnyFile($nested_message)) {

				// получаем файл-мап из сообщения
				$file_map = Type_Thread_Message_Main::getHandler($nested_message)::getFileMap($nested_message);

				// тип файла объявлен как неиндексируемый, то пропускаем
				if (!in_array(\CompassApp\Pack\File::getFileType($file_map), static::_INDEXABLE_FILE_TYPE_LIST)) {
					continue;
				}

				$output[] = $file_map;
			}

			// перебираем вложенные сообщения
			if (static::hasNestedMessages($nested_message)) {
				array_push($output, ...static::_resolveNestedFiles($nested_message));
			}
		}

		return $output;
	}
}
