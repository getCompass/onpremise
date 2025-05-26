<?php

namespace BaseFrame\Controller;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;

/**
 * хендлер мидлвейров
 */
abstract class Api extends Base {

	// кастомные поля апи
	public string $session_uniq = "";
	public int    $role         = 0;
	public int    $permissions  = 0;

	/* @var Action $action */
	public Action $action;

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
	 * @throws ControllerMethodNotFoundException
	 */
	public function work(string $method_name, int $method_version, array $post_data, int $user_id, array $extra):array {

		// присваиваем post-данные
		$this->_post_data = $post_data;

		// устанавливаем версию метода
		$this->method_version = $method_version;

		// назначаем переменную пользователя (инициализируем пользователя уже повторно)
		$this->user_id = $user_id;

		// назначаем переменную $session_uniq
		$this->session_uniq = $extra["user"]["session_uniq"];

		$this->action = new $extra["action"]($this->user_id);

		$this->extra["space"]["is_restricted_access"] = $extra["space"]["is_restricted_access"] ?? false;

		if (isset($extra["user"]["role"])) {

			// назначаем переменную $role
			$this->role = $extra["user"]["role"];

			// назначаем переменную $permissions
			$this->permissions = $extra["user"]["permissions"];
		}

		// выбрасываем ошибку, если метод не доступен
		if (!$this->_isHasMethod($method_name)) {
			throw new ControllerMethodNotFoundException("METHOD in controller is not available.");
		}
		$response = $this->$method_name();

		// добавляем actions к основному response
		if (isset($this->action)) {

			$actions = $this->action->getActions();
			if (count($actions) > 0) {
				$response["actions"] = array_merge($response["actions"] ?? [], $actions);
			}
		}

		return $response;
	}
}