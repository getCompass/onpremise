<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

// @formatter:off
// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

/** исключение уровня доменной логики — внутреннее — ошибка разработчика, аналогична 500 ошибке */
class DomainInternalException extends \Exception {

}

/** исключение уровня доменной логики — внутреннее — ошибка входных данных, аналогична 400 ошибке */
class DomainExternalException extends \Exception {

}

/**
 * исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
 */
class cs_AnswerCommand extends \Exception {

	protected string $_command_name;
	protected array $_command_extra;

	public function __construct(string $command_name, array $command_extra, string $message = "", int $code = 0, \Exception $previous = NULL) {

		$this->_command_name  = $command_name;
		$this->_command_extra = $command_extra;
		parent::__construct($message, $code, $previous);
	}

	public function getCommandName():string {

		return $this->_command_name;
	}

	public function getCommandExtra():array {

		return $this->_command_extra;
	}
}

/**
 * Class cs_IncorrectUserId
 */
class cs_IncorrectUserId extends \Exception {

}

/**
 * Class cs_SessionNotFound
 */
class cs_SessionNotFound extends \Exception {

}

/**
 * Class cs_UserNotFound
 */
class cs_UserNotFound extends \Exception {

}

/**
 * когда микросервис go_talking ответил не ОК
 */
class cs_TalkingBadResponse extends \Exception {

}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * пользовательская сессия не установлена или же некорректна
 */
class cs_InvalidSession extends \Exception {

}

/**
 * ошибка при расшифровке ключа какой-либо сущности
 */
class cs_DecryptHasFailed extends ParamException {

}

/**
 * ошибка при распаковке какой-либо сущности
 */
class cs_UnpackHasFailed extends \Exception {

}

/**
 * ошибка сокет запроса
 */
class cs_ErrorSocketRequest extends \Exception {

}

/**
 * ошибка парсинга данных события
 */
class cs_InvalidEventArgumentsException extends \Exception {

}

# endregion
##########################################################

##########################################################
# region exception связанные с сообщениями и диалогами
##########################################################

/**
 * Неверное значение конфига
 */
class cs_InvalidConfigValue extends \Exception {

}

/**
 * пользователь не имеет право производить действие с диалогом
 */
class cs_Conversation_UserHaveNotPermissionForAction  extends \Exception {

}

// exception когда allow_status = MEMBER_DISABLED -> в диалог нельзя писать потому что один из его участников был заблокирован в системе (is_disabled через админку)
class cs_Conversation_MemberIsDisabled extends \Exception {

}

// exception когда allow_status = USERBOT_DISABLED -> в диалог нельзя писать потому что бот выключен
class cs_Conversation_UserbotIsDisabled extends \Exception {

}

// exception когда allow_status = USERBOT_DELETED -> в диалог нельзя писать потому что бот удалён
class cs_Conversation_UserbotIsDeleted extends \Exception {

}

// когда user1 не имеет право писать user2
class cs_Conversation_CanNotSendMessage extends \Exception {

}

// когда single диалог между двумя пользователями уже существует
class cs_Conversation_SingleIsExist extends \Exception {

	// мап уже существующего single диалога
	protected $_conversation_map;

	public function __construct(string $conversation_map, string $message = "", int $code = 0, \Exception $previous = NULL) {

		$this->_conversation_map = $conversation_map;

		parent::__construct($message, $code, $previous);
	}

	public function getConversationMap():string {

		return $this->_conversation_map;
	}
}

// когда достигли лимита времени отключения уведомлений диалога
class cs_Conversation_NotificationsDisableTimeLimited extends \Exception {

}

// Когда в диалог нельзя писать новые по каким-либо причинам
class cs_Conversation_IsNotAllowedForNewMessage extends \Exception {

}

// когда в групповом диалоге уже есть администратор
class cs_Conversation_IsGroupIfOwnerExist extends \Exception {

}

/**
 * некорректное название группы
 */
class cs_Conversation_IsGroupNameIncorrect extends \Exception {

}

// когда не нашли запись в левом меню
class cs_LeftMenuRowIsNotExist extends \Exception {

}

// когда пытаются поставить или удалить не существующую реакцию
class cs_Message_IsNotAllowedForReaction extends \Exception {

}

// когда превысили лимит добавления реакций
class cs_Message_ReactionLimit  extends \Exception {

}

// когда реакцию уже поставлена пользователем
class cs_Message_ReactionIsExist  extends \Exception {

}

// пользователь не имеет право производить действие с сообщением
class cs_Message_UserHaveNotPermission  extends \Exception {

}

// сообщение нельзя редактировать
class cs_Message_IsNotAllowForEdit  extends \Exception {

}

// превышет лимит сообщений
class cs_Message_Limit extends \Exception {

}

// сообщение удалено
class cs_Message_IsDeleted extends \Exception {

}

// сообщение нельзя удалить
class cs_Message_IsNotAllowForDelete extends \Exception {

}

// сообщение нельзя удалить, потому что вышло время удаления
class cs_Message_IsTimeNotAllowToDelete extends \Exception {

}

// сообщение отметить прочитанным
class cs_Message_IsNotAllowToMarkAsRead extends \Exception {

}

// сообщение нельзя отредачить, потому что вышло время для редактирования
class cs_Message_TimeIsOver extends \Exception {

}

// список сообщений пуст
class cs_MessageList_IsEmpty extends \Exception {

}

// сообщение не существует
class cs_Message_IsNotExist extends \Exception {

}

// сообщение c пустым текстом
class cs_Message_IsEmptyText extends \Exception {

}

// сообщение с уже созданым тредом
class cs_Message_AlreadyContainsThread extends \Exception {

}

// дубликат сообщения с повторяющимся client_message_id
class cs_Message_DuplicateClientMessageId extends \Exception {

}

// блок с сообщениями не существует
class cs_Conversation_BlockIsNotExist extends \Exception {

}

// user не участник диалога
class cs_UserIsNotMember extends \Exception {

}

// пользователь не участник компании
class cs_UserIsNotCompanyMember extends \Exception {

}

// user уже участник диалога
class cs_UserIsMember extends \Exception {

}

/**
 * некорректный user_id_list
 */
class cs_IncorrectUserIdList extends \Exception {

}

/**
 * пользователь не является администратором
 */
class cs_UserIsNotAdmin extends \Exception {

}

/**
 * список пользователей уволеных из компании
 */
class cs_UserIdListIsNotCompanyMember extends \Exception {

	protected array $_kicked_user_id_list;

	public function __construct(array $kicked_user_id_list, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_kicked_user_id_list = $kicked_user_id_list;

		parent::__construct($message, $code, $previous);
	}

	public function getUserIdListIsNotCompanyMember():array {

		return $this->_kicked_user_id_list;
	}
}

// когда сообщение слишком длинное
class cs_Message_IsTooLong extends \Exception {

}

// при попытке написать в закрытый (is_locked) диалог
class cs_ConversationIsLocked extends \Exception {

}

// инвайт уже существует
class cs_InviteIsDuplicated extends \Exception {

}

// инвайт уже принят
class cs_InviteIsAccepted extends \Exception {

}

// инвайт уже отклонен
class cs_InviteIsDeclined extends \Exception {

}

// статус инвайта отозванный
class cs_InviteIsRevoked extends \Exception {

}

// статус инвайта не активный
class cs_InviteIsNotActive extends \Exception {

}

// инвайт не существует
class cs_InviteIsNotExist extends \Exception {

}

// инвайт не принадлежит пользователю
class cs_InviteIsNotMine extends \Exception {

}

// инвайт не был обвновлен
class cs_InviteStatusIsNotExpected extends \Exception {

}

// привышен лимит на отправку активных инвайтов
class cs_InviteActiveSendLimitIsExceeded extends \Exception {

}

// ошибка при невозможности выполнить укзанное действие пользователем, над пользователем, над диалогом
// наследует \paramException, чтобы кидать 400, если не отловлено целенаправленно
class cs_RequestedActionIsNotAble extends ParamException {

}

// ошибка, возгикающая при обработке системного события
class cs_SystemEventException extends \Exception {

}

/** диалог не может быть заархивирован, вылетает в процессе архивации */
class cs_Conversation_IsNolAllowedForArchiving extends ParseFatalException {

}

# endregion
##########################################################

// ошибка при отправке смс
class cs_FailToSendSmsException extends \Exception {

}

// ссылку нельзя парсить
class cs_UrlNotAllowToParse extends \Exception {

	protected $_redirect_url; // ссылка после редиректов

	public function __construct(string $redirect_url, string $message = "", int $code = 0, \Exception $previous = NULL) {

		$this->_redirect_url = $redirect_url;

		parent::__construct($message, $code, $previous);
	}

	public function getRedirectUrl():string {

		return $this->_redirect_url;
	}
}

// ошибка при парсинге ссылки
class cs_UrlParseFailed extends \Exception {

	protected $_error_reason;	// причина ошибки
	protected $_parse_status; 	// статус парсинга ссылки
	protected $_last_http_code; 	// последний полученный http код

	public function __construct(string $error_reason, int $parse_status, int $http_code = 0) {

		$this->_error_reason   = $error_reason;
		$this->_parse_status   = $parse_status;
		$this->_last_http_code = $http_code;

		parent::__construct();
	}

	public function getErrorReason():string {

		return $this->_error_reason;
	}

	public function getParseStatus():int {

		return $this->_parse_status;
	}

	public function getLastHttpCode():int {

		return $this->_last_http_code;
	}
}

/**
 * Ошибка инициализации структуры.
 */
class cs_StructInitUnexpected extends \Exception {

}

/**
 * У пользователя ограничен доступ к данным компании.
 */
class cs_UserCompanyAccessLimited extends \Exception {

}

/**
 * Конфиг не найден
 */
class cs_ConfigNotFound extends \Exception {

}

/**
 * Не валидный тип диалога для действия
 */
class cs_ConversationTypeIsNotValidForAction extends \Exception {

}

/**
 * Строка содержит эмодзи
 */
class cs_StringContainEmoji extends \Exception {

}

/**
 * Не найден пресет для поиска
 */
class cs_SearchPresetNotFound extends \Exception {

}

/**
 * Овнер пытается покинуть "Главный чат"
 */
class cs_OwnerTryToLeaveGeneralConversation extends \Exception {

}

/**
 * Овнер пытается покинуть чат "Спасибо"
 */
class cs_OwnerTryToLeaveRespectConversation extends \Exception {

}

/**
 * Запрещенное действие в "Службе поддержки"
 */
class cs_ActionIsNotAllowedInSupportConversation extends \Exception {

}

/**
 * некорректный список ключей диалогов
 */
class cs_IncorrectConversationMapList extends \Exception {

}

// @formatter:on