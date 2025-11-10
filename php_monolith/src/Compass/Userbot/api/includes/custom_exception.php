<?php

use JetBrains\PhpStorm\Pure;
use BaseFrame\Exception\Request\BlockException;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

/**
 * исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
 */
class cs_AnswerCommand extends Exception {

	protected string $_command_name;
	protected array  $_command_extra;

	#[Pure] public function __construct(string $command_name, array $command_extra, string $message = "", int $code = 0, ?Exception $previous = null) {

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
 * при срабатывании блокировки, но с данными о следующей попытке
 */
class cs_blockException extends BlockException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_PhoneNumberIsBlocked constructor.
	 *
	 */
	public function __construct(int $next_attempt, string $message = "") {

		$this->next_attempt = $next_attempt;
		parent::__construct($message);
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * выпадает если произошло дублирование при insert записи
 */
class cs_RowDuplication extends Exception {

}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends Exception {

}

/**
 * когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends Exception {

}

/**
 * отправленный текст слишком длинный
 */
class cs_Text_IsTooLong extends Exception {

}

/**
 * бот не найден
 */
class cs_Userbot_NotFound extends Exception {

}

/**
 * бот не включён
 */
class cs_Userbot_IsNotEnabled extends Exception {

}

/**
 * запрос для бота некорректен
 */
class cs_Userbot_RequestIncorrect extends Exception {

}

/**
 * запрос для бота зафейлился
 */
class cs_Userbot_RequestFailed extends Exception {

}

/**
 * параметры для запроса бота некорректны
 */
class cs_Userbot_RequestIncorrectParams extends Exception {

}

/**
 * пользователь не найден
 */
class cs_Member_IsNotFound extends Exception {

}

/**
 * пользователь уволен
 */
class cs_Member_IsKicked extends Exception {

}

/**
 * диалог не найден
 */
class cs_Conversation_IsNotFound extends Exception {

}

/**
 * диалог не групповой
 */
class cs_Conversation_IsNotGroup extends Exception {

}

/**
 * диалог недоступен
 */
class cs_Conversation_IsNotAllowed extends Exception {

}

/**
 * сообщение не найдено
 */
class cs_Message_IsNotFound extends Exception {

}

/**
 * сообщение недоступно
 */
class cs_Message_IsNotAllowed extends Exception {

}

/**
 * реакция не найдена
 */
class cs_Reaction_IsNotFound extends Exception {

}

/**
 * некорректная команда бота
 */
class cs_UserbotCommand_IsIncorrect extends Exception {

}

/**
 * превышен лимит для списка команд бота
 */
class cs_UserbotCommand_ExceededLimit extends Exception {

}