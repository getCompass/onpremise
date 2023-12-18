<?php declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер сокет методов для взаимодействия с
 * задачами между pivot сервером и компаниями
 */
class Socket_Company_Task extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"addScheduledCompanyTask",
	];

	/**
	 * Создает задачу в зависимости от типа
	 *
	 * @post task_id
	 * @post company_id
	 * @post type
	 */
	public function addScheduledCompanyTask():array {

		$task_id    = $this->post(\Formatter::TYPE_INT, "task_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");
		$type       = $this->post(\Formatter::TYPE_STRING, "type");

		switch ($type) {

			case "exit":
				Domain_Company_Entity_CronCompanyTask::add($company_id, Domain_Company_Entity_CronCompanyTask::TYPE_EXIT, $task_id);
				break;
			default:
				throw new ParamException("Not found this type {$type}");
		}
		return $this->ok();
	}
}
