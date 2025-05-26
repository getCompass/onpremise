<?php

namespace BaseFrame\Router;

/**
 * хендлер мидлвейров
 */
class Middleware {

	/* @var Middleware\Main[] $_class_list */
	protected array $_class_list = []; // список классов для проверки

	/**
	 * @param array $class_list
	 *
	 * @return void
	 */
	public function __construct(array $class_list = []) {

		$this->_class_list = $class_list;
	}

	/**
	 * формируем запрос обрабатываем все MW по порядку и отдаем ответ
	 */
	public function handler(string $route, array $post, array $extra = []):array {

		$request = new Request($route, $post, $extra);

		foreach ($this->_class_list as $class_name) {

			$request = $class_name::handle($request);
		}

		return $request->response;
	}
}