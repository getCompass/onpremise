<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * главный класс для взаимодействия с сообщениями из ТРЕДОВ
 */
class Type_Thread_Message_Main {

	// текущая версия обработчика сообщений
	// все новые сообщения будут создаваться с ней
	// уже существующие будут обрабатываться на основе поля `handler_version` внутри них
	protected const _MESSAGE_HANDLER_VERSION = 2;

	/**
	 * возвращает обработчик сообщения в зависимости от версии переданного сообщения
	 *
	 * @param array $message сообщение
	 *
	 * @return string
	 *
	 * @throws \parseException если передана невалидная версия
	 *
	 */
	public static function getHandler(array $message):string|Type_Thread_Message_Handler_V1|Type_Thread_Message_Handler_V2 {

		$version = $message["version"];

		switch ($version) {

			case 1:
				return Type_Thread_Message_Handler_V1::class;

			case 2:
				return Type_Thread_Message_Handler_V2::class;

			default:
				throw new ParseFatalException("Unsupported message handler version");
		}
	}

	/**
	 * возвращает обработчик сообщения в зависимости от текущей версии
	 *
	 */
	public static function getLastVersionHandler():string|Type_Thread_Message_Handler_V1|Type_Thread_Message_Handler_V2 {

		$version = self::_MESSAGE_HANDLER_VERSION;
		return __NAMESPACE__ . "\Type_Thread_Message_Handler_V{$version}";
	}
}