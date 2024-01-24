<?php

namespace Compass\Thread;

use BaseFrame\System\Locale;
use Compass\Thread\Domain_Push_Entity_Locale_Message_Body as Body;
use Compass\Thread\Domain_Push_Entity_Locale_Message_Title as Title;
use Compass\Thread\Domain_Push_Entity_Locale_Message as Message;
use JetBrains\PhpStorm\Pure;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * базовый класс для работы со структурой сообщений всех версий
 * все взаимодействие с сообщением нужной версии происходит через
 * класс Type_Thread_Message_Main::getHandler()
 * где возвращается класс-обработчик для нужной версии сообщения
 * обращаться можно только к потомкам этого класса, например Type_Thread_Message_HandlerV1
 *
 * таким образом достигается полная работоспособность со структурами сообщений разных версий
 *
 */
class Type_Thread_Message_Handler_Default {

	protected const _VERSION = 0;

	protected const _ALLOW_TO_EDIT_TIME   = 60 * 10; // время, в течении которого можно редактировать сообщение
	protected const _ALLOW_TO_DELETE_TIME = 60 * 10; // время, в течении которого можно удалять сообщение

	protected const _ALLOW_TO_EDIT                                   = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
	];
	protected const _ALLOW_TO_DELETE                                 = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_DELETED,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_QUOTE_LEGACY                           = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
	];
	protected const _ALLOW_TO_QUOTE                                  = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_CALL,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
		THREAD_MESSAGE_TYPE_REPOST,
	];
	protected const _ALLOW_TO_REACTION                               = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_REPORT                                 = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
	];
	protected const _ALLOW_TO_REPOST_LEGACY                          = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
	];
	protected const _ALLOW_TO_REPOST                                 = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_CALL,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
	];
	protected const _ALLOW_TO_PREPARE_CONVERSATION_MESSAGE_TO_REPOST = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_CALL,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
	];
	protected const _ALLOW_TO_REMIND                                 = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
		THREAD_MESSAGE_TYPE_REPOST,
	];

	// типы сообщений, которые нужно скрывать в пушах
	protected const _PUSH_HIDDEN_MESSAGE_TYPE_LIST = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_SYSTEM,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
		THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];

	protected const _MAX_BODY_LENGTH = 128; // максимальная длина тела пуш уведомления

	protected const _THREAD_MESSAGE_FILE_TYPE_LIST = [
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
	];

	public const MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE = 100; // максимальное количество сообщений, выбранных для пересылки, если нет цитат/репостов
	public const MAX_SELECTED_MESSAGE_COUNT_WITH_REPOST_OR_QUOTE    = 150; // максимальное количество сообщений, выбранных для пересылки, если есть цитаты/репосты

	##########################################################
	# region типы системных сообщений
	##########################################################

	public const THREAD_SYSTEM_MESSAGE_CREATE_HIRING_REQUEST             = "create_hiring_request";
	public const THREAD_SYSTEM_MESSAGE_ACCEPT_HIRING_REQUEST             = "accept_hiring_request";
	public const THREAD_SYSTEM_MESSAGE_CONFIRM_HIRING_REQUEST            = "confirm_hiring_request";
	public const THREAD_SYSTEM_MESSAGE_CANDIDATE_JOIN_COMPANY            = "candidate_join_company";
	public const THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REQUEST             = "reject_hiring_request";
	public const THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REVOKE_SELF         = "reject_hiring_revoke_self";
	public const THREAD_SYSTEM_MESSAGE_DISMISS_HIRING_REQUEST            = "dismiss_hiring_request";
	public const THREAD_SYSTEM_MESSAGE_HIRING_REQUEST_ON_LEFT_COMPANY    = "hiring_request_on_left_company";
	public const THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST          = "create_dismissal_request";
	public const THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST_SELF     = "create_dismissal_request_self";
	public const THREAD_SYSTEM_MESSAGE_APPROVE_DISMISSAL_REQUEST         = "approve_dismissal_request";
	public const THREAD_SYSTEM_MESSAGE_REJECT_DISMISSAL_REQUEST          = "reject_dismissal_request";
	public const THREAD_SYSTEM_MESSAGE_DISMISSAL_REQUEST_ON_LEFT_COMPANY = "dismissal_request_on_left_company";
	public const THREAD_SYSTEM_MESSAGE_USER_RECEIVED_RESPECT             = "user_received_respect";
	public const THREAD_SYSTEM_MESSAGE_USER_RECEIVED_EXACTINGNESS        = "user_received_exactingness";
	public const THREAD_SYSTEM_MESSAGE_USER_RECEIVED_ACHIEVEMENT         = "user_received_achievement";
	public const THREAD_SYSTEM_MESSAGE_USER_FOLLOWED_THREAD              = "user_followed_thread";
	public const THREAD_SYSTEM_MESSAGE_USER_ADDED_EXACTINGNESS           = "user_added_exactingness";

	##########################################################
	# region создание сообщений разных типов
	##########################################################

	public const WITHOUT_PLATFORM = "none";   // сообщение было создано без платформы (например, старое сообщение)
	public const SYSTEM_PLATFORM  = "system"; // сообщение было создано системой (например, ботом)

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
	public static function makeText(int $sender_user_id, string $text, string $client_message_id, array $mention_user_id_list = [], string $platform = self::WITHOUT_PLATFORM):array {

		$message                         = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_TEXT, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"]         = $text;
		$message["mention_user_id_list"] = $mention_user_id_list;

		return $message;
	}

	// создать сообщение типа "файл"
	public static function makeFile(int $sender_user_id, string $text, string $client_message_id, string $file_map, string $file_name = "", string $platform = self::WITHOUT_PLATFORM):array {

		$message                      = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_FILE, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"]      = $text;
		$message["data"]["file_map"]  = $file_map;
		$message["data"]["file_name"] = $file_name;
		$message["data"]["file_uid"]  = generateUUID();

		return $message;
	}

	// создать сообщение типа "цитата"
	public static function makeMassQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list, string $platform = self::WITHOUT_PLATFORM):array {

		// получаем массив сообщений для цитирования
		foreach ($quoted_message_list as $v) {
			self::_throwIfQuoteMessageIsNotAllowed($v, $sender_user_id);
		}

		// получаем стандартную структуру для сообщений
		$message                 = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_MASS_QUOTE, $sender_user_id, $client_message_id, $platform);
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

	/**
	 * Создать сообщение типа репост
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param string $client_message_id
	 * @param array  $reposted_message_list
	 * @param string $platform
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function makeRepost(int   $sender_user_id, string $text, string $client_message_id,
						    array $reposted_message_list, string $platform = self::WITHOUT_PLATFORM):array {

		// бежим по всем сообщениям, проверяем что все они доступны для того, чтобы их переслать + собираем action_users_list
		$action_users_list = [];
		foreach ($reposted_message_list as $reposted_message_item) {

			if (!Type_Thread_Message_Main::getHandler($reposted_message_item)::isAllowToRepost($reposted_message_item, $sender_user_id, true)) {
				throw new ParseFatalException("Trying to repost a message, which is not allow to repost");
			}

			$action_users_list = array_merge(
				$action_users_list,
				Type_Thread_Message_Main::getHandler($reposted_message_item)::getUsers($reposted_message_item));
		}

		// создаем стандартную структуру
		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_REPOST, $sender_user_id, $client_message_id, $platform);

		// добавляем текст и прорепосченные сообщения
		$message["data"]["text"]                  = $text;
		$message["data"]["reposted_message_list"] = (array) $reposted_message_list;

		// мержим action_users_list
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_users_list);
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
		return self::_setNewFileUidIfNeeded($message_list);
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

				$mention_user_id_list = $message["mention_user_id_list"] ?? [];
				$platform             = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message          = Type_Thread_Message_Main::getLastVersionHandler()::makeConversationText(
					$message["sender_user_id"], $message["data"]["text"], $message["client_message_id"],
					$message["created_at"], $mention_user_id_list, $platform
				);
				break;

			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:

				$platform    = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeConversationFile(
					$message["sender_user_id"], $message["data"]["text"], $message["client_message_id"], $message["created_at"],
					$message["data"]["file_map"], $message["data"]["file_name"] ?? "", [], $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_QUOTE:

				$platform    = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeConversationQuote($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], [$message["data"]["quoted_message"]], [], $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE:

				$platform    = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeConversationQuote($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], $message["data"]["quoted_message_list"], [], $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				$platform    = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message = Type_Thread_Message_Main::getLastVersionHandler()::makeConversationRepost($message["sender_user_id"],
					$message["data"]["text"], $message["client_message_id"], $message["created_at"], $message["data"]["repost_message_list"], [], $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_CALL:

				$platform    = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$new_message = Type_Thread_Message_Main::getLastVersionHandler()
					::makeConversationCall($message["sender_user_id"], $message["data"]["call_map"], $platform);

				// если есть информация о звонке - добавляем
				if (isset($message["extra"]["call_report_id"]) && isset($message["extra"]["call_duration"])) {

					$new_message["extra"]["call_report_id"] = $message["extra"]["call_report_id"];
					$new_message["extra"]["call_duration"]  = $message["extra"]["call_duration"];
				}
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
			foreach ($v1 as $v2) {
				$action_user_list[$v2] = true;
			}
		}

		// т.к сейчас в массиве все user_id являются ключами для элементов, получаем все ключи из массива.
		return array_keys($action_user_list);
	}

	// создать сообщение типа "цитата"
	public static function makeQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message, string $platform = self::WITHOUT_PLATFORM):array {

		self::_throwIfNotAllowToQuote($quoted_message, $sender_user_id);

		$message                 = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_QUOTE, $sender_user_id, $client_message_id, $platform);
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

		$message["extra"]["original_message_type"] = $message["type"];
		$message["type"]                           = THREAD_MESSAGE_TYPE_DELETED;

		// если к сообщению прикреплены список ссылок или превью - удаляем их
		$message = Type_Thread_Message_Main::getHandler($message)::removeLinkList($message);
		return Type_Thread_Message_Main::getHandler($message)::removePreview($message);
	}

	/**
	 * Создать сообщение типа "текстовое сообщение для цитаты или репоста в треде"
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param string $client_message_id
	 * @param int    $created_at
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 *
	 * @return array
	 */
	public static function makeConversationText(int   $sender_user_id, string $text, string $client_message_id, int $created_at,
								  array $mention_user_id_list = [], string $platform = self::WITHOUT_PLATFORM):array {

		$message                         = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_TEXT, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"]         = $text;
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		return $message;
	}

	/**
	 * Создать сообщение типа "файловое сообщение для или репоста цитаты в треде"
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param string $client_message_id
	 * @param int    $created_at
	 * @param string $file_map
	 * @param string $file_name
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 *
	 * @return array
	 */
	public static function makeConversationFile(int   $sender_user_id, string $text, string $client_message_id, int $created_at, string $file_map, string $file_name,
								  array $mention_user_id_list = [], string $platform = self::WITHOUT_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_FILE, $sender_user_id, $client_message_id, $platform);

		$message["data"]["text"]         = $text;
		$message["data"]["file_map"]     = $file_map;
		$message["data"]["file_name"]    = $file_name;
		$message["data"]["file_uid"]     = generateUUID();
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		return $message;
	}

	/**
	 * Создать сообщение типа "цитата для цитаты или репоста в треде"
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param string $client_message_id
	 * @param int    $created_at
	 * @param array  $quoted_message_list
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 *
	 * @return array
	 */
	public static function makeConversationQuote(int   $sender_user_id, string $text, string $client_message_id, int $created_at, array $quoted_message_list,
								   array $mention_user_id_list = [], string $platform = self::WITHOUT_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE, $sender_user_id, $client_message_id, $platform);

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

	/**
	 * Создать сообщение типа "репост для цитаты или репоста в треде"
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param string $client_message_id
	 * @param int    $created_at
	 * @param array  $reposted_message_list
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 *
	 * @return array
	 */
	public static function makeConversationRepost(int   $sender_user_id, string $text, string $client_message_id, int $created_at,
								    array $reposted_message_list, array $mention_user_id_list = [], string $platform = self::WITHOUT_PLATFORM):array {

		$message                         = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, $sender_user_id, $client_message_id, $platform);
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
	public static function makeConversationCall(int $sender_user_id, string $call_map, string $platform = self::WITHOUT_PLATFORM):array {

		$message                     = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_CONVERSATION_CALL, $sender_user_id, "", $platform);
		$message["data"]["call_map"] = $call_map;

		return $message;
	}

	// подготавливаем сообщения треда для репоста в диалог
	public static function makeThreadMessageDataForRepost(array $message):array {

		// формируем сообщение
		$selected_message = [
			"version"              => Type_Thread_Message_Main::getHandler($message)::getVersion($message),
			"sender_user_id"       => Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message),
			"message_map"          => Type_Thread_Message_Main::getHandler($message)::getMessageMap($message),
			"created_at"           => Type_Thread_Message_Main::getHandler($message)::getMessageCreatedAt($message),
			"client_message_id"    => Type_Thread_Message_Main::getHandler($message)::getClientMessageId($message),
			"type"                 => Type_Thread_Message_Main::getHandler($message)::getType($message),
			"message_index"        => Type_Thread_Message_Main::getHandler($message)::getThreadMessageIndex($message),
			"mention_user_id_list" => Type_Thread_Message_Main::getHandler($message)::getMentionUserIdList($message),
			"platform"             => Type_Thread_Message_Main::getHandler($message)::getPlatform($message),
			"data"                 => [
				"text" => Type_Thread_Message_Main::getHandler($message)::getText($message),
			],
		];

		if (Type_Thread_Message_Main::getHandler($message)::isAttachedLinkList($message)) {
			$selected_message = Type_Thread_Message_Main::getHandler($message)::addLinkList($selected_message, $message["link_list"]);
		}

		$selected_message = self::_getDataIfFile($selected_message, $message);
		$selected_message = self::_getDataIfQuote($selected_message, $message);
		return self::_getDataIfRepost($selected_message, $message);
	}

	// если сообщение - файловое, добавляем file_map & file_name
	protected static function _getDataIfFile(array $reposted_message, array $message):array {

		if (!Type_Thread_Message_Main::getHandler($message)::isFile($message)) {
			return $reposted_message;
		}

		$reposted_message["data"]["file_map"]  = Type_Thread_Message_Main::getHandler($message)::getFileMap($message);
		$reposted_message["data"]["file_name"] = Type_Thread_Message_Main::getHandler($message)::getFileName($message);

		return $reposted_message;
	}

	// если сообщение - цитата, добавляем процитированные сообщения
	protected static function _getDataIfQuote(array $reposted_message, array $message):array {

		if (!Type_Thread_Message_Main::getHandler($message)::isQuote($message)) {
			return $reposted_message;
		}

		// получаем процитированные сообщения
		$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);

		// готовим к репосту каждое процитированное сообщение
		foreach ($quoted_message_list as $k => $v) {
			$reposted_message["data"]["quoted_message_list"][$k] = self::makeThreadMessageDataForRepost($v);
		}

		// если имеется родитель, то прикрепляем его
		if (isset($message["data"]["parent_message_data"])) {
			$reposted_message["parent_message"] = $message["data"]["parent_message_data"];
		}

		return $reposted_message;
	}

	// если сообщение - цитата, добавляем процитированные сообщения
	protected static function _getDataIfRepost(array $reposted_message, array $message):array {

		if (!Type_Thread_Message_Main::getHandler($message)::isRepost($message)) {
			return $reposted_message;
		}

		// получаем репостнутые сообщения
		$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

		// готовим к репосту каждое репостнутое сообщение
		foreach ($reposted_message_list as $k => $v) {
			$reposted_message["data"]["reposted_message_list"][$k] = self::makeThreadMessageDataForRepost($v);
		}

		// если имеется родитель, то прикрепляем его
		if (isset($message["data"]["parent_message_data"])) {
			$reposted_message["parent_message"] = $message["data"]["parent_message_data"];
		}

		return $reposted_message;
	}

	/**
	 * создать системное сообщение после добавления заявки найма
	 *
	 * @throws \parseException
	 */
	public static function makeSystemCreateHiringRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_CREATE_HIRING_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_create_from_link", Locale::LOCALE_RUSSIAN);
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение о переходе кандидата по ссылке
	 *
	 * @throws \parseException
	 */
	public static function makeSystemAcceptHiringRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_ACCEPT_HIRING_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_candidate_accept_link");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после одобрения заявки найма
	 *
	 * @throws \parseException
	 */
	public static function makeSystemConfirmHiringRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_CONFIRM_HIRING_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_confirm");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение о вступлении пользователя в компанию
	 *
	 * @throws \parseException
	 */
	public static function makeSystemHiringRequestOnCandidateJoinCompany(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_CANDIDATE_JOIN_COMPANY;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_candidate_entered_company");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после отклонения заявки найма
	 *
	 * @throws \parseException
	 */
	public static function makeSystemRejectHiringRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_reject");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после отзыва заявки найма самим сотрудником
	 *
	 * @throws \parseException
	 */
	public static function makeSystemRevokeHiringRequestSelf(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REVOKE_SELF;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_revoke_self");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после того, как сотрудник был уволен (но еще не покинул компанию)
	 *
	 * @throws \parseException
	 */
	public static function makeSystemDismissHiringRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_DISMISS_HIRING_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_dismiss");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после того, как сотрудник покинул компанию
	 *
	 * @throws \parseException
	 */
	public static function makeSystemHiringRequestOnUserLeftCompany(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_HIRING_REQUEST_ON_LEFT_COMPANY;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "hiring_request", "system_message_text_on_left_company");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после добавления заявки увольнения
	 *
	 * @throws \parseException
	 */
	public static function makeSystemCreateDismissalRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "dismissal_request", "system_message_text_on_create");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после добавления заявки самоувольнения
	 *
	 * @throws \parseException
	 */
	public static function makeSystemCreateDismissalRequestSelf(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST_SELF;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "dismissal_request", "system_message_text_on_create_self");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после одобрения заявки на увольнение
	 *
	 * @throws \parseException
	 */
	public static function makeSystemApproveDismissalRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_APPROVE_DISMISSAL_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "dismissal_request", "system_message_text_on_approve");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после отклонения заявки на увольнение
	 *
	 * @throws \parseException
	 */
	public static function makeSystemRejectDismissalRequest(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_REJECT_DISMISSAL_REQUEST;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "dismissal_request", "system_message_text_on_reject");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение после того, как сотрудник покинул компанию
	 *
	 * @throws \parseException
	 */
	public static function makeSystemDismissalRequestOnUserLeftCompany(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_DISMISSAL_REQUEST_ON_LEFT_COMPANY;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "dismissal_request", "system_message_text_on_left_company");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение получения пользователем благодарности
	 */
	public static function makeSystemUserReceivedRespect(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_RESPECT;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "user_card", "user_received_respect");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение получения пользователем требовательности
	 */
	public static function makeSystemUserReceivedExactingness(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_EXACTINGNESS;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "user_card", "user_received_exactingness");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * Создать системное сообщение отправки требовательности пользователем
	 */
	public static function makeSystemUserAddedExactingness(int $sender_user_id, int $week_count, int $month_count, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_USER_ADDED_EXACTINGNESS;
		$message["data"]["text"]                = Locale::getText(getConfig("LOCALE_TEXT"), "user_card", "user_added_exactingness", values: [
			"week_count"  => $week_count,
			"month_count" => $month_count,
		]);
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение получения пользователем достижения
	 */
	public static function makeSystemUserReceivedAchievement(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_ACHIEVEMENT;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "user_card", "user_received_achievement");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	/**
	 * создать системное сообщение подписки на тред
	 */
	public static function makeSystemMessageFollowThread(int $sender_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM, $sender_user_id, generateUUID(), $platform);

		$message["data"]["system_message_type"] = self::THREAD_SYSTEM_MESSAGE_USER_FOLLOWED_THREAD;
		$message["data"]["text"]                = Locale::getText(
			getConfig("LOCALE_TEXT"), "follow_thread", "user_followed_thread");
		$message["data"]["user_id"]             = $sender_user_id;

		$message["action_users_list"][] = $sender_user_id;
		$message["action_users_list"]   = array_unique($message["action_users_list"]);

		return $message;
	}

	// создать сообщение тип "сообщение от Напоминания"
	public static function makeSystemBotRemind(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list, int $recipient_message_sender_id):array {

		$message                  = self::_getDefaultStructure(THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);
		$message["data"]["text"]  = $text;
		$message["data"]["extra"] = [
			"recipient_message_sender_id" => $recipient_message_sender_id,
		];

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

	# endregion
	##########################################################

	##########################################################
	# region получение данных из сообщения
	##########################################################

	// получает file_map файла, прикрепленного к сообщению
	public static function getFileMap(array $message):string {

		self::_checkVersion($message);
		if (!in_array($message["type"], self::_THREAD_MESSAGE_FILE_TYPE_LIST)) {
			throw new ParseFatalException("Trying to get file_map of message, which is not FILE_TYPE");
		}

		return $message["data"]["file_map"];
	}

	// получает file_uid файла, прикрепленного к сообщению
	public static function getFileUuid(array $message):string {

		self::_checkVersion($message);

		if (!in_array($message["type"], self::_THREAD_MESSAGE_FILE_TYPE_LIST)) {
			throw new ParseFatalException("Trying to get file_uid of message, which is not file");
		}
		return $message["data"]["file_uid"];
	}

	// получаем список file_map файлов, которые были в удаленном сообщении
	public static function getFileUuidListFromAnyMessage(array $message):array {

		if (Type_Thread_Message_Main::getHandler($message)::getType($message) === THREAD_MESSAGE_TYPE_DELETED) {
			$message["type"] = Type_Thread_Message_Main::getHandler($message)::getOriginalType($message);
		}

		return match ($message["type"]) {

			THREAD_MESSAGE_TYPE_FILE                                  => [self::getFileUuid($message)],
			THREAD_MESSAGE_TYPE_QUOTE, THREAD_MESSAGE_TYPE_MASS_QUOTE => self::getFileUuidListFromAnyQuote($message),
			default                                                   => [],
		};
	}

	// функция получает список file_uuid файлов из сообщений цитаты
	public static function getFileUuidListFromAnyQuote(array $message, array $file_uuid_list = []):array {

		$quote_message_list = self::getQuotedMessageList($message);
		foreach ($quote_message_list as $v) {

			// если это цитата, то собираем file_map среди его процитированных
			if (self::isQuote($v)) {
				$file_uuid_list = self::getFileUuidListFromAnyQuote($v, $file_uuid_list);
			}

			// если сообщение - файл
			if (self::isFile($v)) {
				$file_uuid_list[] = self::getFileUuid($v);
			}
		}

		return $file_uuid_list;
	}

	// получает file_name файла, прикрепленного к сообщению
	public static function getFileName(array $message):string {

		self::_checkVersion($message);

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

	// возвращает version сообщения
	public static function getVersion(array $message):int {

		self::_checkVersion($message);

		return $message["version"];
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
	public static function getMessageCreatedAt(array $message):int {

		self::_checkVersion($message);

		return $message["created_at"];
	}

	// возвращает первоначальный тип для УДАЛЕННОГО сообщения
	// ИСПОЛЬЗУЕМ ФУНКЦИЮ С УМОМ! рекомендуется юзать для сообщений только что удаленных
	public static function getOriginalType(array $message):int {

		self::_checkVersion($message);

		// если сообщение не удалено
		if ($message["type"] != THREAD_MESSAGE_TYPE_DELETED) {
			throw new ParseFatalException(__METHOD__ . ": passed not deleted type message");
		}

		return $message["extra"]["original_message_type"] ?? THREAD_MESSAGE_TYPE_DELETED;
	}

	// получает event_type для пушера
	public static function getEventType(array $message, string $location_type):int {

		self::_checkVersion($message);

		if (Type_Thread_SourceParentDynamic::isConversationTypeGroup($location_type)) {
			return EVENT_TYPE_THREAD_MESSAGE_MASK | EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;
		}

		return EVENT_TYPE_THREAD_MESSAGE_MASK;
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

	// возвращает тип сообщения
	public static function getType(array $message):int {

		self::_checkVersion($message);

		return $message["type"];
	}

	// возвращает тип сообщения чата
	public static function getConversationMessageType(array $message):int {

		self::_checkVersion($message);

		return $message["type"];
	}

	// возвращает текст сообщения
	public static function getText(array $message):string {

		self::_checkVersion($message);

		return $message["data"]["text"] ?? "";
	}

	// получаем список упомянутых из сообщения
	public static function getMentionUserIdList(array $message):array {

		self::_checkVersion($message);
		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return arrayValuesInt($message["mention_user_id_list"]);
	}

	// получаем платформу, из под которой было отправлено сообщение
	public static function getPlatform(array $message):string {

		self::_checkVersion($message);

		return $message["platform"] ?? self::WITHOUT_PLATFORM;
	}

	/**
	 * достаём отправителя сообщения-оригинала для Напоминания
	 */
	public static function getRemindRecipientMessageSenderId(array $message):int {

		// если это не сообщение-Напоминание
		if (!self::isSystemBotRemind($message)) {
			return 0;
		}

		return (int) ($message["data"]["extra"]["recipient_message_sender_id"] ?? 0);
	}

	/**
	 * достаём отправителя сообщения-оригинала для Напоминания
	 */
	public static function getRemindOriginalMessageList(array $message):array {

		// если это не сообщение-Напоминание
		if (!self::isSystemBotRemind($message)) {
			return [];
		}

		return $message["data"]["quoted_message_list"];
	}

	/**
	 * получаем идентификатор Напоминания из сообщения-оригинала
	 *
	 * @throws ParseFatalException
	 */
	public static function getRemindId(array $message):int {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return 0;
		}

		return $message["extra"]["remind"]["remind_id"];
	}

	/**
	 * получаем время, когда Напоминания выполнится
	 *
	 * @throws ParseFatalException
	 */
	public static function getRemindAt(array $message):int {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return 0;
		}

		return $message["extra"]["remind"]["remind_at"];
	}

	/**
	 * устанавливаем время, когда Напоминание сработает
	 *
	 * @throws ParseFatalException
	 */
	public static function setRemindAt(array $message, int $remind_at):array {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return $message;
		}

		$message["extra"]["remind"]["remind_at"] = $remind_at;

		return $message;
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

		return $message["type"] == THREAD_MESSAGE_TYPE_DELETED;
	}

	/**
	 * системное ли сообщение
	 *
	 * @param array $message
	 *
	 * @return bool
	 */
	public static function isSystemMessage(array $message):bool {

		return $message["type"] == THREAD_MESSAGE_TYPE_SYSTEM;
	}

	/**
	 * системное ли сообщение о получении пользователем сущности карточки (спасибо, благодарность, достижение)
	 */
	public static function isSystemReceivedEmployeeCardEntityMessage(array $message):bool {

		if ($message["type"] != THREAD_MESSAGE_TYPE_SYSTEM) {
			return false;
		}

		if (isset($message["data"]["system_message_type"]) && in_array($message["data"]["system_message_type"],
				[
					self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_RESPECT,
					self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_EXACTINGNESS,
					self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_ACHIEVEMENT,
				])) {

			return true;
		}

		return false;
	}

	// является ли сообщение Напоминанием?
	public static function isSystemBotRemind(array $message):bool {

		return $message["type"] == THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
	}

	/**
	 * системное ли сообщение о подписки на тред пользователем
	 */
	public static function isSystemMessageUserFollowedThread(array $message):bool {

		if ($message["type"] != THREAD_MESSAGE_TYPE_SYSTEM) {
			return false;
		}

		if (isset($message["data"]["system_message_type"]) && in_array($message["data"]["system_message_type"], [self::THREAD_SYSTEM_MESSAGE_USER_FOLLOWED_THREAD])) {
			return true;
		}

		return false;
	}

	// можно ли Напомнить сообщение
	// выставлять их пользователями, скрывшими сообщение
	public static function isAllowToRemind(array $message, int $user_id):bool {

		// если тип сообщения не позволяет добавлять Напоминание
		if (!in_array($message["type"], self::_ALLOW_TO_REMIND)) {
			return false;
		}

		// проверяем, что сообщение не скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	/**
	 * нужно ли отправлять пуш
	 */
	public static function isNeedPush(array $message, int $user_id, array $user):bool {

		// если пользователь НЕ отправитель сообщения
		if (self::getSenderUserId($message) != $user_id) {

			// но упомянут в сообщении
			if (self::isUserMention($message, $user_id)) {
				return true;
			}

			// если сообщение является системным о получении сущности карточки
			if (self::isSystemReceivedEmployeeCardEntityMessage($message)) {
				return false;
			}

			// если сообщение является системным о подписки на тред
			if (self::isSystemMessageUserFollowedThread($message)) {
				return false;
			}

			// иначе получаем флаг "need_push"
			return (bool) $user["need_push"];
		}

		// если пользователь отправитель, и сообщение является системным о получении сущности карточки
		if (self::isSystemReceivedEmployeeCardEntityMessage($message)) {
			return true;
		}

		return false;
	}

	/**
	 * инкрементить ли чисто непрочитанных?
	 */
	public static function isIncrementUnreadCount(array $message, int $user_id):bool {

		// мы отправитель сообщения в тред?
		$is_sender = $user_id == self::getSenderUserId($message);

		// если системное сообщение в тред о получении сущности карточки (спасибо, требовательность)
		if (self::isSystemReceivedEmployeeCardEntityMessage($message)) {
			return $is_sender;
		}

		return !$is_sender;
	}

	// содержание для пуша
	public static function getPushBody(array $message):string {

		self::_checkVersion($message);
		$thread_prefix = "Новый комментарий: ";

		// если это сообщение-Напоминание
		if (self::isSystemBotRemind($message)) {

			$recipient_message_sender_user_id = Type_Thread_Message_Main::getHandler($message)::getRemindRecipientMessageSenderId($message);
			$user_info                        = Gateway_Bus_CompanyCache::getMember($recipient_message_sender_user_id);
			$thread_prefix                    = $user_info->full_name . "\n";
		}

		$is_push_display = Type_Company_Config::init()->get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY)["value"];

		// получаем текст пуша
		$push_body = self::_getPushText($message, $thread_prefix, $is_push_display == 0);

		// заменяем спец синтаксис на обычный текст
		$push_body = self::_replaceSpecialSyntaxToText($push_body);

		// преобразовываем :short_name: в emoji
		$push_body = Type_Api_Filter::replaceShortNameToEmoji($push_body);

		// обрезаем и добавляем 3 точки
		if (mb_strlen($push_body) > self::_MAX_BODY_LENGTH) {

			$temp      = mb_substr($push_body, 0, self::_MAX_BODY_LENGTH - 3);
			$push_body = $temp . "...";
		}

		return $push_body;
	}

	/**
	 * Локализация для тела пуша
	 *
	 * @param array $message
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getPushBodyLocale(array $message):array {

		self::_checkVersion($message);

		$push_body_display_config = Type_Company_Config::init()->get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY);
		$is_need_hide_push        = $push_body_display_config["value"] == 0;

		// заменяем спец синтаксис на обычный текст
		$message_text = self::_replaceSpecialSyntaxToText($message["data"]["text"]);

		// преобразовываем :short_name: в emoji
		$message_text = Type_Api_Filter::replaceShortNameToEmoji($message_text);

		$push_locale = new Body(Message::THREAD_ENTITY);

		// если надо скрыть пуш - скрываем и отдаем результат
		if ($is_need_hide_push && in_array($message["type"], self::_PUSH_HIDDEN_MESSAGE_TYPE_LIST)) {
			return $push_locale->setType(Body::MESSAGE_HIDDEN)->getLocaleResult();
		}

		// в зависимости от типа дополняем объект и сразу возвращаем
		return self::_getPushLocaleType($message, $message_text, $push_locale)->getLocaleResult();
	}

	/**
	 * Данные для локализации заголовка пуша
	 *
	 * @return array
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function getPushTitleLocale(array $message):array {

		self::_checkVersion($message);

		$push_locale = new Title(Message::THREAD_ENTITY);

		// если это сообщение напоминание
		if (Type_Thread_Message_Main::getHandler($message)::isSystemBotRemind($message)) {
			return $push_locale->setType(Title::MESSAGE_REMIND)->getLocaleResult();
		}

		// иначе возвращаем пустой массив
		return [];
	}

	/**
	 * Получить тип сообщения для локализации
	 *
	 * @param array  $message
	 * @param string $message_text
	 * @param Body   $push_locale
	 *
	 * @return Body
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getPushLocaleType(array $message, string $message_text, Body $push_locale):Body {

		return match ($message["type"]) {

			THREAD_MESSAGE_TYPE_TEXT                                            => $push_locale->setType(Body::MESSAGE_TEXT)->addArg($message_text),
			THREAD_MESSAGE_TYPE_FILE                                            => match (\CompassApp\Pack\File::getFileType($message["data"]["file_map"])) {

				FILE_TYPE_IMAGE    => $push_locale->setType(Body::MESSAGE_IMAGE),
				FILE_TYPE_VIDEO    => $push_locale->setType(Body::MESSAGE_VIDEO),
				FILE_TYPE_AUDIO    => $push_locale->setType(Body::MESSAGE_AUDIO),
				FILE_TYPE_DOCUMENT => $push_locale->setType(Body::MESSAGE_DOCUMENT),
				FILE_TYPE_ARCHIVE  => $push_locale->setType(Body::MESSAGE_ARCHIVE),
				FILE_TYPE_VOICE    => $push_locale->setType(Body::MESSAGE_VOICE),
				default            => $push_locale->setType(Body::MESSAGE_FILE),
			},
			THREAD_MESSAGE_TYPE_QUOTE, THREAD_MESSAGE_TYPE_MASS_QUOTE           => match ($message["data"]["text"]) {

				""      => $push_locale->setType(Body::MESSAGE_QUOTE),
				default => $push_locale->setType(Body::MESSAGE_TEXT)->addArg($message_text),
			},
			THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST => match ($message["data"]["text"]) {

				""      => $push_locale->setType(Body::MESSAGE_REPOST),
				default => $push_locale->setType(Body::MESSAGE_TEXT)->addArg($message_text),
			},
			THREAD_MESSAGE_TYPE_SYSTEM                                          => self::_getPushLocaleForSystemMessage($push_locale, $message),
			THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND                               => self::_getPushLocaleForRemind($message, $push_locale),
			default                                                             => $push_locale->setType(Message::MESSAGE_UNKNOWN)
		};
	}

	/**
	 * Устанавливаем тип сообщения для оригинального сообщения-напоминания
	 *
	 * @return Domain_Push_Entity_Locale_Message_Body
	 */
	protected static function _getPushLocaleForRemind(array $message, Body $push_locale):Body {

		// если комментарий у сообщения-Напоминания не пустой, то ничего не делаем – возвращаем текущий push_locale
		if (!isEmptyString($message["data"]["text"])) {
			return $push_locale;
		}

		// достаём оригинальное сообщение из сообщения-Напоминания
		[$original_message] = self::getRemindOriginalMessageList($message);

		// достаем текст из оригинального сообщения
		$original_message_text = Type_Thread_Message_Main::getHandler($original_message)::getText($original_message);

		// для оригинального сообщения устанавливаем локаль
		return self::_getPushLocaleType($original_message, $original_message_text, $push_locale);
	}

	/**
	 * В зависимости от типа системного сообщения треда возвращаем нужный ключ локализации пуша
	 *
	 * @param Body  $push_locale
	 * @param array $message
	 *
	 * @return Body
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getPushLocaleForSystemMessage(Body $push_locale, array $message):Body {

		return match ($message["data"]["system_message_type"]) {
			self::THREAD_SYSTEM_MESSAGE_CREATE_HIRING_REQUEST             => $push_locale->setType(Body::MESSAGE_CREATE_HIRING_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_ACCEPT_HIRING_REQUEST             => $push_locale->setType(Body::MESSAGE_ACCEPT_HIRING_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_CONFIRM_HIRING_REQUEST            => $push_locale->setType(Body::MESSAGE_CONFIRM_HIRING_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_CANDIDATE_JOIN_COMPANY            => $push_locale->setType(Body::MESSAGE_CANDIDATE_JOIN_COMPANY),
			self::THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REQUEST             => $push_locale->setType(Body::MESSAGE_REJECT_HIRING_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_REJECT_HIRING_REVOKE_SELF         => $push_locale->setType(Body::MESSAGE_REVOKE_HIRING_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_DISMISS_HIRING_REQUEST,
			self::THREAD_SYSTEM_MESSAGE_APPROVE_DISMISSAL_REQUEST         => $push_locale->setType(Body::MESSAGE_APPROVE_DISMISSAL_REQUEST),

			self::THREAD_SYSTEM_MESSAGE_HIRING_REQUEST_ON_LEFT_COMPANY,
			self::THREAD_SYSTEM_MESSAGE_DISMISSAL_REQUEST_ON_LEFT_COMPANY => $push_locale->setType(
				Body::MESSAGE_MEMBER_LEFT_COMPANY),
			self::THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST          => $push_locale->setType(Body::MESSAGE_CREATE_DISMISSAL_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_CREATE_DISMISSAL_REQUEST_SELF     => $push_locale->setType(Body::MESSAGE_CREATE_DISMISSAL_REQUEST_SELF),
			self::THREAD_SYSTEM_MESSAGE_REJECT_DISMISSAL_REQUEST          => $push_locale->setType(Body::MESSAGE_REJECT_DISMISSAL_REQUEST),
			self::THREAD_SYSTEM_MESSAGE_USER_FOLLOWED_THREAD              => $push_locale->setType(Body::MESSAGE_USER_FOLLOWED_THREAD),
			self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_RESPECT             => $push_locale->setType(Body::MESSAGE_USER_RECEIVED_RESPECT),
			self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_EXACTINGNESS        => $push_locale->setType(Body::MESSAGE_USER_RECEIVED_EXACTINGNESS),
			self::THREAD_SYSTEM_MESSAGE_USER_RECEIVED_ACHIEVEMENT         => $push_locale->setType(Body::MESSAGE_USER_RECEIVED_ACHIEVEMENT),
			default                                                       => $push_locale->setType(Message::MESSAGE_UNKNOWN),
		};
	}

	// метод для получения текста пуша
	// @long
	protected static function _getPushText(array $message, string $thread_prefix, int $is_push_hide):string {

		$push_body = "";

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_TEXT:
			case THREAD_MESSAGE_TYPE_SYSTEM:

				if ($is_push_hide == 1) {
					$push_body = $thread_prefix . "Сообщение";
				} else {
					$push_body = $thread_prefix . $message["data"]["text"];
				}
				break;
			case THREAD_MESSAGE_TYPE_FILE:

				$file_type = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);

				$push_body = match ($file_type) {
					FILE_TYPE_IMAGE    => $thread_prefix . "🖼 изображение",
					FILE_TYPE_VIDEO    => $thread_prefix . "🎥 видео",
					FILE_TYPE_AUDIO    => $thread_prefix . "🔈 аудиозапись",
					FILE_TYPE_DOCUMENT => $thread_prefix . "📋 документ",
					FILE_TYPE_ARCHIVE  => $thread_prefix . "📁 архив",
					FILE_TYPE_VOICE    => $thread_prefix . "🗣 голосовое сообщение",
					default            => $thread_prefix . "📎 файл",
				};
				break;
			case THREAD_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_MASS_QUOTE:

				if ($is_push_hide == 1) {
					$push_body = $thread_prefix . "Сообщение";
				} else {

					$message_text = $message["data"]["text"] == "" ? "💬 цитата" : $message["data"]["text"];
					$push_body    = $thread_prefix . $message_text;
				}
				break;
			case THREAD_MESSAGE_TYPE_REPOST:
			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:

				if ($is_push_hide == 1) {

					$push_body = $thread_prefix . "↪️ репост";
				} else {

					$message_text = $message["data"]["text"] == "" ? "↪️ репост" : $message["data"]["text"];
					$push_body    = $thread_prefix . $message_text;
				}
				break;

			case THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND:

				if ($is_push_hide) {
					return "Сообщение";
				}

				// если комментарий у сообщения-Напоминания пустой
				if (isEmptyString($message["data"]["text"])) {

					// сначала достаём оригинальное сообщение из сообщения-Напоминания
					[$original_message] = self::getRemindOriginalMessageList($message);

					// в зависимости от типа оригинала-сообщения готов получаем текст для сообщения-Напоминания
					return self::_getPushText($original_message, $thread_prefix, $is_push_hide);
				}

				break;
		}

		return $push_body;
	}

	// преобразовываем спец синтаксис в текст для пуша
	protected static function _replaceSpecialSyntaxToText(string $text):string {

		$text = Type_Api_Filter::replaceShortNameToEmoji($text);

		// регулярка чтобы заменить ["@"|160593|"Имя"] -> "Имя"
		$new_text = preg_replace("/\[\"(@)\"\|\d*\|\"(.*)\"]/mU", "$1$2", $text);

		// если не смогли преобразовать то отдаем обычный текст
		if ($new_text === false || is_array($new_text)) {
			return $text;
		}
		return $new_text;
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

		$quoted_message_list = [];

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_QUOTE:
				$quoted_message_list[] = $message["data"]["quoted_message"];
				break;

			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:
				$quoted_message_list = $message["data"]["quoted_message_list"];
				break;

			default:
				throw new ParseFatalException("Trying to get quoted_message_list of message, which is not QUOTE_TYPE");
		}

		return $quoted_message_list;
	}

	// получаем репостнутые сообщения
	public static function getRepostedMessageList(array $message):array {

		self::_checkVersion($message);

		return match ($message["type"]) {

			THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST => $message["data"]["reposted_message_list"],
			default                                                             => throw new ParseFatalException("Trying to get reposted_message_list of message, which is not REPOST_TYPE"),
		};
	}

	// получаем репостнутые/процитированные сообщения
	public static function getRepostedOrQuotedMessageList(array $message):array {

		if (self::isRepost($message)) {
			return self::getRepostedMessageList($message);
		}

		if (self::isQuote($message)) {
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

	// подготавливает сообщение для цитирования (убирает цитируемое сообщение из цитаты, оставляет только текст, меняет тип сообщения на текст)
	public static function prepareForQuote(array $message):array {

		self::_checkVersion($message);

		unset($message["data"]["quoted_message"]);
		$message["type"] = THREAD_MESSAGE_TYPE_TEXT;

		return $message;
	}

	// устанавливаем новый file_uid
	public static function setNewFileUid(array $message):array {

		$message["data"]["file_uid"] = generateUUID();

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

		// если к сообщению прикреплен список ссылок, то удаляем список ссылок из сообщения
		$message = Type_Thread_Message_Main::getHandler($message)::removeLinkList($message);
		return Type_Thread_Message_Main::getHandler($message)::removePreview($message);
	}

	// помечает сообщение удаленным системой
	public static function setSystemDeleted(array $message):array {

		self::_checkVersion($message);

		$message["extra"]["is_deleted_by_system"] = 1;

		return $message;
	}

	/**
	 * помечает сообщение отправленным пользовательским ботом
	 *
	 * @throws \parseException
	 */
	public static function setUserbotSender(array $message):array {

		self::_checkVersion($message);

		$message["extra"]["is_userbot_sender"] = 1;

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

	/**
	 * устанавливаем данные Напоминания
	 *
	 * @throws ParseFatalException
	 */
	public static function addRemindData(array $message, int $remind_id, int $remind_at, int $creator_user_id, string $comment):array {

		self::_checkVersion($message);

		$message["extra"]["remind"] = [
			"remind_id"       => $remind_id,
			"remind_at"       => $remind_at,
			"creator_user_id" => $creator_user_id,
			"comment"         => $comment,
		];

		return $message;
	}

	// получает превью
	public static function getPreview(array $message):string {

		self::_checkVersion($message);

		return $message["preview_map"];
	}

	// удаляет превью
	public static function removePreview(array $message):array {

		self::_checkVersion($message);

		if (Type_Thread_Message_Main::getHandler($message)::isAttachedPreview($message)) {
			unset($message["preview_map"]);
		}

		return $message;
	}

	// удаляет список ссылок
	public static function removeLinkList(array $message):array {

		self::_checkVersion($message);

		if (Type_Thread_Message_Main::getHandler($message)::isAttachedLinkList($message)) {
			unset($message["link_list"]);
		}

		return $message;
	}

	/**
	 * удаляем данные Напоминания
	 *
	 * @throws ParseFatalException
	 */
	public static function removeRemindData(array $message):array {

		self::_checkVersion($message);

		unset($message["extra"]["remind"]);

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

	// добавляем список сообщений в сообщения репоста
	public static function setRepostedMessageList(array $message_list, array $repost_message):array {

		$repost_message["data"]["reposted_message_list"] = $message_list;

		return $repost_message;
	}

	// добавляем список сообщений в сообщения цитаты
	public static function setQuotedMessageList(array $message_list, array $quote_message):array {

		$quote_message["data"]["quoted_message_list"] = $message_list;

		return $quote_message;
	}

	// формируем сообщения цитаты/репоста в зависимости от типа сообщения
	public static function makeRepostedOrQuotedMessageList(array $data_message_list, array $chunk_message_list, int $key):array {

		$chunk_message_list[$key][] = match ($data_message_list["type"]) {

			THREAD_MESSAGE_TYPE_QUOTE, THREAD_MESSAGE_TYPE_MASS_QUOTE, THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE
									  => self::setQuotedMessageList($data_message_list["message_list"], $data_message_list["parent_message"]),

			THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST
									  => self::setRepostedMessageList($data_message_list["message_list"], $data_message_list["parent_message"]),

			THREAD_MESSAGE_TYPE_TEXT,
			THREAD_MESSAGE_TYPE_FILE,
			THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
			THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
			THREAD_MESSAGE_TYPE_CONVERSATION_CALL => $data_message_list["parent_message"],

			default                               => throw new ParseFatalException("message type is not processed"),
		};

		return $chunk_message_list;
	}

	# endregion
	##########################################################

	##########################################################
	# region функции отвечающие на вопросы бизнес логики
	##########################################################

	// пользователь упомянут в сообщении?
	#[Pure] public static function isUserMention(array $message, int $user_id):bool {

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

	/**
	 * для сообщения отправитель ли пользовательский бот
	 */
	public static function isUserbotSender(array $message):bool {

		return isset($message["extra"]["is_userbot_sender"]) && $message["extra"]["is_userbot_sender"] == 1;
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

		if (in_array($message["type"], [THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST])) {
			return true;
		}

		return false;
	}

	// является ли сообщение простым текстовым
	public static function isText(array $message):bool {

		return in_array($message["type"], [THREAD_MESSAGE_TYPE_TEXT]);
	}

	// является ли сообщение файлом
	#[Pure] public static function isFile(array $message):bool {

		return in_array($message["type"], [THREAD_MESSAGE_TYPE_FILE, THREAD_MESSAGE_TYPE_CONVERSATION_FILE]);
	}

	// можно ли ставить реакцию
	// не проверяем, что сообщение скрыто пользователем ставившим реакцию, чтобы при convert горячих реакций
	// выставлять их пользователями, скрывшими сообщение
	public static function isAllowToReaction(array $message, int $user_id):bool {

		// если тип сообщения не позволяет ставить реакцию
		if (!in_array($message["type"], self::_ALLOW_TO_REACTION)) {
			return false;
		}

		// проверяем, что сообщение не скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	// можно ли редактировать сообщение судя по флагам
	#[Pure] public static function isFlagsAllowToEdit(array $message):bool {

		// если тип сообщения не позволяет его редактировать
		if (!in_array($message["type"], self::_ALLOW_TO_EDIT)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли редактировать сообщение судя по времени
	public static function isTimeAllowToEdit(array $message):bool {

		// если время отправки сообщения не позволяет его редактировать
		if (time() > $message["created_at"] + self::_ALLOW_TO_EDIT_TIME) {
			return false;
		}

		return true;
	}

	// можно ли удалять сообщение судя по флагам
	#[Pure] public static function isFlagsAllowToDelete(array $message):bool {

		// если тип сообщения не позволяет его удалять
		if (!in_array($message["type"], self::_ALLOW_TO_DELETE)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли удалять сообщение судя по времени
	public static function isTimeAllowToDelete(array $message):bool {

		// если время отправки сообщения не позволяет его удалять
		if (time() > $message["created_at"] + self::_ALLOW_TO_DELETE_TIME) {
			return false;
		}

		// условие для тестирование вышедшего времени удаления
		if (Type_System_Testing::forceExpireTimeToDisallowDelete()) {
			return false;
		}

		return true;
	}

	// можно ли цитировать сообщение
	public static function isAllowToQuote(array $message, int $user_id, bool $is_add_repost_quote = true):bool {

		self::_checkVersion($message);

		$allow_type_to_quote = $is_add_repost_quote ? self::_ALLOW_TO_QUOTE : self::_ALLOW_TO_QUOTE_LEGACY;

		// если тип сообщения не позволяет его цитировать
		if (!in_array($message["type"], $allow_type_to_quote)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (!$is_add_repost_quote && self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

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
	public static function isAllowToRepost(array $message, int $user_id, bool $is_add_repost_quote = false):bool {

		self::_checkVersion($message);

		$allow_to_repost_type_list = $is_add_repost_quote ? self::_ALLOW_TO_REPOST : self::_ALLOW_TO_REPOST_LEGACY;

		// если тип сообщения не позволяет его репостнуть
		if (!in_array($message["type"], $allow_to_repost_type_list)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (!$is_add_repost_quote && self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
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

	/**
	 * прикреплено ли Напоминание
	 */
	public static function isAttachedRemind(array $message):bool {

		if (!isset($message["extra"]["remind"])) {
			return false;
		}

		return true;
	}

	/**
	 * истекло ли по времени Напоминание
	 */
	public static function isRemindExpires(array $message):bool {

		// если Напоминание уже истекло по времени
		if ($message["extra"]["remind"]["remind_at"] < time()) {
			return true;
		}

		return false;
	}

	/**
	 * является ли пользователем создателем Напоминания
	 */
	public static function isRemindCreator(array $message, int $user_id):bool {

		if (isset($message["extra"]["remind"]) === false) {
			return false;
		}

		return $message["extra"]["remind"]["creator_user_id"] == $user_id;
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

	/**
	 * получаем флаг нужно ли форсить пуш для сообщения
	 */
	public static function isNeedForcePush(array $message):bool {

		// если сообщение является Напоминанием
		if (self::isSystemBotRemind($message)) {
			return true;
		}

		return false;
	}

	# endregion
	##########################################################

	// функция собирает общую структуру, которая затем передается в Apiv1_Format
	public static function prepareForFormat(array $message, array $message_reaction_list = [], int $last_reaction_updated_ms = 0):array {

		self::_checkVersion($message);
		self::_throwIfMessageMapIsNotSet($message);

		// формируем стандартную структуру ответа
		$output = self::_makeOutput($message);

		// switch по типу сообщения
		$output = self::_prepareMessageByType($output, $message);

		// прикрепляем реакции
		if (in_array($message["type"], self::_ALLOW_TO_REACTION)) {
			$output = self::_attachReactionList($output, $message_reaction_list, $last_reaction_updated_ms);
		}

		// добавляем к сообщению экстра(превью, ссылки, тред) информацию
		return self::_attachExtra($output, $message);
	}

	// проверяем что сообщение имеет полную структуру
	protected static function _throwIfMessageMapIsNotSet(array $message):void {

		if (is_null($message["message_map"]) || (\CompassApp\Pack\Message::isFromThread($message["message_map"])
				&& !isset($message["thread_message_index"]))) {
			throw new ParseFatalException("Trying to prepareForFormat message, which not applied prepareForInsert first");
		}
	}

	// формируем стандартную структуру
	// @long - большая структура получилась
	protected static function _makeOutput(array $message):array {

		$output = [
			"message_map"                 => $message["message_map"],
			"is_edited"                   => $message["extra"]["is_edited_by_user"] ?? 0,
			"message_index"               => self::_getFormatMessageIndex($message),
			"mention_user_id_list"        => self::_getMentionUserIdList($message),
			"sender_id"                   => $message["sender_user_id"],
			"created_at"                  => $message["created_at"],
			"allow_edit_till"             => $message["created_at"] + self::_ALLOW_TO_EDIT_TIME,
			"allow_delete_till"           => $message["created_at"] + self::_ALLOW_TO_DELETE_TIME,
			"type"                        => $message["type"],
			"client_message_id"           => $message["client_message_id"],
			"text"                        => "",
			"platform"                    => $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM,
			"data"                        => [],
			"reaction_list"               => [],
			"last_message_text_edited_at" => $message["extra"]["last_message_text_edited_at"] ?? 0,
		];

		// если сообщение из диалога, то вероятно это родитель треда (или репост оттуда)
		if (\CompassApp\Pack\Message::isFromConversation($message["message_map"])) {

			$output["block_id"]         = \CompassApp\Pack\Message\Conversation::getBlockId($message["message_map"]);
			$output["conversation_map"] = \CompassApp\Pack\Message\Conversation::getConversationMap($message["message_map"]);
		} else {

			$output["block_id"]   = \CompassApp\Pack\Message\Thread::getBlockId($message["message_map"]);
			$output["thread_map"] = \CompassApp\Pack\Message\Thread::getThreadMap($message["message_map"]);
		}

		return $output;
	}

	// получает message_index в зависимости от типа сообщения
	protected static function _getFormatMessageIndex(array $message):int {

		// для сообщений репоста отдаем другой message_index
		if (isset($message["reposted_message_index"])) {
			return $message["reposted_message_index"];
		}

		return $message["thread_message_index"];
	}

	// получаем mention_user_id_list
	protected static function _getMentionUserIdList(array $message):array {

		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return arrayValuesInt($message["mention_user_id_list"]);
	}

	// подготавливаем сообщение в зависимости от его типа
	// @long - switch..case по типу сообщения в треде
	protected static function _prepareMessageByType(array $output, array $message):array {

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_TEXT:

				$output["text"] = $message["data"]["text"];
				break;

			case THREAD_MESSAGE_TYPE_FILE:

				$output["text"]              = $message["data"]["text"];
				$output["data"]["file_map"]  = $message["data"]["file_map"];
				$output["data"]["file_type"] = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);

				$output = self::_getFileNameIfExist($output, $message);

				if (isset($message["data"]["file_uid"])) {
					$output["data"]["file_uid"] = $message["data"]["file_uid"];
				}

				switch ($output["data"]["file_type"]) {

					case FILE_TYPE_IMAGE:
					case FILE_TYPE_VIDEO:

						// достаем размеры оригинального изображения
						$width  = \CompassApp\Pack\File::getImageWidth($message["data"]["file_map"]);
						$height = \CompassApp\Pack\File::getImageHeight($message["data"]["file_map"]);

						// если размеры переданы, то устанавливаем их в data
						if ($width + $height > 0) {

							$output["data"]["file_width"]  = $width;
							$output["data"]["file_height"] = $height;
						}

						break;

					default:
						break;
				}

				break;

			case THREAD_MESSAGE_TYPE_QUOTE:

				$quoted_message = $message["data"]["quoted_message"];

				$output["text"]                   = $message["data"]["text"];
				$output["data"]["quoted_message"] = Type_Thread_Message_Main::getHandler($quoted_message)::prepareForFormat($quoted_message);
				break;

			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND:

				$output["text"] = $message["data"]["text"];

				// если имеется родительское сообщение
				$output = self::_addParentMessageFromConversation($output, $message);

				$quoted_message_list = $message["data"]["quoted_message_list"];
				foreach ($quoted_message_list as $v) {
					$output["data"]["quoted_message_list"][] = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
				}
				$output["data"]["quoted_message_count"] = self::getRepostedAndQuotedMessageCount($quoted_message_list);

				break;

			case THREAD_MESSAGE_TYPE_DELETED:

				$output["text"] = "";
				$output["data"] = [];
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_TEXT:

				$output["text"] = $message["data"]["text"];

				if (isset($message["child_tread"])) {
					$output["child_thread"]["thread_map"] = $message["child_tread"]["thread_map"];
				}
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

				$output["text"]              = $message["data"]["text"];
				$output["data"]["file_map"]  = $message["data"]["file_map"];
				$output["data"]["file_type"] = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);

				$output = self::_getFileNameIfExist($output, $message);

				if (isset($message["data"]["file_uid"])) {
					$output["data"]["file_uid"] = $message["data"]["file_uid"];
				}

				if (isset($message["child_tread"])) {
					$output["child_thread"]["thread_map"] = $message["child_tread"]["thread_map"];
				}

				switch ($output["data"]["file_type"]) {

					case FILE_TYPE_IMAGE:
					case FILE_TYPE_VIDEO:

						// достаем размеры оригинального изображения
						$width  = \CompassApp\Pack\File::getImageWidth($message["data"]["file_map"]);
						$height = \CompassApp\Pack\File::getImageHeight($message["data"]["file_map"]);

						// если размеры переданы, то устанавливаем их в data
						if ($width + $height > 0) {

							$output["data"]["file_width"]  = $width;
							$output["data"]["file_height"] = $height;
						}

						break;

					default:
						break;
				}
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:

				$output["text"] = $message["data"]["text"];

				$quoted_message_list = $message["data"]["quoted_message_list"];
				foreach ($quoted_message_list as $v) {
					$output["data"]["quoted_message_list"][] = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
				}
				$output["data"]["quoted_message_count"] = self::getRepostedAndQuotedMessageCount($quoted_message_list);

				if (isset($message["child_tread"])) {
					$output["child_thread"]["thread_map"] = $message["child_tread"]["thread_map"];
				}
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:

				$output["text"] = $message["data"]["text"];

				$reposted_message_list = $message["data"]["reposted_message_list"];
				foreach ($reposted_message_list as $v) {
					$output["data"]["reposted_message_list"][] = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
				}

				if (isset($message["child_tread"])) {
					$output["child_thread"]["thread_map"] = $message["child_tread"]["thread_map"];
				}
				break;
			case THREAD_MESSAGE_TYPE_REPOST:

				$output["text"] = $message["data"]["text"];

				$reposted_message_list = $message["data"]["reposted_message_list"];
				foreach ($reposted_message_list as $v) {
					$output["data"]["reposted_message_list"][] = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
				}
				break;
			case THREAD_MESSAGE_TYPE_CONVERSATION_CALL:

				$output["data"]["call_map"] = $message["data"]["call_map"];

				// если имеется дополнительная информация (например при репосте сообщения со звонком)
				if (isset($message["extra"]["call_report_id"], $message["extra"]["call_duration"])) {

					$output["data"]["call_report_id"] = $message["extra"]["call_report_id"];
					$output["data"]["call_duration"]  = $message["extra"]["call_duration"];
				}
				break;

			case THREAD_MESSAGE_TYPE_SYSTEM:

				$output["text"]                        = $message["data"]["text"];
				$output["data"]["system_message_type"] = $message["data"]["system_message_type"];
				$output["data"]["user_id"]             = $message["data"]["user_id"];
				break;

			default:
				throw new ParseFatalException(__CLASS__ . ": unsupported message type");
		}

		return $output;
	}

	// получаем file_name если есть
	protected static function _getFileNameIfExist(array $output, array $message):array {

		if (isset($message["data"]["file_name"])) {
			$output["data"]["file_name"] = $message["data"]["file_name"];
		}

		return $output;
	}

	// добавляем родительское сообщение из диалога
	protected static function _addParentMessageFromConversation(array $output, array $message):array {

		if (!isset($message["data"]["parent_message_data"])) {
			return $output;
		}

		$parent_message = $message["data"]["parent_message_data"];

		// создаем стандартную струкрутру для сообщения
		$new_message = self::createStandardMessageStructure($parent_message);
		$new_message = Type_Thread_Message_Main::getHandler($new_message)::prepareForInsert($new_message, $parent_message["message_map"], 0);

		// добавляем thread_map прикрепленного треда
		$new_message["extra"]["thread_map"] = $parent_message["thread_map"];

		// добавляем в процитированные сообщения
		$output["data"]["quoted_message_list"][] = Type_Thread_Message_Main::getHandler($new_message)::prepareForFormat($new_message);

		return $output;
	}

	// создаем стандартную структуру для сообщений из диалога
	// @long - из-за switch..case по типу сообщения
	public static function createStandardMessageStructure(array $message):array {

		switch ($message["type"]) {

			case CONVERSATION_MESSAGE_TYPE_TEXT:
			case CONVERSATION_MESSAGE_TYPE_RESPECT:

				$platform             = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$mention_user_id_list = $message["mention_user_id_list"] ?? [];
				$message              = Type_Thread_Message_Main::getLastVersionHandler()::makeText($message["sender_user_id"], $message["data"]["text"],
					$message["client_message_id"], $mention_user_id_list, $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_FILE:

				$platform = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$message  = Type_Thread_Message_Main::getLastVersionHandler()::makeFile($message["sender_user_id"], $message["data"]["text"],
					$message["client_message_id"], $message["data"]["file_map"], $message["data"]["file_name"], $platform);
				break;

			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				$platform = $message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM;
				$message  = Type_Thread_Message_Main::getLastVersionHandler()::makeMassQuote($message["sender_user_id"], $message["data"]["text"],
					$message["client_message_id"], $message["data"]["quoted_message_list"], $platform);
				break;
			default:
				throw new ParseFatalException("Unknown message type");
		}

		return $message;
	}

	// получаем количество репостнутых/процитированных сообщений
	public static function getRepostedAndQuotedMessageCount(array $message_list):int {

		$message_count = 0;
		foreach ($message_list as $v) {

			if (self::isQuote($v)) {

				$quoted_message_list = $v["data"]["quoted_message_list"] ?? [$v["data"]["quoted_message"]];
				$message_count       += self::getRepostedAndQuotedMessageCount($quoted_message_list);
			}

			if (self::isRepost($v)) {

				$reposted_message_list = $v["data"]["reposted_message_list"];
				$message_count         += self::getRepostedAndQuotedMessageCount($reposted_message_list);
			}

			// пропускаем, если репост/цитата имеет пустой текст
			if ((self::isQuote($v) || self::isRepost($v)) && mb_strlen($v["data"]["text"]) == 0) {
				continue;
			}

			$message_count++;
		}

		return $message_count;
	}

	// функция прикрепляет список поставленных реакций к сообщению и их количество
	protected static function _attachReactionList(array $message, array $message_reaction_list, int $last_reaction_updated_ms):array {

		$reaction_list = [];

		// собираем нужную структуру для клиента
		foreach ($message_reaction_list as $k => $v) {

			$reaction_list[] = [
				"reaction_name" => (string) $k,
				"count"         => (int) count($v),
				"user_id_list"  => $v,
			];
		}

		$message["reaction_list"]        = $reaction_list;
		$message["last_reaction_edited"] = $last_reaction_updated_ms;

		return $message;
	}

	// возвращает список пользователей, которые нужны клиенту для отображения сообщения
	public static function getUsers(array $message):array {

		self::_checkVersion($message);

		$output = [];

		// приводим
		foreach ($message["action_users_list"] as $v) {
			$output[] = (int) $v;
		}

		return $output;
	}

	// убираем пустые сообщения
	public static function removeEmptyMessageFromMessageList(array $message_list):array {

		// если текст отсутствует, то просто убираем его из сообщений
		foreach ($message_list as $k => $v) {

			if (!isset($v["data"]["file_map"]) && !isset($v["data"]["call_map"]) && mb_strlen($v["data"]["text"]) == 0) {
				unset($message_list[$k]);
			}
		}

		return $message_list;
	}

	// -------------------------------------------------------
	// TEMPORARY
	// -------------------------------------------------------

	// очищаем реакции сообщения
	public static function clearReactionList(array $message):array {

		self::_checkVersion($message);

		// если список реакций не задан, то возвращаем сообщение обратно
		if (!isset($message["extra"]["reaction_list"])) {
			return $message;
		}

		// иначе очищаем массив
		$message["extra"]["reaction_list"] = [];

		return $message;
	}

	/**
	 * Подготавливаем сообщения чата перед репостом
	 *
	 * @param array      $message_list
	 * @param array|null $allow_message_types
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function prepareConversationMessageListBeforeRepost(array $message_list, array $allow_message_types = null):array {

		// индекс сообщений
		$message_index           = 0;
		$count                   = 0;
		$is_with_quote_or_repost = 0;

		// если не переданы дозволенные типы сообщений
		if (is_null($allow_message_types)) {
			$allow_message_types = self::_ALLOW_TO_PREPARE_CONVERSATION_MESSAGE_TO_REPOST;
		}

		// для каждого сообщения из message_list
		$prepared_message_list = [];

		foreach ($message_list as $k => $v) {

			// если тип сообщени чата не поддерживается для этого метода
			if (!in_array($v["type"], $allow_message_types)) {
				throw new ParseFatalException("Not allowed message type");
			}

			// формируем стандартную структуру сообщения для версии V2
			$message = self::makeStructureForConversationMessage($v);
			$count++;

			// считаем также все сообщения в репостах
			if ($message["type"] == THREAD_MESSAGE_TYPE_CONVERSATION_REPOST) {

				$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);
				$count                 += Type_Thread_Message_Main::getHandler($message)::getRepostedAndQuotedMessageCount($reposted_message_list);
			}

			// инкрементим message_index
			$message_index++;
			$message["thread_message_index"] = $message_index;

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$prepared_message_list[$k] = Type_Thread_Message_Main::getHandler($message)::prepareForInsert($message, $v["message_map"], $message_index);
		}

		return [$prepared_message_list, $count, $is_with_quote_or_repost];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить сообщение стандартной структуры
	protected static function _getDefaultStructure(int $type, int $sender_user_id, string $client_message_id = "", string $platform = self::WITHOUT_PLATFORM):array {

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
			"platform"             => $platform,
			"data"                 => [],
			"extra"                => [],
			"action_users_list"    => $action_users_list,
			"mention_user_id_list" => [],
		];
	}

	// проверить что версия - ок
	protected static function _checkVersion(array $message):void {

		if (!isset($message["version"]) || $message["version"] != static::_VERSION) {
			throw new ParseFatalException(__CLASS__ . ": passed message with incorrect version parameter");
		}
	}

	// добавляем к сообщению превью, ссылки, тред если имеются
	protected static function _attachExtra(array $output, array $message):array {

		if (isset($message["preview_map"])) {
			$output["preview_map"] = $message["preview_map"];
		}

		if (isset($message["preview_type"])) {
			$output["preview_type"] = $message["preview_type"];
		}

		if (isset($message["preview_image"])) {
			$output["preview_image"] = $message["preview_image"];
		}

		// ссылки
		if (isset($message["link_list"])) {
			$output["link_list"] = $message["link_list"];
		}

		// тред
		if (isset($message["extra"]["thread_map"])) {
			$output["child_thread"]["thread_map"] = $message["extra"]["thread_map"];
		}

		if (in_array($message["type"], self::_ALLOW_TO_REMIND) && isset($message["extra"]["remind"]) && self::getRemindAt($message) > time()) {
			$output["remind"] = $message["extra"]["remind"];
		}

		return $output;
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
}
