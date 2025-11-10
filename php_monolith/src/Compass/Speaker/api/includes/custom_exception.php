<?php

namespace Compass\Speaker;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

// исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
class cs_AnswerCommand extends \Exception {

	protected $_command_name;
	protected $_command_extra;

	// @mixed
	public function __construct(string $command_name, array $command_extra, $message = "", $code = 0, ?Exception $previous = null) {

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

// когда микросервис go_talking ответил не ОК
class cs_TalkingBadResponse extends \Exception {

}

// когда пришел некорректный идентификатор платформы
class cs_PlatformNotFound extends \Exception {

}

// когда пришла некорректная версия платформы
class cs_PlatformVersionNotFound extends \Exception {

}

// пользовательская сессия не установлена или же некорректна
class cs_InvalidSession extends \Exception {

}

# endregion
##########################################################

##########################################################
# region exception связанные со звонками
##########################################################

// безуспешный API запрос на janus-gateway
class cs_FailedJanusGatewayAPIRequest extends \Exception {

	protected $_response;
	protected $node_id;

	// @mixed
	public function __construct(int $node_id, array $response, $message = "", $code = 0, ?Throwable $previous = null) {

		$this->_response = $response;
		$this->node_id   = $node_id;

		parent::__construct($message, $code, $previous);
	}

	public function getResponse():array {

		return $this->_response;
	}
}

// ошибка - линия пользователя занята
class cs_Call_LineIsBusy extends \Exception {

	protected $_user_id;
	protected $_call_map;
	protected $_conversation_map;

	// @mixed
	public function __construct(int $user_id, ?string $call_map = null, ?string $conversation_map = null, $message = "", $code = 0, ?Exception $previous = null) {

		$this->_user_id          = $user_id;
		$this->_call_map         = $call_map;
		$this->_conversation_map = $conversation_map;

		parent::__construct($message, $code, $previous);
	}

	// получить идентификатор пользователя, у которого занята линия
	public function getBusyLineUserId():int {

		return $this->_user_id;
	}

	// получить call_map созданного звонка
	// @mixed - если имеется call_map, то вернет string; иначе - null
	public function getCallMap() {

		return $this->_call_map;
	}

	// получить conversation_map между собеседниками инициирующими звонок
	// @mixed - если имеется conversation_map, то вернет string; иначе - null
	public function getConversationMap() {

		return $this->_conversation_map;
	}
}

// ошибка - отсутствует single-диалог между пользователями
class cs_Call_ConversationNotExist extends \Exception {

}

// ошибка - собеседник заблокирован системой
class cs_Call_MemberIsDisabled extends \Exception {

}

// ошибка — пользователь уже принял звонок
class cs_Call_UserAlreadyAcceptedCall extends \Exception {

}

// ошибка — звонок завершен
class cs_Call_IsFinished extends \Exception {

}

// аналитика — нет доступа
class cs_Analytics_HaveNotAccess extends \Exception {

}

// запрашиваемая janus нода не найдена
class cs_Janus_Node_Not_Exist extends \Exception {

}

// разговорная комнату уже существует
class cs_Janus_CallRoomAlreadyExist extends \Exception {

}

// разговорная комнату переполнена
class cs_Call_NumberOfMembersExceeded extends \Exception {

}

// действие невозможно
class cs_Call_ActionIsNotAllowed extends \Exception {

}

// ip адресс клиента не найден
class cs_Client_IpAddressNotFound extends \Exception {

}

# endregion
##########################################################

// ошибка при отправке смс
class cs_FailToSendSmsException extends \Exception {

}

// ошибка парсинга данных события
class cs_InvalidEventArgumentsException extends \Exception {

}