<?php

namespace Compass\Company;

/**
 * Действие получения статистики экранного времени пользователя
 */
class Domain_Member_Action_GetScreenTimeStat {

	protected const _STAT_DAYS_COUNT         = 60;            // получаем статистику за последние 60 дней
	protected const _START_WORKING_DAY_HOURS = "08:00";      // время начала рабочего дня пользователя
	protected const _END_WORKING_DAY_HOURS   = "00:00";      // время конца рабочего дня пользователя

	/**
	 * @param int $member_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function do(int $member_id):array {

		$stat_list = Gateway_Socket_Pivot::getScreenTimeStat($member_id, self::_STAT_DAYS_COUNT);

		return self::_makeDayListForClient($stat_list);
	}

	/**
	 * Формируем статистику для клиента
	 *
	 * @param array $stat_list
	 *
	 * @return array
	 * @long
	 */
	protected static function _makeDayListForClient(array $stat_list):array {

		$day_list = [];
		foreach ($stat_list as $user_local_date => $screen_time_list) {

			$temp = [
				"total_day_stat"  => (int) 0,
				"min15_stat_list" => (array) [],
			];

			// переформатируем дату из формата dd.mm.yyyy в формат для клиентов: yyyy-mm-dd
			$tt                        = explode(".", $user_local_date);
			$formatted_user_local_date = "{$tt[2]}-{$tt[1]}-{$tt[0]}";

			// проходим по каждому часу
			foreach ($screen_time_list as $local_time => $screen_time) {

				// если не нужно добавлять этот промежуток времени в ответ
				if (!self::_isNeedAddToOutput($local_time, self::_START_WORKING_DAY_HOURS, self::_END_WORKING_DAY_HOURS)) {
					continue;
				}

				$temp["total_day_stat"]    += $screen_time;
				$temp["min15_stat_list"][] = [
					"min15_start" => $local_time,
					"value"       => $screen_time,
				];
			}

			// сортируем по возрастанию
			usort($temp["min15_stat_list"], function(array $a, array $b) {

				return $a["min15_start"] > $b["min15_start"] ? 1 : -1;
			});

			// если за день была только ночная активность - она не возвращается и пустой день нет смысла добавлять
			if (count($temp["min15_stat_list"]) > 0) {
				$day_list[$formatted_user_local_date] = $temp;
			}
		}

		// сортируем дни
		if (count($day_list) > 0) {
			krsort($day_list);
		}

		return $day_list;
	}

	/**
	 * Нужно ли добавлять время в ответ
	 *
	 * @param string $time
	 * @param string $start_hours
	 * @param string $end_hours
	 *
	 * @return bool
	 */
	protected static function _isNeedAddToOutput(string $time, string $start_hours, string $end_hours):bool {

		$date_time  = \DateTime::createFromFormat("H:i", $time);
		$start_time = \DateTime::createFromFormat("H:i", $start_hours);
		$end_time   = \DateTime::createFromFormat("H:i", $end_hours);

		// проверяем что переданное время не попадает в целевой временной промежуток рабочего дня
		// т.е что время попадает в период с 08:00 до 00:00
		return !($date_time >= $end_time && $date_time < $start_time);
	}
}