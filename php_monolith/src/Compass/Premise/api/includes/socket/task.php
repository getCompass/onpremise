<?php

namespace Compass\Premise;

/**
 * Системный класс.
 * Входная точка для задач приложения.
 */
class Socket_Task extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"processList",
	];

	/**
	 * Обработать пачку задач
	 *
	 * @return array
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function processList():array {

		$event_list = $this->post(\Formatter::TYPE_ARRAY, "task_list");

		$handler  = Type_Task_Handler::instance();
		$response = $handler->handleList($event_list);

		return $this->ok([
			"task_process_result_list" => (object) $response,
		]);
	}
}
