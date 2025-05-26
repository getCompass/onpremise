<?php

namespace BaseFrame\Controller;

/**
 * хендлер мидлвейров
 */
abstract class Base {

	// разрешенные методы
	public const ALLOW_METHODS = [];

	public int      $method_version = 1;
	public int      $user_id        = 0;
	public array    $extra          = [];
	protected array $_post_data     = [];

	// методы, которые триггерят обновление активности
	public const MEMBER_ACTIVITY_METHOD_LIST = [];

	// методы, доступные только с премиум-доступом
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	// методы, доступ к которым ограничен без оплаты мест
	public const ALLOWED_WITH_PAYMENT_ONLY_METHODS = [];

	// список запрещенных методов по ролям, пример:
	// [
	//	ROLE_GUEST => [
	//		"sendMessage"
	//	]
	// ];
	// отсутствие роли говорит об отсутствии ограничения для нее
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [];

	/**
	 * Выполняем метод из контроллера
	 *
	 * @param string $method_name
	 * @param int    $method_version
	 * @param array  $post_data
	 * @param int    $user_id
	 * @param array  $extra
	 *
	 * @return array
	 */
	abstract public function work(string $method_name, int $method_version, array $post_data, int $user_id, array $extra):array;

	/**
	 * получаем action
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	protected function _isHasMethod(string $action):bool {

		// вызываем метод, если он доступен
		foreach ($this::ALLOW_METHODS as $value) {

			if (strtolower($action) == strtolower($value)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Возвращает код ошибки с сообщением
	 *
	 * @param int    $error_code
	 * @param string $message
	 * @param array  $extra
	 *
	 * @return array
	 */
	public function error(int $error_code, string $message = "", array $extra = []):array {

		$output = [
			"error_code" => $error_code,
			"message"    => $message,
		];

		// если пришла экстра, то мерджим в основной массив
		if (count($extra) > 0) {
			$output = array_merge($extra, $output);
		}

		return [
			"status"      => (string) "error",
			"response"    => (object) $output,
			"server_time" => (int) time(),
		];
	}

	/**
	 * Возвращает ответ
	 *
	 * @param array $output
	 *
	 * @return array
	 */
	public function ok(array $output = []):array {

		return [
			"status"      => (string) "ok",
			"response"    => (object) $output,
			"server_time" => (int) time(),
		];
	}

	/**
	 * возвращает параметр который пришел от пользователя
	 *
	 * @param string $type
	 * @param string $key
	 * @param null   $default
	 *
	 * @return mixed
	 * @mixed
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function post(string $type, string $key, $default = null):mixed {

		return \Formatter::post($this->_post_data, $type, $key, $default);
	}

	/**
	 * Триггерит ли метод обновление активности
	 *
	 * @param string $method_name
	 *
	 * @return bool
	 */
	public function isNeedRefreshHibernationDelayTokenMethod(string $method_name):bool {

		// провеяем, что метод триггерит активность
		foreach ($this::MEMBER_ACTIVITY_METHOD_LIST as $value) {

			if (strtolower($method_name) == strtolower($value)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Необходим ли премиум для вызова метода.
	 */
	public function isActivePremiumRequiredForMethodCall(string $method_name):bool {

		// провеяем, что метод триггерит активность
		foreach ($this::ALLOW_WITH_PREMIUM_ONLY_METHODS as $value) {

			if (strtolower($method_name) === strtolower($value)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Необходима ли оплата для вызова метода.
	 */
	public function isPaymentRequiredForMethodCall(string $method_name):bool {

		// провеяем, что метод требует оплаты
		foreach ($this::ALLOWED_WITH_PAYMENT_ONLY_METHODS as $method) {

			if (strtolower($method_name) === strtolower($method)) {
				return true;
			}
		}

		return false;
	}
}