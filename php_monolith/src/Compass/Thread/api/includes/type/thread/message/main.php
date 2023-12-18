<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

// главный класс для взаимодействия с сообщениями из ТРЕДОВ
class Type_Thread_Message_Main {

	// текущая версия обработчика сообщений
	// все новые сообщения будут создаваться с ней
	// уже существующие будут обрабатываться на основе поля `handler_version` внутри них
	protected const _MESSAGE_HANDLER_VERSION = 2;

	/**
	 * возвращает обработчик сообщения в зависимости от версии переданного сообщения
	 *
	 * @param $message array сообщение
	 *
	 * @return string|Type_Thread_Message_Handler_V1|Type_Thread_Message_Handler_V2
	 *
	 * @throws \parseException если передана невалидная версия
	 *
	 */
	// @mixed
	public static function getHandler(array $message) {

		$version = $message["version"];

		return match ($version) {
			1 => Type_Thread_Message_Handler_V1::class,
			2 => Type_Thread_Message_Handler_V2::class,
			default => throw new ParseFatalException("Unsupported message handler version"),
		};
	}

	/**
	 * возвращает обработчик сообщения в зависимости от текущей версии
	 *
	 * @return string|Type_Thread_Message_Handler_V2
	 */
	public static function getLastVersionHandler() {

		$version = self::_MESSAGE_HANDLER_VERSION;
		return __NAMESPACE__ . "\Type_Thread_Message_Handler_V{$version}";
	}
}