<?php

namespace Compass\Jitsi;

/** класс для работы с метриками */
class Domain_Jitsi_Entity_Metrics {

	/**
	 * Возвращаем кол-во записей, созданных за период
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getCountByPeriod(int $from_date, int $to_date):int {

		return Gateway_Db_JitsiData_ConferenceList::getCountByPeriod($from_date, $to_date);
	}
}