<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для получения общей аналитики в приложении
 * @package Compass\Pivot
 */
class Domain_Analytic_Entity_General {

	/**
	 * Получаем количество зарегистрированных пользователей за интервал
	 * Функция исключает тестовых пользователей (зарегистрированных QA) из ответа
	 *
	 * @return int
	 */
	public static function countRegisteredUsersByInterval(int $from_date, int $to_date):int {

		return count(self::_loadRegisteredUsersByInterval($from_date, $to_date));
	}

	/**
	 * Получаем количество уникальных создателей пространств зарегистрированных за интервал
	 * Функция исключает из подсчета создателей, которые являются тестовыми пользователями (зарегистрированные QA)
	 *
	 * @return int
	 */
	public static function countSpaceCreationMetricsByInterval(int $from_date, int $to_date):array {

		// получаем пользователей, зарегистрированных за интервал
		$registered_user_list_by_interval = self::_loadRegisteredUsersByInterval($from_date, $to_date);

		/**
		 * @var array $space_creator_list список уникальных создателей пространств за интервал
		 * @var array $space_list         список пространств созданных за интервал
		 */
		[$space_creator_list, $space_list] = self::_loadSpaceCreationDataByInterval($from_date, $to_date);

		// оставляем только тех создателей, что были зарегистрированы за переданный интервал
		$space_creator_list = array_intersect($registered_user_list_by_interval, $space_creator_list);

		return [count($space_creator_list), count($space_list)];
	}

	/**
	 * Получаем количество пользователей вступивших в команду за промежуток времени
	 * Функция исключает из подсчета пользователей, который за этот же промежуток создали команду
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_UserNotFound
	 */
	public static function countUniqueSpaceJoinedUsersByInterval(int $from_date, int $to_date):int {

		// получаем пользователей, зарегистрированных за интервал
		$registered_user_list_by_interval = self::_loadRegisteredUsersByInterval($from_date, $to_date);

		// получаем список пользователей вступивших в компанию
		$action_user_list = Gateway_Socket_CollectorServer::getSpaceActionUserList(Type_Space_ActionAnalytics::NEW_MEMBER, $from_date, $to_date);

		// оставляем в списке всех пользователей, которые зарегистрировались за интервал
		$action_user_list = array_intersect($registered_user_list_by_interval, $action_user_list);

		return count($action_user_list);
	}

	/**
	 * Получаем зарегистрированных пользователей за интервал
	 * Ответ не содержит тестовых пользователей, зарегистрированных QA
	 *
	 * @return int
	 */
	protected static function _loadRegisteredUsersByInterval(int $from_date, int $to_date):array {

		// получаем список пользователей, зарегистрированных за интервал
		$user_list = Gateway_Db_PivotUser_UserList::getAllByInterval($from_date, $to_date);

		// фильтруем пользователей, убирая тестовых
		$user_list = array_filter($user_list, static fn(Struct_Db_PivotUser_User $user) => !Domain_User_Entity_User::isQATestUser($user));

		return array_column($user_list, "user_id");
	}

	/**
	 * Получаем список уникальных создателей пространств за интервал и список этих пространств за переданный интервал
	 * Функция исключает из ответа сущности, которые были заведены тестовыми пользователями (зарегистрированные QA)
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_UserNotFound
	 */
	protected static function _loadSpaceCreationDataByInterval(int $from_date, int $to_date):array {

		$space_list = Gateway_Db_PivotCompany_CompanyList::getByInterval($from_date, $to_date);

		// получаем уникальный массив создателей пространств
		$creator_user_id_list = array_unique(array_column($space_list, "created_by_user_id"));

		// получаем информацию о пользователях-создателях
		$creator_user_map = Gateway_Bus_PivotCache::getUserListInfo($creator_user_id_list);

		// фильтруем пользователей, убирая тестовых
		$filtered_space_creators = array_filter($creator_user_map, static fn(Struct_Db_PivotUser_User $user) => !Domain_User_Entity_User::isQATestUser($user));

		// фильтруем пространства, убирая те что были созданы тестовыми пользователями
		$filtered_space_list = array_filter($space_list,
			static fn(Struct_Db_PivotCompany_Company $space) => !Domain_User_Entity_User::isQATestUser($creator_user_map[$space->created_by_user_id]));

		return [array_column($filtered_space_creators, "user_id"), $filtered_space_list];
	}

	/**
	 * Подсчитываем количество вступлений пользователей в пространства
	 *
	 * @return int
	 */
	public static function countSpaceJoiningByInterval(int $from_date, int $to_date):int {

		return Gateway_Socket_CollectorServer::getSpaceActionCount(Type_Space_ActionAnalytics::NEW_MEMBER, $from_date, $to_date);
	}

	/**
	 * Получаем сумму выручки текстом за переданный интервал
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getRevenueSumText(int $from_date, int $to_date):string {

		$revenue_list_by_day = Gateway_Socket_Billing::getRevenueStat($from_date, $to_date);

		return self::_formatRevenueSum($revenue_list_by_day);
	}

	/**
	 * Получаем метрики по конференциям
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getConferenceMetrics(int $from_date, int $to_date):array {

		$temporary_conference_row_data = Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row0", "day", $from_date, $to_date);
		$single_conference_row_data    = Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row1", "day", $from_date, $to_date);
		$permanent_conference_row_data = Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row2", "day", $from_date, $to_date);

		return [
			self::_sumRowValues($temporary_conference_row_data),
			self::_sumRowValues($single_conference_row_data),
			self::_sumRowValues($permanent_conference_row_data),
		];
	}

	/**
	 * Получаем метрики по конференциям
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getTotalConferenceMetricsChartData(int $from_date, int $to_date):array {

		$output = [];

		// здесь будут сырые данные по метрикам, которые нужно будет просуммировать и объединить в один график
		$row_data_list = [
			Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row0", "day", $from_date, $to_date), // temporary конференции
			Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row1", "day", $from_date, $to_date), // single конференции
			Gateway_Socket_CollectorServer::getRowByDate("jitsi", "row2", "day", $from_date, $to_date), // permanent конференции
		];

		// пробегаемся по каждой сырой метрике и формируем данные для графика
		foreach ($row_data_list as $raw_data) {

			// сортируем временные метки по убыванию
			usort($raw_data, function(array $a, array $b) {

				return $b["date"] <=> $a["date"];
			});

			// складываем все это в output по дням
			foreach ($raw_data as $graph) {

				// если в output еще нет информации по этому дню, то инициализируем
				if (!isset($output[$graph["date"]])) {
					$output[$graph["date"]] = [$graph["date"], 0];
				}

				// получаем из output информацию по этому дню
				[$date, $value] = $output[$graph["date"]];

				// суммируем value
				$value += $graph["value"];

				// сохраняем в output
				$output[$date] = [$date, $value];
			}
		}

		// возвращаем только значения, без ключа
		return array_values($output);
	}

	/**
	 * Суммируем value полученной статистики
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	protected static function _sumRowValues(array $row_data):int {

		$sum = 0;
		foreach ($row_data as $row) {

			if (!isset($row["value"])) {
				throw new ParseFatalException("incorrect input data");
			}

			$sum += $row["value"];
		}

		return $sum;
	}

	/**
	 * Форматируем информацию из биллинга по выручке в текст
	 *
	 * @return string
	 */
	protected static function _formatRevenueSum(array $revenue_list):string {

		if (count($revenue_list) < 1) {
			return "0";
		}

		// обрезаем копейки и делим на 1000 (специально делаем не сразу /100000, чтобы не путаться)
		$sum     = floor($revenue_list["amount_rub"] / 100);
		$revenue = floor($sum / 1000);
		if ($revenue < 1) {
			return $revenue;
		}

		// форматируем
		return "{$revenue}к";
	}
}