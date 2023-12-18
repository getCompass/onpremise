<?php

namespace Compass\FileNode;

// здесь определяются все исключения которые используются для логики
// они никак не влияют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system \Exceptions
##########################################################
use ErrorException;

/**
 * Исключение уровня handler, для того чтобы вернуть на клиент команду ВМЕСТО ответа
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
 * Когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * Когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends \Exception {

}

/**
 * Если куки пусты
 */
class cs_CookieIsEmpty extends \Exception {

}

/**
 * Ошибка скачивания файла
 */
class cs_DownloadFailed extends \Exception {

}

/**
 * Файл не смог обработаться
 */
class cs_FileProcessFailed extends \Exception {

}

/**
 * Видео не смогло обработаться
 */
class cs_VideoProcessFailed extends \Exception {

}

/**
 * Невалидный тип файла для этого источника
 */
class cs_InvalidFileTypeForSource extends \Exception {

}

set_error_handler(function($errno, $errstr, $errfile, $errline) {

	throw new WarningException($errstr, 0, $errno, $errfile);
}, E_WARNING);

/**
 * Поймали warning
 */
class WarningException extends ErrorException {

}