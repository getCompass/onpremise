<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс, который наследует любой userbot класс
 */
abstract class Userbot_Default {

	// разрешенные методы
	public const ALLOW_METHODS = [];

	public int      $user_id    = 0;
	protected array $_post_data = [];

	/**
	 * Выполняет метод из контроллера
	 *
	 * @throws \apiAccessException
	 */
	public function work(string $action, array $post_data):array {

		// очищаем переменную пользователя
		unset($this->user);

		// присваиваем post-данные
		$this->_post_data = $post_data;

		// выбрасываем ошибку, если метод не доступен
		if (!$this->_isHasAction($action)) {
			throw new \apiAccessException("METHOD in controller is not available.");
		}
		return $this->$action();
	}

	/**
	 * получаем action
	 */
	protected function _isHasAction(string $action):bool {

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
			"status"   => (string) "error",
			"response" => (object) $output,
		];
	}

	/**
	 * Возвращает ответ
	 */
	public function ok(array $output = []):array {

		return [
			"status"   => (string) "ok",
			"response" => (object) $output,
		];
	}

	/**
	 * Возвращает параметр, который пришел от пользователя
	 *
	 * @param string $type
	 * @param string $key
	 * @param null   $default
	 *
	 * @mixed
	 * @return mixed
	 * @throws ParamException
	 */
	public function post(string $type, string $key, $default = null):mixed {

		return \Formatter::post($this->_post_data, $type, $key, $default);
	}
}