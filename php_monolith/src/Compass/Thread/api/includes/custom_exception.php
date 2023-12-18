<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

// здесь определяются все исключения которые используются для логики
// они никак не влияют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

// исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
class cs_AnswerCommand extends \Exception {

	protected string $_command_name;
	protected array  $_command_extra;

	public function __construct(string $command_name, array $command_extra, string $message = "", int $code = 0, \Exception $previous = null) {

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

class cs_SessionNotFound extends \Exception {

}

class cs_UserNotFound extends \Exception {

}

// когда микросервис go_talking ответил не ОК
class cs_TalkingBadResponse extends \Exception {

}

// когда пришел некорректный идентификатор платформы
class cs_PlatformNotFound extends \Exception {

}

// пользовательская сессия не установлена или же некорректна
class cs_InvalidSession extends \Exception {

}

// ошибка при расшифровке ключа какой-либо сущности
class cs_DecryptHasFailed extends \Exception {

}

// ошибка при распаковке какой-либо сущности
class cs_UnpackHasFailed extends \Exception {

}

// когда не участник треда что-то хочет сделать с тредом
class cs_Thread_UserNotMember extends \Exception {

}

// когда пытаются поставить или удалить не существующую реакцию
class cs_Message_IsNotAllowedForReaction extends \Exception {

}

// когда превысили лимит добавления реакций
class cs_Message_ReactionLimit extends \Exception {

}

class cs_Message_ReactionIsExist extends \Exception {

}

// пользователь не является отправителем сообщения
class cs_Message_UserNotSender extends \Exception {

}

// сообщение нельзя редактировать
class cs_Message_IsNotAllowForEdit extends \Exception {

}

// вышло время для действия над сообщением
class cs_Message_IsTimeNotAllowForDoAction extends \Exception {

}

// сообщение нельзя удалить
class cs_Message_IsNotAllowForDelete extends \Exception {

}

// сообщение нельзя удалить, потому что вышло время удаления
class cs_Message_IsTimeNotAllowToDelete extends \Exception {

}

// сообщение удалено
class cs_Message_IsDeleted extends \Exception {

}

// родительское сообщение удалено
class cs_ParentMessage_IsDeleted extends \Exception {

}

// родительское сообщение - респект
class cs_ParentMessage_IsRespect extends \Exception {

}

// список сообщений пуст
class cs_MessageList_IsEmpty extends \Exception {

}

// достигнут лимит сообщений
class cs_Message_Limit extends \Exception {

}

// сообщение невозможно получить
class cs_Message_HaveNotAccess extends \Exception {

}

// сообщение c пустым текстом
class cs_Message_IsEmptyText extends \Exception {

}

// пользователь не имеет право выполнять действия с meta сущность треда - диалогом
class cs_UserIsNotParentMetaMember extends \Exception {

}

// возникает когда пытаются написать сообщение в тред который закрыт для их отправки
class cs_ThreadIsLocked extends \Exception {

}

// когда тред доступен только для чтения
class cs_ThreadIsReadOnly extends \Exception {

}

// когда родительская сущность треда находилась на сервере, которого больше нет
class cs_Thread_ParentEntityNotFound extends \Exception {

}

// возникает когда пытаются прикрепить тред к сообщению диалога который закрыт для их отправки
class cs_ConversationIsLocked extends \Exception {

}

// когда сообщение слишком длинное
class cs_Message_IsTooLong extends \Exception {

}

// когда слишком много текстовых частей сообщения
class cs_Message_ToManyMessageChunks extends \Exception {

}

// дубликат сообщения с повторяющимся client_message_id
class cs_Message_DuplicateClientMessageId extends \Exception {

}

// user не участник диалога
class cs_UserIsNotMember extends \Exception {

}

// когда обращаемся к Helper_Threads::getMetaIfUserMember по треду из single-диалога
// и собеседник либо заблокировал нас, либо мы заблокировали его, либо он заблокирован в системе
class cs_Conversation_IsBlockedOrDisabled extends \Exception {

	protected $_meta_row; // запись с meta_row
	protected $_allow_status; // передает, что собеседник заблокировал нас, либо мы заблокировали его, либо он заблокирован в системе

	public function __construct(int $allow_status, array $meta_row = [], string $message = "", int $code = 0, \Exception $previous = null) {

		$this->_meta_row     = $meta_row;
		$this->_allow_status = $allow_status;

		parent::__construct($message, $code, $previous);
	}

	// получаем meta_row
	public function getMetaRow():array {

		// попытка обратиться к мета, когда она не записана в объект исключения (например в случае создания треда)
		if (!isset($this->_meta_row["thread_map"])) {
			throw new ParseFatalException(__METHOD__ . ": attempt get meta_row in context when meta is not exist");
		}

		return $this->_meta_row;
	}

	// получаем allow_status
	public function getAllowStatus():int {

		return $this->_allow_status;
	}
}

/** к заявке на наем нельзя прицепить тред */
class cs_HiringRequestIsNotAllowedForAddThread extends \baseException {

}

# endregion
##########################################################

// ссылку нельзя парсить
class cs_UrlNotAllowToParse extends \Exception {

	protected $_redirect_url; // ссылка после редиректов

	public function __construct(string $redirect_url, string $message = "", int $code = 0, \Exception $previous = null) {

		$this->_redirect_url = $redirect_url;

		parent::__construct($message, $code, $previous);
	}

	public function getRedirectUrl():string {

		return $this->_redirect_url;
	}
}

// ошибка при парсинге ссылки
class cs_UrlParseFailed extends \Exception {

	protected $_error_reason;      // причина ошибки
	protected $_parse_status;      // статус парсинга ссылки
	protected $_last_http_code;      // последний полученный http код

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

// достигли лимита чатов в избранном
class cs_Thread_ToManyInFavorite extends \Exception {

}

// @formatter:on
