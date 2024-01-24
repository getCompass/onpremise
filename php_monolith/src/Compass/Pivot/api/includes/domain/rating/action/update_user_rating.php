<?php

namespace Compass\Pivot;

/**
 * класс обновляет каждую ночь статистику пользователя по всему приложению
 */
class Domain_Rating_Action_UpdateUserRating {

	protected const _LIMIT = 1000; // лимит кол-ва записей получаемых за раз

	/**
	 * запускаем
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @long
	 */
	public static function run():void {

		$screen_time_updated_user_list         = self::_updateScreenTime();
		$total_action_count_updated_user_list  = self::_updateTotalActionCount();
		$message_answer_time_updated_user_list = self::_updateAvgMessageAnswerTime();
		$updated_user_id_list                  = array_unique(array_merge(
			array_keys($screen_time_updated_user_list),
			array_keys($total_action_count_updated_user_list),
			array_keys($message_answer_time_updated_user_list)
		));

		// обновляем данные пользователей
		foreach ($updated_user_id_list as $user_id) {

			// получаем информацию по пользователю
			$user_row = Gateway_Db_PivotUser_UserList::getOne($user_id);

			// если нужно обновить среднее экранное время
			if (isset($screen_time_updated_user_list[$user_id])) {
				$user_row->extra = Type_User_Main::setAvgScreenTime($user_row->extra, $screen_time_updated_user_list[$user_id]);
			}

			// если нужно обновить количество действий
			if (isset($total_action_count_updated_user_list[$user_id])) {

				$total_action_count = Type_User_Main::getTotalActionCount($user_row->extra) + $total_action_count_updated_user_list[$user_id];
				$user_row->extra    = Type_User_Main::setTotalActionCount($user_row->extra, $total_action_count);
			}

			// если нужно среднее время ответа на сообщения
			if (isset($message_answer_time_updated_user_list[$user_id])) {
				$user_row->extra = Type_User_Main::setAvgMessageAnswerTime($user_row->extra, $message_answer_time_updated_user_list[$user_id]);
			}

			// обновляем в базе
			Gateway_Db_PivotUser_UserList::set($user_id, [
				"updated_at" => time(),
				"extra"      => $user_row->extra,
			]);

			Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
			Type_Phphooker_Main::onUserInfoChange($user_id);
		}
	}

	/**
	 * Обновляем экранное время
	 *
	 * @return array
	 * @throws \cs_RowIsEmpty
	 * @long
	 */
	protected static function _updateScreenTime():array {

		$start_at = dayStart() - DAY1;
		$end_at   = dayStart() - 1;

		// получаем время начала каждого дня за последние 7 дней (не считая текущий)
		$user_local_date_list = [];
		for ($i = 1; $i <= 7; $i++) {
			$user_local_date_list[] = date(DATE_FORMAT_SMALL, dayStart() - (DAY1 * $i));
		}

		$updated_user_id_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_user_id) {

			$offset = 0;
			while (true) {

				// получаем всех пользователей, у которых была активность за предыдущий день
				$user_id_list = Gateway_Db_PivotRating_ScreenTimeRawList::getUserIdListBetweenCreatedAt($shard_user_id, $start_at, $end_at, self::_LIMIT, $offset);
				$offset       += self::_LIMIT;

				// проходим по каждому пользователю
				foreach ($user_id_list as $user_id) {

					$row_list = Gateway_Db_PivotRating_ScreenTimeUserDayList::getByUserIdAndUserLocalDateList($user_id, $user_local_date_list);
					if (count($row_list) < 1) {
						continue;
					}

					// обновляем среднее экранное время каждого пользователя
					$user_row        = Gateway_Db_PivotUser_UserList::getOne($user_id);
					$avg_screen_time = self::_calculateAvgScreenTime($user_row->created_at, $row_list);
					if ($avg_screen_time < 1) {
						continue;
					}
					$updated_user_id_list[$user_id] = $avg_screen_time;
				}

				if (count($user_id_list) < self::_LIMIT) {
					break;
				}
			}
		}

		return $updated_user_id_list;
	}

	/**
	 * Считаем среднее экранное время пользователя (суммируем полученное время за 7 дней и делим на 5 рабочих дней)
	 *
	 * @param int   $user_created_at
	 * @param array $row_list
	 *
	 * @return int
	 */
	protected static function _calculateAvgScreenTime(int $user_created_at, array $row_list):int {

		// считаем сколько дней пользователь зарегистрирован в приложении
		$user_days_count = floor((time() - $user_created_at) / DAY1);
		if ($user_days_count < 1) {
			return 0;
		}

		$total_screen_time = 0;
		foreach ($row_list as $row) {

			foreach ($row->screen_time_list as $screen_time) {
				$total_screen_time += $screen_time;
			}
		}

		// если зареган меньше 5 дней назад, то делим на количество полных дней в приложении
		return $user_days_count < 5 ? floor($total_screen_time / $user_days_count) : floor($total_screen_time / 5);
	}

	/**
	 * Обновляем общее количество действий пользователя время
	 *
	 * @return array
	 * @long
	 */
	protected static function _updateTotalActionCount():array {

		$day_start_at = dayStart() - DAY1;
		$end_at       = dayStart() - 1;

		$updated_user_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_user_id) {

			$offset = 0;
			while (true) {

				// получаем всех пользователей, у которых была активность за предыдущий день
				$user_id_list = Gateway_Db_PivotRating_ActionRawList::getUserIdListBetweenActionAt($shard_user_id, $day_start_at, $end_at, self::_LIMIT, $offset);
				$offset       += self::_LIMIT;

				// проходим по каждому пользователю
				foreach ($user_id_list as $user_id) {

					try {
						$row = Gateway_Db_PivotRating_ActionUserDayList::getOne($user_id, $day_start_at);
					} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
						continue;
					}

					// обновляем общее количество действий пользователя
					$inc_action_count            = Domain_Rating_Entity_Action::getTotalActionCount($row->action_list);
					$updated_user_list[$user_id] = $inc_action_count;
				}

				if (count($user_id_list) < self::_LIMIT) {
					break;
				}
			}
		}

		return $updated_user_list;
	}

	/**
	 * Обновляем среднее время ответа пользователей на сообщения
	 *
	 * @return array
	 * @long
	 */
	protected static function _updateAvgMessageAnswerTime():array {

		$day_start_at                   = dayStart() - DAY1;
		$end_at                         = dayStart() - 1;
		$days_count_for_avg_answer_time = 10; // количество дней, за которые считаем статистику среднего времени ответа

		$updated_user_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_user_id) {

			$offset = 0;
			while (true) {

				// получаем всех пользователей, у которых были ответы за предыдущий день
				$user_id_list = Gateway_Db_PivotRating_MessageAnswerTimeRawList::getUserIdListBetweenAnswerAt($shard_user_id, $day_start_at, $end_at, self::_LIMIT, $offset);
				$offset       += self::_LIMIT;

				// проходим по каждому пользователю
				foreach ($user_id_list as $user_id) {

					// обновляем среднее время ответа на сообщения
					$list                        = Gateway_Db_PivotRating_MessageAnswerTimeUserDayList::getListByUserId($user_id, $days_count_for_avg_answer_time);
					$updated_user_list[$user_id] = self::_calculateAvgAnswerMessageTime($list);
				}

				if (count($user_id_list) < self::_LIMIT) {
					break;
				}
			}
		}

		return $updated_user_list;
	}

	/**
	 * Считаем среднее время ответа пользователя на сообщения
	 *
	 * @param Struct_Db_PivotRating_MessageAnswerTimeUserDay[] $user_day_answer_list
	 *
	 * @return int
	 * @long
	 */
	protected static function _calculateAvgAnswerMessageTime(array $user_day_answer_list):int {

		$total_avg_answer_time = 0;
		foreach ($user_day_answer_list as $item) {

			// сортируем массив по убыванию
			$answer_time_list = $item->answer_time_list;
			usort($answer_time_list, function(array $a, array $b) {

				return $b["answer_time"] <=> $a["answer_time"];
			});

			// если у пользователя 10 или больше ответов - убираем 15% самых долгих
			if ($answer_time_list >= 10) {

				// убираем 15% самых долгих и округляем в меньшую сторону
				$need_cut_answer_count = floor(count($answer_time_list) * 0.15);
				if ($need_cut_answer_count > 0) {
					$answer_time_list = array_slice($answer_time_list, $need_cut_answer_count);
				}
			}

			// считаем среднее время ответа за один день и добавляем к общему
			$total_user_day_answer_time = 0;
			if (count($answer_time_list) > 0) {

				foreach ($answer_time_list as $answer_time_item) {
					$total_user_day_answer_time += $answer_time_item["answer_time"];
				}
				$total_avg_answer_time += $total_user_day_answer_time / count($answer_time_list);
			}
		}

		// считаем общее среднее время
		return floor($total_avg_answer_time / count($user_day_answer_list));
	}
}