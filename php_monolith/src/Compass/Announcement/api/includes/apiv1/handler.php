<?php

namespace Compass\Announcement;

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
 * 6. ошибки и исклчюения отрабатываются сервер HTTP кодами (например 404)
 * 7. авторизация ОБЯЗАТЕЛЬНА для всех методов кроме GLOBAL и LOGIN
 * 8. соотвественно все методы GLOBAL - анонимны
 * 9. методы регистро НЕ зависимые
 */
class Apiv1_Handler extends Api implements \RouteHandler  {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"announcement",
		"private_announcement",
	];

	// поддерживаемые методы которые доступны без авторизации (при создании новой группы заносятся вручную)
	public const ALLOWED_NOT_AUTHORIZED = [
		"announcement",
		"private_announcement",
	];

	// версия методов хендлера
	protected const _METHOD_VERSION = 1;

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

		return "apiv1";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(string $route, array $post_data, int $user_id = 0):array {

		try {

			### !!! Инициализируем пользователя!       ###
			### Здесь будет запрос на проверку сессии! ###
			$token = Type_Session_Main::getTokenFromCookie();

			Type_Auth_Main::initUserFromToken($token, getDeviceId());
			$user = Type_Auth_Main::getUser();

			if ($user->getUserId() === 0 && $token !== "") {
				Type_Session_Main::removeTokenFromCookie();
			}

			// делаем запрос и получаем ответ
			$output = self::_getResponse($route, $post_data, $user);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			return [
				"command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra()),
			];
		} catch (\cs_DecryptHasFailed) {

			throw new \paramException("wrong key format");
		}

		// выбрасываем ошибку, если что-то некорректное пришло в ответе (например забыли вернуть return $this->ok)
		self::_throwIfStatusIsNotSet($output);

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		// отдаем финальный ответ
		return $output;
	}

	// ---------------------------------------------------
	// PROTECTED UTILS METHODS
	// ---------------------------------------------------

	// роутим юзера в нужную функцию
	protected static function _getResponse(string $api_method, array $post_data, Type_Auth_User $user):array {

		$method = explode(".", strtolower($api_method));

		// если количество аргументов пришло неверное то выбрасываем что такого контроллера нет
		if (count($method) < 2 || count($method) > 3) {
			throw new \apiAccessException("CONTROLLER is not found.");
		}

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

		// выбрасываем ошибку, если контроллер нельзя дергать из под неавторизованного юзера
		self::_throwIfMethodNotAllowedForUnauthorizedUser($controller, $user);

		// возвращаем работу метода
		$class = __NAMESPACE__ . "\Apiv1_{$controller}";

		$extra = [
			"user"   => [
				"session_uniq" => "",
				"role"         => 1,
				"permissions"  => 0,
			],
			"action" => Type_Api_Action::class,
		];
		return (new $class())->work($method_name, self::_METHOD_VERSION, $post_data, $user->getUserId(), $extra);
	}

	// выбрасываем ошибку, если контроллер недоступен
	protected static function _throwIfControllerIsNotAllowed(string $controller):void {

		// проверяем доступные контролеры
		if (!in_array($controller, self::ALLOW_CONTROLLERS)) {
			throw new \apiAccessException("CONTROLLER is not allowed. Check Handler allow controller.");
		}
	}

	// выбрасываем ошибку, если не задали метод
	protected static function _throwIfMethodIsNotSet(string $method):void {

		// проверяем что задан метод внутри контролера
		if (mb_strlen($method) < 1) {
			throw new \apiAccessException("CONTROLLER method name is EMPTY. Check method action name.");
		}
	}

	// выбрасываем ошибку, если контроллер нельзя дергать из под неавторизованного юзера
	protected static function _throwIfMethodNotAllowedForUnauthorizedUser(string $controller, Type_Auth_User $user):void {

		if (!in_array($controller, self::ALLOWED_NOT_AUTHORIZED) && $user->getUserId() < 1) {
			throw new \userAccessException("User not authorized for this actions.");
		}
	}

	// выбрасываем ошибку, если вернулось что-то некорректное
	protected static function _throwIfStatusIsNotSet(array $output):void {

		// если что-то некорректное пришло в ответе (например забыли вернуть return $this->ok)
		if (!isset($output["status"])) {
			throw new \returnException("Final api response do not have status field.");
		}
	}

	// закрываем соединения, если запускали не из консоли
	protected function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}
	}
}
