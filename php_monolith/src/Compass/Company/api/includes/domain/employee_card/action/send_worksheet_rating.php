<?php

namespace Compass\Company;

/**
 * Базовый класс для добавления требовательности в карточку сотрудника компании
 */
class Domain_EmployeeCard_Action_SendWorksheetRating {

	/**
	 * Составляет таблицу рейтинга рабочих часов за указанный период и триггерит сообщение от бота.
	 *
	 * @param int $period_start_date начало периода
	 * @param int $period_end_date   конец периода
	 *
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $period_start_date, int $period_end_date):void {

		// получаем общее количество участников в компании
		$config               = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MEMBER_COUNT);
		$company_member_count = $config["value"];

		// получаем всех участников компании
		$roles        = \CompassApp\Domain\Member\Entity\Member::ALLOWED_FOR_GET_LIST;
		$member_list  = Gateway_Db_CompanyData_MemberList::getListByRoles($roles, $company_member_count);
		$user_id_list = \CompassApp\Domain\Member\Entity\Member::getUserIdListFromMemberStruct($member_list);

		// ничего не шлем если пользователь один в компании
		if (count($user_id_list) <= 1) {
			return;
		}

		// получаем чат в который шлем сообщение с рейтингом
		$conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		$conversation_map    = $conversation_config["value"];

		// получаем списки часов по лидерам/догоняющим
		$employee_worksheet_rating_list = self::_getWorksheetRating($period_start_date, $period_end_date, $user_id_list);

		// пушим событие
		self::_pushFixedWorksheetRatingEvent(
			$conversation_map, $period_start_date, $period_end_date, $employee_worksheet_rating_list["leader_list"], $employee_worksheet_rating_list["driven_list"]
		);
	}

	/**
	 * Составляет таблицу рейтинга рабочих часов за указанный период.
	 *
	 * @param int $period_start_date начало периода
	 * @param int $period_end_date   конец периода
	 *
	 * @throws \busException
	 */
	protected static function _getWorksheetRating(int $period_start_date, int $period_end_date, array $employee_user_list):array {

		// выбираем все записи, которые были созданы в указанный период
		$full_list = Gateway_Db_CompanyMember_UsercardWorkedHourList::getWorkedByPeriod($period_start_date, $period_end_date, $employee_user_list);

		// список рабочих часов по пользователям
		$total_by_user = self::_makeUserTotalTimeList($full_list);

		// убираем из списка пользователей тех, кто заблокирован или не является человеком
		$total_by_user = self::_filterAllowed($total_by_user);

		// получаем списки часов по лидерам/догоняющим
		return self::_makeWorksheetRatingList($total_by_user, Type_User_Card_WorkedHours::WORKSHEET_USER_PER_LIST_COUNT);
	}

	/**
	 * Создает список с полным временем по сотрудникам.
	 */
	protected static function _makeUserTotalTimeList(array $list):array {

		$total_by_user = [];

		foreach ($list as $v) {

			$value   = floatToInt($v["float_value"]);
			$user_id = $v["user_id"];

			// добавляем запись
			$total_by_user[$user_id] = isset($total_by_user[$user_id]) ? $total_by_user[$user_id] + $value : $value;
		}

		return $total_by_user;
	}

	/**
	 * Фильтрует список пользователей, убирая из него неактивных/ботов.
	 *
	 * Такое тут делать не совсем корректно, поскольку пользователя не должно
	 * быть в списках сотрудников компании и он даже не должен попасть в выборку.
	 *
	 * @throws \busException
	 */
	protected static function _filterAllowed(array $list):array {

		$user_id_list = array_keys($list);

		// получаем пользователей
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list);

		// ид подходящих пользователей
		$allowed_user_id_list = [];

		// убираем всех тех, кто уволен или не является человеком из списка идентификаторов,
		// такое тут делать не совсем корректно, поскольку пользователя не должно
		// быть в списках сотрудников компании и он даже не должен попасть в выборку
		foreach ($user_info_list as $user_info) {

			if (!\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($user_info->role) && Type_User_Main::isHuman($user_info->npc_type)) {
				$allowed_user_id_list[$user_info->user_id] = true;
			}
		}

		// фильтруем список пользователей для итоговой выборки
		return array_intersect_key($list, $allowed_user_id_list);
	}

	/**
	 * Формирует leader/driven списки для указанного набора пользователей.
	 *
	 * @param array $total_by_user user_id => work_hours список отработанных часов для пользователей
	 */
	protected static function _makeWorksheetRatingList(array $total_by_user, int $limit):array {

		// сортируем массив
		asort($total_by_user);
		$total_by_user = array_reverse($total_by_user, true);

		$top_list = [];
		$low_list = [];

		foreach ($total_by_user as $k => $v) {

			// если набралось достаточное число лидеров
			if (count($top_list) >= $limit) {
				break;
			}

			// переносим в лидеры и ансетим в исходном массиве
			// ансетим, поскольку дальше будет работать на список догоняющих с теми же данными
			$top_list[$k] = $v;
			unset($total_by_user[$k]);
		}

		foreach (array_reverse($total_by_user, true) as $k => $v) {

			// если набралось достаточное число догоняющих
			if (count($low_list) >= $limit) {
				break;
			}

			$low_list[$k] = $v;
		}

		$low_list  = array_reverse($low_list, true);
		$user_list = $top_list + $low_list;

		return [
			"leader_list" => array_slice($user_list, 0, ceil(count($user_list) / 2), true),
			"driven_list" => array_slice($user_list, ceil(count($user_list) / 2), count($user_list), true),
		];
	}

	/**
	 * Пушит событие о фиксации рабочих часов за указанный период времени.
	 *
	 * @throws \parseException
	 */
	protected static function _pushFixedWorksheetRatingEvent(string $conversation_map, int $period_start_date, int $period_end_date, array $leader_list, array $driven_list):void {

		$leader_user_work_item_list = self::_makeUserWorkItemList($leader_list);
		$driven_user_work_item_list = self::_makeUserWorkItemList($driven_list);

		// пушим событие о зафиксированном рейтинге
		Gateway_Event_Dispatcher::dispatch(Type_Event_CompanyRating_WorksheetRatingFixed::create(
			$conversation_map,
			(int) $period_start_date,
			(int) $period_end_date,
			$leader_user_work_item_list,
			$driven_user_work_item_list,
		), true);
	}

	/**
	 * Конвертирует список пользователь-время в список элеметно отработанного времени
	 */
	protected static function _makeUserWorkItemList(array $user_work_list):array {

		$output = [];

		// подготавливаем рабочие часы, округляем total-значения до одной цифры после запятой
		foreach ($user_work_list as $user_id => $time) {

			$output[] = [
				"user_id"   => (int) $user_id,
				"work_time" => (float) round($time / 1000, 1) * HOUR1,
			];
		}

		return $output;
	}
}
