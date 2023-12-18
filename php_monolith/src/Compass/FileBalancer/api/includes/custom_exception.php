<?php

namespace Compass\FileBalancer;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

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
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends \Exception {

}

/**
 * если куки пусты
 */
class cs_CookieIsEmpty extends \Exception {

}