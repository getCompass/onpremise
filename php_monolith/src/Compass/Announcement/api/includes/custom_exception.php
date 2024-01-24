<?php

namespace Compass\Announcement;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

/**
 * исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
 */
class cs_AnswerCommand extends \Exception {

	protected string $_command_name;
	protected array  $_command_extra;

	/**
	 * Исключение формирования команды приложения.
	 */
	#[\JetBrains\PhpStorm\Pure]
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
 * при срабатывании блокировки, но с данными о следующей попытке
 */
class cs_blockException extends \BaseFrame\Exception\Request\BlockException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_PhoneNumberIsBlocked constructor.
	 *
	 * @param int             $next_attempt
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $next_attempt, $code, $previous);
	}

	/**
	 * @return int
	 */
	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends \Exception {

}
