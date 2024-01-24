<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * класс который наследует любой Api класс
 */
class Apiv1_Default {

	// разрешенные методы
	public const ALLOW_METHODS = [];

	public string   $userbot_id = "";
	public string   $token      = "";
	protected array $_post_data = [];

	/**
	 * выполняет метод из контроллера
	 *
	 * @throws CaseException
	 */
	public function work(string $action, array $post_data, string $userbot_id, string $token):array {

		// присваиваем post-данные
		$this->_post_data = $post_data;

		// назначаем переменную бота
		$this->userbot_id = $userbot_id;

		// назначаем переменную token
		$this->token = $token;

		// выбрасываем ошибку, если метод не доступен
		if (!$this->_isHasAction($action)) {
			throw new CaseException(CASE_EXCEPTION_CODE_9, "method incorrect or not available");
		}
		return $this->$action();
	}

	/**
	 * получаем action
	 */
	#[Pure] protected function _isHasAction(string $action):bool {

		// вызываем метод, если он доступен
		foreach ($this::ALLOW_METHODS as $value) {

			if (strtolower($action) == strtolower($value)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * возвращает код ошибки с сообщением
	 */
	#[ArrayShape(["status" => "string", "response" => "object"])]
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
	 * возвращает ответ
	 */
	#[ArrayShape(["status" => "string", "response" => "object"])]
	public function ok(array $output = []):array {

		return [
			"status"   => (string) "ok",
			"response" => (object) $output,
		];
	}

	/**
	 * возвращает параметр который пришел от пользователя
	 *
	 * @mixed
	 */
	public function post(string $type, string $key, $default = null) {

		return Type_Formatter::post($this->_post_data, $type, $key, $default);
	}
}
