<?php

namespace Compass\Pivot;

/**
 * Сценарии для сокет действий с тарифами
 */
class Domain_Space_Scenario_Tariff_Socket {

	/**
	 * Получить заваленные задачи по тарифам
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getFailedTaskList():array {

		return Domain_Space_Action_Tariff_GetFailedTaskList::do();
	}

	/**
	 * Получить зависшие задачи
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getStuckTaskList():array {

		return Domain_Space_Action_Tariff_GetStuckTaskList::do();
	}

	/**
	 * Получить заваленные обсервы
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getFailedObserveList():array {

		return Domain_Space_Action_Tariff_GetFailedObserveList::do();
	}

	/**
	 * Получить зависшие обсервы
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getStuckObserveList():array {

		return Domain_Space_Action_Tariff_GetStuckObserveList::do();
	}

	/**
	 * Получить среднее время задач в очереди
	 *
	 * @param int $start_time
	 * @param int $end_time
	 *
	 * @return int
	 */
	public static function getAverageQueueTime(int $start_time, int $end_time):int {

		return Domain_Space_Action_Tariff_GetAverageQueueTime::do($start_time, $end_time);
	}

	/**
	 * Получить историю заваленных задач
	 *
	 * @param int $start_time
	 * @param int $end_time
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getFailedTaskHistory(int $start_time, int $end_time):array {

		return Domain_Space_Action_Tariff_GetFailedTaskHistory::do($start_time, $end_time);
	}

	/**
	 * Изменить ограничение тарифа по количеству участников, если нужно
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @return bool[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	public static function increaseMemberCountLimit(int $user_id, int $space_id):array {

		$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);

		return Domain_SpaceTariff_Action_IncreaseMemberCountLimit::do($user_id, $company_row);
	}
}