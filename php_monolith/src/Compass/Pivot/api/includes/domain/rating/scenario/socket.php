<?php

namespace Compass\Pivot;

/**
 * Сценарии сокет-рейтинга по приложению
 */
class Domain_Rating_Scenario_Socket {

	/**
	 * Сохраняем экранное время
	 *
	 * @param array $screen_time_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @long
	 */
	public static function saveScreenTime(array $screen_time_list):void {

		if (count($screen_time_list) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		foreach ($screen_time_list as $item) {

			$space_id              = $item["space_id"];
			$user_screen_time_list = $item["user_screen_time_list"];

			// инкрементим пользователям экранное время за день
			[$space_screen_time_user_list, $insert_array] = self::_updateScreenTimeUserDayList($space_id, $user_screen_time_list);

			// обновляем экранное время по пространству
			foreach ($space_screen_time_user_list as $space_id => $day_list) {

				foreach ($day_list as $local_date => $user_list) {

					try {

						$row                    = Gateway_Db_PivotRating_ScreenTimeSpaceDayList::getOne($space_id, $local_date);
						$space_screen_time_list = $row->screen_time_list;
					} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

						$space_screen_time_list = [];
						Gateway_Db_PivotRating_ScreenTimeSpaceDayList::insert($space_id, $local_date, $space_screen_time_list);
					}

					foreach ($user_list as $user_id => $user_item) {

						$local_time  = $user_item["local_time"];
						$screen_time = $user_item["screen_time"];
						if (!isset($space_screen_time_list[$user_id])) {
							$space_screen_time_list[$user_id] = [];
						}
						$space_screen_time_list[$user_id][$local_time] = $screen_time;
					}

					Gateway_Db_PivotRating_ScreenTimeSpaceDayList::update($space_id, $local_date, [
						"screen_time_list" => $space_screen_time_list,
					]);
				}
			}

			Gateway_Db_PivotRating_ScreenTimeRawList::insertArray($insert_array);
		}
	}

	/**
	 * Обновляем статистику по пользователям за день
	 *
	 * @param int   $space_id
	 * @param array $user_screen_time_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @long
	 */
	protected static function _updateScreenTimeUserDayList(int $space_id, array $user_screen_time_list):array {

		// инкрементим пользователям экранное время
		$space_screen_time_list = [];
		$insert_array           = [];
		$update_array           = [];
		$updated_user_id_list   = [];
		foreach ($user_screen_time_list as $item) {

			$user_id          = $item["user_id"];
			$screen_time      = $item["screen_time"];
			$local_online_at  = $item["local_online_at"];
			$tt               = explode(" ", $local_online_at);
			$user_local_date  = $tt[0];
			$user_local_hours = $tt[1];

			// если уже добавили время пользователю
			if (in_array($user_id, $updated_user_id_list) || Gateway_Db_PivotRating_ScreenTimeRawList::isExistByUserIdAndUserLocalTime($user_id, $local_online_at)) {
				continue;
			}

			if (!isset($update_array[$user_id])) {
				$update_array[$user_id] = [];
			}
			if (!isset($update_array[$user_id][$user_local_date])) {
				$update_array[$user_id][$user_local_date] = [];
			}
			$update_array[$user_id][$user_local_date][$user_local_hours] = $screen_time;

			if (!isset($space_screen_time_list[$space_id])) {
				$space_screen_time_list[$space_id] = [];
			}
			if (!isset($space_screen_time_list[$space_id][$user_local_date])) {
				$space_screen_time_list[$space_id][$user_local_date] = [];
			}

			$space_screen_time_list[$space_id][$user_local_date][$user_id] = [
				"local_time"  => $user_local_hours,
				"screen_time" => $screen_time,
			];

			$insert_array[]         = Gateway_Db_PivotRating_ScreenTimeRawList::makeInsertRow($user_id, $space_id, $local_online_at, $screen_time);
			$updated_user_id_list[] = $user_id;
		}

		// проходим по пользователям
		foreach ($update_array as $user_id => $local_date_screen_time_list) {

			// проходим по дням в которые был онлайн
			foreach ($local_date_screen_time_list as $user_local_date => $screen_time_list) {

				Gateway_Db_PivotRating_Main::beginTransaction($user_id);

				// получаем запись за конкретный день
				$row                      = self::_getScreenTimeUserDayRowForUpdate($user_id, $user_local_date);
				$updated_screen_time_list = array_merge($row->screen_time_list, $screen_time_list);
				ksort($updated_screen_time_list);

				// обновляем запись
				Gateway_Db_PivotRating_ScreenTimeUserDayList::update($user_id, $user_local_date, [
					"screen_time_list" => $updated_screen_time_list,
				]);

				Gateway_Db_PivotRating_Main::commitTransaction($user_id);
			}
		}

		return [$space_screen_time_list, $insert_array];
	}

	/**
	 * Получаем запись пользователя за день на обновление
	 *
	 * @param int    $user_id
	 * @param string $user_local_date
	 *
	 * @return Struct_Db_PivotRating_ScreenTimeUserDay
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _getScreenTimeUserDayRowForUpdate(int $user_id, string $user_local_date):Struct_Db_PivotRating_ScreenTimeUserDay {

		try {
			$row = Gateway_Db_PivotRating_ScreenTimeUserDayList::getForUpdate($user_id, $user_local_date);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotRating_ScreenTimeUserDayList::insert($user_id, $user_local_date, []);
			$row = Gateway_Db_PivotRating_ScreenTimeUserDayList::getForUpdate($user_id, $user_local_date);
		}

		return $row;
	}

	/**
	 * Сохраняем количество действий польователей
	 *
	 * @param array $user_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @long
	 */
	public static function saveUserActionList(array $user_list):void {

		if (count($user_list) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		// пробегаем по пришедшим данным
		$space_action_count_list = [];
		$insert_array            = [];
		foreach ($user_list as $item) {

			$user_id      = $item["user_id"];
			$space_id     = $item["space_id"];
			$action_at    = $item["action_at"];
			$action_list  = $item["action_list"];
			$day_start_at = dayStart($action_at);

			Gateway_Db_PivotRating_Main::beginTransaction($user_id);

			// если уже добавили время пользователю
			if (Gateway_Db_PivotRating_ActionRawList::isExistByUserIdAndSpaceIdAndActionAt($user_id, $space_id, $action_at)) {

				Gateway_Db_PivotRating_Main::rollback($user_id);
				continue;
			}

			$row = self::_getActionUserDayRowForUpdate($user_id, $day_start_at);
			Gateway_Db_PivotRating_ActionUserDayList::update($user_id, $day_start_at, [
				"action_list" => Domain_Rating_Entity_Action::incActionList($row->action_list, $action_list),
			]);

			Gateway_Db_PivotRating_Main::commitTransaction($user_id);

			if (!isset($space_action_count_list[$day_start_at][$space_id])) {
				$space_action_count_list[$day_start_at][$space_id] = Domain_Rating_Entity_Action::makeDefaultActionList();
			}
			$space_action_count_list[$day_start_at][$space_id] =
				Domain_Rating_Entity_Action::incActionList($space_action_count_list[$day_start_at][$space_id], $action_list);
			$insert_array[]                                    =
				Gateway_Db_PivotRating_ActionRawList::makeInsertRow($user_id, $space_id, $action_at, $action_list);
		}

		foreach ($space_action_count_list as $day_start_at => $space_list) {

			foreach ($space_list as $space_id => $inc_action_list) {

				try {
					$row         = Gateway_Db_PivotRating_ActionSpaceDayList::getOne($space_id, $day_start_at);
					$action_list = $row->action_list;
				} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

					$action_list = Domain_Rating_Entity_Action::makeDefaultActionList();
					Gateway_Db_PivotRating_ActionSpaceDayList::insert($space_id, $day_start_at, $action_list);
				}

				Gateway_Db_PivotRating_ActionSpaceDayList::update($space_id, $day_start_at, [
					"action_list" => Domain_Rating_Entity_Action::incActionList($action_list, $inc_action_list),
				]);
			}
		}

		Gateway_Db_PivotRating_ActionRawList::insertArray($insert_array);
	}

	/**
	 * Получаем запись с количеством действий пользователя за день на обновление
	 *
	 * @param int $user_id
	 * @param int $day_start_at
	 *
	 * @return Struct_Db_PivotRating_ActionUserDay
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _getActionUserDayRowForUpdate(int $user_id, int $day_start_at):Struct_Db_PivotRating_ActionUserDay {

		try {
			$row = Gateway_Db_PivotRating_ActionUserDayList::getForUpdate($user_id, $day_start_at);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotRating_ActionUserDayList::insert($user_id, $day_start_at, Domain_Rating_Entity_Action::makeDefaultActionList());
			$row = Gateway_Db_PivotRating_ActionUserDayList::getForUpdate($user_id, $day_start_at);
		}

		return $row;
	}

	/**
	 * Сохраняем время ответа пользователей на сообщения
	 *
	 * @param array $conversation_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @long
	 */
	public static function saveUserAnswerTime(array $conversation_list):void {

		if (count($conversation_list) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		// группируем по пользователям и пространствам
		[$answer_list_grouped_by_user_id, $answer_list_grouped_by_space_id] = self::_groupAnswerListByUsersAndSpaces($conversation_list);

		// обновляем пользователей
		self::_updateUserAnswerTimeDayList($answer_list_grouped_by_user_id);

		// обновляем пространства
		self::_updateSpaceAnswerTimeDayList($answer_list_grouped_by_space_id);

		// вставляем сырую статистику в базу
		$insert_array = [];
		foreach ($conversation_list as $item) {

			foreach ($item["user_answer_time_list"] as $user_answer_time_item) {

				// в answer_at пишем время начала 15-ти минутки
				$insert_array[] = Gateway_Db_PivotRating_MessageAnswerTimeRawList::makeInsertRow($user_answer_time_item["user_id"],
					$item["min15_start_at"], $item["conversation_key"], $user_answer_time_item["answer_time"], $item["space_id"]);
			}
		}
		Gateway_Db_PivotRating_MessageAnswerTimeRawList::insertArray($insert_array);
	}

	/**
	 * Группируем время ответа по пользователям и по пространствам
	 *
	 * @param array $conversation_list
	 *
	 * @return array[]
	 * @long
	 */
	protected static function _groupAnswerListByUsersAndSpaces(array $conversation_list):array {

		// пробегаем по пришедшим данным
		$answer_list_grouped_by_user_id  = [];
		$answer_list_grouped_by_space_id = [];
		foreach ($conversation_list as $item) {

			$min15_start_at        = $item["min15_start_at"];
			$space_id              = $item["space_id"];
			$user_answer_time_list = $item["user_answer_time_list"];
			if (!isset($answer_list_grouped_by_space_id[$space_id])) {

				$answer_list_grouped_by_space_id[$space_id] = [
					"space_id"               => $space_id,
					"space_answer_time_list" => [],
				];
			}

			foreach ($user_answer_time_list as $user_answer_time_item) {

				$user_id = $user_answer_time_item["user_id"];
				if (!isset($answer_list_grouped_by_user_id[$user_id])) {

					$answer_list_grouped_by_user_id[$user_id] = [
						"user_id"               => $user_id,
						"user_answer_time_list" => [],
					];
				}

				$day_start_at = dayStart($min15_start_at);

				if (!isset($answer_list_grouped_by_user_id[$user_id]["user_answer_time_list"][$day_start_at])) {
					$answer_list_grouped_by_user_id[$user_id]["user_answer_time_list"][$day_start_at] = [];
				}
				$answer_list_grouped_by_user_id[$user_id]["user_answer_time_list"][$day_start_at][] =
					self::_makeUserAnswerTimeItem($user_answer_time_item["answer_time"], $user_answer_time_item["answered_at"]);

				if (!isset($answer_list_grouped_by_space_id[$space_id]["space_answer_time_list"][$day_start_at])) {
					$answer_list_grouped_by_space_id[$space_id]["space_answer_time_list"][$day_start_at] = [];
				}
				$answer_list_grouped_by_space_id[$space_id]["space_answer_time_list"][$day_start_at][] =
					self::_makeSpaceAnswerTimeItem($user_id, $user_answer_time_item["answer_time"], $user_answer_time_item["answered_at"]);
			}
		}

		return [$answer_list_grouped_by_user_id, $answer_list_grouped_by_space_id];
	}

	/**
	 * Формируем один элемент сгруппированного времени ответа на сообщение у пользователя
	 *
	 * @param int $answer_time
	 * @param int $answered_at
	 *
	 * @return array
	 */
	protected static function _makeUserAnswerTimeItem(int $answer_time, int $answered_at):array {

		return [
			"answer_time" => $answer_time,
			"answered_at" => $answered_at,
		];
	}

	/**
	 * Формируем один элемент сгруппированного времени ответа на сообщение в пространстве
	 *
	 * @param int $user_id
	 * @param int $answer_time
	 * @param int $answered_at
	 *
	 * @return array
	 */
	protected static function _makeSpaceAnswerTimeItem(int $user_id, int $answer_time, int $answered_at):array {

		return [
			"user_id"     => $user_id,
			"answer_time" => $answer_time,
			"answered_at" => $answered_at,
		];
	}

	/**
	 * Обновляем список ответов пользователей за день
	 *
	 * @param array $answer_list_grouped_by_user_id
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _updateUserAnswerTimeDayList(array $answer_list_grouped_by_user_id):void {

		$grouped_user_id_list = [];
		foreach ($answer_list_grouped_by_user_id as $item) {

			foreach ($item["user_answer_time_list"] as $day_start_at => $_) {

				if (!isset($grouped_user_id_list[$day_start_at])) {
					$grouped_user_id_list[$day_start_at] = [];
				}
				$grouped_user_id_list[$day_start_at][] = $item["user_id"];
			}
		}

		// проходим по каждому пользователю
		foreach ($answer_list_grouped_by_user_id as $item) {

			// проходим по каждому дню
			$user_id = $item["user_id"];
			foreach ($item["user_answer_time_list"] as $day_start_at => $user_answer_time_list) {

				// обязательно открываем транзакцию, мало ли на одного пользователя с двух домино запрос придет
				Gateway_Db_PivotRating_Main::beginTransaction($user_id);

				// обновляем
				$row = self::_getMessageAnswerTimeUserDayRowForUpdate($user_id, $day_start_at);
				Gateway_Db_PivotRating_MessageAnswerTimeUserDayList::update($user_id, $day_start_at, [
					"updated_at"       => time(),
					"answer_time_list" => array_merge($row->answer_time_list, $user_answer_time_list),
				]);

				// закрываем транзакцию
				Gateway_Db_PivotRating_Main::commitTransaction($user_id);
			}
		}
	}

	/**
	 * Получаем запись пользователя за день на обновление
	 *
	 * @param int $user_id
	 * @param int $day_start_at
	 *
	 * @return Struct_Db_PivotRating_MessageAnswerTimeUserDay
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _getMessageAnswerTimeUserDayRowForUpdate(int $user_id, int $day_start_at):Struct_Db_PivotRating_MessageAnswerTimeUserDay {

		try {
			$row = Gateway_Db_PivotRating_MessageAnswerTimeUserDayList::getForUpdate($user_id, $day_start_at);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotRating_MessageAnswerTimeUserDayList::insert($user_id, $day_start_at, []);
			$row = Gateway_Db_PivotRating_MessageAnswerTimeUserDayList::getForUpdate($user_id, $day_start_at);
		}

		return $row;
	}

	/**
	 * Обновляем список ответов по пространству за день
	 *
	 * @param array $answer_list_grouped_by_space_id
	 *
	 * @return void
	 * @long
	 */
	protected static function _updateSpaceAnswerTimeDayList(array $answer_list_grouped_by_space_id):void {

		$grouped_space_id_list = [];
		foreach ($answer_list_grouped_by_space_id as $item) {

			foreach ($item["space_answer_time_list"] as $day_start_at => $_) {

				if (!isset($grouped_space_id_list[$day_start_at])) {
					$grouped_space_id_list[$day_start_at] = [];
				}
				$grouped_space_id_list[$day_start_at][] = $item["space_id"];
			}
		}

		$space_list = [];
		foreach ($grouped_space_id_list as $day_start_at => $space_id_list) {

			$temp_list = Gateway_Db_PivotRating_MessageAnswerTimeSpaceDayList::getSpaceList($space_id_list, $day_start_at);
			foreach ($temp_list as $item) {
				$space_list[$item->space_id] = $item;
			}
		}

		// проходим по каждому пространству
		foreach ($answer_list_grouped_by_space_id as $item) {

			// проходим по каждому дню
			$space_id = $item["space_id"];
			foreach ($item["space_answer_time_list"] as $day_start_at => $space_answer_time_list) {

				// если записи не оказалось, то просто вставляем
				// здесь транзакция не нужна, т.к одно пространство может быть одновременно лишь на одном домино
				if (!isset($space_list[$space_id])) {

					Gateway_Db_PivotRating_MessageAnswerTimeSpaceDayList::insert($space_id, $day_start_at, $space_answer_time_list);
					continue;
				}

				// иначе обновляем
				Gateway_Db_PivotRating_MessageAnswerTimeSpaceDayList::update($space_id, $day_start_at, [
					"updated_at"       => time(),
					"answer_time_list" => array_merge($space_list[$space_id]->answer_time_list, $space_answer_time_list),
				]);
			}
		}
	}
}
