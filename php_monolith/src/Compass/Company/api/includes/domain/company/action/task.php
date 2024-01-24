<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Action для увольнения сотрудника
 */
class Domain_Company_Action_Task {

	/**
	 * Выполним задачу
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $task_id, int $type):bool {

		$type = Domain_User_Entity_TaskType::TYPE_INT_TO_STRING[$type];

		switch ($type) {

			case Domain_User_Entity_TaskType::TYPE_EXIT:

				$task     = Gateway_Db_CompanyData_ExitList::getOne($task_id);
				$complete = Domain_User_Entity_TaskExit::run($task);
				break;
			default:
				throw new ParamException("Not correct type");
		}

		return $complete;
	}
}
