<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;

use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;
use Compass\Conversation\Domain_Push_Entity_Locale_Message as Message;
use Compass\Conversation\Domain_Push_Entity_Locale_Message_Body as Body;
use Compass\Conversation\Domain_Push_Entity_Locale_Message_Title as Title;
use CompassApp\Domain\User\Main;

/**
 * Базовый класс для работы со структурой сообщений всех версий
 * все взаимодействие с сообщением нужной версии происходит через...
 * ... класс Type_Conversation_Message_Main::getHandler(), где возвращается класс-обработчик
 * для нужной версии сообщения
 *
 * обращаться можно только к потомкам этого класса, например Type_Thread_Message_HandlerV1
 *
 * таким образом достигается полная работоспособность со структурами сообщений разных версий
 */
class Type_Conversation_Message_Handler_Default {

	use Type_Conversation_Message_Handler_Indexation; // трейт для работы с индексацией сообщений

	// версия класса для работы с сообщением
	protected const _CURRENT_HANDLER_VERSION = 0;

	protected const _ALLOW_TO_EDIT_TIME   = 60 * 10; // время, в течении которого можно редактировать сообщение
	protected const _ALLOW_TO_DELETE_TIME = 60 * 10; // время, в течении которого можно удалять сообщение

	protected const _ALLOW_TO_EDIT                                    = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
	];
	protected const _ALLOW_TO_DELETE                                  = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_DELETED,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_QUOTE_LEGACY                            = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_CALL,
	];
	protected const _ALLOW_TO_QUOTE                                   = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_CALL,
		CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
	];
	protected const _ALLOW_TO_REPOST_LEGACY                           = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_CALL,
	];
	protected const _ALLOW_TO_REPOST                                  = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_CALL,
		CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
	];
	protected const _ALLOW_TO_REACTION                                = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING,
		CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING,
		CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY,
		CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC,
		CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST,
		CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_THREAD                                  = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING,
		CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING,
		CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY,
		CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_REPORT                                  = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
	];
	protected const _ALLOW_TO_BOT                                     = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
	];
	protected const _ALLOW_TO_HIDE                                    = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_DELETED,
		CONVERSATION_MESSAGE_TYPE_CALL,
		CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];
	protected const _ALLOW_TO_TRANSFER_THREAD_MESSAGE_TO_CONVERSATION = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
	];
	protected const _ALLOW_TO_PREPARE_THREAD_MESSAGE_TO_REPOST        = [
		THREAD_MESSAGE_TYPE_TEXT,
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT,
		THREAD_MESSAGE_TYPE_FILE,
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE,
		THREAD_MESSAGE_TYPE_QUOTE,
		THREAD_MESSAGE_TYPE_REPOST,
		THREAD_MESSAGE_TYPE_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST,
		THREAD_MESSAGE_TYPE_CONVERSATION_CALL,
		THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE,
	];
	protected const _ALLOW_TO_REMIND                                  = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
	];

	// типы сообщений, которые нужно скрывать в пушах
	protected const _PUSH_HIDDEN_MESSAGE_TYPE_LIST = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
	];

	/** @var array Список сообщений, которые можно «прочитать» */
	protected const _ALLOW_TO_MARK_AS_READ = [
		CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA,
		CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY,
		CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY,
		CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING,
		CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE,
	];

	/** @var int[] массив типов сообщений, которые могут быть проиндексированы */
	public const _INDEXABLE_TYPE_LIST = [
		CONVERSATION_MESSAGE_TYPE_TEXT,
		CONVERSATION_MESSAGE_TYPE_FILE,
		CONVERSATION_MESSAGE_TYPE_QUOTE,
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		CONVERSATION_MESSAGE_TYPE_REPOST,
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		CONVERSATION_MESSAGE_TYPE_RESPECT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT,
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND,
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

	protected const _MAX_PUSH_BODY_LENGTH = 200; // максимальная длина тела push-уведомления

	protected const _MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE = 100; // максимальное количество сообщений, выбранных для пересылки, если нет цитат/репостов
	protected const _MAX_SELECTED_MESSAGE_COUNT_WITH_REPOST_OR_QUOTE    = 150; // максимальное количество сообщений, выбранных для пересылки, если есть цитаты/репосты

	##########################################################
	# region создание сообщений разных типов
	##########################################################

	public const WITHOUT_PLATFORM = "none";   // сообщение было создано без платформы (например, старое сообщение)
	public const SYSTEM_PLATFORM  = "system"; // сообщение было создано системой (например, ботом)

	##########################################################
	# region типы системных сообщений
	##########################################################

	public const SYSTEM_MESSAGE_USER_INVITED_TO_GROUP           = "user_invited_to_group";
	public const SYSTEM_MESSAGE_USER_JOINED_TO_GROUP            = "user_joined_to_group";
	public const SYSTEM_MESSAGE_USER_LEFT_GROUP                 = "user_left_group";
	public const SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP          = "user_kicked_from_group";
	public const SYSTEM_MESSAGE_USER_PROMOTED_TO_ADMIN          = "user_promoted_to_admin";
	public const SYSTEM_MESSAGE_ADMIN_DEMOTED_TO_USER           = "admin_demoted_to_user";
	public const SYSTEM_MESSAGE_ADMIN_RENAMED_GROUP             = "admin_renamed_group";
	public const SYSTEM_MESSAGE_USER_DECLINED_INVITE            = "user_declined_invite";
	public const SYSTEM_MESSAGE_USER_LEFT_COMPANY               = "user_left_company";
	public const SYSTEM_MESSAGE_USER_ADD_GROUP                  = "user_add_group";
	public const SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_DESCRIPTION = "admin_changed_group_description";
	public const SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR      = "admin_changed_group_avatar";

	/*
	 * метод изменяет поля в сообщении, которые отвечают за его идентификацию в рамках системы.
	 * дело в том, что нам недоступен message_map до тех пор, пока...
	 * ... мы не обратились в базу и не выяснили номер сообщения, но при этом нужна неполная структура сообщения
	 *
	 * для этого нужна универсальная функция, которая устанавливает структуре значение этого поля
	 */
	public static function prepareForInsert(array $message, string $message_map):array {

		self::_checkVersion($message);

		$message["message_map"] = $message_map;

		return $message;
	}

	// создать сообщение типа "текст"
	public static function makeText(int $sender_user_id, string $text, string $client_message_id, string $platform = self::WITHOUT_PLATFORM):array {

		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_TEXT, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"] = $text;

		return $message;
	}

	// создать сообщение типа "файл"
	public static function makeFile(int $sender_user_id, string $text, string $client_message_id, string $file_map, string $file_name = "", string $platform = self::WITHOUT_PLATFORM):array {

		$message                       = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_FILE, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"]       = $text;
		$message["data"]["file_map"]   = $file_map;
		$message["data"]["file_uid"]   = generateUUID();
		$message["data"]["file_name"]  = $file_name;
		$message["extra"]["file_name"] = $file_name;

		return $message;
	}

	// создать сообщение типа "инвайт"
	public static function makeInvite(int $sender_user_id, string $invite_map, string $platform = self::WITHOUT_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_INVITE, $sender_user_id, "", $platform);

		$message["data"]["invite_map"] = $invite_map;

		return $message;
	}

	// создать сообщение типа "массовая_цитата"
	public static function makeMassQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list, string $platform = self::WITHOUT_PLATFORM, bool $is_add_repost_quote = false):array {

		// проверяем что можем процитировать сообщения
		foreach ($quoted_message_list as $v) {
			self::_throwIfQuoteMessageIsNotAllowed($v, $sender_user_id, $is_add_repost_quote);
		}

		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_MASS_QUOTE, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"] = $text;

		// обновляем поле file_uid процитированным файлам
		$quoted_message_list = self::_setNewFileUidIfNeeded($quoted_message_list);

		// прикрепляем к сообщению процитированное
		$message["data"]["quoted_message_list"] = $quoted_message_list;

		// получаем массив action_user_list со всех сообщений
		$action_user_list = self::_getActionUserList($quoted_message_list);

		// мержим action_users_list с дочерним
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_user_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// проверяет, можно ли совершать цитирование сообщения
	protected static function _throwIfQuoteMessageIsNotAllowed(array $message, int $sender_user_id, bool $is_add_repost_quote):void {

		if ($is_add_repost_quote) {

			if (!Type_Conversation_Message_Main::getLastVersionHandler()::isAllowToQuoteNew($message)) {
				throw new ParamException("you have not permissions to quote this message");
			}
			return;
		}

		if (!Type_Conversation_Message_Main::getLastVersionHandler()::isAllowToQuote($message, $sender_user_id)) {
			throw new ParamException("you have not permissions to quote this message");
		}
	}

	// обновляем поле file_uid для процитированных файлов
	protected static function _setNewFileUidIfNeeded(array $quoted_message_list):array {

		foreach ($quoted_message_list as $k => $v) {

			// если сообщение является файлом - меняем file_uid
			if (Type_Conversation_Message_Main::getHandler($v)::isFile($v)) {
				$quoted_message_list[$k] = Type_Conversation_Message_Main::getHandler($v)::setNewFileUid($v);
			}
		}

		return $quoted_message_list;
	}

	// создать сообщение типа "цитата"
	public static function makeQuote(int $sender_user_id, string $text, string $client_message_id, array $quoted_message, string $platform = self::WITHOUT_PLATFORM):array {

		self::_throwIfNotAllowToQuote($quoted_message, $sender_user_id);

		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_QUOTE, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"] = $text;

		// если сообщение было цитатой - превращаем его в текст, чтобы процитировать только текст цитаты
		if (Type_Conversation_Message_Main::getHandler($quoted_message)::isQuote($quoted_message)) {
			$quoted_message = Type_Conversation_Message_Main::getHandler($quoted_message)::prepareForQuote($quoted_message);
		}

		// если сообщение является файлом - меняем file_uid
		if (Type_Conversation_Message_Main::getHandler($quoted_message)::isFile($quoted_message)) {
			$quoted_message = Type_Conversation_Message_Main::getHandler($quoted_message)::setNewFileUid($quoted_message);
		}

		// прикрепляем к сообщению процитированное
		$message["data"]["quoted_message"] = $quoted_message;

		// мержим action_users_list с дочерним
		$quoted_message_action_users_list = Type_Conversation_Message_Main::getHandler($quoted_message)::getUsers($quoted_message);
		$message["action_users_list"]     = array_merge($message["action_users_list"], $quoted_message_action_users_list);

		return $message;
	}

	// выбрасываем исключение, если сообщение нельзя цитировать
	protected static function _throwIfNotAllowToQuote(array $quoted_message, int $sender_user_id):void {

		if (!Type_Conversation_Message_Main::getHandler($quoted_message)::isAllowToQuote($quoted_message, $sender_user_id)) {
			throw new ParseFatalException("Trying to quote message, which is not available to quote for some reasons");
		}
	}

	// создать сообщение типа "репост"
	public static function makeRepost(int $sender_user_id, string $text, string $client_message_id, array $reposted_message_list, string $platform = self::WITHOUT_PLATFORM):array {

		// бежим по всем сообщениям, проверяем что все они доступны для того, чтобы их переслать + собираем action_users_list
		$action_users_list = [];
		foreach ($reposted_message_list as $reposted_message_item) {

			if (!Type_Conversation_Message_Main::getHandler($reposted_message_item)::isAllowToRepost($reposted_message_item, $sender_user_id)) {
				throw new ParseFatalException("Trying to repost a message, which is not allow to repost");
			}

			$action_users_list = array_merge(
				$action_users_list,
				Type_Conversation_Message_Main::getHandler($reposted_message_item)::getUsers($reposted_message_item));
		}

		// создаем стандартную структуру
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_REPOST, $sender_user_id, $client_message_id, $platform);

		// добавляем текст и прорепосченные сообщения
		$message["data"]["text"]                = $text;
		$message["data"]["repost_message_list"] = (array) $reposted_message_list;

		// мержим action_users_list
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_users_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// создать сообщение типа "респект"
	public static function makeRespect(int $sender_user_id, string $text, string $client_message_id, string $platform = self::WITHOUT_PLATFORM):array {

		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_RESPECT, $sender_user_id, $client_message_id, $platform);
		$message["data"]["text"] = $text;

		return $message;
	}

	// системное сообщение о том что пользователь приглашен в группу
	public static function makeSystemUserInvitedToGroup(int $invited_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", $platform);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_INVITED_TO_GROUP;
		$message["data"]["extra"]               = [
			"invited_user_id" => $invited_user_id,
		];

		$message["action_users_list"][] = $invited_user_id;

		return $message;
	}

	/**
	 * системное сообщение о том что пользователь создал группу
	 */
	public static function makeSystemUserAddGroup(int $creator_user_id, string $group_name, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", $platform);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_ADD_GROUP;
		$message["data"]["extra"]               = [
			"creator_user_id" => $creator_user_id,
			"group_name"      => $group_name,
		];

		$message["action_users_list"][] = $creator_user_id;

		return $message;
	}

	// системное сообщение о том что пользователь вступил в группу
	public static function makeSystemUserJoinedToGroup(int $joined_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", $platform);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_JOINED_TO_GROUP;
		$message["data"]["extra"]               = [
			"joined_user_id" => $joined_user_id,
		];

		$message["action_users_list"][] = $joined_user_id;

		return $message;
	}

	// системное сообщение о том что пользователь отклонил инвайт
	public static function makeSystemUserDeclinedInvite(int $declined_user_id, string $platform = self::SYSTEM_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", $platform);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_DECLINED_INVITE;
		$message["data"]["extra"]               = [
			"declined_user_id" => $declined_user_id,
		];

		$message["action_users_list"][] = $declined_user_id;

		return $message;
	}

	// системное сообщение о том что пользователь покинул группу
	public static function makeSystemUserLeftGroup(int $left_user_id):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_LEFT_GROUP;
		$message["data"]["extra"]               = [
			"left_user_id" => $left_user_id,
		];

		$message["action_users_list"][] = $left_user_id;

		return $message;
	}

	// системное сообщение о том что пользователь кикнут из группы
	public static function makeSystemUserKickedFromGroup(int $kicked_user_id):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", self::SYSTEM_PLATFORM);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP;
		$message["data"]["extra"]               = [
			"kicked_user_id" => $kicked_user_id,
		];

		$message["action_users_list"][] = $kicked_user_id;

		return $message;
	}

	// системное сообщение о том что пользователь повышен до администратора
	public static function makeSystemUserPromotedToAdmin(int $promoted_user_id):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", self::SYSTEM_PLATFORM);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_PROMOTED_TO_ADMIN;
		$message["data"]["extra"]               = [
			"promoted_user_id" => $promoted_user_id,
		];

		$message["action_users_list"][] = $promoted_user_id;

		return $message;
	}

	// системное сообщение о том что администратор разжалован до пользователя
	public static function makeSystemAdminDemotedToUser(int $admin_user_id):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", self::SYSTEM_PLATFORM);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_DEMOTED_TO_USER;
		$message["data"]["extra"]               = [
			"admin_user_id" => $admin_user_id,
		];

		$message["action_users_list"][] = $admin_user_id;

		return $message;
	}

	// системное сообщение о том что администратор переименовал группу
	public static function makeSystemAdminRenamedGroup(int $user_id, string $group_name, string $old_group_name):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", self::SYSTEM_PLATFORM);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_RENAMED_GROUP;
		$message["data"]["extra"]               = [
			"user_id"        => $user_id,
			"group_name"     => $group_name,
			"old_group_name" => $old_group_name,
		];

		$message["action_users_list"][] = $user_id;

		return $message;
	}

	// это системное сообщение о смене администратором аватарки группы?
	public static function isMessageSystemAdminChangedAvatar(array $message):bool {

		self::_checkVersion($message);

		// если не системное сообщение
		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_SYSTEM) {
			return false;
		}

		// если не системное сообщение о смене администратором аватарки группы
		if ($message["data"]["system_message_type"] != self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR) {
			return false;
		}

		return true;
	}

	// системное сообщение о том что администратор сменил аватарку у группы
	public static function makeSystemAdminChangedAvatar(int $user_id, string $file_map):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM, 0, "", self::SYSTEM_PLATFORM);

		$message["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR;
		$message["data"]["extra"]               = [
			"user_id"  => $user_id,
			"file_map" => $file_map,
		];

		$message["action_users_list"][] = $user_id;

		return $message;
	}

	// создать сообщение типа "звонок"
	public static function makeCall(int $sender_user_id, string $call_map, string $platform = self::WITHOUT_PLATFORM):array {

		$message                     = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_CALL, $sender_user_id, "", $platform);
		$message["data"]["call_map"] = $call_map;

		return $message;
	}

	// создать сообщение типа "конференция"
	public static function makeMediaConference(int $sender_user_id, string $conference_id, string $conference_accept_status
		, string $conference_link, string $platform = self::WITHOUT_PLATFORM):array {

		$message                          = self::_getDefaultStructure(
			CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE, $sender_user_id, "", $platform);
		$message["data"]["conference_id"] = $conference_id;
		$message["data"]["conference_accept_status"] = $conference_accept_status;
		$message["data"]["conference_link"]          = $conference_link;

		return $message;
	}

	// создать сообщение типа "приветственное сообщение от пользовательского бота"
	public static function makeUserbotWelcomeMessage(int $userbot_user_id):array {

		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_TEXT, $userbot_user_id);
		$message["data"]["text"] = Locale::getText(
			getConfig("LOCALE_TEXT"), "userbot", "message_text_on_first_add_to_group", locale: Locale::LOCALE_ENGLISH);

		return $message;
	}

	// создать сообщения тип "сообщение от системного бота"
	public static function makeSystemBotText(int $sender_id, string $text, string $client_message_id):array {

		// CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT
		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT, $sender_id, $client_message_id, self::SYSTEM_PLATFORM);
		$message["data"]["text"] = $text;

		return $message;
	}

	// создать сообщения тип "сообщение о смене типа чата оповещения"
	public static function makeSystemBotMessagesMovedNotification(int $sender_id, string $client_message_id):array {

		// CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_MESSAGES_MOVED_NOTIFICATION
		$message                 = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_MESSAGES_MOVED_NOTIFICATION, $sender_id, $client_message_id, self::SYSTEM_PLATFORM);
		$message["data"]["text"] = "";

		return $message;
	}

	// создать сообщения тип сообщение с рейтингом от системного бота
	public static function makeSystemBotRating(int $sender_id, int $year, int $week, int $count, string $company_name, string $client_message_id):array {

		$message                  = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING, $sender_id, $client_message_id, self::SYSTEM_PLATFORM);
		$message["data"]["year"]  = $year;
		$message["data"]["week"]  = $week;
		$message["data"]["count"] = $count;
		$message["data"]["name"]  = $company_name;

		// добавляем прочитанность
		return self::_attachReadAtByList($message);
	}

	// создать сообщение типа "файл от системного бота"
	public static function makeSystemBotFile(int $sender_user_id, string $text, string $client_message_id, string $file_map, string $file_name = ""):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]       = $text;
		$message["data"]["file_map"]   = $file_map;
		$message["data"]["file_uid"]   = generateUUID();
		$message["data"]["file_name"]  = $file_name;
		$message["extra"]["file_name"] = $file_name;

		return $message;
	}

	// создать сообщение типа "инвайт от системного бота"
	public static function makeSystemBotInvite(int $sender_user_id, string $invite_map, string $platform = self::WITHOUT_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_INVITE, $sender_user_id, "", $platform);

		$message["data"]["invite_map"] = $invite_map;

		return $message;
	}

	// создать сообщение тип "сообщение-Напоминание"
	public static function makeSystemBotRemind(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list, int $recipient_message_sender_id):array {

		// проверяем что можем процитировать сообщения
		foreach ($quoted_message_list as $v) {
			self::_throwIfQuoteMessageIsNotAllowed($v, $sender_user_id, true);
		}

		$message                  = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);
		$message["data"]["text"]  = $text;
		$message["data"]["extra"] = [
			"recipient_message_sender_id" => $recipient_message_sender_id,
		];

		// обновляем поле file_uid процитированным файлам
		$quoted_message_list = self::_setNewFileUidIfNeeded($quoted_message_list);

		// прикрепляем к сообщению процитированное
		$message["data"]["quoted_message_list"] = $quoted_message_list;

		// получаем массив action_user_list со всех сообщений
		$action_user_list = self::_getActionUserList($quoted_message_list);

		// мержим action_users_list с дочерним
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_user_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
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

	// создать сообщение с исчисляем метрикой для сотрудника
	public static function makeEmployeeCountableMetric(int $sender_user_id, string $text, string $client_message_id, string $metric_type, int $metric_id, int $editor_user_id, int $target_user_id, string $header, string $comment, int $value_delta, string $source_message_map):array {

		// формируем стандартную структуру сообщения и добавляем нужные данные
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]        = $text;
		$message["data"]["metric_type"] = $metric_type;

		// дополняем экстра данные для метрики
		$message["data"]["metric_extra"] = [
			"editor_user_id"     => $editor_user_id,
			"header_text"        => $header,
			"comment_text"       => $comment,
			"value_delta"        => $value_delta,
			"metric_id"          => $metric_id,
			"source_message_map" => $source_message_map,
		];

		// добавляем сотрудника в список пользователей, связанных с сообщением
		$message = self::addUsersToActionList($message, [$target_user_id, $editor_user_id]);

		// добавляем прочитанность
		return self::_attachReadAtByList($message);
	}

	/**
	 * Создает сообщение типа «У сотрудника годовщина работы в компании».
	 *
	 */
	public static function makeEditorEmployeeAnniversary(int $sender_user_id, string $text, string $client_message_id, int $employee_user_id, int $hired_at):array {

		// формируем стандартную структуру сообщения и добавляем нужные данные
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]             = $text;
		$message["data"]["employee_user_id"] = $employee_user_id;
		$message["data"]["hired_at"]         = $hired_at;

		// добавляем сотрудника в список пользователей, связанных с сообщением
		$message = self::addUsersToActionList($message, [$employee_user_id]);

		// добавляем прочитанность
		return self::_attachReadAtByList($message);
	}

	/**
	 * Создает сообщение типа «У сотрудника годовщина работы в компании».
	 *
	 */
	public static function makeEmployeeAnniversary(int $sender_user_id, string $text, string $client_message_id, int $hired_at):array {

		// формируем стандартную структуру сообщения и добавляем нужные данные
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]     = $text;
		$message["data"]["hired_at"] = $hired_at;

		// добавляем прочитанность
		return self::_attachReadAtByList($message);
	}

	/**
	 * Создает сообщение типа «Зафиксирован рейтинг рабочего времени за период».
	 *
	 */
	public static function makeEditorWorksheetRating(int $sender_user_id, string $text, string $client_message_id, array $leader_user_list, array $driven_user_list, int $period_id, int $period_start_date, int $period_end_date):array {

		// формируем стандартную структуру сообщения и добавляем нужные данные
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]                       = $text;
		$message["data"]["leader_user_work_item_list"] = $leader_user_list;
		$message["data"]["driven_user_work_item_list"] = $driven_user_list;

		// цепляем пользователей к action user list
		$leader_user_id_list = self::_parseUserIdListFromUserWorkItemList($leader_user_list);
		$driven_user_id_list = self::_parseUserIdListFromUserWorkItemList($driven_user_list);

		// список пользователей, связанных с сообщением
		$action_user_list = array_unique(array_merge($leader_user_id_list, $driven_user_id_list));

		// добавляем сотрудника в список пользователей, связанных с сообщением
		$message = self::addUsersToActionList($message, $action_user_list);

		// набиваем дату
		return self::_attachWorkPeriod($message, $period_id, $period_start_date, $period_end_date);
	}

	/**
	 * Парсит сотрудников из списка рейтинга рабочих часов
	 *
	 */
	protected static function _parseUserIdListFromUserWorkItemList(array $user_work_item_ist):array {

		$output = [];

		foreach ($user_work_item_ist as $v) {
			$output[] = $v["user_id"];
		}

		return $output;
	}

	/**
	 * Создает сообщение типа «Сотрудник запросил обратную связь».
	 *
	 */
	public static function makeEditorFeedbackRequest(int $sender_user_id, string $text, string $client_message_id, string $feedback_request_id, int $employee_user_id, int $period_id, int $period_start_date, int $period_end_date):array {

		// формируем стандартную структуру сообщения и добавляем текст и процитированные сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EDITOR_FEEDBACK_REQUEST, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]                = $text;
		$message["data"]["employee_user_id"]    = $employee_user_id;
		$message["data"]["feedback_request_id"] = $feedback_request_id;

		// добавляем сотрудника в список пользователей, связанных с сообщением
		$message = self::addUsersToActionList($message, [$employee_user_id]);

		// набиваем дату
		return self::_attachWorkPeriod($message, $period_id, $period_start_date, $period_end_date);
	}

	/**
	 * Сообщение с отчетом по метрикам сотрудников за период времени.
	 *
	 */
	public static function makeCompanyEmployeeMetricStatistic(int $sender_user_id, string $text, string $client_message_id, string $company_name, array $metric_count_item_list, int $period_id, int $period_start_date, int $period_end_date):array {

		// формируем стандартную структуру сообщения и добавляем текст и процитированные сообщения
		$message = self::_getDefaultStructure(
			CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC,
			$sender_user_id,
			$client_message_id,
			self::SYSTEM_PLATFORM);

		$message["data"]["text"]                   = $text;
		$message["data"]["metric_count_item_list"] = $metric_count_item_list;
		$message["data"]["company_name"]           = $company_name;

		// добавляем прочитанность
		$message = self::_attachReadAtByList($message);

		// набиваем дату
		return self::_attachWorkPeriod($message, $period_id, $period_start_date, $period_end_date);
	}

	/**
	 * Сообщение с напоминанем по метрикам сотрудников за период времени.
	 *
	 */
	public static function makeEditorEmployeeMetricNotice(int $sender_user_id, string $text, string $client_message_id, int $employee_user_id):array {

		// формируем стандартную структуру сообщения и добавляем текст и процитированные сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]             = $text;
		$message["data"]["employee_user_id"] = $employee_user_id;

		// добавляем сотрудника в список пользователей, связанных с сообщением
		// набиваем дату
		return self::addUsersToActionList($message, [$employee_user_id]);
	}

	/**
	 * Сообщение с уведомлением о автоматически списанных часах.
	 *
	 */
	public static function makeWorkTimeAutoLogNotice(int $sender_user_id, string $text, string $client_message_id, int $work_time):array {

		// формируем стандартную структуру сообщения и добавляем текст и процитированные сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_WORK_TIME_AUTO_LOG_NOTICE, $sender_user_id, $client_message_id, self::SYSTEM_PLATFORM);

		$message["data"]["text"]      = $text;
		$message["data"]["work_time"] = $work_time;

		// набиваем дату
		return $message;
	}

	/**
	 * Сообщение с заявкой на найм
	 *
	 */
	public static function makeHiringRequest(int $sender_user_id, string $client_message_id, int $hiring_request_id):array {

		// формируем стандартную структуру сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST, $sender_user_id, $client_message_id);

		$message["data"]["hiring_request_id"] = $hiring_request_id;

		return $message;
	}

	/**
	 *  Сообщение с заявкой на увольнение
	 *
	 */
	public static function makeDismissalRequest(int $sender_user_id, string $client_message_id, int $dismissal_request_id):array {

		// формируем стандартную структуру сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST, $sender_user_id, $client_message_id);

		$message["data"]["dismissal_request_id"] = $dismissal_request_id;

		return $message;
	}

	/**
	 * Сообщение с приглашением в сингл с пригласившим в компанию
	 *
	 */
	public static function makeInviteToCompanyInviterSingle(int $sender_user_id, string $client_message_id, string $text, int $company_inviter_user_id):array {

		// формируем стандартную структуру сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE, $sender_user_id, $client_message_id);

		// текстовое содержимое
		$message["data"]["text"]                    = $text;
		$message["data"]["company_inviter_user_id"] = $company_inviter_user_id;

		// добавляем прочитанность
		$message = self::_attachReadAtByList($message);

		// добавляем сотрудника в список пользователей, связанных с сообщением
		return self::addUsersToActionList($message, [$company_inviter_user_id]);
	}

	// -------------------------------------------------------
	// сообщения типа репост из треда
	// -------------------------------------------------------

	// создать сообщение типа "репост из треда"
	public static function makeThreadRepost(int $sender_user_id, string $text, string $client_message_id, array $reposted_message_list, array $parent_message = [], string $platform = self::WITHOUT_PLATFORM):array {

		$action_users_list = self::_getActionUserList($reposted_message_list);

		// создаем стандартную структуру
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_THREAD_REPOST, $sender_user_id, $client_message_id, $platform);

		// добавляем текст и репостнутые сообщения
		$message["data"]["text"]                = $text;
		$message["data"]["repost_message_list"] = $reposted_message_list;

		// если имеется родитель
		if (count($parent_message) > 0) {

			$parent_message                         = self::_prepareParentFromThreadRepost($parent_message);
			$message["data"]["parent_message_data"] = $parent_message;
		}

		// мержим action_users_list
		$message["action_users_list"] = array_merge($message["action_users_list"], $action_users_list);
		$message["action_users_list"] = array_unique($message["action_users_list"]);

		return $message;
	}

	// подготовить родителя треда для сообщения-репоста из треда
	// @long - switch..case по типу сообщения
	protected static function _prepareParentFromThreadRepost(array $parent_message):array {

		$message_type = Type_Conversation_Message_Main::getHandler($parent_message)::getType($parent_message);
		switch ($message_type) {

			// если родитель является обычным файлом или файлом, репостнутым из треда, то устанавливаем ему новый file_uid
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:

				$parent_message = Type_Conversation_Message_Main::getHandler($parent_message)::setNewFileUid($parent_message);
				break;

			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE:

				// достаем процитированные сообщения из цитаты
				$quoted_message_list = Type_Conversation_Message_Main::getHandler($parent_message)::getQuotedMessageList($parent_message);

				// делаем обработку для всех процитированных
				$quoted_message_list = Type_Conversation_Message_Main::getHandler($parent_message)::doAdaptationIfIssetRepostOrFileOrQuote($quoted_message_list);

				// добавляем обновленный список процитированных сообщений родителю
				$parent_message = Type_Conversation_Message_Main::getHandler($parent_message)::setQuotedMessageList($quoted_message_list, $parent_message);
				break;

			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				// достаем репостнутые сообщения из репоста
				$message_list = Type_Conversation_Message_Main::getHandler($parent_message)::getRepostedMessageList($parent_message);

				// делаем обработку для всех репостнутых
				$message_list = Type_Conversation_Message_Main::getHandler($parent_message)::doAdaptationIfIssetRepostOrFileOrQuote($message_list);

				// добавляем обновленный список репостнутых сообщений родителю
				$parent_message = Type_Conversation_Message_Main::getHandler($parent_message)::setRepostedMessageList($message_list, $parent_message);
				break;
		}

		return $parent_message;
	}

	// создать сообщение типа "текстовое сообщение для репоста из треда"
	public static function makeThreadRepostItemText(
		int    $sender_user_id,
		string $text,
		string $client_message_id,
		int    $created_at,
		array  $mention_user_id_list = [],
		string $platform = self::WITHOUT_PLATFORM,
		array  $link_list = []
	):array {

		// формируем стандартную структуру сообщения и добавляем текст
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT, $sender_user_id, $client_message_id, $platform);

		$message["data"]["text"]         = $text;
		$message["created_at"]           = $created_at;
		$message["mention_user_id_list"] = $mention_user_id_list;

		if (count($link_list) > 0) {
			$message = self::addLinkList($message, $link_list);
		}

		return $message;
	}

	// создать сообщение типа "файловое сообщение для репоста из треда"
	public static function makeThreadRepostItemFile(int $sender_user_id, string $text, string $client_message_id, string $file_map, int $created_at, string $file_name = "", string $platform = self::WITHOUT_PLATFORM):array {

		// формируем стандартную структуру сообщения и добавляем текст с картой файла
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE, $sender_user_id, $client_message_id, $platform);

		$message["data"]["text"]      = $text;
		$message["data"]["file_map"]  = $file_map;
		$message["data"]["file_uid"]  = generateUUID();
		$message["data"]["file_name"] = $file_name;
		$message["created_at"]        = $created_at;

		return $message;
	}

	// создать сообщение типа "цитата для репоста из треда" - версия V2
	public static function makeThreadRepostItemQuoteV2(int $sender_user_id, string $text, string $client_message_id, array $quoted_message_list, int $created_at, int $message_index, array $mention_user_id_list, string $platform):array {

		// формируем стандартную структуру сообщения и добавляем текст и процитированные сообщения
		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE, $sender_user_id, $client_message_id, $platform);

		$message["data"]["text"]           = $text;
		$message["created_at"]             = $created_at;
		$message["reposted_message_index"] = $message_index;
		$message["mention_user_id_list"]   = $mention_user_id_list;
		$message_index++;

		// подготавливаем каждое сообщение в процитированных
		$quoted_message_list = self::_prepareQuotedMessageListForThreadRepostItemQuoteV2($quoted_message_list, $message_index);

		// убираем пустые сообщения
		$quoted_message_list = self::removeEmptyMessageFromMessageList($quoted_message_list);

		$message["data"]["quoted_message_list"] = $quoted_message_list;
		return $message;
	}

	// подготавливаем список процитированных для цитаты из треда - версия V2
	// @long - из-за switch.case по типу сообщения
	protected static function _prepareQuotedMessageListForThreadRepostItemQuoteV2(array $message_list, int $message_index):array {

		foreach ($message_list as $k => $v) {

			// раскидываем по типу сообщений
			switch ($v["type"]) {

				case THREAD_MESSAGE_TYPE_TEXT:
				case THREAD_MESSAGE_TYPE_CONVERSATION_TEXT:

					$platform             = $v["platform"] ?? self::WITHOUT_PLATFORM;
					$mention_user_id_list = $v["mention_user_id_list"] ?? [];

					$new_message                           = self::makeThreadRepostItemText(
						$v["sender_user_id"],
						$v["data"]["text"],
						$v["client_message_id"],
						$v["created_at"],
						$mention_user_id_list,
						$platform);
					$new_message["reposted_message_index"] = $message_index;
					$message_list[$k]                      = self::prepareForInsert($new_message, $v["message_map"]);
					break;

				case THREAD_MESSAGE_TYPE_FILE:
				case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

					$new_message = self::makeThreadRepostItemFile(
						$v["sender_user_id"],
						$v["data"]["text"],
						$v["client_message_id"],
						$v["data"]["file_map"],
						$v["created_at"],
						$v["data"]["file_name"],
						$v["platform"] ?? self::WITHOUT_PLATFORM);

					$new_message["reposted_message_index"] = $message_index;
					$message_list[$k]                      = self::prepareForInsert($new_message, $v["message_map"]);
					break;

				case THREAD_MESSAGE_TYPE_REPOST:
				case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:

					// добавляем репост как сообщение типа text
					$message_list = self::_addAsTextIfIssetTextV2($message_list, $k, $v);

					// достаем сообщения репоста бывшего репоста и готовим их
					return self::_setMessageListOfRepostOrQuoteThreadV2($message_list, $v["data"]["reposted_message_list"], $k, $message_index);

				case THREAD_MESSAGE_TYPE_MASS_QUOTE:
				case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:

					// добавляем цитату как сообщение типа text
					$message_list = self::_addAsTextIfIssetTextV2($message_list, $k, $v);

					// достаем сообщения репоста бывшей цитаты и готовим их
					return self::_setMessageListOfRepostOrQuoteThreadV2($message_list, $v["data"]["quoted_message_list"], $k, $message_index);
			}

			// инкрементим message_index
			$message_index++;
		}

		return $message_list;
	}

	// меняем сообщение на тип текст, если имеется текст у репоста/цитаты версии V2
	protected static function _addAsTextIfIssetTextV2(array $message_list, int $ex_repost_index, array $ex_repost):array {

		// меняем структуру сообщения на сообщение типа text
		$new_message                    = self::makeThreadRepostItemText(
			Type_Conversation_Message_Main::getHandler($ex_repost)::getSenderUserId($ex_repost),
			Type_Conversation_Message_Main::getHandler($ex_repost)::getText($ex_repost),
			Type_Conversation_Message_Main::getHandler($ex_repost)::getClientMessageId($ex_repost),
			Type_Conversation_Message_Main::getHandler($ex_repost)::getCreatedAt($ex_repost),
			Type_Conversation_Message_Main::getHandler($ex_repost)::getMentionedUsers($ex_repost),
			Type_Conversation_Message_Main::getHandler($ex_repost)::getPlatform($ex_repost));
		$message_list[$ex_repost_index] = self::prepareForInsert($new_message, $ex_repost["message_map"]);

		return $message_list;
	}

	/**
	 * устанавливаем сообщения репоста/цитаты в список сообщений версии v2
	 *
	 * @param int $parent_message_index - индекс родительского сообщения, которое подменяем и на его место вставляем все процитированные/репостнутые сообщения
	 *
	 */
	protected static function _setMessageListOfRepostOrQuoteThreadV2(array $main_message_list, array $added_message_list, int $parent_message_index, int $message_index):array {

		// берем список сообщений до индекса удаленного сообщения
		$message_list_1 = array_slice($main_message_list, 0, $parent_message_index + 1);

		// берем список сообщений после индекса удаленного сообщения
		$message_list_2 = array_slice($main_message_list, $parent_message_index + 1);

		// добавляем между полученными списками сообщений сообщения репоста/цитаты
		$new_message_list = array_merge($message_list_1, $added_message_list, $message_list_2);

		// проходимся по получившемуся списку сообщений
		$new_message_list = self::_prepareQuotedMessageListForThreadRepostItemQuoteV2($new_message_list, $message_index);

		// устанавливаем reposted_message_index для сообщений, чтобы они корректно выстраивались в репосте
		return self::_setRepostedMessageIndexForMessageList($new_message_list);
	}

	// создать сообщение типа CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER
	public static function makeSharedMember(int $sender_user_id, array $shared_user_id_list, string $platform = self::WITHOUT_PLATFORM):array {

		$message = self::_getDefaultStructure(CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER, $sender_user_id, generateUUID(), $platform);

		$message["data"]["shared_user_id_list"] = $shared_user_id_list;

		return $message;
	}

	// -------------------------------------------------------

	# endregion
	##########################################################

	##########################################################
	# region получение данных из сообщения
	##########################################################

	// получает file_map файла, прикрепленного к сообщению
	public static function getFileMap(array $message):string {

		self::_checkVersion($message);

		if (!in_array($message["type"], [CONVERSATION_MESSAGE_TYPE_FILE, CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE, CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE])) {
			throw new ParseFatalException("Trying to get file_map of message, which is not file");
		}

		return $message["data"]["file_map"];
	}

	// получает file_name файла, прикрепленного к сообщению
	public static function getFileName(array $message):string {

		self::_checkVersion($message);

		if (!in_array($message["type"], [CONVERSATION_MESSAGE_TYPE_FILE, CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE, CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE])) {
			throw new ParseFatalException("Trying to get file_map of message, which is not file");
		}

		return $message["extra"]["file_name"] ?? "";
	}

	// получает file_uid файла, прикрепленного к сообщению
	public static function getFileUuid(array $message):string {

		self::_checkVersion($message);

		if (!in_array($message["type"], [CONVERSATION_MESSAGE_TYPE_FILE, CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE, CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE])) {
			throw new ParseFatalException("Trying to get file_map of message, which is not file");
		}

		return $message["data"]["file_uid"];
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

	// получаем created_at сообщения
	public static function getCreatedAt(array $message):int {

		self::_checkVersion($message);

		return $message["created_at"];
	}

	// получает event_type для пушера
	public static function getEventType(array $message):int {

		self::_checkVersion($message);

		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_INVITE) {
			return EVENT_TYPE_INVITE_MESSAGE_MASK;
		}

		return EVENT_TYPE_CONVERSATION_MESSAGE_MASK;
	}

	// скрыто ли сообщение для пользователя (никак не фигуририует в ws/api)
	public static function isMessageHiddenForUser(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если скрыто у пользователя (например в случае с инвайтом)
		if (self::isHiddenByUser($message, $user_id)) {
			return true;
		}

		// если сообщение удалено системой
		if (self::isMessageDeletedBySystem($message)) {
			return true;
		}

		// системное сообщение о смене аватарки и клиент НЕ поддерживает такое сообщение
		if (self::isMessageSystemAdminChangedAvatar($message)) {
			return true;
		}

		return false;
	}

	// скрывал ли кто-либо сообщение
	public static function isMessageHiddenBySomeone(array $message):bool {

		self::_checkVersion($message);

		// если пользователи не скрывали сообщение
		if (count($message["user_rel"]["hidden_by"]) > 0) {
			return true;
		}

		// если сообщение удалено системой
		if (self::isMessageDeletedBySystem($message)) {
			return true;
		}

		return false;
	}

	// возвращает список идентификаторов пользователей скрывших сообщение
	public static function getHiddenByUserIdList(array $message):array {

		self::_checkVersion($message);

		// если пользователи не скрывали сообщение
		if (count($message["user_rel"]["hidden_by"]) < 1) {
			return [];
		}

		return $message["user_rel"]["hidden_by"];
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

	// нужно ли обновлять левое меню для этого типа сообщения
	public static function isNeedUpdateLeftMenu(array $message):bool {

		self::_checkVersion($message);

		// проверяем тип сообщения
		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM) {
			return false;
		}

		return true;
	}

	// удалено ли сообщение системой
	public static function isMessageDeletedBySystem(array $message):bool {

		return isset($message["extra"]["is_deleted_by_system"]) && $message["extra"]["is_deleted_by_system"] == 1;
	}

	// необходимо ли скрывать системное сообщение о приглашении и вступлении в группу
	public static function isNeedHideSystemMessageOnInviteAndJoin(array $message, array $extra):bool {

		if (!Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($extra)) {

			if (isset($message["data"]["system_message_type"]) && $message["data"]["system_message_type"] === self::SYSTEM_MESSAGE_USER_INVITED_TO_GROUP) {
				return true;
			}

			if (isset($message["data"]["system_message_type"]) && $message["data"]["system_message_type"] === self::SYSTEM_MESSAGE_USER_JOINED_TO_GROUP) {
				return true;
			}
		}

		return false;
	}

	// необходимо ли скрывать системное сообщение о покидании/кике из группы
	public static function isNeedHideSystemMessageOnLeaveAndKicked(array $message, array $extra):bool {

		if (!Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($extra)) {

			if (isset($message["data"]["system_message_type"]) && $message["data"]["system_message_type"] === self::SYSTEM_MESSAGE_USER_LEFT_GROUP) {
				return true;
			}

			if (isset($message["data"]["system_message_type"]) && $message["data"]["system_message_type"] === self::SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP) {
				return true;
			}

			if (isset($message["data"]["system_message_type"]) && $message["data"]["system_message_type"] === self::SYSTEM_MESSAGE_USER_LEFT_COMPANY) {
				return true;
			}
		}

		return false;
	}

	// необходимо ли скрывать системное сообщение об удалении сообщения в группе
	public static function isNeedHideSystemDeletedMessage(array $message, array $extra):bool {

		if (!Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($extra)) {
			return self::isMessageDeleted($message);
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

	// удалено ли сообщение
	public static function isMessageDeleted(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_DELETED;
	}

	// заголовок для пуша
	public static function getPushTitle(array $message, int $conversation_type, string $conversation_name, string $full_name):string {

		self::_checkVersion($message);

		// если сообщение является Напоминанием
		if (Type_Conversation_Message_Main::getHandler($message)::isSystemBotRemind($message)) {
			return $full_name;
		}

		// если формируем пуш для single диалога
		if (Type_Conversation_Meta::isSubtypeOfSingle($conversation_type) || Type_Conversation_Meta::isNotesConversationType($conversation_type)) {
			return $full_name;
		}

		// если формируем пуш для group диалога
		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {
			return $conversation_name;
		}

		throw new \parseException(__CLASS__ . ": unhandled conversation_type");
	}

	/**
	 * Данные для локализации заголовка пуша
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function getPushTitleLocale(array $message, int $sender_npc_type):array {

		self::_checkVersion($message);

		$push_locale = new Title(Message::CONVERSATION_ENTITY);

		// если это сообщение напоминание
		if (Type_Conversation_Message_Main::getHandler($message)::isSystemBotRemind($message)) {
			return $push_locale->setType(Title::MESSAGE_REMIND)->getLocaleResult();
		}

		// если это сообщение от оператора службы поддержки или от бота поддержки (приветственное сообщение)
		if (Type_User_Main::isOperator($sender_npc_type) || Main::isSystemBotSupport($sender_npc_type)) {
			return $push_locale->setType(Title::MESSAGE_SUPPORT)->getLocaleResult();
		}

		// иначе возвращаем пустой массив
		return [];
	}

	// получаем флаг, нужно ли обрабатывать текст в пуше
	public static function getPushNeedStringProcessing(int $conversation_type):string {

		// если формируем пуш для single диалога
		if (Type_Conversation_Meta::isSubtypeOfSingle($conversation_type)) {
			return 1;
		}

		// если формируем пуш для group диалога
		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {
			return 2;
		}

		return 0;
	}

	// содержание для пуша
	public static function getPushBody(array $message, int $conversation_type, string $full_name):string {

		self::_checkVersion($message);

		$is_push_display = Type_Company_Config::init()->get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY)["value"];

		// получаем текст push уведомления
		$message_text = self::_getPushText($message, $is_push_display == 0);

		// заменяем спец синтаксис на обычный текст
		$push_body = self::_replaceSpecialSyntaxToText($message_text);

		// обрезаем и добавляем 3 точки
		if (mb_strlen($push_body) > self::_MAX_PUSH_BODY_LENGTH) {

			$temp      = mb_substr($push_body, 0, self::_MAX_PUSH_BODY_LENGTH - 3);
			$push_body = $temp . "...";
		}

		// если формируем пуш для сообщения-Напоминания
		if (Type_Conversation_Message_Main::getHandler($message)::isSystemBotRemind($message)) {

			$push_body = $full_name . "\n" . $push_body;
			return $push_body;
		}

		// если формируем пуш для сообщения о годовщине
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) === CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY) {
			return $push_body;
		}

		// если формируем пуш для group диалога
		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {
			$push_body = $full_name . ": " . $push_body;
		}
		return $push_body;
	}

	/**
	 * Локализация пуша
	 *
	 * @param array  $message
	 * @param int    $conversation_type
	 * @param string $full_name
	 * @param int    $sender_npc_type
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public static function getPushBodyLocale(array $message, int $conversation_type, string $full_name, int $sender_npc_type):array {

		self::_checkVersion($message);

		$push_body_display_config = Type_Company_Config::init()->get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY);
		$is_need_hide_push        = $push_body_display_config["value"] == 0;

		$push_locale = new Body(Message::CONVERSATION_ENTITY);

		// если формируем пуш для group диалога
		if (Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)
			&& Type_Conversation_Message_Main::getHandler($message)::getType($message) !== CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY) {
			$push_locale = $push_locale->setIsGroup()->addArg($full_name);
		}

		// если это сообщение от бота службы поддержки
		if (Main::isSystemBotSupport($sender_npc_type)) {

			// пересоздадим push_locale, чтобы она была чиста от значений локализаций
			$push_locale = new Body(Message::CONVERSATION_ENTITY);

			// пометим, что сообщение от бота службы поддержки
			$push_locale = $push_locale->setIsSupportBotSender();
		}

		// если надо скрыть пуш - скрываем и отдаем результат
		if ($is_need_hide_push && in_array($message["type"], self::_PUSH_HIDDEN_MESSAGE_TYPE_LIST)) {
			return $push_locale->setType(Body::MESSAGE_HIDDEN)->getLocaleResult();
		}

		// если это сообщение от бота службы поддержки
		if (Main::isSystemBotSupport($sender_npc_type)) {

			// устанавливаем тип локали через отдельную функцию
			return self::_setPushLocaleTypeForSystemBotSupport($message, $push_locale)->getLocaleResult();
		}

		// в зависимости от типа дополняем объект и сразу возвращаем
		return self::_setPushLocaleType($message, $push_locale)->getLocaleResult();
	}

	/**
	 * Получить тип сообщения для локализации сообщения от системного бота службы поддержки
	 *
	 * @return Domain_Push_Entity_Locale_Message_Body
	 * @throws ParseFatalException
	 */
	protected static function _setPushLocaleTypeForSystemBotSupport(array $message, Body $push_locale):Body {

		// если тип сообщения текстовый, то добавляем такой тип и аргумент (сам текст сообщения)
		// на текущий момент это нужно делать только для сообщений от бота службы поддержки,
		// поскольку нужно локализовать имя бота "Служба поддержки" на стороне клиента
		// во всех остальных кейсах это не нужно, поскольку такие сообщения нет смысла локализовывать
		if (in_array(self::getType($message), [CONVERSATION_MESSAGE_TYPE_TEXT, CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT])) {

			// подготавливаем текст к пушу
			$message_text = self::_replaceSpecialSyntaxToText(self::getText($message));

			return $push_locale->setType(Body::MESSAGE_TEXT)->addArg($message_text);
		}

		// в остальных кейсах – идем по дефолтному сценарию
		return self::_setPushLocaleType($message, $push_locale);
	}

	/**
	 * Получить тип сообщения для локализации
	 *
	 * @param array $message
	 * @param Body  $push_locale
	 *
	 * @return Body
	 * @throws ParseFatalException
	 *
	 * @long разбираем буквально каждый тип сообщения, а их много :)
	 */
	protected static function _setPushLocaleType(array $message, Body $push_locale):Body {

		return match ($message["type"]) {

			CONVERSATION_MESSAGE_TYPE_FILE => match (\CompassApp\Pack\File::getFileType($message["data"]["file_map"])) {

				FILE_TYPE_IMAGE => $push_locale->setType(Body::MESSAGE_IMAGE),
				FILE_TYPE_VIDEO => $push_locale->setType(Body::MESSAGE_VIDEO),
				FILE_TYPE_AUDIO => $push_locale->setType(Body::MESSAGE_AUDIO),
				FILE_TYPE_DOCUMENT => $push_locale->setType(Body::MESSAGE_DOCUMENT),
				FILE_TYPE_ARCHIVE => $push_locale->setType(Body::MESSAGE_ARCHIVE),
				FILE_TYPE_VOICE => $push_locale->setType(Body::MESSAGE_VOICE),
				default => $push_locale->setType(Body::MESSAGE_FILE),
			},
			CONVERSATION_MESSAGE_TYPE_INVITE => $push_locale->setType(Body::MESSAGE_INVITE),
			CONVERSATION_MESSAGE_TYPE_QUOTE, CONVERSATION_MESSAGE_TYPE_MASS_QUOTE => match ($message["data"]["text"]) {

				"" => $push_locale->setType(Body::MESSAGE_QUOTE),
				default => $push_locale->setType(Message::MESSAGE_UNKNOWN)->addArg($message["data"]["text"]),
			},
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST, CONVERSATION_MESSAGE_TYPE_REPOST => match ($message["data"]["text"]) {

				"" => $push_locale->setType(Body::MESSAGE_REPOST),
				default => $push_locale->setType(Message::MESSAGE_UNKNOWN)->addArg($message["data"]["text"]),
			},
			CONVERSATION_MESSAGE_TYPE_SYSTEM => $push_locale->setType(Body::MESSAGE_SYSTEM),
			CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST => $push_locale->setType(Body::MESSAGE_HIRING),
			CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST => $push_locale->setType(Body::MESSAGE_DISMISSAL),
			CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY => $push_locale->setType(Body::MESSAGE_EDITOR_EMPLOYEE_ANNIVERSARY),
			CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC => $push_locale->setType(Body::MESSAGE_COMPANY_EMPLOYEE_METRIC_STATISTIC),
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING => $push_locale->setType(Body::MESSAGE_SYSTEM_BOT_RATING),
			CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING => $push_locale->setType(Body::MESSAGE_EDITOR_WORKSHEET_RATING),
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND => self::_setPushLocaleTypeForRemind($message, $push_locale),
			default => $push_locale->setType(Message::MESSAGE_UNKNOWN)
		};
	}

	/**
	 * Устанавливаем тип сообщения для оригинального сообщения-напоминания
	 *
	 * @return Domain_Push_Entity_Locale_Message_Body
	 * @throws ParseFatalException
	 */
	protected static function _setPushLocaleTypeForRemind(array $message, Body $push_locale):Body {

		// если комментарий у сообщения-Напоминания не пустой, то ничего не делаем – возвращаем текущий push_locale
		if (!isEmptyString($message["data"]["text"])) {
			return $push_locale;
		}

		// достаём оригинальное сообщение из сообщения-напоминания
		[$original_message] = self::getRemindOriginalMessageList($message);

		// для оригинального сообщения устанавливаем локаль
		return self::_setPushLocaleType($original_message, $push_locale);
	}

	// получаем текст пуша
	// @long
	protected static function _getPushText(array $message, bool $is_push_hide):string {

		switch ($message["type"]) {

			case CONVERSATION_MESSAGE_TYPE_TEXT:
			case CONVERSATION_MESSAGE_TYPE_RESPECT:
			case CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA:
			case CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT:

				if ($is_push_hide) {
					return "Сообщение";
				}
				return $message["data"]["text"];

			case CONVERSATION_MESSAGE_TYPE_FILE:

				$file_type = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);

				return match ($file_type) {

					FILE_TYPE_IMAGE => "🖼 изображение",
					FILE_TYPE_VIDEO => "🎥 видео",
					FILE_TYPE_AUDIO => "🔈 аудиозапись",
					FILE_TYPE_DOCUMENT => "📋 документ",
					FILE_TYPE_ARCHIVE => "📁 архив",
					FILE_TYPE_VOICE => "🗣 голосовое сообщение",
					default => "📎 файл",
				};

			case CONVERSATION_MESSAGE_TYPE_INVITE:
				return "🤝 приглашение";

			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				if ($is_push_hide) {
					return "Сообщение";
				}
				return $message["data"]["text"] == "" ? "💬 цитата" : $message["data"]["text"];

			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:
			case CONVERSATION_MESSAGE_TYPE_REPOST:

				if ($is_push_hide) {
					return "Сообщение";
				}
				return $message["data"]["text"] == "" ? "↪️ репост" : $message["data"]["text"];

			case CONVERSATION_MESSAGE_TYPE_SYSTEM:
				return "Системное сообщение";

			case CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST:
				return "Заявка на наём";

			case CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST:
				return "Заявка на увольнение";

			case CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY:
				return "Участник сегодня празднует годовщину";

			case CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND:

				if ($is_push_hide) {
					return "Сообщение";
				}

				// если комментарий у сообщения-Напоминания пустой
				if (isEmptyString($message["data"]["text"])) {

					// сначала достаём оригинальное сообщение из сообщения-Напоминания
					[$original_message] = self::getRemindOriginalMessageList($message);

					// в зависимости от типа оригинала-сообщения готов получаем текст для сообщения-Напоминания
					return self::_getPushText($original_message, $is_push_hide);
				}

				return $message["data"]["text"];

			default:
				return "";
		}
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

	// устанавливаем client_message_id
	public static function setClientMessageId(array $message, string $client_message_id):array {

		self::_checkVersion($message);

		$message["client_message_id"] = $client_message_id;
		return $message;
	}

	// возвращает invite_map дял сообщения с пригалшением
	public static function getInviteMap(array $message):string {

		self::_checkVersion($message);

		if (!isset($message["data"]["invite_map"])) {
			throw new ParseFatalException(__METHOD__ . ": passed invalid message that not contain invite_map");
		}

		return $message["data"]["invite_map"];
	}

	// получаем список сообщений репоста
	public static function getRepostedMessageList(array $repost_message):array {

		$repost_message_type = Type_Conversation_Message_Main::getHandler($repost_message)::getType($repost_message);
		return match ($repost_message_type) {

			CONVERSATION_MESSAGE_TYPE_REPOST
			=> Type_Conversation_Message_Main::getHandler($repost_message)::getRepostedMessageListFromConversation($repost_message),
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST, THREAD_MESSAGE_TYPE_REPOST, THREAD_MESSAGE_TYPE_CONVERSATION_REPOST
			=> Type_Conversation_Message_Main::getHandler($repost_message)::getRepostedMessageListFromThread($repost_message),
			default
			=> throw new ParseFatalException("Trying get reposted message list not from repost: " . var_export($repost_message, true)),
		};
	}

	// получаем список сообщений репоста в диалоге
	public static function getRepostedMessageListFromConversation(array $message):array {

		// проверяем что сообщение имеет тип репост из диалога
		if (Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != CONVERSATION_MESSAGE_TYPE_REPOST) {
			throw new ParseFatalException("Trying get reposted message list not from repost");
		}

		return $message["data"]["repost_message_list"];
	}

	// получаем список file_map из репоста/цитаты
	public static function getFileMapListFromRepostOrQuote(array $message, array $file_map_list = [], array $file_uuid_list = []):array {

		// если это репост, то собираем file_map среди его репостнутых
		if (Type_Conversation_Message_Main::getLastVersionHandler()::isRepost($message)) {

			[$reposted_file_map_list, $reposted_file_uuid_list] = Type_Conversation_Message_Main::getLastVersionHandler()::getFileMapListFromAnyRepost($message);
			return [array_merge($file_map_list, $reposted_file_map_list), array_merge($file_uuid_list, $reposted_file_uuid_list)];
		}

		// если это цитата, то собираем file_map среди его процитированных
		if (Type_Conversation_Message_Main::getLastVersionHandler()::isQuote($message)) {

			[$quoted_file_map_list, $quoted_file_uuid_list] = Type_Conversation_Message_Main::getLastVersionHandler()::getFileMapListFromAnyQuote($message);
			return [array_merge($file_map_list, $quoted_file_map_list), array_merge($file_uuid_list, $quoted_file_uuid_list)];
		}

		// если сообщение - файл
		if (Type_Conversation_Message_Main::getLastVersionHandler()::isAnyFile($message)) {

			$file_map_list[]  = Type_Conversation_Message_Main::getLastVersionHandler()::getFileMap($message);
			$file_uuid_list[] = Type_Conversation_Message_Main::getLastVersionHandler()::getFileUuid($message);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// функция получает список file_map файлов из сообщений репоста
	public static function getFileMapListFromAnyRepost(array $message):array {

		$repost_type_list = [
			CONVERSATION_MESSAGE_TYPE_REPOST,
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
		];

		// проверяем что сообщение имеет тип репост
		if (!in_array(Type_Conversation_Message_Main::getLastVersionHandler()::getType($message), $repost_type_list)) {
			throw new ParseFatalException("Trying get file map list not from repost");
		}

		// проходим по сообщениям в репосте, ищем сообщения с типом - файл
		$file_map_list         = [];
		$file_uuid_list        = [];
		$reposted_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getRepostedMessageList($message);
		foreach ($reposted_message_list as $v) {
			[$file_map_list, $file_uuid_list] = self::getFileMapListFromRepostOrQuote($v, $file_map_list, $file_uuid_list);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// функция получает список file_map файлов из сообщений репоста
	public static function getFileMapListFromRepost(array $message):array {

		// проверяем что сообщение имеет тип репост
		if (Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != CONVERSATION_MESSAGE_TYPE_REPOST) {
			throw new ParseFatalException("Trying get file map list not from repost");
		}

		// проходим по сообщениям в репосте, ищем сообщения с типом - файл
		$file_map_list         = [];
		$file_uuid_list        = [];
		$reposted_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getRepostedMessageList($message);
		foreach ($reposted_message_list as $v) {
			[$file_map_list, $file_uuid_list] = self::getFileMapListFromRepostOrQuote($v, $file_map_list, $file_uuid_list);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// получаем список пересылаемых сообщений репоста из треда
	public static function getRepostedMessageListFromThread(array $message):array {

		// проверяем что сообщение имеет тип репост из треда
		if (
			Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != CONVERSATION_MESSAGE_TYPE_THREAD_REPOST
			&& Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != THREAD_MESSAGE_TYPE_REPOST
			&& Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != THREAD_MESSAGE_TYPE_CONVERSATION_REPOST
		) {
			throw new ParseFatalException("Trying get reposted message list not from thread repost");
		}

		return $message["data"]["repost_message_list"] ?? $message["data"]["reposted_message_list"];
	}

	// получаем количество репостнутых сообщений
	public static function getRepostedMessageCount(array $reposted_message_list):int {

		$reposted_message_count = 0;
		foreach ($reposted_message_list as $v) {

			// если попалось сообщение типа repost, то подсчитываем количество сообщений в нем
			if (in_array($v["type"], [CONVERSATION_MESSAGE_TYPE_REPOST, CONVERSATION_MESSAGE_TYPE_THREAD_REPOST])) {
				$reposted_message_count += self::getRepostedMessageCount($v["data"]["repost_message_list"]);
			}

			$reposted_message_count++;
		}

		return $reposted_message_count;
	}

	// получаем количество процитированных сообщений
	public static function getQuotedMessageCount(array $quoted_message_list):int {

		$quoted_message_count = 0;
		foreach ($quoted_message_list as $v) {

			// если попалось сообщение типа цитата, то подсчитываем количество сообщений в нем
			if (self::isQuote($v)) {
				$quoted_message_count += self::getQuotedMessageCount($v["data"]["quoted_message_list"]);
			}

			if (self::isRepost($v)) {
				$quoted_message_count += self::getRepostedMessageCount($v["data"]["repost_message_list"]);
			}

			$quoted_message_count++;
		}

		return $quoted_message_count;
	}

	// получаем количество процитированных/репостнутых сообщений
	public static function getMessageCountIfRepostOrQuote(array $message):int {

		$message_list = [];

		if (self::isRepost($message)) {
			$message_list = self::getRepostedMessageList($message);
		}

		if (self::isQuote($message)) {
			$message_list = self::getQuotedMessageList($message);
		}

		if (count($message_list) < 1) {
			throw new ParseFatalException("Can't get reposted or quoted message list");
		}

		$message_count = 0;
		foreach ($message_list as $v) {

			// если попалось сообщение типа цитата или репост, то подсчитываем количество сообщений в нем
			if (self::isRepost($v) || self::isQuote($v)) {
				$message_count += self::getMessageCountIfRepostOrQuote($v);
			}

			$message_count++;
		}

		return $message_count;
	}

	// получаем список file_map файлов из сообщений репоста из треда
	public static function getFileMapListFromThreadRepost(array $message):array {

		// проверяем что сообщение имеет тип репост из треда
		if (Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != CONVERSATION_MESSAGE_TYPE_THREAD_REPOST) {
			throw new ParseFatalException("Trying get file map list not from thread repost");
		}

		// ищем файлы в сообщениях репоста из треда
		$file_map_list         = [];
		$file_uuid_list        = [];
		$reposted_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getRepostedMessageListFromThread($message);
		foreach ($reposted_message_list as $v) {
			[$file_map_list, $file_uuid_list] = self::getFileMapListFromRepostOrQuote($v, $file_map_list, $file_uuid_list);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// возвращает conversation_map диалога донора из сообщения с типом репост
	public static function getDonorConversationMap(array $message):string {

		self::_checkVersion($message);

		// если сообщение удалено
		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {

			// получаем первоначальный тип сообщения
			$message["type"] = self::getOriginalType($message);
		}

		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_REPOST) {
			throw new ParseFatalException(__METHOD__ . ": passed not repost type message");
		}

		// получаем первое репостнутное сообщение из тела
		$reposted_message = $message["data"]["repost_message_list"][0];

		// получаем conversation_map диалога донора
		return \CompassApp\Pack\Message\Conversation::getConversationMap($reposted_message["message_map"]);
	}

	// возвращает thread_map треда донора откуда был совершен репост
	public static function getDonorThreadMap(array $message):string {

		self::_checkVersion($message);

		// если сообщение удалено
		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {

			// получаем первоначальный тип сообщения
			$message["type"] = self::getOriginalType($message);
		}

		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_THREAD_REPOST) {
			throw new ParseFatalException(__METHOD__ . ": passed not thread_repost type message");
		}

		// получаем первое репостнутное сообщение из тела
		$reposted_message = $message["data"]["repost_message_list"][0];

		// получаем thread_map диалога донора
		return \CompassApp\Pack\Message\Thread::getThreadMap($reposted_message["message_map"]);
	}

	// возвращает первоначальный тип для УДАЛЕННОГО сообщения
	// ИСПОЛЬЗУЕМ ФУНКЦИЮ С УМОМ! рекомендуется юзать для сообщений только что удаленных
	public static function getOriginalType(array $message):int {

		self::_checkVersion($message);

		// если сообщение не удалено
		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_DELETED) {
			throw new ParseFatalException(__METHOD__ . ": passed not deleted type message");
		}

		return $message["extra"]["original_message_type"] ?? CONVERSATION_MESSAGE_TYPE_DELETED;
	}

	// функция получает список file_map файлов из сообщений цитаты
	public static function getFileMapListFromMassQuote(array $message):array {

		// проверяем что сообщение имеет тип цитата из треда
		if (Type_Conversation_Message_Main::getLastVersionHandler()::getType($message) != CONVERSATION_MESSAGE_TYPE_MASS_QUOTE) {
			throw new ParseFatalException("Trying get file map list not from quote");
		}

		// проходим по сообщениям в цитате, ищем сообщения с типом - файл
		$file_map_list  = [];
		$file_uuid_list = [];

		$quote_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getQuotedMessageList($message);
		foreach ($quote_message_list as $v) {
			[$file_map_list, $file_uuid_list] = self::getFileMapListFromRepostOrQuote($v, $file_map_list, $file_uuid_list);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// функция получает список file_map файлов из сообщений цитаты
	public static function getFileMapListFromAnyQuote(array $message):array {

		$quote_type_list = [
			CONVERSATION_MESSAGE_TYPE_QUOTE,
			CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE,
		];

		// проверяем что сообщение имеет тип цитата
		if (!in_array(Type_Conversation_Message_Main::getLastVersionHandler()::getType($message), $quote_type_list)) {
			throw new ParseFatalException("Trying get file map list not from quote");
		}

		// проходим по сообщениям в цитате, ищем сообщения с типом - файл
		$file_map_list      = [];
		$file_uuid_list     = [];
		$quote_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getQuotedMessageList($message);
		foreach ($quote_message_list as $v) {
			[$file_map_list, $file_uuid_list] = self::getFileMapListFromRepostOrQuote($v, $file_map_list, $file_uuid_list);
		}

		return [$file_map_list, $file_uuid_list];
	}

	// получаем список file_map файлов, которые были в удаленном сообщении
	public static function getFileMapAndFileUuidListFromAnyMessage(array $message):array {

		$hidden_file_map_list  = [];
		$hidden_file_uuid_list = [];

		switch ($message["type"]) {

			// тип сообщения - файл
			case CONVERSATION_MESSAGE_TYPE_FILE:

				$hidden_file_map_list[]  = self::getFileMap($message);
				$hidden_file_uuid_list[] = self::getFileUuid($message);
				break;

			// тип сообщения - цитата
			case CONVERSATION_MESSAGE_TYPE_QUOTE:

				// получаем процитированное сообщение
				$quoted_message = self::getQuotedMessage($message);

				// проверяем что сообщение имеет тип - файл
				if (Type_Conversation_Message_Main::getHandler($quoted_message)::isAnyFile($quoted_message)) {

					// получаем file_map файла
					$hidden_file_map_list[]  = Type_Conversation_Message_Main::getHandler($quoted_message)::getFileMap($quoted_message);
					$hidden_file_uuid_list[] = Type_Conversation_Message_Main::getHandler($quoted_message)::getFileUuid($quoted_message);
				}
				break;

			// тип сообщения - массовая цитата
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				[$hidden_file_map_list, $hidden_file_uuid_list] = Type_Conversation_Message_Main::getHandler($message)::getFileMapListFromMassQuote($message);
				break;

			// тип сообщения - репост
			case CONVERSATION_MESSAGE_TYPE_REPOST:

				[$hidden_file_map_list, $hidden_file_uuid_list] = Type_Conversation_Message_Main::getHandler($message)::getFileMapListFromRepost($message);
				break;

			// тип сообщения - репост из треда
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				[$hidden_file_map_list, $hidden_file_uuid_list] = Type_Conversation_Message_Main::getHandler($message)::getFileMapListFromThreadRepost($message);
		}

		return [$hidden_file_map_list, $hidden_file_uuid_list];
	}

	// функция возвращает сообщение из цитаты
	public static function getQuotedMessage(array $message):array {

		// если сообщение не цитата
		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_QUOTE) {
			throw new ParseFatalException("Trying get quoted message not from quote");
		}

		return $message["data"]["quoted_message"];
	}

	// функция возвращает сообщения из цитаты
	public static function getQuotedMessageList(array $message):array {

		$quote_message_type = self::getType($message);
		switch ($quote_message_type) {

			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_QUOTE:
				$quoted_message_list[] = self::getQuotedMessage($message);
				break;

			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
				$quoted_message_list = self::getMassQuotedMessageList($message);
				break;

			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE:
				$quoted_message_list = self::getQuotedMessageListFromThread($message);
				break;

			case CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND:
				$quoted_message_list = self::getRemindQuotedMessageList($message);
				break;

			default:
				throw new ParseFatalException("Trying get quoted message not from quote");
		}

		return $quoted_message_list;
	}

	// функция возвращает сообщения из цитаты
	public static function getMassQuotedMessageList(array $message):array {

		// если сообщение не цитата
		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_MASS_QUOTE) {
			throw new ParseFatalException("Trying get quoted message list not from quote");
		}

		return $message["data"]["quoted_message_list"];
	}

	// функция возвращает сообщения из цитаты треда
	public static function getQuotedMessageListFromThread(array $message):array {

		// если сообщение не цитата треда
		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE) {
			throw new ParseFatalException("Trying get quoted message list not from quote of thread");
		}

		return $message["data"]["quoted_message_list"];
	}

	/**
	 * Возвращает процитированные сообщения из треда.
	 */
	public static function getQuotedMessageFromThreadList(array $message):array {

		$quoted_message_list = [];

		switch ($message["type"]) {

			case THREAD_MESSAGE_TYPE_QUOTE:
				$quoted_message_list[] = $message["data"]["quoted_message"];
				break;

			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND:
				$quoted_message_list = $message["data"]["quoted_message_list"];
				break;

			default:
				throw new ParseFatalException("Trying to get quoted_message_list of message, which is not QUOTE_TYPE");
		}

		return $quoted_message_list;
	}

	// функция возвращает сообщения из напоминания
	public static function getRemindQuotedMessageList(array $message):array {

		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND) {
			throw new ParseFatalException("Trying get quoted message list not from remind");
		}

		return $message["data"]["quoted_message_list"];
	}

	// получает call_map звонка, прикрепленного к сообщению
	public static function getCallMap(array $message):string {

		self::_checkVersion($message);

		// если сообщение не типа звонок
		if ($message["type"] != CONVERSATION_MESSAGE_TYPE_CALL) {
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

	// получает время последнего редактирования сообщения
	public static function getLastMessageTextEditedAt(array $message):int {

		self::_checkVersion($message);

		return $message["extra"]["last_message_text_edited_at"];
	}

	// получаем mention_user_id_list
	public static function getMentionedUsers(array $message):array {

		self::_checkVersion($message);

		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return $message["mention_user_id_list"];
	}

	/**
	 * получаем платформу, из под которой было отправлено сообщение
	 *
	 * @throws \parseException
	 */
	public static function getPlatform(array $message):string {

		self::_checkVersion($message);

		return $message["platform"] ?? self::WITHOUT_PLATFORM;
	}

	// достаем репостнутые/процитированные сообщения
	public static function getRepostedOrQuotedMessageList(array $message):array {

		if (self::isRepost($message)) {
			return self::getRepostedMessageList($message);
		}

		if (self::isQuote($message) || self::isSystemBotRemind($message)) {
			return self::getQuotedMessageList($message);
		}

		if (self::isQuoteFromThread($message) || self::isSystemBotRemindFromThread($message)) {
			return self::getQuotedMessageFromThreadList($message);
		}

		throw new ParseFatalException("get message list from not repost or quote");
	}

	/**
	 * получаем данные Напоминания из сообщения-оригинала
	 *
	 * @throws ParseFatalException
	 */
	public static function getRemind(array $message):array {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return [];
		}

		return $message["extra"]["remind"];
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
	 * получаем время, когда Напоминание сработает
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

	/**
	 * получаем создателя Напоминания
	 *
	 * @throws ParseFatalException
	 */
	public static function getRemindCreator(array $message):int {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return 0;
		}

		return $message["extra"]["remind"]["creator_user_id"];
	}

	/**
	 * получаем комментарий для Напоминания
	 *
	 * @throws ParseFatalException
	 */
	public static function getRemindComment(array $message):string {

		self::_checkVersion($message);

		if (!self::isAttachedRemind($message)) {
			return "";
		}

		return $message["extra"]["remind"]["comment"];
	}

	// формируем сообщения цитаты/репоста в зависимости от типа сообщения
	public static function makeRepostedOrQuotedMessageList(array $data_message_list, array $chunk_message_list, int $key):array {

		switch ($data_message_list["type"]) {

			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				$chunk_message_list[$key][] = self::setQuotedMessageList($data_message_list["message_list"], $data_message_list["parent_message"]);
				break;

			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				$chunk_message_list[$key][] = self::setRepostedMessageList($data_message_list["message_list"], $data_message_list["parent_message"]);
				break;

			case CONVERSATION_MESSAGE_TYPE_TEXT:
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_CALL:
			case CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE:
			case CONVERSATION_MESSAGE_TYPE_RESPECT:

				$chunk_message_list[$key][] = $data_message_list["parent_message"];
				break;

			default:
				throw new ParseFatalException("message type is not processed");
		}

		return $chunk_message_list;
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
		$message["type"] = CONVERSATION_MESSAGE_TYPE_TEXT;

		return $message;
	}

	// изменяем текст сообщения
	public static function editMessageText(array $message, string $text, array $mention_user_id_list):array {

		$message["data"]["text"]               = $text;
		$message["extra"]["is_edited_by_user"] = 1;

		$message["extra"]["last_message_text_edited_at"] = intval(microtime(true) * 1000);

		$message["mention_user_id_list"] = $mention_user_id_list;
		$message["action_users_list"]    = array_merge($message["action_users_list"], $mention_user_id_list);
		$message["action_users_list"]    = array_unique($message["action_users_list"]);

		// если к сообщению прикреплен список ссылок, то удаляем его из сообщения
		$message = self::removeLinkList($message);

		// если к сообщению прикреплено превью, то избавляемся от него
		return self::removePreview($message);
	}

	// устанавливаем новый file_uid
	public static function setNewFileUid(array $message):array {

		$message["data"]["file_uid"] = generateUUID();

		return $message;
	}

	// помечает сообщение удаленным
	public static function setDeleted(array $message):array {

		self::_checkVersion($message);

		$message["extra"]["original_message_type"] = $message["type"];
		$message["type"]                           = CONVERSATION_MESSAGE_TYPE_DELETED;

		// если к сообщению прикреплены список ссылок или превью - удаляем их
		$message = self::removeLinkList($message);
		return self::removePreview($message);
	}

	// помечает сообщение удаленным системой
	public static function setSystemDeleted(array $message):array {

		self::_checkVersion($message);

		$message["extra"]["is_deleted_by_system"] = 1;

		// если к сообщению прикреплены список ссылок или превью - удаляем их
		$message = self::removeLinkList($message);
		return self::removePreview($message);
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

	// получает превью
	public static function getPreview(array $message):string {

		self::_checkVersion($message);

		return $message["preview_map"];
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

	// удаляет превью
	public static function removePreview(array $message):array {

		self::_checkVersion($message);

		// если к сообщению прикреплено превью, то избавляемся от него
		if (!self::isAttachedPreview($message)) {
			return $message;
		}

		unset($message["preview_map"]);
		unset($message["preview_type"]);
		unset($message["preview_image"]);

		return $message;
	}

	// удаляет список ссылок
	public static function removeLinkList(array $message):array {

		self::_checkVersion($message);

		if (!self::isAttachedLinkList($message)) {
			return $message;
		}

		unset($message["link_list"]);

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

	// меняем время создания сообщения
	public static function changeCreatedAt(array $message, int $created_at):array {

		$message["created_at"] = $created_at;

		return $message;
	}

	// меняем сообщение типа repost на text
	public static function changeMessageRepostToMessageText(array $repost_message, int $new_message_type):array {

		// получаем дефолтную структуру сообщения типа text
		$message = self::_getDefaultStructure($new_message_type, $repost_message["sender_user_id"]);

		// добавляем данные репоста в новое сообщение
		$message["data"]["text"]         = $repost_message["data"]["text"];
		$message["message_map"]          = $repost_message["message_map"];
		$message["created_at"]           = $repost_message["created_at"];
		$message["updated_at"]           = $repost_message["updated_at"];
		$message["client_message_id"]    = $repost_message["client_message_id"];
		$message["mention_user_id_list"] = $repost_message["mention_user_id_list"] ?? [];
		$message["platform"]             = $repost_message["platform"] ?? self::WITHOUT_PLATFORM;

		if (isset($repost_message["extra"]["is_edited_by_user"])) {
			$message["extra"]["is_edited_by_user"] = $repost_message["extra"]["is_edited_by_user"];
		}

		if (isset($repost_message["extra"]["last_message_text_edited_at"])) {
			$message["extra"]["last_message_text_edited_at"] = $repost_message["extra"]["last_message_text_edited_at"];
		}

		return $message;
	}

	// добавляем список сообщений в сообщения репоста
	public static function setRepostedMessageList(array $message_list, array $repost_message):array {

		$repost_message["data"]["repost_message_list"] = $message_list;

		return $repost_message;
	}

	// добавляем список сообщений в сообщения цитаты
	public static function setQuotedMessageList(array $message_list, array $quote_message):array {

		$quote_message["data"]["quoted_message_list"] = $message_list;

		return $quote_message;
	}

	// если внутри репостнутых/процитированных сообщений находится файл, репост или цитата, то выполняем определенные действия
	// @long - действия на тип сообщения (файл/репост/цитата)
	public static function doAdaptationIfIssetRepostOrFileOrQuote(array $message_list, int $index = 1):array {

		foreach ($message_list as $k => $message) {

			if (Type_Conversation_Message_Main::getLastVersionHandler()::isFile($message) || Type_Conversation_Message_Main::getLastVersionHandler()::isFileFromThreadRepost($message)) {

				// устанавливаем новый file_uid для файла
				$message_list[$k] = Type_Conversation_Message_Main::getLastVersionHandler()::setNewFileUid($message);

				// устанавливаем индекс для сообщений
				$message_list[$k]["reposted_message_index"] = $index;
				$index++;
				continue;
			}

			if (Type_Conversation_Message_Main::getLastVersionHandler()::isRepost($message)) {

				// если у найденного репоста имеется текст, то заменяем его на тип text
				$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::_addRepostAsTextIfIssetText($message_list, $k, $message);

				// достаем сообщения бывшего репоста
				$ex_repost_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getRepostedMessageList($message);

				// все сообщения бывшего репоста делаем частью списка сообщений
				$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::_setMessageListOfRepost($message_list, $ex_repost_message_list, $k, $index);

				// так как список сообщений сильно изменился, то проходимся еще раз по нему
				return Type_Conversation_Message_Main::getLastVersionHandler()::doAdaptationIfIssetRepostOrFileOrQuote($message_list, $index);
			}

			if (Type_Conversation_Message_Main::getLastVersionHandler()::isQuote($message)) {

				// получаем список цитированных сообщений у цитаты
				$ex_quote_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getQuotedMessageList($message);

				// превращаем комментарий цитаты в новое текстовое сообщение
				$new_message = Type_Conversation_Message_Main::getLastVersionHandler()::_changeMessageRepostOrQuoteToMessageText($message);

				// и удаляем цитату из прошлого списка
				unset($message_list[$k]);

				// вставляем новое сообщение в нужное место списка, в зависимости от константы
				array_push($ex_quote_message_list, $new_message);

				// все сообщения бывшей цитаты делаем частью списка сообщений
				$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::_setMessageListOfQuote($message_list, $ex_quote_message_list, $k, $index);

				// так как список сообщений сильно изменился, то проходимся еще раз по нему
				return Type_Conversation_Message_Main::getLastVersionHandler()::doAdaptationIfIssetRepostOrFileOrQuote($message_list, $index);
			}

			// устанавливаем индекс для сообщений
			$message_list[$k]["reposted_message_index"] = $index;
			$index++;
		}

		return $message_list;
	}

	// устанавливаем сообщения репоста/цитаты в список сообщений
	protected static function _setMessageListOfQuote(array $main_message_list, array $added_message_list, int $deleted_message_index, int $index):array {

		// берем список сообщений до индекса удаленного сообщения
		$message_list_1 = array_slice($main_message_list, 0, $deleted_message_index);

		// берем список сообщений после индекса удаленного сообщения
		$message_list_2 = array_slice($main_message_list, $deleted_message_index);

		// добавляем между полученными списками сообщений сообщения репоста/цитаты
		$new_message_list = array_merge($message_list_1, $added_message_list, $message_list_2);

		// устанавливаем reposted_message_index для сообщений, чтобы они корректно выстраивались в репосте
		return self::_setRepostedMessageIndexForMessageList($new_message_list, $index);
	}

	// меняем сообщение на тип текст, если имеется текст у репоста/цитаты
	protected static function _addRepostAsTextIfIssetText(array $message_list, int $ex_repost_index, array $ex_repost):array {

		// меняем структуру репоста/цитаты на сообщение типа text
		$new_message = Type_Conversation_Message_Main::getHandler($ex_repost)::_changeMessageRepostOrQuoteToMessageText($ex_repost);

		// ставим измененное сообщение на то же место
		$message_list[$ex_repost_index] = $new_message;

		return $message_list;
	}

	// меняем сообщение репоста/цитаты на сообщение типа text
	protected static function _changeMessageRepostOrQuoteToMessageText(array $ex_repost):array {

		if (\CompassApp\Pack\Message::isFromConversation($ex_repost["message_map"])) {
			return Type_Conversation_Message_Main::getHandler($ex_repost)::changeMessageRepostToMessageText($ex_repost, CONVERSATION_MESSAGE_TYPE_TEXT);
		}

		if (\CompassApp\Pack\Message::isFromThread($ex_repost["message_map"])) {

			return Type_Conversation_Message_Main::getHandler($ex_repost)::changeMessageRepostToMessageText(
				$ex_repost,
				CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT);
		}

		throw new ReturnFatalException("Unknown message from");
	}

	// устанавливаем сообщения репоста/цитаты в список сообщений
	protected static function _setMessageListOfRepost(array $main_message_list, array $added_message_list, int $deleted_message_index, int $index):array {

		// берем список сообщений до индекса удаленного сообщения
		$message_list_1 = array_slice($main_message_list, 0, $deleted_message_index + 1);

		// берем список сообщений после индекса удаленного сообщения
		$message_list_2 = array_slice($main_message_list, $deleted_message_index + 1);

		// добавляем между полученными списками сообщений сообщения репоста/цитаты
		$new_message_list = array_merge($message_list_1, $added_message_list, $message_list_2);

		// устанавливаем reposted_message_index для сообщений, чтобы они корректно выстраивались в репосте
		return self::_setRepostedMessageIndexForMessageList($new_message_list, $index);
	}

	// устанавливаем reposted_message_index для сообщений
	protected static function _setRepostedMessageIndexForMessageList(array $message_list, int $index = 1):array {

		foreach ($message_list as $k => $v) {

			$message_list[$k] = Type_Conversation_Message_Main::getHandler($v)::_setRepostedMessageIndex($v, $index);
			$index++;
		}

		return $message_list;
	}

	// устанавливаем reposted_message_index для сообщения
	protected static function _setRepostedMessageIndex(array $message, int $reposted_message_index):array {

		$message["reposted_message_index"] = $reposted_message_index;

		return $message;
	}

	// метод преобразует сообщения из треда в сообщения диалога с их структурой
	// @long because switch ... case
	public static function transferThreadMessageListToConversationMessageStructure(array $thread_message_list, array $parent_message_data = [], array $allow_message_types = null):array {

		// флаг, имеются ли в списке пересланные сообщения
		$is_have_quote_or_repost = false;

		// если передали родительское сообщение
		$output = [];
		if (count($parent_message_data) > 0) {

			// устанавливаем новый client_message_id, чтобы не уперлись в проблему с дублированием сообщений
			$message = Type_Conversation_Message_Main::getLastVersionHandler()::setClientMessageId($parent_message_data, generateUUID());
			array_push($output, $message);
		}

		// если не переданы дозволенные типы сообщений
		if (is_null($allow_message_types)) {
			$allow_message_types = Type_Conversation_Message_Main::getLastVersionHandler()::_ALLOW_TO_TRANSFER_THREAD_MESSAGE_TO_CONVERSATION;
		}

		// проходимся по всем сообщениям из треда
		foreach ($thread_message_list as $thread_message) {

			$thread_message_type = Type_Thread_Message_Main::getHandler($thread_message)::getType($thread_message);

			// если такой тип сообщений отсутствует в списке дозволенных для этого метода
			if (!in_array($thread_message_type, $allow_message_types)) {
				throw new ParseFatalException("Not allowed message type");
			}

			switch ($thread_message_type) {

				case THREAD_MESSAGE_TYPE_TEXT:
				case THREAD_MESSAGE_TYPE_CONVERSATION_TEXT:

					// преобразуем сообщение из треда в сообщение диалога типа Text
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeText($thread_message);
					break;

				case THREAD_MESSAGE_TYPE_FILE:
				case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

					// преобразуем сообщение из треда в сообщение диалога типа File
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeFile($thread_message);
					break;

				case THREAD_MESSAGE_TYPE_QUOTE:
				case THREAD_MESSAGE_TYPE_MASS_QUOTE:
				case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:

					$is_have_quote_or_repost = true;

					// преобразуем сообщение из треда в сообщение диалога типа Quote
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeQuote($thread_message);
					break;

				case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:
				case THREAD_MESSAGE_TYPE_REPOST:

					$is_have_quote_or_repost = true;

					// преобразуем сообщение из треда в сообщение диалога типа Repost
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeRepost($thread_message);
					break;

				case THREAD_MESSAGE_TYPE_CONVERSATION_CALL:

					// преобразуем сообщение из треда в сообщение диалога типа Call
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeCall($thread_message);

					break;

				case THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE:

					// преобразуем сообщение из треда в сообщение диалога типа Call
					$message = Type_Conversation_Message_Main::getHandler($thread_message)::_transferThreadMessageToConversationMessageTypeMediaConference(
						$thread_message);

					break;

				default:
					throw new ParseFatalException("Unknown message type");
			}

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$thread_messsage_map = Type_Thread_Message_Main::getHandler($thread_message)::getMessageMap($thread_message);
			$message             = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForInsert($message, $thread_messsage_map);
			array_push($output, $message);

			// определяем, сколько же будет лимит и проверяем превышен ли
			$limit = $is_have_quote_or_repost ?
				Type_Conversation_Message_Main::getLastVersionHandler()::_MAX_SELECTED_MESSAGE_COUNT_WITH_REPOST_OR_QUOTE : Type_Conversation_Message_Main::getLastVersionHandler()::_MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE;
			if (count($output) > $limit) {
				throw new cs_Message_Limit();
			}
		}

		return $output;
	}

	// создаем из сообщения треда сообщение диалога типа Text
	protected static function _transferThreadMessageToConversationMessageTypeText(array $thread_message):array {

		// достаем данные из сообщения треда
		$sender_user_id            = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$thread_message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                  = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// формируем сообщение типа text для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeText($sender_user_id, $thread_message_text, generateUUID(), $platform);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа Call
	protected static function _transferThreadMessageToConversationMessageTypeCall(array $thread_message):array {

		// достаем данные из сообщения треда
		$sender_user_id            = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_call_map   = Type_Thread_Message_Main::getHandler($thread_message)::getCallMap($thread_message);
		$thread_message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                  = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// формируем сообщение типа call для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeCall($sender_user_id, $thread_message_call_map, $platform);

		// приклеиваем дополнительную информацию о звонке
		if (isset($thread_message["extra"]["call_report_id"]) && isset ($thread_message["extra"]["call_duration"])) {
			$message = $class_handler::attachRepostedCallInfo($message, $thread_message["extra"]["call_report_id"], $thread_message["extra"]["call_duration"]);
		}

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа media_conference
	protected static function _transferThreadMessageToConversationMessageTypeMediaConference(array $thread_message):array {

		// достаем данные из сообщения треда
		$sender_user_id                   = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_conference_id     = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceId($thread_message);
		$thread_message_conference_status = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceAcceptStatus($thread_message);
		$thread_message_conference_link   = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceLink($thread_message);
		$thread_message_created_at        = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                         = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// формируем сообщение типа call для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeMediaConference(
			$sender_user_id, $thread_message_conference_id, $thread_message_conference_status, $thread_message_conference_link, $platform);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа File
	protected static function _transferThreadMessageToConversationMessageTypeFile(array $thread_message):array {

		// достаем данные из сообщения треда
		$sender_user_id            = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$file_map                  = Type_Thread_Message_Main::getHandler($thread_message)::getFileMap($thread_message);
		$thread_message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                  = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// формируем сообщение типа file для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeFile($sender_user_id, $thread_message_text, generateUUID(), $file_map, "", $platform);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа Quote
	protected static function _transferThreadMessageToConversationMessageTypeQuote(array $thread_message):array {

		$sender_user_id            = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$thread_message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                  = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// получаем процитированные сообщения и преобразуем каждого из них
		$quoted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getQuotedMessageList($thread_message);
		$quoted_message_list = self::transferThreadMessageListToConversationMessageStructure($quoted_message_list);

		// преобразуем цитату из треда в цитату для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeMassQuote($sender_user_id, $thread_message_text, generateUUID(), $quoted_message_list, $platform, true);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа Repost
	protected static function _transferThreadMessageToConversationMessageTypeRepost(array $thread_message):array {

		$sender_user_id            = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$thread_message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$thread_message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform                  = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// получаем репостнутые сообщения и преобразуем каждого из них
		$reposted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getRepostedMessageList($thread_message);
		$reposted_message_list = self::transferThreadMessageListToConversationMessageStructure($reposted_message_list);

		// преобразуем сообщение из треда в репост для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeRepost($sender_user_id, $thread_message_text, generateUUID(), $reposted_message_list, $platform);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $thread_message_created_at);
	}

	// подготавливаем сообщения треда перед репостом
	public static function prepareThreadMessageListBeforeRepost(array $message_list, array $allow_message_types = null):array {

		// индекс сообщений
		$message_index = 1;

		// если не переданы дозволенные типы сообщений
		if (is_null($allow_message_types)) {
			$allow_message_types = self::_ALLOW_TO_PREPARE_THREAD_MESSAGE_TO_REPOST;
		}

		// для каждого сообщения из message_list
		$prepared_message_list = [];
		foreach ($message_list as $k => $v) {

			// если тип сообщения не поддерживается для этого метода
			if (!in_array(Type_Thread_Message_Main::getHandler($v)::getType($v), $allow_message_types)) {
				throw new ParseFatalException("Not allowed message type");
			}

			// формируем стандартную структуру сообщения для версии V2
			$message = self::createStandardMessageStructureV2($v, $message_index);

			// инкрементим message_index
			$message_index++;

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$thread_message_map        = Type_Thread_Message_Main::getHandler($v)::getMessageMap($v);
			$prepared_message_list[$k] = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForInsert($message, $thread_message_map);
		}

		return $prepared_message_list;
	}

	// метод для создания стандартной стуктуры сообщения версии V2
	// @long - из-за switch..case
	public static function createStandardMessageStructureV2(array $thread_message, int $message_index):array {

		// получаем класс обработчика последней версии для сообщения диалога
		$last_handler_class = Type_Conversation_Message_Main::getLastVersionHandler();

		// получаем отправителя и текст сообщения из треда
		$sender_user_id       = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$message_text         = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$mention_user_id_list = Type_Thread_Message_Main::getHandler($thread_message)::getMentionUserIdList($thread_message);

		// в зависимости от типа сообщения из треда
		$thread_message_type = Type_Thread_Message_Main::getHandler($thread_message)::getType($thread_message);
		switch ($thread_message_type) {

			case THREAD_MESSAGE_TYPE_TEXT:
			case THREAD_MESSAGE_TYPE_CONVERSATION_TEXT:

				$client_message_id    = Type_Thread_Message_Main::getHandler($thread_message)::getClientMessageId($thread_message);
				$message_created_at   = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
				$mention_user_id_list = Type_Thread_Message_Main::getHandler($thread_message)::getMentionUserIdList($thread_message);
				$platform             = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);
				$link_list            = Type_Thread_Message_Main::getHandler($thread_message)::getLinkListIfExist($thread_message);

				$reposted_message = $last_handler_class::makeThreadRepostItemText(
					$sender_user_id,
					$message_text,
					$client_message_id,
					$message_created_at,
					$mention_user_id_list,
					$platform,
					$link_list);
				break;

			case THREAD_MESSAGE_TYPE_FILE:
			case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

				$client_message_id  = Type_Thread_Message_Main::getHandler($thread_message)::getClientMessageId($thread_message);
				$file_map           = Type_Thread_Message_Main::getHandler($thread_message)::getFileMap($thread_message);
				$file_name          = Type_Thread_Message_Main::getHandler($thread_message)::getFileName($thread_message);
				$message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
				$platform           = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

				$reposted_message = $last_handler_class::makeThreadRepostItemFile(
					$sender_user_id,
					$message_text,
					$client_message_id,
					$file_map,
					$message_created_at,
					$file_name,
					$platform);
				break;

			case THREAD_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:

				$client_message_id   = Type_Thread_Message_Main::getHandler($thread_message)::getClientMessageId($thread_message);
				$message_created_at  = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
				$quoted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getQuotedMessageList($thread_message);
				$platform            = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

				$reposted_message = $last_handler_class::makeThreadRepostItemQuoteV2(
					$sender_user_id,
					$message_text,
					$client_message_id,
					$quoted_message_list,
					$message_created_at,
					$message_index,
					$mention_user_id_list,
					$platform);
				break;

			case THREAD_MESSAGE_TYPE_REPOST:
			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:

				$reposted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getRepostedMessageList($thread_message);
				$message_created_at    = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
				$platform              = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

				// получаем репостнутые сообщения и преобразуем каждого из них
				$reposted_message_list = self::_transferThreadMessageListToConversationMessageStructureV2($reposted_message_list, $message_index);

				// преобразуем сообщение в репост для диалога
				$reposted_message = $last_handler_class::makeRepost($sender_user_id, $message_text, generateUUID(), $reposted_message_list, $platform);

				// В case выше и ниже поле created_at передается в метод. Здесь мы не можем сделать так же
				// т.к. метод много откуда вызывается и это может оказаться болезненно
				// во избежание боли, решено установить время после вызова makeRepost
				$reposted_message = $last_handler_class::changeCreatedAt($reposted_message, $message_created_at);
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_CALL:

				$call_map = Type_Thread_Message_Main::getHandler($thread_message)::getCallMap($thread_message);
				$platform = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

				// преобразуем сообщение в звонок для диалога
				$reposted_message = $last_handler_class::makeCall($sender_user_id, $call_map, $platform);

				// приклеиваем дополнительную информацию о звонке
				if (isset($thread_message["extra"]["call_report_id"]) && isset($thread_message["extra"]["call_duration"])) {

					$reposted_message = $last_handler_class::attachRepostedCallInfo(
						$reposted_message, $thread_message["extra"]["call_report_id"], $thread_message["extra"]["call_duration"]);
				}
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE:

				$conference_id = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceId($thread_message);
				$status        = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceAcceptStatus($thread_message);
				$link          = Type_Thread_Message_Main::getHandler($thread_message)::getConferenceLink($thread_message);
				$platform      = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

				// преобразуем сообщение в звонок для диалога
				$reposted_message = $last_handler_class::makeMediaConference($sender_user_id, $conference_id, $status, $link, $platform);

				break;
			default:

				Gateway_Bus_Statholder::inc("conversations", "row323");
				throw new ParseFatalException("Unknown message type");
		}
		$reposted_message["reposted_message_index"] = $message_index;

		return $reposted_message;
	}

	// метод преобразует сообщения из треда в сообщения диалога с их структурой - версии V2
	// @long because switch ... case
	protected static function _transferThreadMessageListToConversationMessageStructureV2(array $thread_message_list, int $message_index):array {

		$output = [];

		// проходимся по всем сообщениям
		foreach ($thread_message_list as $v) {

			// получаем тип сообщения треда и проходимся по действиям в зависимости от типа
			$thread_message_type = Type_Thread_Message_Main::getHandler($v)::getType($v);
			$message             = match ($thread_message_type) {

				THREAD_MESSAGE_TYPE_TEXT, THREAD_MESSAGE_TYPE_CONVERSATION_TEXT
				=> self::_transferThreadMessageToConversationMessageTypeTextV2($v),
				THREAD_MESSAGE_TYPE_FILE, THREAD_MESSAGE_TYPE_CONVERSATION_FILE
				=> self::_transferThreadMessageToConversationMessageTypeFileV2($v),
				THREAD_MESSAGE_TYPE_QUOTE,
				THREAD_MESSAGE_TYPE_MASS_QUOTE,
				THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE
				=> self::_transferThreadMessageToConversationMessageTypeQuoteV2(
					$v,
					$message_index
				),
				THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST
				=> self::_transferThreadMessageToConversationMessageTypeRepostV2(
					$v,
					$message_index
				),
				THREAD_MESSAGE_TYPE_CONVERSATION_CALL
				=> self::_transferThreadMessageToConversationMessageTypeCall($v),
				THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE
				=> self::_transferThreadMessageToConversationMessageTypeMediaConference($v),
				default
				=> throw new ParseFatalException("Unknown message type"),
			};

			$message["reposted_message_index"] = $message_index;

			// инкрементим message_index
			$message_index++;

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$message = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForInsert($message, $v["message_map"]);
			array_push($output, $message);

			if (count($output) > self::_MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE) {
				throw new cs_Message_Limit();
			}
		}

		return $output;
	}

	// создаем из сообщения треда сообщение диалога типа Text
	protected static function _transferThreadMessageToConversationMessageTypeTextV2(array $thread_message):array {

		$sender_user_id     = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform           = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// создаем сообщение типа text для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$message       = $class_handler::makeText($sender_user_id, $message_text, generateUUID(), $platform);

		// меняем created_at на тот что был у сообщения треда
		return self::changeCreatedAt($message, $message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа File
	protected static function _transferThreadMessageToConversationMessageTypeFileV2(array $thread_message):array {

		$sender_user_id     = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$message_text       = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$file_map           = Type_Thread_Message_Main::getHandler($thread_message)::getFileMap($thread_message);
		$message_created_at = Type_Thread_Message_Main::getHandler($thread_message)::getCreatedAt($thread_message);
		$platform           = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// создаем сообщение типа file для диалога
		$class_handler = Type_Conversation_Message_Main::getLastVersionHandler();
		$new_message   = $class_handler::makeFile($sender_user_id, $message_text, generateUUID(), $file_map, "", $platform);

		// меняем created_at на тот что был у сообщения треда
		return Type_Conversation_Message_Main::getHandler($new_message)::changeCreatedAt($new_message, $message_created_at);
	}

	// создаем из сообщения треда сообщение диалога типа Quote (version 2)
	protected static function _transferThreadMessageToConversationMessageTypeQuoteV2(array $thread_message, int $message_index):array {

		$quoted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getQuotedMessageList($thread_message);
		$sender_user_id      = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$message_text        = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$platform            = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// преобразуем каждое из процитированных в сообщении треда
		$quoted_message_list = self::_transferThreadMessageListToConversationMessageStructureV2($quoted_message_list, $message_index);

		// преобразуем цитату из треда в цитату для диалога
		$handler_class = Type_Conversation_Message_Main::getLastVersionHandler();
		return $handler_class::makeMassQuote($sender_user_id, $message_text, generateUUID(), $quoted_message_list, $platform, true);
	}

	// создаем из сообщения треда сообщение диалога типа Repost (version 2)
	protected static function _transferThreadMessageToConversationMessageTypeRepostV2(array $thread_message, int $message_index):array {

		$reposted_message_list = Type_Thread_Message_Main::getHandler($thread_message)::getRepostedMessageList($thread_message);
		$sender_user_id        = Type_Thread_Message_Main::getHandler($thread_message)::getSenderUserId($thread_message);
		$message_text          = Type_Thread_Message_Main::getHandler($thread_message)::getText($thread_message);
		$platform              = Type_Thread_Message_Main::getHandler($thread_message)::getPlatform($thread_message);

		// преобразуем каждого из репостнутых в сообщении треда
		$reposted_message_list = self::_transferThreadMessageListToConversationMessageStructureV2($reposted_message_list, $message_index);

		// преобразуем сообщение в репост для диалога
		return Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost($sender_user_id, $message_text, generateUUID(), $reposted_message_list, $platform);
	}

	# endregion
	##########################################################

	##########################################################
	# region функции отвечающие на вопросы бизнес логики
	##########################################################

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

	// является ли сообщение простым текстовым
	public static function isText(array $message):bool {

		return in_array($message["type"], [CONVERSATION_MESSAGE_TYPE_TEXT]);
	}

	/**
	 * Является ли сообщение цитатой из треда или диалога.
	 */
	public static function isQuoteFromThread(array $message):bool {

		$quote_type_list = [
			THREAD_MESSAGE_TYPE_QUOTE,
			THREAD_MESSAGE_TYPE_MASS_QUOTE,
			THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE,
		];

		if (in_array($message["type"], $quote_type_list)) {
			return true;
		}

		return in_array($message["type"], $quote_type_list) || static::isQuote($message);
	}

	// является ли сообщение цитатой
	public static function isQuote(array $message):bool {

		$quote_type_list = [
			CONVERSATION_MESSAGE_TYPE_QUOTE,
			CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE,
		];

		if (!in_array($message["type"], $quote_type_list)) {
			return false;
		}

		if (!isset($message["data"]["quoted_message_list"]) && !isset($message["data"]["quoted_message"])) {
			return false;
		}

		return true;
	}

	// является ли сообщение репостом
	public static function isRepost(array $message):bool {

		return in_array(
			$message["type"],
			[CONVERSATION_MESSAGE_TYPE_REPOST, CONVERSATION_MESSAGE_TYPE_THREAD_REPOST, THREAD_MESSAGE_TYPE_CONVERSATION_REPOST, THREAD_MESSAGE_TYPE_REPOST]);
	}

	// является ли сообщение файлом
	public static function isFile(array $message):bool {

		return in_array($message["type"], [CONVERSATION_MESSAGE_TYPE_FILE, THREAD_MESSAGE_TYPE_FILE]);
	}

	/**
	 * является ли сообщение голосовым сообщением
	 */
	public static function isVoiceMessage(array $message):bool {

		return self::isFile($message) && \CompassApp\Pack\File::getFileType(self::getFileMap($message)) == FILE_TYPE_VOICE;
	}

	// является ли сообщение звонком
	public static function isCall(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_CALL;
	}

	// является ли сообщение репоста из треда файлом
	public static function isFileFromThreadRepost(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE;
	}

	// является ли сообщение каким-либо файлом?
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

	// является ли сообщение от системного бота?
	public static function isSystemBotText(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT;
	}

	// является ли сообщение файлом от системного бота?
	public static function isSystemBotFile(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE;
	}

	// является ли сообщение Напоминанием?
	public static function isSystemBotRemind(array $message):bool {

		return $message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
	}

	// является ли сообщение Напоминанием из треда?
	public static function isSystemBotRemindFromThread(array $message):bool {

		return $message["type"] == THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND;
	}

	// можно ли ставить реакцию
	// не проверяем, что сообщение скрыто пользователем ставившим реакцию, чтобы при convert горячих реакций
	// выставлять их пользователями, скрывшими сообщение
	public static function isAllowToReaction(array $message, int $user_id,):bool {

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

	// можно ли ставить прикрепить тред к сообщению
	public static function isAllowToThread(array $message, int $user_id):bool {

		// если тип сообщения не позволяет прикрепить тред
		if (!in_array($message["type"], self::_ALLOW_TO_THREAD)) {
			return false;
		}

		// если пользователь скрыл это сообщение
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		return true;
	}

	// можно ли редактировать сообщение, проверяем флаги и тип сообщения
	public static function isFlagsAllowToEdit(array $message, int $user_id):bool {

		// если тип сообщения не позволяет его редактировать
		if (!in_array($message["type"], self::_ALLOW_TO_EDIT)) {
			return false;
		}

		// пользователь - отправитель сообщения?
		if ($message["sender_user_id"] != $user_id) {
			return false;
		}

		// если пользователь скрыл это сообщение
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли редактировать сообщение, проверяем время
	public static function isTimeAllowToEdit(array $message, bool $is_unusual_message = false):bool {

		$message_created_at = $message["created_at"];

		// если сообщение необычное, из особенного чата (например, Личный Heroes), то берем иное время создания сообщения
		if ($is_unusual_message) {
			$message_created_at = self::getAdditionalWorkedHoursMessageCreatedAt($message);
		}

		// если время отправки сообщения не позволяет его редактировать
		if (time() > $message_created_at + self::_ALLOW_TO_EDIT_TIME) {
			return false;
		}

		// условие для тестирование вышедшего времени редактирования
		if (Type_System_Testing::isForceExpireTimeEdit()) {
			return false;
		}

		return true;
	}

	// можно ли удалять сообщение, проверяем флаги и тип сообщения
	// была убрана проверка на то, что удалить сообщение может только его отправитель,
	// потому что добавлена логика удаления чужих сообщений администратором в групповых диалогах
	public static function isFlagsAllowToDelete(array $message, int $user_id):bool {

		// если тип сообщения не позволяет его удалять
		if (!in_array($message["type"], self::_ALLOW_TO_DELETE)) {
			return false;
		}

		// если пользователь скрыл это сообщение
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли удалять сообщение, проверяем время отправки сообщения
	public static function isTimeAllowToDelete(array $message, bool $is_unusual_message = false):bool {

		// условие для удаления сообщения без проверку
		if (Type_System_Testing::isForceSetDeleted()) {
			return true;
		}

		$message_created_at = $message["created_at"];

		// если сообщение необычное, из особенного чата (например, Личный Heroes), то берем иное время создания сообщения
		if ($is_unusual_message) {
			$message_created_at = self::getAdditionalWorkedHoursMessageCreatedAt($message);
		}

		// если время отправки сообщения не позволяет его редактировать
		if (time() > $message_created_at + self::_ALLOW_TO_DELETE_TIME) {
			return false;
		}

		// условие для тестирование вышедшего времени удаления
		if (Type_System_Testing::isForceExpireTimeDelete()) {
			return false;
		}

		return true;
	}

	// можно ли цитировать сообщение (старая версия)
	public static function isAllowToQuote(array $message, int $user_id):bool {

		self::_checkVersion($message);

		// если тип сообщения не позволяет его цитировать
		if (!in_array($message["type"], self::_ALLOW_TO_QUOTE_LEGACY)) {
			return false;
		}

		// если сообщение является достижением
		if (self::isContainAdditionalAchievement($message)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли цитировать сообщение (новая версия)
	public static function isAllowToQuoteNew(array $message):bool {

		self::_checkVersion($message);

		// если тип сообщения не позволяет его цитировать
		if (!in_array($message["type"], self::_ALLOW_TO_QUOTE)) {
			return false;
		}

		// если сообщение является достижением
		if (self::isContainAdditionalAchievement($message)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяет, может ли сообщение быть прочитанным.
	 *
	 * @throws \parseException
	 */
	public static function isAllowedForMarkAsRead(array $message):bool {

		self::_checkVersion($message);
		return in_array($message["type"], self::_ALLOW_TO_MARK_AS_READ);
	}

	// можно ли репостить сообщение
	public static function isAllowToRepost(array $message, int $user_id):bool {

		self::_checkVersion($message);

		$is_allow_new_repost = self::_isAllowNewRepost();

		// если тип сообщения не позволяет его репостить
		$allow_repost_type_list = $is_allow_new_repost ? self::_ALLOW_TO_REPOST : self::_ALLOW_TO_REPOST_LEGACY;
		if (!in_array($message["type"], $allow_repost_type_list)) {
			return false;
		}

		// если сообщение является достижением
		if (self::isContainAdditionalAchievement($message)) {
			return false;
		}

		// если сообщение скрыто пользователем
		if (!$is_allow_new_repost && self::isHiddenByUser($message, $user_id)) {
			return false;
		}

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		return true;
	}

	// можно ли репостить новый репост?
	protected static function _isAllowNewRepost():bool {

		if (ServerProvider::isTest()) {
			return true;
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

	// можно ли отправлять боту
	public static function isMessageNeedSendToBot(array $message, int $user_id):bool {

		// если тип сообщения не позволяет его цитировать
		if (!in_array($message["type"], self::_ALLOW_TO_BOT)) {
			return false;
		}

		// если отправитель сообщения бот
		if (self::getSenderUserId($message) == $user_id) {
			return false;
		}

		return true;
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

		// если сообщение - респект, требовательность или достижение
		if (self::isContainAdditionalRespect($message) || self::isContainAdditionalExactingness($message) || self::isContainAdditionalAchievement($message)) {
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

	// сообщение удалено?
	public static function isDeleted(array $message):bool {

		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {
			return true;
		}

		return false;
	}

	// проверяет возможность прятать сообщения определенного типа
	public static function isAllowToHide(array $message):bool {

		// если отправителем сообщения является пользовательский бот
		if (self::isUserbotSender($message)) {
			return false;
		}

		// если тип сообщения не позволяет прикрепить тред
		return in_array($message["type"], self::_ALLOW_TO_HIDE);
	}

	// пользователь упомянут в сообщении?
	public static function isUserMention(array $message, int $user_id):bool {

		if (!isset($message["mention_user_id_list"])) {
			return false;
		}

		// если пользователь упомянул сам себя
		if ($message["sender_user_id"] == $user_id) {
			return false;
		}

		return in_array($user_id, $message["mention_user_id_list"]);
	}

	// можно ли редактировать с пустым текстом
	public static function isEditEmptyText(array $message):bool {

		// получаем тип сообщения
		$message_type = self::getType($message);

		// если репост
		if ($message_type == CONVERSATION_MESSAGE_TYPE_REPOST) {
			return true;
		}

		// если цитата
		if ($message_type == CONVERSATION_MESSAGE_TYPE_QUOTE) {
			return true;
		}

		// если массовая цитата
		if ($message_type == CONVERSATION_MESSAGE_TYPE_MASS_QUOTE) {
			return true;
		}

		// если репост из треда
		if ($message_type == CONVERSATION_MESSAGE_TYPE_THREAD_REPOST) {
			return true;
		}

		return false;
	}

	# endregion
	##########################################################

	// возвращает список пользователей, которые нужны клиенту для отображения сообщения
	public static function getUsers(array $message):array {

		self::_checkVersion($message);

		$user_list = $message["action_users_list"] ?? [];

		// если сообщения типа respect, exactingness, achievement, то достаем пользователей из additional-полей
		if (self::isContainAdditionalRespect($message)) {
			$user_list[] = self::getAdditionalRespectReceiver($message);
		}
		if (self::isContainAdditionalExactingness($message)) {
			$user_list[] = self::getAdditionalExactingnessReceiver($message);
		}
		if (self::isContainAdditionalAchievement($message)) {
			$user_list[] = self::getAdditionalAchievementReceiver($message);
		}

		return array_unique($user_list);
	}

	// добавляем юзеров в action
	public static function addUsersToActionList(array $message, array $user_id_list):array {

		self::_checkVersion($message);

		// мерджим юзеров
		$merged_array = array_merge($message["action_users_list"], $user_id_list);

		// добавляем уникальных в action
		$message["action_users_list"] = array_unique($merged_array);

		return $message;
	}

	// добавляем реакции к сообщению из горячего блока
	public static function addReactionList(array $message, array $reaction_list, int $reaction_last_edited_at):array {

		self::_checkVersion($message);

		$temp = [];

		foreach ($reaction_list as $k => $v) {

			$temp[] = [
				"reaction_name" => $k,
				"user_list"     => $v["user_sort_list"],
				"created_at"    => $v["created_at"],
			];
		}

		// добавляем горячие реакции и время их последнего редактирования
		$message["extra"]["reaction_list"]           = $temp;
		$message["extra"]["reaction_last_edited_at"] = $reaction_last_edited_at;

		return $message;
	}

	// -------------------------------------------------------
	// все что касается дополнительных полей в сообщении
	// дополнительные поля не привязаны за каким-либо типом
	// -------------------------------------------------------

	// сюда добавляем каждый новый тип дополнительного поля
	protected const _AVAILABLE_ADDITIONAL_TYPES  = [
		self::ADDITIONAL_TYPE_WORKED_HOURS,
		self::ADDITIONAL_TYPE_RESPECT,
		self::ADDITIONAL_TYPE_EXACTINGNESS,
		self::ADDITIONAL_TYPE_ACHIEVEMENT,
	];
	public const    ADDITIONAL_TYPE_WORKED_HOURS = 1; // дополнительное поле, для привязки отработанных часов к сообщению
	public const    ADDITIONAL_TYPE_RESPECT      = 2; // дополнительное поле, для привязки пользователя, которому отреспектовали
	public const    ADDITIONAL_TYPE_EXACTINGNESS = 3; // дополнительное поле, для привязки пользователя, к которому проявили Требовательность
	public const    ADDITIONAL_TYPE_ACHIEVEMENT  = 4; // дополнительное поле, для привязки пользователя, которому добавили Достижение

	public const ADDITIONAL_TYPE_USER_SENDER   = "user";      // дополнительное поле, показывающие, что пользователь отправитель сообщения
	public const ADDITIONAL_TYPE_SYSTEM_SENDER = "system";    // дополнительное поле, показывающие, что сообщение было отправлено системой

	// привязать данные для Требовательности к сообщению
	public static function attachExactingnessData(array $message, int $receiver_user_id, int $exactingness_id):array {

		$data = [
			"receiver_user_id" => $receiver_user_id,
			"exactingness_id"  => $exactingness_id,
		];
		return self::_attachAdditional($message, self::ADDITIONAL_TYPE_EXACTINGNESS, $data);
	}

	// привязать данные респекта к сообщению
	public static function attachRespectData(array $message, int $respect_id, int $receiver_user_id):array {

		$data = [
			"receiver_user_id" => $receiver_user_id,
			"respect_id"       => $respect_id,
		];
		return self::_attachAdditional($message, self::ADDITIONAL_TYPE_RESPECT, $data);
	}

	// привязать отработанные часы к сообщению
	public static function attachWorkedHours(
		array  $message,
		int    $worked_hours_id,
		string $day_start_at_iso,
		int    $worked_hours_created_at = null,
		bool   $is_user_sender = true,
		int    $message_created_at = null
	):array {

		$data = [
			"worked_hours_id"         => $worked_hours_id,
			"day_start_string"        => $day_start_at_iso,
			"worked_hours_created_at" => is_null($worked_hours_created_at) ? time() : $worked_hours_created_at,
			"message_created_at"      => is_null($message_created_at) ? time() : $message_created_at,
			"sender_type"             => $is_user_sender ? self::ADDITIONAL_TYPE_USER_SENDER : self::ADDITIONAL_TYPE_SYSTEM_SENDER,
		];
		return self::_attachAdditional($message, self::ADDITIONAL_TYPE_WORKED_HOURS, $data);
	}

	// привязать данные достижения к сообщению
	public static function attachAchievementData(array $message, int $achievement_id, int $receiver_user_id):array {

		$data = [
			"receiver_user_id" => $receiver_user_id,
			"achievement_id"   => $achievement_id,
		];
		return self::_attachAdditional($message, self::ADDITIONAL_TYPE_ACHIEVEMENT, $data);
	}

	// привязываем инфу по репостнотому звонку к сообщению со звонком
	public static function attachRepostedCallInfo(array $message, int $call_report_id, int $call_duration):array {

		self::_checkVersion($message);
		if (self::getType($message) != CONVERSATION_MESSAGE_TYPE_CALL) {
			throw new ParseFatalException(__METHOD__ . ": passed message type not equal CONVERSATION_MESSAGE_TYPE_CALL");
		}

		// сохарняем информацию о звонке в теле сообщения
		$message["extra"]["call_report_id"] = $call_report_id;
		$message["extra"]["call_duration"]  = $call_duration;

		return $message;
	}

	// содержит ли сообщение additional типа worked_hours
	public static function isContainAdditionalWorkedHours(array $message):bool {

		if (!isset($message["additional"])) {
			return false;
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал
			if ($item["type"] == self::ADDITIONAL_TYPE_WORKED_HOURS) {
				return true;
			}
		}

		return false;
	}

	// содержит ли сообщение additional типа respect
	public static function isContainAdditionalRespect(array $message):bool {

		if (!isset($message["additional"])) {
			return false;
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал
			if ($item["type"] == self::ADDITIONAL_TYPE_RESPECT) {
				return true;
			}
		}

		return false;
	}

	// содержит ли сообщение additional типа exactingness
	public static function isContainAdditionalExactingness(array $message):bool {

		if (!isset($message["additional"])) {
			return false;
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал
			if ($item["type"] == self::ADDITIONAL_TYPE_EXACTINGNESS) {
				return true;
			}
		}

		return false;
	}

	// содержит ли сообщение additional типа achievement
	public static function isContainAdditionalAchievement(array $message):bool {

		if (!isset($message["additional"])) {
			return false;
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал
			if ($item["type"] == self::ADDITIONAL_TYPE_ACHIEVEMENT) {
				return true;
			}
		}

		return false;
	}

	// получить id получателя требовательности из сообщения с additional
	public static function getAdditionalExactingnessReceiver(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_EXACTINGNESS) {
				return $item["data"]["receiver_user_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_EXACTINGNESS");
	}

	// получить id получателя респекта из сообщения с additional типа respect
	public static function getAdditionalRespectReceiver(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_RESPECT) {
				return $item["data"]["receiver_user_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_RESPECT");
	}

	// получить id получателя достижения из сообщения с additional
	public static function getAdditionalAchievementReceiver(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_ACHIEVEMENT) {
				return $item["data"]["receiver_user_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_ACHIEVEMENT");
	}

	// получить id респекта из сообщения с additional типа respect
	public static function getAdditionalRespectId(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_RESPECT) {
				return $item["data"]["respect_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_RESPECT");
	}

	// получить id требовательности из сообщения с additional типа exactingness
	public static function getAdditionalExactingnessId(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_EXACTINGNESS) {
				return $item["data"]["exactingness_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_EXACTINGNESS");
	}

	// получить worked_hours_id из сообщения с additional типа worked_hours
	public static function getAdditionalWorkedHoursId(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_WORKED_HOURS) {
				return $item["data"]["worked_hours_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_WORKED_HOURS");
	}

	// получить время создания сообщения из сообщения с additional типа worked_hours
	public static function getAdditionalWorkedHoursMessageCreatedAt(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_WORKED_HOURS) {
				return self::getWorkedHoursMessageCreatedAt($item["data"]);
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_WORKED_HOURS");
	}

	// получаем created_at рабочих часов
	public static function getWorkedHoursCreatedAt(array $additional_data):int {

		return $additional_data["worked_hours_created_at"] ?? 0;
	}

	// получаем created_at сообщения фиксации рабочих часов
	public static function getWorkedHoursMessageCreatedAt(array $additional_data):int {

		return $additional_data["message_created_at"] ?? 0;
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

	// получить id достижения из сообщения с additional типа achievement
	public static function getAdditionalAchievementId(array $message):int {

		if (!isset($message["additional"])) {
			throw new ParseFatalException(__METHOD__ . ": passed message without additional");
		}

		// пробегаемся по каждому и ищем
		foreach ($message["additional"] as $item) {

			// если тип совпал, то достаем идентификатор
			if ($item["type"] == self::ADDITIONAL_TYPE_ACHIEVEMENT) {
				return $item["data"]["achievement_id"];
			}
		}

		throw new ParseFatalException(__METHOD__ . ": passed message have not additional with type ADDITIONAL_TYPE_ACHIEVEMENT");
	}

	// -------------------------------------------------------
	// функция преобразующая сообщение любой версии
	// в структуру для передачи в Apiv1_Format
	// + тут же вспомогательные к ней
	// -------------------------------------------------------
	public static function prepareForFormatLegacy(
		array $message,
		int   $user_id = 0,
		array $thread_rel_list = [],
		array $reaction_user_list = [],
		int   $last_reaction_updated_ms = 0,
		bool  $is_need_child_thread_for_deleted_message = false,
	):array {

		self::_checkVersion($message);
		self::_throwIfMessageMapIsNotSet($message["message_map"]);

		$output = self::_makeOutput($message);

		// добавляем в массив map сущности
		$output = self::_getMap($output, $message["message_map"]);

		// подготавливаем сообщение в зависимости от его типа
		$output = self::_prepareMessageByType($output, $message);

		// прикрепляем реакции если положено
		if (in_array($message["type"], self::_ALLOW_TO_REACTION)) {
			$output = self::_attachReactionUserList($output, $reaction_user_list, $last_reaction_updated_ms);
		}

		$output = self::_attachExtra($output, $message);
		$output = self::_attachThreadIfExist($output, $is_need_child_thread_for_deleted_message, $message, $thread_rel_list, $user_id);
		return self::_prepareAdditionalData($output, $message);
	}

	// -------------------------------------------------------
	// функция преобразующая сообщение любой версии
	// в структуру для передачи в Apiv1_Format
	// + тут же вспомогательные к ней
	// -------------------------------------------------------
	public static function prepareForFormat(array $message, int $user_id = 0, array $reaction_list = [], array $thread_rel_list = [], bool $is_need_child_thread = false):array {

		self::_checkVersion($message);
		self::_throwIfMessageMapIsNotSet($message["message_map"]);

		$output = self::_makeOutput($message);

		// добавляем в массив map сущности
		$output = self::_getMap($output, $message["message_map"]);

		// подготавливаем сообщение в зависимости от его типа
		$output = self::_prepareMessageByType($output, $message);

		$output = self::_attachExtra($output, $message);
		$output = self::_attachThreadIfExist($output, $is_need_child_thread, $message, $thread_rel_list, $user_id);
		$output = self::_attachReactionList($output, $reaction_list);

		return self::_prepareAdditionalData($output, $message);
	}

	##########################################################
	# region вспомогательные функции к prepareForFormat
	##########################################################

	// формируем массив
	protected static function _makeOutput(array $message):array {

		return [
			"message_map"          => $message["message_map"],
			"block_id"             => self::_getFormatBlockId($message["message_map"]),
			"message_index"        => self::_getFormatMessageIndex($message),
			"is_edited"            => $message["extra"]["is_edited_by_user"] ?? 0,
			"sender_id"            => $message["sender_user_id"],
			"created_at"           => $message["created_at"],
			"allow_edit_till"      => $message["created_at"] + self::_ALLOW_TO_EDIT_TIME,
			"allow_delete_till"    => $message["created_at"] + self::_ALLOW_TO_DELETE_TIME,
			"last_message_edited"  => $message["extra"]["last_message_text_edited_at"] ?? 0,
			"last_reaction_edited" => $message["extra"]["reaction_last_edited_at"] ?? 0,
			"type"                 => $message["type"],
			"client_message_id"    => $message["client_message_id"],
			"text"                 => "",
			"reaction_list"        => [],
			"mention_user_id_list" => self::_getMentionUserIdList($message),
			"platform"             => $message["platform"] ?? Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM,
			"data"                 => [],
			"additional"           => $message["additional"] ?? [],
			"remind"               => [],
		];
	}

	// получаем mention_user_id_list
	protected static function _getMentionUserIdList(array $message):array {

		if (!isset($message["mention_user_id_list"])) {
			return [];
		}

		return arrayValuesInt($message["mention_user_id_list"]);
	}

	// добавляем в массив map в зависимости от родительской сущности
	protected static function _getMap(array $output, string $message_map):array {

		if (\CompassApp\Pack\Message::isFromThread($message_map)) {

			$output["thread_map"] = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
			return $output;
		}

		$output["conversation_map"] = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		return $output;
	}

	// подготавливаем сообщение в зависимости от его типа
	// @long
	protected static function _prepareMessageByType(array $output, array $message):array {

		$message_type = (int) $message["type"];

		return match ($message_type) {

			CONVERSATION_MESSAGE_TYPE_TEXT,
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT,
			CONVERSATION_MESSAGE_TYPE_RESPECT,
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_MESSAGES_MOVED_NOTIFICATION => self::_prepareText($output, $message),
			CONVERSATION_MESSAGE_TYPE_FILE,
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE => self::_prepareFile($output, $message),
			CONVERSATION_MESSAGE_TYPE_INVITE => self::_prepareInvite($output, $message),
			CONVERSATION_MESSAGE_TYPE_QUOTE => self::_prepareQuote($output, $message),
			CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND => self::_prepareMassQuote($output, $message),
			CONVERSATION_MESSAGE_TYPE_REPOST => self::_prepareRepost($output, $message),
			CONVERSATION_MESSAGE_TYPE_SYSTEM => self::_prepareSystem($output, $message),
			CONVERSATION_MESSAGE_TYPE_DELETED => self::_prepareDeleted($output),
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST => self::_prepareThreadRepost($output, $message),
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT => self::_prepareThreadRepostItemText($output, $message),
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE => self::_prepareThreadRepostItemFile($output, $message),
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE => self::_prepareThreadRepostItemQuote($output, $message),
			CONVERSATION_MESSAGE_TYPE_CALL => self::_prepareCall($output, $message),
			CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE => self::_prepareMediaConference($output, $message),
			THREAD_MESSAGE_TYPE_CONVERSATION_CALL => self::_prepareThreadRepostCall($output, $message),
			CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING => self::_prepareSystemBotRating($output, $message),
			CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA => self::_prepareEmployeeMetricDelta($output, $message),
			CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY => self::_prepareEditorEmployeeAnniversary($output, $message),
			CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY => self::_prepareEmployeeAnniversary($output, $message),
			CONVERSATION_MESSAGE_TYPE_EDITOR_FEEDBACK_REQUEST => self::_prepareEditorFeedbackRequest($output, $message),
			CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING => self::_prepareEditorWorksheetRating($output, $message),
			CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC => self::_prepareCompanyEmployeeMetricStatistic($output, $message),
			CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE => self::_prepareEditorEmployeeMetricNotice($output, $message),
			CONVERSATION_MESSAGE_TYPE_WORK_TIME_AUTO_LOG_NOTICE => self::_prepareWorkTimeAutoLogNotice($output, $message),
			CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST => self::_prepareHiringRequest($output, $message),
			CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST => self::_prepareDismissalRequest($output, $message),
			CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE => self::_prepareInviteToCompanyInviterSingle($output, $message),
			CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER => self::_prepareSharedMember($output, $message),
			THREAD_MESSAGE_TYPE_CONVERSATION_MEDIA_CONFERENCE => self::_prepareThreadRepostMediaConference($output, $message),
			default => throw new ParseFatalException(
				__CLASS__ . ": unsupported message type = $message_type"
			),
		};
	}

	// получает block_id в зависимости от того, какой message_map передали
	protected static function _getFormatBlockId(string $message_map):int {

		// если сообщение из диалога
		if (\CompassApp\Pack\Message::isFromConversation($message_map)) {
			return \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		}

		// если сообщение из треда
		if (\CompassApp\Pack\Message::isFromThread($message_map)) {
			return \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		}

		throw new ParseFatalException("Got unsupported message_map '$message_map' in " . __METHOD__);
	}

	// получает message_index в зависимости от типа сообщения
	protected static function _getFormatMessageIndex(array $message):int {

		// для сообщений репоста отдаем другой message_index
		if (isset($message["reposted_message_index"])) {
			return $message["reposted_message_index"];
		}

		if (\CompassApp\Pack\Message::isFromThread($message["message_map"])) {
			return \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message["message_map"]);
		}

		return \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message["message_map"]);
	}

	// готовим сообщение типа TEXT
	protected static function _prepareText(array $output, array $message):array {

		$output["text"] = $message["data"]["text"];
		return $output;
	}

	// готовим сообщение типа FILE
	protected static function _prepareFile(array $output, array $message):array {

		$file_type                   = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);
		$output["text"]              = $message["data"]["text"];
		$output["data"]["file_map"]  = $message["data"]["file_map"];
		$output["data"]["file_type"] = $file_type;

		$output = self::_getFileUuidIfExist($output, $message);
		$output = self::_getFileNameIfExist($output, $message);

		if ($file_type == FILE_TYPE_IMAGE || $file_type == FILE_TYPE_VIDEO) {

			// достаем размеры оригинального изображения
			$width  = \CompassApp\Pack\File::getImageWidth($message["data"]["file_map"]);
			$height = \CompassApp\Pack\File::getImageHeight($message["data"]["file_map"]);

			// если размеры переданы, то устанавливаем их в data
			if ($width + $height > 0) {

				$output["data"]["file_width"]  = $width;
				$output["data"]["file_height"] = $height;
			}
		}
		return $output;
	}

	// получаем file_uid если есть
	protected static function _getFileUuidIfExist(array $output, array $message):array {

		if (isset($message["data"]["file_uid"])) {
			$output["data"]["file_uid"] = $message["data"]["file_uid"];
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

	// готовим сообщение типа INVITE
	protected static function _prepareInvite(array $output, array $message):array {

		$formatted_invite_type         = Type_Invite_Utils::getInviteType($message["data"]["invite_map"]);
		$output["data"]["invite_map"]  = $message["data"]["invite_map"];
		$output["data"]["invite_type"] = $formatted_invite_type;

		return $output;
	}

	// готовим сообщение типа HIRING_REQUEST
	protected static function _prepareHiringRequest(array $output, array $message):array {

		$output["data"]["hiring_request_id"] = $message["data"]["hiring_request_id"];

		return $output;
	}

	// готовим сообщение типа DISMISSAL_REQUEST
	protected static function _prepareDismissalRequest(array $output, array $message):array {

		$output["data"]["dismissal_request_id"] = $message["data"]["dismissal_request_id"];

		return $output;
	}

	// готовим сообщение типа QUOTE
	protected static function _prepareQuote(array $output, array $message):array {

		$quoted_message = $message["data"]["quoted_message"];

		$output["text"]                   = $message["data"]["text"];
		$output["data"]["quoted_message"] = Type_Conversation_Message_Main::getHandler($quoted_message)::prepareForFormatLegacy($quoted_message);

		return $output;
	}

	// готовим сообщение типа MASS_QUOTE
	protected static function _prepareMassQuote(array $output, array $message):array {

		$output["text"]      = $message["data"]["text"];
		$quoted_message_list = [];

		foreach ($message["data"]["quoted_message_list"] as $v) {
			$quoted_message_list[] = Type_Conversation_Message_Main::getHandler($v)::prepareForFormatLegacy($v);
		}

		$output["data"]["quoted_message_list"]  = $quoted_message_list;
		$output["data"]["quoted_message_count"] = self::_getRepostedAndQuotedMessageCount($quoted_message_list);

		return $output;
	}

	// готовим сообщение типа REPOST
	protected static function _prepareRepost(array $output, array $message):array {

		$reposted_message_list = [];

		foreach ($message["data"]["repost_message_list"] as $item) {
			$reposted_message_list[] = Type_Conversation_Message_Main::getHandler($item)::prepareForFormatLegacy($item);
		}

		$output["data"]["reposted_message_list"]  = $reposted_message_list;
		$output["data"]["reposted_message_count"] = self::_getRepostedAndQuotedMessageCount($reposted_message_list);
		$output["text"]                           = $message["data"]["text"];

		return $output;
	}

	// получаем количество репостнутых/процитированных сообщений
	protected static function _getRepostedAndQuotedMessageCount(array $message_list):int {

		$message_count = 0;
		foreach ($message_list as $v) {

			if (self::isRepost($v)) {
				$message_count += self::_getRepostedAndQuotedMessageCount($v["data"]["reposted_message_list"]);
			}

			if (self::isQuote($v)) {

				$quoted_message_list = $v["data"]["quoted_message_list"] ?? [$v["data"]["quoted_message"]];
				$message_count       += self::_getRepostedAndQuotedMessageCount($quoted_message_list);
			}

			// пропускаем, если репост/цитата имеет пустой текст
			if ((self::isRepost($v) || self::isQuote($v)) && mb_strlen($v["text"]) == 0) {
				continue;
			}

			$message_count++;
		}

		return $message_count;
	}

	// готовим сообщение типа SYSTEM
	// @long
	protected static function _prepareSystem(array $output, array $message):array {

		// switch по типу системного сообщения
		switch ($message["data"]["system_message_type"]) {
			case self::SYSTEM_MESSAGE_USER_INVITED_TO_GROUP:

				$output["text"] = "[{$message["data"]["extra"]["invited_user_id"]}] invited to group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_INVITED_TO_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["invited_user_id"];
				break;
			case self::SYSTEM_MESSAGE_USER_ADD_GROUP:

				$output["text"] = "[{$message["data"]["extra"]["creator_user_id"]}] add group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_ADD_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["creator_user_id"];
				$output["data"]["extra"]["group_name"] = $message["data"]["extra"]["group_name"];
				break;
			case self::SYSTEM_MESSAGE_USER_JOINED_TO_GROUP:

				$output["text"] = "[{$message["data"]["extra"]["joined_user_id"]}] joined to group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_JOINED_TO_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["joined_user_id"];
				break;
			case self::SYSTEM_MESSAGE_USER_DECLINED_INVITE:

				$output["text"] = "[{$message["data"]["extra"]["declined_user_id"]}] declined invite to group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_DECLINED_INVITE;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["declined_user_id"];
				break;
			case self::SYSTEM_MESSAGE_USER_LEFT_GROUP:
			case self::SYSTEM_MESSAGE_USER_LEFT_COMPANY:

				$output["text"] = "[{$message["data"]["extra"]["left_user_id"]}] left the group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_LEFT_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["left_user_id"];
				break;
			case self::SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP:

				$output["text"] = "[{$message["data"]["extra"]["kicked_user_id"]}] kicked from group";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["kicked_user_id"];
				break;
			case self::SYSTEM_MESSAGE_USER_PROMOTED_TO_ADMIN:

				$output["text"] = "[{$message["data"]["extra"]["promoted_user_id"]}] was promoted to admin";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_USER_PROMOTED_TO_ADMIN;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["promoted_user_id"];
				break;
			case self::SYSTEM_MESSAGE_ADMIN_DEMOTED_TO_USER:

				$output["text"] = "[{$message["data"]["extra"]["admin_user_id"]}] was demoted from admin to user";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_DEMOTED_TO_USER;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["admin_user_id"];
				break;
			case self::SYSTEM_MESSAGE_ADMIN_RENAMED_GROUP:

				$output["text"] = "[{$message["data"]["extra"]["user_id"]}] renamed group to {$message["data"]["extra"]["group_name"]}";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_RENAMED_GROUP;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["user_id"];
				$output["data"]["extra"]["group_name"] = $message["data"]["extra"]["group_name"];

				// если пришло старое имя группы
				if (isset($message["data"]["extra"]["old_group_name"])) {

					$output["text"] = "[{$message["data"]["extra"]["user_id"]}] renamed group from {$message["data"]["extra"]["old_group_name"]} to {$message["data"]["extra"]["group_name"]}";

					$output["data"]["extra"]["old_group_name"] = $message["data"]["extra"]["old_group_name"];
				}
				break;
			case self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_DESCRIPTION:

				$output["text"] = "[{$message["data"]["extra"]["user_id"]}] changed group description to {$message["data"]["extra"]["description"]}";

				$output["data"]["system_message_type"]  = self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_DESCRIPTION;
				$output["data"]["extra"]["user_id"]     = $message["data"]["extra"]["user_id"];
				$output["data"]["extra"]["description"] = $message["data"]["extra"]["description"];

				// если пришло старое описание группы
				if (isset($message["data"]["extra"]["old_group_description"])) {

					$output["text"] = "[{$message["data"]["extra"]["user_id"]}] changed group description from {$message["data"]["extra"]["old_group_description"]} to {$message["data"]["extra"]["description"]}";

					$output["data"]["extra"]["old_group_description"] = $message["data"]["extra"]["old_group_description"];
				}
				break;
			case self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR:

				$output["text"] = "[{$message["data"]["extra"]["user_id"]}] changed group avatar";

				$output["data"]["system_message_type"] = self::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR;
				$output["data"]["extra"]["user_id"]    = $message["data"]["extra"]["user_id"];
				$output["data"]["extra"]["file_map"]   = $message["data"]["extra"]["file_map"];
				break;
		}

		return $output;
	}

	// готовим сообщение типа DELETED
	protected static function _prepareDeleted(array $output):array {

		$output["text"] = "";
		$output["data"] = [];

		return $output;
	}

	// готовим сообщение типа THREAD_REPOST
	protected static function _prepareThreadRepost(array $output, array $message):array {

		$reposted_message_list = [];

		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data     = $message["data"]["parent_message_data"];
			$parent_message_data     = self::_setRepostedMessageIndex($parent_message_data, 0);
			$reposted_message_list[] = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForFormatLegacy($parent_message_data);
		}

		foreach ($message["data"]["repost_message_list"] as $item) {
			$reposted_message_list[] = Type_Conversation_Message_Main::getHandler($item)::prepareForFormatLegacy($item);
		}

		$output["data"]["reposted_message_list"]  = $reposted_message_list;
		$output["data"]["reposted_message_count"] = self::_getRepostedAndQuotedMessageCount($reposted_message_list);
		$output["text"]                           = $message["data"]["text"];

		return $output;
	}

	// готовим сообщение типа THREAD_REPOST_ITEM_TEXT
	protected static function _prepareThreadRepostItemText(array $output, array $message):array {

		$output["text"] = $message["data"]["text"];

		return $output;
	}

	// готовим сообщение типа THREAD_REPOST_ITEM_FILE
	protected static function _prepareThreadRepostItemFile(array $output, array $message):array {

		$file_type                   = \CompassApp\Pack\File::getFileType($message["data"]["file_map"]);
		$output["text"]              = $message["data"]["text"];
		$output["data"]["file_map"]  = $message["data"]["file_map"];
		$output["data"]["file_type"] = $file_type;

		if (isset($message["data"]["file_name"])) {
			$output["data"]["file_name"] = $message["data"]["file_name"];
		}

		if (isset($message["data"]["file_uid"])) {
			$output["data"]["file_uid"] = $message["data"]["file_uid"];
		}

		// добавляем высоту и ширину, если есть
		return self::_getFileWidthAndHeight($output, $file_type, $message);
	}

	// готовим сообщение типа THREAD_REPOST_ITEM_QUOTE
	protected static function _prepareThreadRepostItemQuote(array $output, array $message):array {

		$quoted_message_list = [];

		foreach ($message["data"]["quoted_message_list"] as $v) {
			$quoted_message_list[] = Type_Conversation_Message_Main::getHandler($v)::prepareForFormatLegacy($v);
		}

		$output["data"]["quoted_message_list"]  = $quoted_message_list;
		$output["data"]["quoted_message_count"] = self::_getRepostedAndQuotedMessageCount($quoted_message_list);
		$output["text"]                         = $message["data"]["text"];

		return $output;
	}

	// готовим сообщение типа CONVERSATION_MESSAGE_TYPE_CALL
	protected static function _prepareCall(array $output, array $message):array {

		$output["data"]["call_map"] = $message["data"]["call_map"];

		// если имеется дополнительная информация (например при репосте сообщения со звонком)
		if (isset($message["extra"]["call_report_id"], $message["extra"]["call_duration"])) {

			$output["data"]["call_report_id"] = $message["extra"]["call_report_id"];
			$output["data"]["call_duration"]  = $message["extra"]["call_duration"];
		}

		return $output;
	}

	// готовим сообщение типа CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE
	protected static function _prepareMediaConference(array $output, array $message):array {

		$output["data"]["conference_id"] = $message["data"]["conference_id"];
		$output["data"]["conference_accept_status"] = $message["data"]["conference_accept_status"];
		$output["data"]["conference_link"]          = $message["data"]["conference_link"];

		return $output;
	}

	// готовим сообщение типа THREAD_MESSAGE_TYPE_CONVERSATION_CALL
	protected static function _prepareThreadRepostCall(array $output, array $message):array {

		$output["data"]["call_map"] = $message["data"]["call_map"];

		// если имеется дополнительная информация (например при репосте сообщения со звонком)
		if (isset($message["extra"]["call_report_id"], $message["extra"]["call_duration"])) {

			$output["data"]["call_report_id"] = $message["extra"]["call_report_id"];
			$output["data"]["call_duration"]  = $message["extra"]["call_duration"];
		}

		// заменяем тип сообщения THREAD_MESSAGE_TYPE_CONVERSATION_CALL -> CONVERSATION_MESSAGE_TYPE_CALL
		$output["type"] = CONVERSATION_MESSAGE_TYPE_CALL;

		return $output;
	}

	// готовим сообщение типа THREAD_MESSAGE_TYPE_CONVERSATION_CALL
	protected static function _prepareThreadRepostMediaConference(array $output, array $message):array {

		$output["data"]["conference_id"] = $message["data"]["conference_id"];
		$output["data"]["conference_accept_status"] = $message["data"]["conference_accept_status"];
		$output["data"]["conference_link"]          = $message["data"]["conference_link"];

		// заменяем тип сообщения THREAD_MESSAGE_TYPE_CONVERSATION_CALL -> CONVERSATION_MESSAGE_TYPE_CALL
		$output["type"] = CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE;

		return $output;
	}

	// готовим сообщение типа SYSTEM_BOT_RATING
	protected static function _prepareSystemBotRating(array $output, array $message):array {

		$output["data"]["year"]  = $message["data"]["year"];
		$output["data"]["week"]  = $message["data"]["week"];
		$output["data"]["count"] = $message["data"]["count"];

		if (isset($message["data"]["name"])) {
			$output["data"]["name"] = $message["data"]["name"];
		}

		// добавляем прочитанность
		return self::_attachReadAtByListToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA.
	 *
	 * @return array $message
	 **/
	protected static function _prepareEmployeeMetricDelta(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                = $message["data"]["text"];
		$output["data"]["metric_type"] = $message["data"]["metric_type"];

		// дополняем экстра данные для метрики
		$output["data"]["metric_extra"] = $message["data"]["metric_extra"];

		// добавляем прочитанность
		return self::_attachReadAtByListToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY.
	 *
	 * @return array $message
	 **/
	protected static function _prepareEditorEmployeeAnniversary(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                     = $message["data"]["text"];
		$output["data"]["employee_user_id"] = $message["data"]["employee_user_id"];
		$output["data"]["hired_at"]         = $message["data"]["hired_at"];

		// добавляем прочитанность
		return self::_attachReadAtByListToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY.
	 *
	 * @return array $message
	 **/
	protected static function _prepareEmployeeAnniversary(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]             = $message["data"]["text"];
		$output["data"]["hired_at"] = $message["data"]["hired_at"];

		// добавляем прочитанность
		return self::_attachReadAtByListToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EDITOR_FEEDBACK_REQUEST.
	 *
	 * @return array $message
	 */
	protected static function _prepareEditorFeedbackRequest(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                        = $message["data"]["text"];
		$output["data"]["employee_user_id"]    = $message["data"]["employee_user_id"];
		$output["data"]["feedback_request_id"] = $message["data"]["feedback_request_id"];

		return self::_attachWorkPeriodToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EDITOR_WORK_SHEET_RATING.
	 *
	 * @return array $message
	 */
	protected static function _prepareEditorWorksheetRating(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                               = $message["data"]["text"];
		$output["data"]["leader_user_work_item_list"] = $message["data"]["leader_user_work_item_list"];
		$output["data"]["driven_user_work_item_list"] = $message["data"]["driven_user_work_item_list"];

		return self::_attachWorkPeriodToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC.
	 *
	 * @return array $message
	 **/
	protected static function _prepareCompanyEmployeeMetricStatistic(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                           = $message["data"]["text"];
		$output["data"]["metric_count_item_list"] = $message["data"]["metric_count_item_list"];
		$output["data"]["company_name"]           = $message["data"]["company_name"] ?? "";

		// добавляем прочитанность
		$output = self::_attachReadAtByListToPrepared($output, $message);

		return self::_attachWorkPeriodToPrepared($output, $message);
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE.
	 *
	 * @return array $message
	 **/
	protected static function _prepareEditorEmployeeMetricNotice(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                     = $message["data"]["text"];
		$output["data"]["employee_user_id"] = $message["data"]["employee_user_id"];

		return $output;
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE.
	 *
	 **/
	protected static function _prepareWorkTimeAutoLogNotice(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]              = $message["data"]["text"];
		$output["data"]["work_time"] = $message["data"]["work_time"];

		return $output;
	}

	/**
	 * Подготавливает сообщение типа CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGlE.
	 *
	 * @return array $message
	 **/
	protected static function _prepareInviteToCompanyInviterSingle(array $output, array $message):array {

		// добавляем текст и тип метрики
		$output["text"]                            = $message["data"]["text"];
		$output["data"]["company_inviter_user_id"] = $message["data"]["company_inviter_user_id"];

		// добавляем прочитанность
		return self::_attachReadAtByListToPrepared($output, $message);
	}

	// прикрепляем к сообщению реакции (реакция + пользователи)
	protected static function _attachReactionUserList(array $output, array $reaction_user_list, int $last_reaction_updated_ms):array {

		$reaction_list = [];

		// собираем нужную структуру для клиента
		foreach ($reaction_user_list as $k => $v) {

			$reaction_list[] = [
				"reaction_name" => (string) $k,
				"count"         => (int) count($v),
				"user_id_list"  => (array) $v,
			];
		}

		$output["reaction_list"]        = $reaction_list;
		$output["last_reaction_edited"] = $last_reaction_updated_ms;

		return $output;
	}

	// добавляем дополнительные штуки к сообщухе
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

		if (isset($message["link_list"])) {
			$output["link_list"] = $message["link_list"];
		}

		if (in_array($message["type"], self::_ALLOW_TO_REMIND) && isset($message["extra"]["remind"]) && self::getRemindAt($message) > time()) {
			$output["remind"] = $message["extra"]["remind"];
		}

		return $output;
	}

	/**
	 * прикрепляем список реакций к сообщению
	 */
	protected static function _attachReactionList(array $output, array $message_reaction_list):array {

		$reaction_list = [];

		// собираем нужную структуру для клиента
		foreach ($message_reaction_list as $reaction_name => $user_list) {

			$reaction_list[] = [
				"reaction_name" => (string) $reaction_name,
				"count"         => (int) count($user_list),
				"user_id_list"  => (array) $user_list,
			];
		}

		$output["reaction_list"] = $reaction_list;

		return $output;
	}

	// если к сообщению есть тред - прикрепляем
	protected static function _attachThreadIfExist(
		array $output,
		bool  $is_need_child_thread_for_deleted_message,
		array $message,
		array $thread_rel_list,
		int   $user_id
	):array {

		// если к сообщению существует тред
		if (isset($thread_rel_list[$message["message_map"]])) {

			// прикрепляем тред в случае когда сообщение не удалено или когда требуем
			if ($is_need_child_thread_for_deleted_message === true || $message["type"] != CONVERSATION_MESSAGE_TYPE_DELETED) {

				$output["child_thread"]["thread_map"] = $thread_rel_list[$message["message_map"]]["thread_map"];

				$is_hidden = 0;
				if ($user_id > 0 && in_array($user_id, $thread_rel_list[$message["message_map"]]["thread_hidden_user_list"])) {
					$is_hidden = 1;
				}
				if ($user_id > 0 && $thread_rel_list[$message["message_map"]]["is_thread_hidden_for_all_users"] === 1) {
					$is_hidden = 1;
				}

				$output["child_thread"]["is_hidden"] = (int) $is_hidden;
			}
		}

		return $output;
	}

	// подготавливаем информацию о дополнительных полях в теле сообщения
	protected static function _prepareAdditionalData(array $output, array $message):array {

		$output["additional"] = [];

		if ($message["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {
			return $output;
		}

		// добавляем дополнительные поля, если они есть
		$output["additional"] = $message["additional"] ?? [];

		foreach ($output["additional"] as $k => $v) {

			switch ($v["type"]) {

				case self::ADDITIONAL_TYPE_WORKED_HOURS:

					// пробуем достать время создания сообщения фиксации рабочего времени и самого объекта рабочего времени
					$worked_hours_created_at = self::getWorkedHoursCreatedAt($v["data"]);
					$message_created_at      = self::getWorkedHoursMessageCreatedAt($v["data"]);

					// меняем время редактирования и удаления для сообщения рабочих часов
					if ($worked_hours_created_at != 0) {
						$output["allow_edit_till"] = $worked_hours_created_at + self::_ALLOW_TO_EDIT_TIME;
					}
					if ($message_created_at != 0) {
						$output["allow_delete_till"] = $message_created_at + self::_ALLOW_TO_DELETE_TIME;
					}

					// убираем из данных ненужные поля для клиентов
					unset($v["data"]["worked_hours_created_at"]);
					unset($v["data"]["message_created_at"]);
					break;

				default:
			}

			// добавляем additional-данные к сообщению
			$output["additional"][$k] = $v;
		}

		return $output;
	}

	/**
	 * готовим сообщение типа shared_member
	 *
	 * @param array $output
	 * @param array $message
	 *
	 * @return array
	 */
	protected static function _prepareSharedMember(array $output, array $message):array {

		$output["data"]["shared_user_id_list"] = $message["data"]["shared_user_id_list"];

		return $output;
	}

	# endregion
	##########################################################

	// получить сообщение стандартной структуры
	protected static function _getDefaultStructure(int $type, int $sender_user_id, string $client_message_id = "", string $platform = self::WITHOUT_PLATFORM):array {

		$action_users_list = [];
		if ($sender_user_id > 0) {
			$action_users_list[] = $sender_user_id;
		}

		return [
			"message_map"          => null,
			"version"              => static::_CURRENT_HANDLER_VERSION,
			"type"                 => $type,
			"sender_user_id"       => $sender_user_id,
			"client_message_id"    => mb_strlen($client_message_id) < 1 ? generateUUID() : $client_message_id,
			"created_at"           => time(),
			"updated_at"           => 0,
			"user_rel"             => [
				"hidden_by" => [],
			],
			"platform"             => $platform,
			"data"                 => [],
			"extra"                => [],
			"action_users_list"    => $action_users_list,
			"additional"           => [],
			"mention_user_id_list" => [],
		];
	}

	// проверяем, что в сообщении присутствует message_map
	protected static function _throwIfMessageMapIsNotSet(?string $message_map):void {

		// проверяем, что сообщение имеет полную структуру
		if (is_null($message_map)) {
			throw new ParseFatalException("Trying to prepareForFormatLegacy message, which not applied prepareForInsert first");
		}
	}

	// проверить что версия - ок
	protected static function _checkVersion(array $message):void {

		if (!isset($message["version"]) || $message["version"] != static::_CURRENT_HANDLER_VERSION) {
			throw new ParseFatalException(__CLASS__ . ": passed message with incorrect version parameter");
		}
	}

	// получаем ширину и высоту файла
	protected static function _getFileWidthAndHeight(array $output, int $file_type, array $message):array {

		switch ($file_type) {

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

		return $output;
	}

	// получаем action_user_list
	protected static function _getActionUserList(array $message_list):array {

		// получаем всех action users для каждого сообщения
		$message_action_user_list = [];
		foreach ($message_list as $v) {
			$message_action_user_list[] = Type_Conversation_Message_Main::getHandler($v)::getUsers($v);
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

	// привязываем additional к сообщению
	protected static function _attachAdditional(array $message, int $additional_type, array $additional_data):array {

		self::_checkVersion($message);

		// если такой тип не существует, то ругаемся
		if (!in_array($additional_type, self::_AVAILABLE_ADDITIONAL_TYPES)) {
			throw new ParseFatalException(__METHOD__ . ": passed unexpected additional type");
		}

		// штука не новая, поэтому может не существовать у старых сообщений этого же типа
		$message["additional"] = $message["additional"] ?? [];

		// если такой тип ранее уже был привязан к сообщению
		$message_additional_type_list = array_column($message["additional"], "type");
		if (in_array($additional_type, $message_additional_type_list)) {

			// то просто удалим содержимое, а затем дозапишем новое
			foreach ($message["additional"] as $k => $v) {

				if ($v["type"] != $additional_type) {
					continue;
				}

				unset($message["additional"][$k]);
				break;
			}

			throw new ParseFatalException(__METHOD__ . ": did not found existing additional");
		}

		// добавляем дополнение
		$message["additional"][] = [
			"type" => $additional_type,
			"data" => $additional_data,
		];

		return $message;
	}

	##########################################################
	# region UTILS - вспомогательные функции
	##########################################################

	// получить группу дозволенных типов для изменения сообщений треда под сообщения диалога
	public static function getAllowMessageTypesForTransferThreadMessage():array {

		return self::_ALLOW_TO_TRANSFER_THREAD_MESSAGE_TO_CONVERSATION;
	}

	// получить группу дозволенных типов для подготовки сообщений тредов под репост в диалог
	public static function getAllowMessageTypesForPrepareThreadMessageToRepost():array {

		return self::_ALLOW_TO_PREPARE_THREAD_MESSAGE_TO_REPOST;
	}

	/**
	 * Привязывает рабочий период к сообщению.
	 * Вызывается при создании сообщения.
	 *
	 * @param int $period_date_start Дата начала периода
	 * @param int $period_date_to    Дата окончания периода
	 *
	 */
	protected static function _attachWorkPeriod(array $message, int $period_id, int $period_date_start, int $period_date_to):array {

		$message["data"]["period_start_date"] = $period_date_start;
		$message["data"]["period_end_date"]   = $period_date_to;
		$message["data"]["period_id"]         = $period_id;

		return $message;
	}

	/**
	 * Привязывает рабочий период к сообщению.
	 * Вызывается при подготовке сообщения перед отправкой клиенту.
	 *
	 * @param array $prepared_message Подготавливаемое сообщение
	 * @param array $message          Сообщение из базы
	 *
	 */
	protected static function _attachWorkPeriodToPrepared(array $prepared_message, array $message):array {

		$prepared_message["data"]["period_start_date"] = $message["data"]["period_start_date"];
		$prepared_message["data"]["period_end_date"]   = $message["data"]["period_end_date"];
		$prepared_message["data"]["period_id"]         = $message["data"]["period_id"];

		return $prepared_message;
	}

	/**
	 * Добавить информацию о прочитанности сообщения
	 *
	 */
	protected static function _attachReadAtByList(array $output):array {

		$output["data"]["read_at_by_list"] = [];
		return $output;
	}

	/**
	 * Добавить информацию о прочитанности сообщения
	 *
	 */
	protected static function _attachReadAtByListToPrepared(array $prepared_message, array $message):array {

		// прочитанность сообщения
		$read_at_by_list = $message["data"]["read_at_by_list"] ?? [];

		$prepared_message["data"]["is_read"]         = count($read_at_by_list) > 0;
		$prepared_message["data"]["read_at_by_list"] = $read_at_by_list;

		return $prepared_message;
	}

	# endregion
	##########################################################
}
