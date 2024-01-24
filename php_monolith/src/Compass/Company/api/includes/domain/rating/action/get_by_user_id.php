<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для получения рейтинга по пользователю
 */
class Domain_Rating_Action_GetByUserId {

	/**
	 * получени рейтинга по id пользователя
	 *
	 * @return Struct_Bus_Rating_User[]
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $year, int $start_week, int $end_week, \CompassApp\Domain\Member\Struct\Main $member_info):array {

		// подготавливаем параметры для запроса
		[$from_date_at_arr, $to_date_at_arr, $is_from_cache_arr, $week_arr] = self::_prepareParams($start_week, $end_week, $year);

		if (isBackendTest()) {
			Type_System_Admin::log("getStatByUserId", [$from_date_at_arr, $to_date_at_arr, $is_from_cache_arr, $week_arr]);
		}

		// получаем данные о рейтинге пользователя из go_rating
		$user_rating_list = Gateway_Bus_Company_Rating::getByUserId($user_id, $year, $week_arr, $from_date_at_arr, $to_date_at_arr, $is_from_cache_arr);

		return self::_doValidateUserRatingList($user_rating_list, $member_info);
	}

	/**
	 * подготавливаем параметры для запроса
	 *
	 * @return array[]
	 */
	protected static function _prepareParams(int $start_week, int $end_week, int $year):array {

		$from_date_at_arr  = [];
		$to_date_at_arr    = [];
		$is_from_cache_arr = [];
		$week_arr          = [];

		// проверяем, какое максимальное количество недель в году и проверяем, что не вышли за границы
		$last_week_date = new \DateTime("December 28th, $year");
		$last_year_week = (int) $last_week_date->format("W");
		$start_week     = min($start_week, $last_year_week);
		$end_week       = min($end_week, $last_year_week);

		// составляем список недель для разбивки рейтинга
		for ($week = $start_week; $week <= $end_week; $week++) {

			// получаем timestamp начала недели
			$from_date_at = Type_Rating_Helper::getFromDateAt($year, $week);

			// получаем время, до которого нужно получить рейтинг
			$to_date_at = Type_Rating_Helper::getToDateAt($year, $week, $last_year_week);
			$to_date_at = max($to_date_at, $from_date_at);

			// если текущее время находится в диапазоне запрашиваемыех данных, то в кэш не лезем и получаем актуальные данные из базы
			$to_date_day_end_at = strtotime("this day 23:59", $to_date_at);
			$is_from_cache      = time() >= dayStart($from_date_at) && time() <= $to_date_day_end_at ? 0 : 1;

			// добавляем данные в массивы
			array_push($from_date_at_arr, $from_date_at);
			array_push($to_date_at_arr, $to_date_at);
			array_push($is_from_cache_arr, $is_from_cache);
			array_push($week_arr, $week);
		}

		return [
			$from_date_at_arr, $to_date_at_arr, $is_from_cache_arr, $week_arr,
		];
	}

	/**
	 * Метод для валидации пользовательского рейтинга
	 */
	protected static function _doValidateUserRatingList(array $user_rating_list, \CompassApp\Domain\Member\Struct\Main $member_info):array {

		// сортируем по неделям по возрастанию
		usort($user_rating_list, function(Struct_Bus_Rating_User $first, Struct_Bus_Rating_User $second) {

			return ($first->week > $second->week) ? +1 : -1;
		});

		return self::_checkByUserJoinAtAndUserActivity($user_rating_list, $member_info);
	}

	/**
	 * Метод проверка даты вступления пользователя в компанию
	 */
	protected static function _checkByUserJoinAtAndUserActivity(array $user_rating_list, \CompassApp\Domain\Member\Struct\Main $member_info):array {

		// получаем неделю и год даты с которой пользователь вступил в компанию
		$join_week_number = date("W", $member_info->created_at);
		$join_year        = date("o", $member_info->created_at);

		foreach ($user_rating_list as $key => $value) {

			// если пользователь совершал действия до вступления в компанию (вступил, уволился, снова вступил) мы стопаем
			if ($value->general_count != 0) {

				break;
			}

			// если год меньше года вступления в компанию
			if ($value->year < $join_year) {

				unset($user_rating_list[$key]);
				continue;
			}

			// если год тот же что и год когда пользователь вступил, но неделя более ранняя
			if ($value->year == $join_year && $value->week < $join_week_number) {

				unset($user_rating_list[$key]);
			}
		}

		return $user_rating_list;
	}
}