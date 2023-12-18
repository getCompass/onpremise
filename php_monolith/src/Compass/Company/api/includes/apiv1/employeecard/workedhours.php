<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы с рабочими часами сотрудника
 */
class Apiv1_EmployeeCard_WorkedHours extends \BaseFrame\Controller\Api {

	/**
	 * поддерживаемые методы. регистр не имеет значение
	 */
	public const ALLOW_METHODS = [
		"getByMonth",
		"getByYear",
		"getBatching",
		"tryEdit",
		"getSummaryStat",
		"getCalendarDataBatching",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"tryEdit",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	// -------------------------------------------------------
	// ОБЩИЕ МЕТОДЫ
	// -------------------------------------------------------

	/**
	 * получаем сколько сотрудник отработал за конкретный месяц
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getByMonth():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$month   = $this->post(\Formatter::TYPE_INT, "month");
		$year    = $this->post(\Formatter::TYPE_INT, "year");

		// проверяем параметры на корректность
		if ($user_id < 1 || $month < 1 || $month > 12 || $year < 0) {
			throw new ParamException("incorrect params");
		}

		// пробуем получить информацию о пользователе
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем временную метку timestamp начала месяца, передавая дату вида 1.1.2012
		$day_start_at = strtotime("1.{$month}.{$year}");

		// получаем итемы рабочего времени за месяц
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getListByDayStartAt($user_id, $day_start_at);

		// фильтруем полученные итемы рабочего времени, оставляя только за выбранную дату
		$worked_hours_obj_list = $this->_filteredWorkedHoursObjListForSelectDate($worked_hours_obj_list, $month, $year);

		// получаем данные значений рабочего времени за месяц
		$value_list = $this->_getValueListForMonth($worked_hours_obj_list);

		return $this->ok([
			"month_stat" => (object) $this->_getFormattedAvgAndValueList($value_list, Type_User_Card_WorkedHours::WORKED_HOURS_FOR_DAY_STANDARD),
		]);
	}

	/**
	 * Фильтруем полученные итемы рабочего времени, оставляя только за выбранный дату
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $month
	 * @param int                                  $year
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	protected function _filteredWorkedHoursObjListForSelectDate(array $worked_hours_obj_list, int $month, int $year):array {

		// получаем временную метку когда выбранный месяц выбранного года заканчивается
		$date         = new \DateTime("{$year}-{$month}");
		$month_end_at = $date->modify("last day of")->setTime(23, 59, 59)->format("U");

		$filtered_worked_hours_obj_list = [];
		foreach ($worked_hours_obj_list as $obj) {

			// при первом же встреченного итеме, чье время превышает время когда месяц заканчивается - останавливаем перебор
			if ($obj->day_start_at > $month_end_at) {
				break;
			}

			// собираем в массив итемы выбранных месяца и года
			$filtered_worked_hours_obj_list[] = $obj;
		}

		return $filtered_worked_hours_obj_list;
	}

	/**
	 * Получаем данные значений рабочего времени за месяц
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 *
	 * @return array<object>
	 */
	protected function _getValueListForMonth(array $worked_hours_obj_list):array {

		$value_list = [];

		foreach ($worked_hours_obj_list as $obj) {

			// собираем значение и дату каждой полученной оценки
			$value_list[] = (object) [
				"date"  => (int) $obj->day_start_at,
				"value" => (float) $obj->float_value,
			];
		}

		return $value_list;
	}

	/**
	 * получаем сколько сотрудник отработал за конкретный год
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getByYear():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$year    = $this->post(\Formatter::TYPE_INT, "year");

		// проверяем параметры на корректность
		if ($user_id < 1 || $year < 0) {
			throw new ParamException("incorrect params");
		}

		// пробуем получить информацию о пользователе
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем временную метку timestamp начала год, передавая дату вида 1.1.2012
		$day_start_at = strtotime("1.1.{$year}");

		// получаем итемы рабочего времени за год
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getListByDayStartAt($user_id, $day_start_at, 500);

		// фильтруем полученные итемы рабочего времени, оставляя только за выбранный год
		$worked_hours_obj_list = $this->_filteredWorkedHoursObjListForSelectYear($worked_hours_obj_list, $year);

		// получаем данные значений рабочего времени за год
		$value_list = $this->_getValueListForYear($worked_hours_obj_list, $year);

		return $this->ok([
			"year_stat" => (object) $this->_getFormattedAvgAndValueList($value_list, Type_User_Card_WorkedHours::WORKED_HOURS_FOR_WEEK_STANDARD),
		]);
	}

	/**
	 * Получаем данные значений рабочего времени за год
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $year
	 *
	 * @return array<array>
	 */
	protected function _getValueListForYear(array $worked_hours_obj_list, int $year):array {

		$week_value_list = [];

		foreach ($worked_hours_obj_list as $obj) {

			// получаем неделю выбранного дня
			$week_of_obj = date("W", $obj->day_start_at);

			// собираем значение для каждой недели года
			if (isset($week_value_list[$week_of_obj])) {
				$week_value_list[$week_of_obj] += $obj->float_value;
			} else {
				$week_value_list[$week_of_obj] = $obj->float_value;
			}
		}

		// получаем значение для каждой недели
		return $this->_getValueForWeek($week_value_list, $year);
	}

	/**
	 * получаем информацию о нескольких фиксациях отработанных часов
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getBatching():array {

		$user_id             = $this->post(\Formatter::TYPE_INT, "user_id");
		$worked_hour_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "worked_hours_id_list");

		// проверяем параметры на корректность
		if ($user_id < 1 || count($worked_hour_id_list) == 0) {
			throw new ParamException("incorrect params");
		}

		// пробуем получить информацию о пользователе
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// достаем из базы информацию о запрошенных фиксациях времени
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getList($worked_hour_id_list);

		// приводим к формату под клиентов
		$formatted_worked_hours_list = [];
		foreach ($worked_hours_obj_list as $obj) {
			$formatted_worked_hours_list[] = Apiv1_Format::workedHoursItem($obj);
		}

		return $this->ok([
			"worked_hours_list" => (array) $formatted_worked_hours_list,
		]);
	}

	/**
	 * вернет резюмированную информацию, сколько в среднем сотрудник отрабатывает в часах
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getSummaryStat():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// проверяем параметр на корректность
		if ($user_id < 1) {
			throw new ParamException("incorrect param");
		}

		// пробуем получить данные о пользователе
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id, false);

		// получаем запись из dynamic таблицы сотрудника, которая содержит среднее время зафиксированных рабочих часов
		$card_dynamic_obj = Type_User_Card_DynamicData::get($user_id);

		// получаем все итемы фиксации рабочего времени пользователя (лимит для записей = 100k)
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getListByDayStartAt($user_id, 0, 100 * 1000);

		// получаем текущий месяц и год
		$current_month = date("m");
		$current_year  = date("Y");

		// высчитываем все необходимые значения
		$values_data = $this->_getValuesDataForOutputOnSummaryStat($worked_hours_obj_list, $current_month, $current_year, $card_dynamic_obj, $user_info->created_at);

		// форматируем и возвращаем ответ
		return $this->_getFormatOutputForSummaryStat(
			$values_data["avg_for_last_weeks"],
			$values_data["avg_for_all_time"],
			$values_data["over_time_hours"],
			$current_year,
			$current_month,
			$values_data["year_value_list"],
			$values_data["month_value_list"],
			$user_info->created_at
		);
	}

	/**
	 * Получаем все необходимые значения для ответа
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $current_month
	 * @param int                                  $current_year
	 * @param Struct_Domain_Usercard_Dynamic       $dynamic_obj
	 * @param int                                  $join_company_at
	 *
	 * @return array
	 * @long - много вычислений добавлено
	 */
	protected function _getValuesDataForOutputOnSummaryStat(array $worked_hours_obj_list, int $current_month, int $current_year, Struct_Domain_Usercard_Dynamic $dynamic_obj, int $join_company_at):array {

		$value_for_last_weeks          = 0;
		$worked_hours_grouping_by_week = [];
		$month_value_list              = [];
		$year_value_list_by_week       = [];

		$current_time = time();

		$day_start_at = dayStart($current_time) - DAY7 * Type_User_Card_WorkedHours::LAST_WEEKS_COUNT_FOR_AVG_HOURS;

		// для значения рабочих часов и флага была ли сегодня фиксация
		$earliest_day_value = 0;
		$is_exist_today     = false;

		$worked_hours_obj_count = 0;

		// проходимся по каждому итему фиксации рабочего времени
		foreach ($worked_hours_obj_list as $obj) {

			// собираем рабочие часы за всё время, сгруппированный по неделям
			$worked_hours_grouping_by_week = self::_getWorkedHoursGroupingByWeeks($obj, $worked_hours_grouping_by_week);

			// получаем значения каждого дня для текущего месяца
			$month_value_list = $this->_getMonthValueList($month_value_list, $current_year, $current_month, $obj);

			// получаем значения для текущего года, сгруппированный по неделям
			$year_value_list_by_week = $this->_getYearValueListGroupingByWeek($year_value_list_by_week, $current_year, $obj);

			// если время итема за последние N недель, то складываем его значения с другими
			if ($obj->day_start_at >= $day_start_at) {

				// если фиксация появилась до того момента как мы вступили в компанию, то ее не нужно учитывать
				if ($obj->day_start_at < dayStart($join_company_at)) {
					continue;
				}

				// если фиксация в первый же день N недель назад
				if ($obj->day_start_at == $day_start_at) {
					$earliest_day_value = $obj->float_value;
				}

				// отмечаем что сегодня была фиксация
				if ($obj->day_start_at == dayStart($current_time)) {
					$is_exist_today = true;
				}

				$value_for_last_weeks += $obj->float_value;
				$worked_hours_obj_count++;
			}
		}

		// если сегодня была фиксация, то не учитываем первый же день N недель назад
		if ($is_exist_today) {
			$value_for_last_weeks -= $earliest_day_value;
		}

		// получаем переменную для формулы высчитывания среднего времени рабочих часов
		$variable         = Type_User_Card_WorkedHours::getVariableForAvgWorkedHours($worked_hours_obj_count, $current_time, $join_company_at);
		$avg_worked_hours = Type_User_Card_DynamicData::getAvgWorkedHours($dynamic_obj->data);

		return [
			"avg_for_last_weeks" => $value_for_last_weeks / $variable,
			"avg_for_all_time"   => round($avg_worked_hours, 1),
			"over_time_hours"    => Type_User_Card_WorkedHours::getOverTimeHours($worked_hours_grouping_by_week),
			"year_value_list"    => $this->_getValueForWeek($year_value_list_by_week, $current_year),
			"month_value_list"   => $month_value_list,
		];
	}

	/**
	 * получаем значение накопленных рабочих часов, сгруппированный по неделям
	 *
	 * @param Struct_Domain_Usercard_WorkedHours $obj
	 * @param array                              $worked_hours_grouping_by_week
	 *
	 * @return array
	 */
	protected static function _getWorkedHoursGroupingByWeeks(Struct_Domain_Usercard_WorkedHours $obj, array $worked_hours_grouping_by_week):array {

		// получаем неделю и год фиксации рабочих часов
		$worked_hours_week = date("W", $obj->day_start_at);
		$worked_hours_year = date("o", $obj->day_start_at);

		// складываем значения фиксации рабочего времени, каждый за свою неделю
		if (isset($worked_hours_grouping_by_week[$worked_hours_year][$worked_hours_week])) {
			$worked_hours_grouping_by_week[$worked_hours_year][$worked_hours_week] += $obj->float_value;
		} else {
			$worked_hours_grouping_by_week[$worked_hours_year][$worked_hours_week] = $obj->float_value;
		}

		return $worked_hours_grouping_by_week;
	}

	/**
	 * получаем список значение для текущего месяца
	 *
	 * @param array                              $month_value_list
	 * @param int                                $current_year
	 * @param int                                $current_month
	 * @param Struct_Domain_Usercard_WorkedHours $obj
	 *
	 * @return array
	 */
	protected function _getMonthValueList(array $month_value_list, int $current_year, int $current_month, Struct_Domain_Usercard_WorkedHours $obj):array {

		// если месяц итема не совпадает с номером текущего месяца или года
		if ($current_month != date("m", $obj->day_start_at) || $current_year != date("Y", $obj->day_start_at)) {
			return $month_value_list;
		}

		// добавляем в список
		$month_value_list[] = (object) [
			"date"  => (int) $obj->day_start_at,
			"value" => (float) round($obj->float_value, 1),
		];

		return $month_value_list;
	}

	/**
	 * получаем список значений года, сгруппированный по неделям
	 *
	 * @param array                              $year_value_list_by_week
	 * @param int                                $current_year
	 * @param Struct_Domain_Usercard_WorkedHours $obj
	 *
	 * @return array
	 */
	protected function _getYearValueListGroupingByWeek(array $year_value_list_by_week, int $current_year, Struct_Domain_Usercard_WorkedHours $obj):array {

		// если год итема не совпадает с номером текущего года
		if ($current_year != date("Y", $obj->day_start_at)) {
			return $year_value_list_by_week;
		}

		// получаем неделю выбранного дня
		$week_of_obj = date("W", $obj->day_start_at);

		// собираем значение для каждой недели года
		if (isset($year_value_list_by_week[$week_of_obj])) {
			$year_value_list_by_week[$week_of_obj] += $obj->float_value;
		} else {
			$year_value_list_by_week[$week_of_obj] = $obj->float_value;
		}

		return $year_value_list_by_week;
	}

	/**
	 * получаем отформатированные под клиентов данные для ответа
	 *
	 * @param float $avg_for_last_weeks
	 * @param float $avg_for_all_time
	 * @param float $over_time_hours
	 * @param int   $current_year
	 * @param int   $current_month
	 * @param array $year_value_list
	 * @param array $month_value_list
	 * @param int   $join_company_at
	 *
	 * @return array
	 */
	protected function _getFormatOutputForSummaryStat(float $avg_for_last_weeks, float $avg_for_all_time, float $over_time_hours,
									  int   $current_year, int $current_month, array $year_value_list, array $month_value_list,
									  int   $join_company_at):array {

		return $this->ok([
			"avg_for_last_weeks" => (float) round($avg_for_last_weeks, 1),
			"avg_for_all_time"   => (float) round($avg_for_all_time, 1),
			"over_time_hours"    => (int) round($over_time_hours),
			"year_stat"          => (object) [
				"current_year"      => (int) $current_year,
				"join_company_year" => (int) date("Y", $join_company_at),
				"line_avg_value"    => (int) Type_User_Card_WorkedHours::WORKED_HOURS_FOR_WEEK_STANDARD,
				"value_list"        => (array) $year_value_list,
			],
			"month_stat"         => (object) [
				"current_month"      => (int) $current_month,
				"join_company_month" => (int) date("m", $join_company_at),
				"line_avg_value"     => (int) Type_User_Card_WorkedHours::WORKED_HOURS_FOR_DAY_STANDARD,
				"value_list"         => (array) $month_value_list,
			],
		]);
	}

	/**
	 * отредактировать зафиксированное ранее затраченное время
	 *
	 * @return array
	 * @throws \blockException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException|\cs_RowIsEmpty
	 */
	public function tryEdit():array {

		$worked_hour_id = $this->post(\Formatter::TYPE_INT, "worked_hours_id");
		$worked_hours   = $this->post(\Formatter::TYPE_FLOAT, "worked_hours");

		// проверяем, что передали некорректные параметры
		$this->_throwIfIncorrectWorkedHours($worked_hours);
		$this->_throwIfIncorrectWorkedHoursId($worked_hour_id);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::WORKEDHOURS_EDIT);

		// получаем запись
		$worked_hours_obj = Type_User_Card_WorkedHours::getOne($worked_hour_id);

		// проверяем существование
		if ($worked_hours_obj->is_deleted == 1) {
			throw new ParamException(__METHOD__ . ": passed worked_hour_id not found");
		}

		// проверяем, что пользователь имеет доступ
		if ($worked_hours_obj->user_id != $this->user_id) {
			throw new ParamException(__METHOD__ . ": user have not access to this worked_hour_id");
		}

		// проверяем, что время для редактирования рабочих часов еще не истекло
		if (!Type_User_Card_WorkedHours::isTimeAllowToEdit($worked_hours_obj->created_at)) {
			return $this->error(917, "Timed out for edit worked_hours");
		}

		$new_value = round($worked_hours, 1);

		// если поменялось значение
		if ($worked_hours_obj->float_value != $new_value) {

			// изменяем время
			Type_User_Card_WorkedHours::editWorkedHours($worked_hour_id, $worked_hours);

			// обновляем total-значение и среднее значение рабочих часов в dynamic-данных
			Type_User_Card_WorkedHours::updateDynamicDataIfEdit($this->user_id, $new_value, $worked_hours_obj->float_value);
			Type_User_Card_WorkedHours::recountAvgWorkedHours($this->user_id);
		}

		// возвращаем оке
		return $this->ok([]);
	}

	/**
	 * выбрасываем paramException, если передали некорректное значение worked_hours
	 *
	 * @param float $worked_hours
	 *
	 * @throws paramException
	 */
	protected function _throwIfIncorrectWorkedHours(float $worked_hours):void {

		if ($worked_hours < 0 || $worked_hours > 48) {

			throw new ParamException(__METHOD__ . ": passed incorrect worked hours value");
		}
	}

	/**
	 * выбрасываем paramException, если передали некорректный параметр worked_hour_id
	 *
	 * @param int $worked_hour_id
	 *
	 * @throws paramException
	 */
	protected function _throwIfIncorrectWorkedHoursId(int $worked_hour_id):void {

		if ($worked_hour_id < 1) {

			throw new ParamException(__METHOD__ . ": passed incorrect worked_hour_id");
		}
	}

	/**
	 * получаем данные о рабочем времени за указанный промежуток лет для календаря пользователя
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getCalendarDataBatching():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$year_start = $this->post(\Formatter::TYPE_INT, "year_start", (int) date("Y"));
		$year_end   = $this->post(\Formatter::TYPE_INT, "year_end", (int) date("Y"));

		// проверяем параметры на корректность
		if ($user_id < 1 || $year_start < 0 || $year_end < 0) {
			throw new ParamException("incorrect params");
		}
		if ($year_start > $year_end) {
			throw new ParamException("year_end less year_start");
		}

		// пробуем получить информацию о пользователе
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем временную метку, когда началась первая неделя года
		$date = new \DateTime();
		$date->setISODate($year_start, 1);
		$year_start_at = strtotime($date->format("Y-m-d"));

		// получаем итемы рабочего времени с стартового дня (лимит для получения записей = 100k)
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getListByDayStartAt($user_id, $year_start_at, 100 * 1000);

		// фильтруем полученные итемы рабочего времени, обрубая последним днем конечного года
		$worked_hours_obj_list = $this->_filteredWorkedHoursObjListUntilYear($worked_hours_obj_list, $year_end);

		// группируем итемы рабочего времени по годам и неделям
		$grouped_worked_hours_obj_list = $this->_groupedWorkedHoursObjListForSelectYearAndWeek($worked_hours_obj_list, $year_start);

		// формируем ответ
		$output = $this->_makeOutputForGetCalendarDataBatching($grouped_worked_hours_obj_list);

		return $this->ok([
			"year_list" => (array) $output,
		]);
	}

	/**
	 * Группируем итемы рабочего времени по годам и неделям
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $year_start
	 *
	 * @return array
	 */
	protected function _groupedWorkedHoursObjListForSelectYearAndWeek(array $worked_hours_obj_list, int $year_start):array {

		$grouped_worked_hours_obj_list = [];
		foreach ($worked_hours_obj_list as $obj) {

			// получаем год итема
			$year_number = (int) date("o", $obj->day_start_at);
			$year_number = $year_number < $year_start ? $year_start : $year_number;

			$week_number = (int) date("W", $obj->day_start_at);

			// группируем по годам и неделям
			$grouped_worked_hours_obj_list[$year_number][$week_number][] = $obj;
		}

		return $grouped_worked_hours_obj_list;
	}

	/**
	 * Формируем ответ для метода getCalendarDataBatching
	 *
	 * @param array $grouped_worked_hours_obj_list
	 *
	 * @return array
	 */
	protected function _makeOutputForGetCalendarDataBatching(array $grouped_worked_hours_obj_list):array {

		$output = [];

		// собираем и форматируем ответ для отдачи фронтенду
		$i = 0;
		foreach ($grouped_worked_hours_obj_list as $year_number => $week_worked_hours_object_list) {

			$output[$i]["year"] = $year_number;

			foreach ($week_worked_hours_object_list as $week_number => $v) {
				$output[$i]["week_list"][] = Apiv1_Format::workedWeek($week_number, $year_number, $v);
			}

			$i++;
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
	 * @param int  $user_id
	 * @param bool $is_short_info
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main|\CompassApp\Domain\Member\Struct\Short
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _tryGetUserInfoAndThrowIfIncorrectUserId(int $user_id, bool $is_short_info = true):\CompassApp\Domain\Member\Struct\Main|\CompassApp\Domain\Member\Struct\Short {

		if ($user_id < 1) {
			throw new ParamException("incorrect param user_id");
		}

		if ($is_short_info) {

			$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
			if (!isset($user_info_list[$user_id])) {
				throw new ParamException("dont found user in company cache");
			}
			$user_info = $user_info_list[$user_id];
		} else {

			// получаем информацию о пользователе
			try {

				$user_info = Gateway_Bus_CompanyCache::getMember($user_id);
			} catch (\cs_RowIsEmpty) {

				throw new ParamException("dont found user in company cache");
			}
		}

		// если это бот
		if (Type_User_Main::isBot($user_info->npc_type)) {
			throw new ParamException("you can't do this action on bot-user");
		}

		return $user_info;
	}

	/**
	 * получаем приведенные к формату для клиентов данные
	 *
	 * @param array $value_list
	 * @param int   $line_avg_value
	 *
	 * @return array
	 */
	protected function _getFormattedAvgAndValueList(array $value_list, int $line_avg_value):array {

		return [
			"line_avg_value" => (int) $line_avg_value,
			"value_list"     => (array) $value_list,
		];
	}

	/**
	 * Фильтруем полученные итемы рабочего времени, оставляя только за выбранный год
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $year
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	protected function _filteredWorkedHoursObjListForSelectYear(array $worked_hours_obj_list, int $year):array {

		$filtered_worked_hours_obj_list = [];
		foreach ($worked_hours_obj_list as $obj) {

			// получаем год полученного итема
			$year_of_obj = (int) date("Y", $obj->day_start_at);

			// если полученный год не совпадает с выбранным, то пропускаем
			if ($year_of_obj != $year) {
				continue;
			}

			$filtered_worked_hours_obj_list[] = $obj;
		}

		return $filtered_worked_hours_obj_list;
	}

	/**
	 * Фильтруем полученные итемы рабочего времени, оставляя только до переданного года ($until_year)
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 * @param int                                  $until_year
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	protected function _filteredWorkedHoursObjListUntilYear(array $worked_hours_obj_list, int $until_year):array {

		$filtered_worked_hours_obj_list = [];
		foreach ($worked_hours_obj_list as $obj) {

			// получаем год полученного итема
			$year_of_obj = (int) date("Y", $obj->day_start_at);

			// если полученный год больше выбранного, то стопим перебор
			if ($year_of_obj > $until_year) {
				break;
			}

			$filtered_worked_hours_obj_list[] = $obj;
		}

		return $filtered_worked_hours_obj_list;
	}

	/**
	 * получаем значение для каждой недели
	 *
	 * @param array $week_value_list
	 * @param int   $year
	 *
	 * @return array
	 */
	protected function _getValueForWeek(array $week_value_list, int $year):array {

		// получаем значение за каждую неделю, дату начала и конца недели
		$value_list = [];
		foreach ($week_value_list as $week_number => $week_value) {

			$week_start_at = strtotime(sprintf("%4dW%02d", $year, $week_number));
			$value_list[]  = [
				"week_start_at" => (int) $week_start_at,
				"week_end_at"   => (int) $week_start_at + DAY7,
				"value"         => (float) round($week_value, 1),
			];
		}

		return $value_list;
	}
}