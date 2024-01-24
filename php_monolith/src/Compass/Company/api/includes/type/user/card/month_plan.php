<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с планами на месяц карточки пользователя
 */
class Type_User_Card_MonthPlan {

	public const MONTH_PLAN_RESPECT_TYPE      = 1; // тип респекта плана на месяц
	public const MONTH_PLAN_EXACTINGNESS_TYPE = 2; // тип требовательности плана на месяц

	// список доступных типов плана на месяц
	public const ALLOW_MONTH_PLAN_TYPE_LIST = [
		self::MONTH_PLAN_RESPECT_TYPE,
		self::MONTH_PLAN_EXACTINGNESS_TYPE,
	];

	/**
	 * Получаем лог плана на месяц пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $row_id):Struct_Domain_Usercard_MonthPlan {

		return Gateway_Db_CompanyMember_UsercardMonthPlanList::get($row_id);
	}

	/**
	 * Получаем несколько записей-логов плана на месяц пользователя
	 *
	 * @return Struct_Domain_Usercard_MonthPlan[]
	 */
	public static function getAll(int $user_id, int $type, int $month_start_at, int $limit):array {

		return Gateway_Db_CompanyMember_UsercardMonthPlanList::getAll($user_id, $type, $month_start_at, $limit);
	}

	/**
	 * получаем записи всех типов
	 *
	 * @return Struct_Domain_Usercard_MonthPlan[]
	 */
	public static function getAllType(int $user_id, int $month_start_at):array {

		return Gateway_Db_CompanyMember_UsercardMonthPlanList::getAllType($user_id, $month_start_at);
	}

	/**
	 * Добавляем/изменяем план на текущий месяц пользователя карточки
	 */
	public static function insertOrUpdate(int $user_id, int $type, int $month_start_at, int $plan_value, int $current_value = null):void {

		Gateway_Db_CompanyMember_UsercardMonthPlanList::insertOrUpdate($user_id, $type, $month_start_at, $plan_value, $current_value);
	}

	/**
	 * обновляем план на месяц
	 */
	public static function set(int $user_id, int $type, int $month_start_at, array $set):void {

		Gateway_Db_CompanyMember_UsercardMonthPlanList::set($user_id, $type, $month_start_at, $set);
	}

	/**
	 * удаляем запись плана на месяц
	 *
	 * @param int $user_id
	 * @param int $type
	 * @param int $month_start_at
	 *
	 * @throws ParseFatalException
	 */
	public static function delete(int $user_id, int $type, int $month_start_at):void {

		if (!ServerProvider::isTest()) {
			throw new ParseFatalException("use only on test-server");
		}

		Gateway_Db_CompanyMember_UsercardMonthPlanList::delete($user_id, $type, $month_start_at);
	}

	/**
	 * Инкрементим значение плана на месяц
	 *
	 * @throws \returnException
	 */
	public static function incUserValue(int $user_id, int $type, int $month_start_at, int $inc_value = 1):void {

		Gateway_Db_CompanyMember_UsercardMonthPlanList::incUserValue($user_id, $type, $month_start_at, $inc_value);
	}

	/**
	 * Декрементим значение плана на месяц
	 *
	 * @throws \returnException
	 */
	public static function decUserValue(int $user_id, int $type, int $month_start_at, int $dec_value = 1):void {

		Gateway_Db_CompanyMember_UsercardMonthPlanList::decUserValue($user_id, $type, $month_start_at, $dec_value);
	}

	/**
	 * получаем планы на месяц для выбранного года
	 *
	 * @param int $user_id
	 * @param int $year
	 * @param int $type
	 *
	 * @return array
	 */
	public static function getByYear(int $user_id, int $year, int $type):array {

		// получаем время когда заканчивается выбранный год
		$year_last_day_at = strtotime("{$year}-12-31 12:00");
		$month_start_at   = monthStart($year_last_day_at);

		// достаем записи логов планов на месяц для выбранного года
		$month_plan_list = self::getAll($user_id, $type, $month_start_at, 13);
		$months_count    = count($month_plan_list);

		// фильтруем те, что не принадлежат выбранному году, и возвращаем ответ
		[$filtered_month_plan_list, $months_count] = self::_filteredForNeedYear($month_plan_list, $year, $months_count);

		return [$filtered_month_plan_list, $months_count];
	}

	/**
	 * Создать планы на текущий месяц если они не созданы
	 *
	 * @param int   $type
	 * @param array $month_plan_list
	 * @param int   $user_id
	 *
	 * @return bool
	 */
	public static function createCurrentMonthPlanIfNotExist(int $type, array $month_plan_list, int $user_id):bool {

		// проверяем существуют ли планы на текущий месяц
		$is_plan_exist = self::_checkExistCurrentMonthPlan($month_plan_list);
		if (!$is_plan_exist) {

			// попробуем получить предыдущий план
			$previous_plan = self::_tryGetPreviousMonthPlan($user_id, $type, $month_plan_list);

			// если нашли план на предыдущий месяц
			if ($previous_plan !== false) {

				// создаем новый с тем же планом на месяц, что и на предыдущий месяц
				self::insertOrUpdate($user_id, $type, monthStart(), $previous_plan->plan_value);
				return true;
			}

			// создаем с нулями так как нет других планов
			self::insertOrUpdate($user_id, $type, monthStart(), 0);
			return true;
		}

		return false;
	}

	/**
	 * Проверяем есть ли среди переданного списка планов план на текущий месяц
	 *
	 * @param array $month_plan_list
	 *
	 * @return bool
	 */
	protected static function _checkExistCurrentMonthPlan(array $month_plan_list):bool {

		$month_start_at = monthStart();
		foreach ($month_plan_list as $month_plan) {

			if ($month_plan->created_at == $month_start_at) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Получить план на предыдущий месяц
	 *
	 * @param int   $user_id
	 * @param int   $type
	 * @param array $month_plan_list
	 *
	 * @return Struct_Domain_Usercard_MonthPlan|false
	 */
	protected static function _tryGetPreviousMonthPlan(int $user_id, int $type, array $month_plan_list):Struct_Domain_Usercard_MonthPlan|false {

		// проверим, что если сейчас начало года, то нужно проверять планы за предыдущий год.
		if (date("n") == 1) {

			$year = (int) date("Y") - 1;
			[$month_plan_list] = Type_User_Card_MonthPlan::getByYear($user_id, $year, $type);
		}

		// получаем время плана предыдущего месяца
		$previous_month_started_at = monthStart(monthStart() - DAY7);

		// проходим по всем планам и возвращаем план за предыдущий месяц
		foreach ($month_plan_list as $plan_obj) {

			if ($plan_obj->created_at === $previous_month_started_at) {
				return $plan_obj;
			}
		}

		// план не найден
		return false;
	}

	/**
	 * фильтруем планы на месяц для выбранного года
	 *
	 * @param Struct_Domain_Usercard_MonthPlan[] $month_plan_data_list
	 */
	protected static function _filteredForNeedYear(array $month_plan_data_list, int $year, int $months_count):array {

		// получаем скорректированное время
		$current_month_started_at      = self::getCorrectedMonthStartAt();
		$filtered_month_plan_data_list = [];

		foreach ($month_plan_data_list as $v) {

			// если год времени, за которым закреплена сущность, не равен выбранному году
			if (date("Y", $v->created_at) != $year) {
				continue;
			}

			// если попался месяц, который больше текущего месяца
			if ($current_month_started_at < $v->created_at) {

				$months_count--;
				continue;
			}

			$filtered_month_plan_data_list[] = $v;
		}

		return [$filtered_month_plan_data_list, $months_count];
	}

	/**
	 * !!! Корректируем время для корректного получения планов.
	 * !!! Так как до апреля (включительно) создание плана происходило по гринвичу
	 *
	 * @param int|null $time
	 *
	 * @return int
	 */
	public static function getCorrectedMonthStartAt(int $time = null):int {

		$current_month_started_at = monthStart($time);

		// !!! проверяем начало какого месяца запрашивается. Чтоб не пропускался план в течении апреля,
		// так как это последний месяц который создан по гринвичу
		// 1648760400 - Mar 31 2022 21:00:00 GMT+00 || Apr 01 2022 00:00:00 GMT+03 (MSK)
		if ($current_month_started_at <= 1648760400) {

			// отходим от стыка месяцев, чтоб не получить предыдущий месяц
			$current_month_started_at = monthStartOnGreenwich($current_month_started_at + DAY1);
		}

		return $current_month_started_at;
	}

	/**
	 * Получаем записи значений плана на месяц для всех типов сущностей
	 */
	public static function getAllTypeByMonthAt(int $user_id, int $month_start_at):array {

		return self::getAllType($user_id, $month_start_at);
	}

	/**
	 * Извлекаем данные планов на месяц по типу
	 *
	 * @param Struct_Domain_Usercard_MonthPlan[] $month_plan_list
	 */
	public static function extractPlanByType(array $month_plan_list, int $type):array {

		foreach ($month_plan_list as $v) {

			if ($v->type == $type) {

				return [
					"plan_value" => $v->plan_value,
					"user_value" => $v->user_value,
				];
			}
		}

		return [];
	}
}
