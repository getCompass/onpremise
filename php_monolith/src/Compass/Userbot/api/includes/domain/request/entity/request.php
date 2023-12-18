<?php

namespace Compass\Userbot;

use JetBrains\PhpStorm\ArrayShape;

/**
 * класс сущности запросов для бота
 *
 * Class Domain_Request_Entity_Request
 */
class Domain_Request_Entity_Request {

	public const STATUS_NEED_WORK = 0; // статус - необходимо выполнить
	public const STATUS_FAILED    = 1; // статус - завершился с ошибкой
	public const STATUS_SUCCESS   = 2; // статус - завершился успешно

	// список действий в запросах
	public const ACTION_REQUEST             = 0;  // выполняется запрос
	public const ACTION_SEND_USER           = 1;  // отправка сообщения пользователю
	public const ACTION_SEND_GROUP          = 2;  // отправка сообщения в группу
	public const ACTION_SEND_THREAD         = 3;  // отправка сообщения в тред
	public const ACTION_ADD_REACTION        = 4;  // добавление реакции на сообщение
	public const ACTION_REMOVE_REACTION     = 5;  // удаление реакции с сообщения
	public const ACTION_GET_USERS           = 6;  // получение данных о пользователе
	public const ACTION_GET_GROUPS          = 7;  // получение данных о группах бота
	public const ACTION_UPDATE_COMMANDS     = 8;  // обновить список команд бота
	public const ACTION_GET_COMMANDS        = 9;  // получение списка команд бота
	public const ACTION_GET_FILE_NODE_URL   = 10; // получение данных о файловой ноде для загрузки файла
	public const ACTION_SET_WEBHOOK_VERSION = 11; // получение данных о файловой ноде для загрузки файла

	// список ошибок для действий запроса бота
	public const ERROR_LIST = [
		self::ACTION_REQUEST             => [
			CASE_EXCEPTION_CODE_5 => "exceeded limit on work request",
			CASE_EXCEPTION_CODE_6 => "unknown error in work request",
		],
		self::ACTION_SEND_USER           => [
			CASE_EXCEPTION_CODE_1000 => "passed incorrect params",
			CASE_EXCEPTION_CODE_1001 => "user is not found",
			CASE_EXCEPTION_CODE_1002 => "user is kicked for company",
		],
		self::ACTION_SEND_GROUP          => [
			CASE_EXCEPTION_CODE_1000 => "passed incorrect params",
			CASE_EXCEPTION_CODE_1003 => "group is not allowed for userbot",
			CASE_EXCEPTION_CODE_1004 => "group is not found",
		],
		self::ACTION_SEND_THREAD         => [
			CASE_EXCEPTION_CODE_1000 => "passed incorrect params",
			CASE_EXCEPTION_CODE_1005 => "message is not allowed for userbot",
			CASE_EXCEPTION_CODE_1007 => "message is not found",
		],
		self::ACTION_ADD_REACTION        => [
			CASE_EXCEPTION_CODE_1000 => "passed incorrect params",
			CASE_EXCEPTION_CODE_1005 => "message is not allowed for userbot",
			CASE_EXCEPTION_CODE_1006 => "reaction is not found",
			CASE_EXCEPTION_CODE_1007 => "message is not found",
		],
		self::ACTION_REMOVE_REACTION     => [
			CASE_EXCEPTION_CODE_1000 => "passed incorrect params",
			CASE_EXCEPTION_CODE_1005 => "message is not allowed for userbot",
			CASE_EXCEPTION_CODE_1006 => "reaction is not found",
			CASE_EXCEPTION_CODE_1007 => "message is not found",
		],
		self::ACTION_GET_USERS           => [],
		self::ACTION_GET_GROUPS          => [],
		self::ACTION_UPDATE_COMMANDS     => [
			CASE_EXCEPTION_CODE_1008 => "exceeded limit for command_list",
			CASE_EXCEPTION_CODE_1009 => "incorrect command in command_list",
		],
		self::ACTION_GET_COMMANDS        => [],
		self::ACTION_GET_FILE_NODE_URL   => [],
		self::ACTION_SET_WEBHOOK_VERSION => [
			CASE_EXCEPTION_CODE_1011 => "passed incorrect webhook version",
		],
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * получаем ошибку для действия
	 */
	public static function getError(int $action = 0, int $error_code = 0):string {

		return self::ERROR_LIST[$action][$error_code] ?? "unknown error in worked request";
	}

	/**
	 * получаем ответ с ошибкой
	 */
	#[ArrayShape(["error_code" => "int", "message" => "string"])]
	public static function getErrorResponse(int $action, int $error_code):array {

		$error_text = self::ERROR_LIST[$action][$error_code] ?? "unknown error in worked request";

		return [
			"error_code" => (int) $error_code,
			"message"    => (string) $error_text,
		];
	}
}