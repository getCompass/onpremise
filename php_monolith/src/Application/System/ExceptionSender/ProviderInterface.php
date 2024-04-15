<?php

namespace Application\System\ExceptionSender;

/** интерфейс для отправителя иключений */
interface ProviderInterface {

	/** отправляем сообщение с исключением */
	public static function send(string $text):void;
}