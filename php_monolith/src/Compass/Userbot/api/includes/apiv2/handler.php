<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Handler\Api;

/**
 * api Handler - version 2.0
 * задачи класса - максимально быстро с минимальными запросами отдавать информацию для чтения.
 */

/**
 * примечания:
 * 1. все запросы к API методом POST
 * 2. все ответы в формате JSON
 * 3. у всех ответов есть поле status (ok||error) && response
 * 4. кодировка UTF-8
 * 5. все ошибки задокументированы в апи документаторе для каждого метода
 * 6. ошибки и исключения отрабатываются сервер HTTP кодами (например 404)
 * 7. методы регистро НЕ зависимые
 */
class Apiv2_Handler extends Api implements \RouteHandler {

	protected const _CLASS_PREFIX = "Apiv2"; // префикс для контроллера

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"command",
		"file",
		"group",
		"message",
		"request",
		"thread",
		"user",
		"webhook",
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $method) => str_replace("_", ".", $method), static::ALLOW_CONTROLLERS);
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "apiv2";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * единая точка входа в API
	 * в качестве параметров принимает:
	 *      @$api_method - название метода вида test.code401
	 *      @$post_data  - параметры post запроса которые будут использоваться внутри контролеров
	 */
	public function handle(string $api_method, array $post_data, int $user_id = 0):array {

		try {

			[$token, $signature] = self::_getAuthorizationData();
			$output = self::_doStart($api_method, $post_data["payload"], $token, $signature);
		} catch (CaseException $exception) {
			return self::_handleCaseError($exception);
		}

		// если запуск был не из консоли - закрываем соединения
		self::_closeConnectionsIfRunFromNotCli();

		// отдаем финальный ответ
		return $output;
	}

	// ---------------------------------------------------
	// PROTECTED UTILS METHODS
	// ---------------------------------------------------

	/**
	 * получаем данные для авторизации запроса
	 */
	protected static function _getAuthorizationData():array {

		// ожидаем заголовок формата "Authorization: bearer=<токен бота>"
		$header_for_token = getHeader("HTTP_AUTHORIZATION");
		$tmp              = explode("=", $header_for_token);
		if (count($tmp) != 2 || trim(mb_strtolower($tmp[0])) != "bearer") {
			throw new CaseException(CASE_EXCEPTION_CODE_1, "incorrect required header - \"authorization\"");
		}
		$token = trim($tmp[1]);

		// ожидаем заголовок формата "Signature: signature=<подпись для запроса>"
		$header_for_signature = getHeader("HTTP_SIGNATURE");
		$tmp                  = explode("=", $header_for_signature);
		if (count($tmp) != 2 || trim(mb_strtolower($tmp[0])) != "signature") {
			throw new CaseException(CASE_EXCEPTION_CODE_1, "incorrect required header - \"signature\"");
		}
		$signature = trim($tmp[1]);

		return [$token, $signature];
	}

	/**
	 * начинаем выполнение
	 *
	 * @throws CaseException
	 */
	protected static function _doStart(string $api_method, string $payload, string $token, string $signature):array {

		// проверяем дефолт поля в запросе
		self::_checkDefaultFields($token, $signature);

		// проверяем валидность подписи
		self::_throwIfPassedInvalidSignature($signature);

		// находим бота, от которого делается запрос
		try {
			$userbot = Userbot::init($token);
		} catch (\cs_Userbot_NotFound) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::USERBOT_REQUEST);
			throw new CaseException(CASE_EXCEPTION_CODE_2, "request token not found");
		}

		// проверяем статус бота
		if (in_array($userbot->status, [Domain_Userbot_Entity_Userbot::STATUS_DISABLE, Domain_Userbot_Entity_Userbot::STATUS_DELETE])) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted - the request cannot be completed");
		}

		// проверяем, что подпись корректна
		self::_throwIfSignatureIsNotAgreed($signature, $payload, $userbot->token, $userbot->secret_key);

		// делаем запрос и получаем ответ
		try {
			$response = self::_getResponse($api_method, fromJson($payload), $userbot);
		} catch (CaseException $e) {
			throw $e;
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		return $response;
	}

	/**
	 * проверяет дефолт поля
	 *
	 * @throws CaseException
	 */
	protected static function _checkDefaultFields(string $token, string $signature):void {

		// если поля, которые не должны быть пустыми, пусты
		if (isEmptyString($token) || isEmptyString($signature)) {
			throw new CaseException(CASE_EXCEPTION_CODE_1, "required header \"authorization\" or \"signature\" are empty");
		}
	}

	/**
	 * выбрасываем ошибку, если пришла некорректная подпись
	 *
	 * @throws CaseException
	 */
	protected static function _throwIfPassedInvalidSignature(string $signature):void {

		Type_Userbot_Main::assertCorrectSignature($signature);
	}

	/**
	 * выбрасываем ошибку, если подписи не совпали
	 *
	 * @throws CaseException
	 */
	protected static function _throwIfSignatureIsNotAgreed(string $signature, string $payload, string $token, string $secret_key):void {

		// формируем и сверяем signature
		$formed_signature = Type_Userbot_Main::getApiSignature($payload, $token, $secret_key);
		if ($formed_signature != $signature) {

			// инкрементим блокировку
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::USERBOT_REQUEST);
			throw new CaseException(CASE_EXCEPTION_CODE_4, "passed invalid signature");
		}
	}

	/**
	 * роутим в нужный метод, получаем ответ
	 *
	 * @throws CaseException
	 */
	protected static function _getResponse(string $api_method, array $post_data, Userbot $userbot):array {

		// если количество аргументов пришло неверное то выбрасываем что такого контроллера нет
		$method = self::_getMethod($api_method);

		$controller  = $method[0];
		$method_name = $method[1];

		// для поддержки 3 уровня
		if (count($method) === 3) {

			$controller  .= "_" . $method[1];
			$method_name = $method[2];
		}

		// выбрасываем ошибку, если контроллер недоступен
		self::_throwIfControllerIsNotAllowed($controller);

		// выбрасываем ошибку, если не пришел метод
		self::_throwIfMethodIsNotSet($method_name);

		// возвращаем работу метода
		$class = __NAMESPACE__ . "\\" . self::_CLASS_PREFIX . "_" . $controller;
		$response = (new $class())->work($method_name, $post_data, $userbot->userbot_id, $userbot->token);

		// выбрасываем ошибку, если какой-то левак пришел в ответе (например забыли вернуть return $this->ok)
		self::_throwIfStatusIsNotSet($response);

		return $response;
	}

	/**
	 * достаём метод
	 *
	 * @throws CaseException
	 */
	protected static function _getMethod(string $api_method):array {

		$method = explode(".", strtolower($api_method));

		// если количество аргументов пришло неверное, то выбрасываем ошибку
		if (count($method) < 2 || count($method) > 3) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "request method incorrect");
		}

		return $method;
	}

	/**
	 * выбрасываем ошибку, если контроллер недоступен
	 *
	 * @throws CaseException
	 */
	protected static function _throwIfControllerIsNotAllowed(string $controller):void {

		// проверяем доступные контролеры
		if (!in_array($controller, self::ALLOW_CONTROLLERS)) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "request method unknown");
		}
	}

	/**
	 * выбрасываем ошибку, если не задали метод
	 *
	 * @throws CaseException
	 */
	protected static function _throwIfMethodIsNotSet(string $method):void {

		// проверяем что задан метод внутри контролера
		if (mb_strlen($method) < 1) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "request method unknown");
		}
	}

	/**
	 * выбрасываем ошибку, если вернулся какой-то левак
	 *
	 * @throws CaseException
	 */
	protected static function _throwIfStatusIsNotSet(array $output):void {

		// если какой-то левак пришел в ответе (например забыли вернуть return $this->ok)
		if (!isset($output["status"])) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}
	}

	/**
	 * Обработка ошибки при совершении запроса
	 */
	protected static function _handleCaseError(CaseException $exception):array {

		$error_code = $exception->getErrorCode();

		// устанавливаем http code ошибки
		http_response_code($exception->getHttpCode());

		// формируем ответ
		$response_body = array_merge([
			"error_code" => (int) $error_code,
			"message"    => (string) $exception->getMessage(),
		], $exception->getExtra());

		// возвращаем ответ
		return [
			"status"   => (string) "error",
			"response" => (object) $response_body,
		];
	}

	/**
	 * закрываем соединения, если запускали не из консоли
	 */
	protected function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}
	}
}
