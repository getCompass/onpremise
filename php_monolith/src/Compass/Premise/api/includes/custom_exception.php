<?php

namespace Compass\Premise;

use BaseFrame\Exception\Request\BlockException;

/**
 * исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
 */
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

/**
 * если куки пусты
 */
class cs_CookieIsEmpty extends \Exception {

}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * превышен лимит ошибок
 */
class cs_ErrorCountLimitExceeded extends \Exception {

	protected int $_next_attempt;

	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_next_attempt = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->_next_attempt;
	}
}

/**
 * mocked-данные не найдены
 */
class cs_MockedDataIsNotFound extends \Exception {

}

/**
 * передан хэш с неверной структурой
 */
class cs_InvalidHashStruct extends \Exception {

}

/**
 * неверная версия соли
 */
class cs_IncorrectSaltVersion extends \Exception {

}

/**
 * некоректный company_id
 */
class cs_CompanyIncorrectCompanyId extends \Exception {

}

/**
 * Получили некорректный id пользователя
 */
class cs_IncorrectUserId extends \Exception {

}

/**
 * неверная подпись
 */
class cs_WrongSignature extends \Exception {

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
	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $next_attempt, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * Компания в гибернации
 */
class cs_CompanyIsHibernate extends \Exception {

}