<?php

namespace Compass\Company;

/**
 * контроллер для сокет-методов работы с задачами на выполнение
 */
class Socket_Company_Task extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"doTask",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод выполнения задачу которая пришла
	 *
	 * @throws \Exception
	 */
	public function doTask():array {

		$task_id  = $this->post(\Formatter::TYPE_INT, "task_id");
		$type     = $this->post(\Formatter::TYPE_INT, "type");
		$complete = Domain_Company_Scenario_Socket::doTask($task_id, $type);

		return $this->ok([
			"complete" => (bool) $complete,
		]);
	}
}
