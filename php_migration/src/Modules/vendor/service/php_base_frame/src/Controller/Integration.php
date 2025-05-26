<?php

namespace BaseFrame\Controller;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * хендлер мидлвейров
 */
abstract class Integration extends Base {

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
	 * @throws EndpointAccessDeniedException
	 */
	public function work(string $method_name, int $method_version, array $post_data, int $user_id, array $extra):array {

		// устанавливаем версию метода
		$this->method_version = $method_version;

		// присваиваем post-данные
		$this->_post_data = fromJson($post_data["json_params"]);

		// назначаем переменную пользователя (инициализируем пользователя уже повторно)
		$this->user_id = $user_id;

		// выбрасываем ошибку, если метод не доступен
		if (!$this->_isHasMethod($method_name)) {
			throw new EndpointAccessDeniedException("METHOD in controller is not available.");
		}
		return $this->$method_name();
	}
}