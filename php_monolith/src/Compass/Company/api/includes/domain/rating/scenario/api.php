<?php

namespace Compass\Company;

use AnalyticUtils\Domain\Event\Entity\User;
use AnalyticUtils\Domain\Event\Entity\Main;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use DateTime;

/**
 * Сценарии компании для API
 */
class Domain_Rating_Scenario_Api {

	/**
	 * Сценарий для получения количества ивентов по типам
	 *
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $method_version
	 * @param string $event
	 * @param int    $period_type
	 * @param int    $year
	 * @param int    $month
	 * @param int    $week
	 * @param int    $top_list_offset
	 * @param int    $top_list_count
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \CompassApp\Domain\Member\Exception\UserIsGuest
	 * @throws \busException
	 * @throws cs_RatingIncorrectDateParams
	 * @throws cs_RatingIncorrectEvent
	 * @throws cs_RatingIncorrectLimit
	 * @throws cs_RatingIncorrectOffset
	 * @throws cs_RatingIncorrectPeriodType
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function get(int $user_id, int $user_role, int $method_version, string $event, int $period_type, int $year, int $month, int $week, int $top_list_offset, int $top_list_count):array {

		// выдаем exception если пришли некорректные параметры
		Domain_Rating_Entity_Validator::assertIncorrectOffset($top_list_offset);
		Domain_Rating_Entity_Validator::assertIncorrectLimit($top_list_count);
		Domain_Rating_Entity_Validator::assertIncorrectEvent($event);
		Domain_Rating_Entity_Validator::assertIncorrectPeriodType($period_type);
		Domain_Rating_Entity_Validator::assertIncorrectDateParams($year, $month, $week);
		\CompassApp\Domain\Member\Entity\Member::assertUserNotGuest($user_role);
		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SHOW_COMPANY_MEMBER_ENABLED);

		[$year, $week, $month] = self::_preventFutureDate($year, $week, $month);

		$rating = Domain_Rating_Action_GetRatingByPeriod::do($period_type, $event, $year, $month, $week, $top_list_offset, $top_list_count);

		// собираем пользователей для action users
		$user_id_list = [];
		foreach ($rating->top_list as $top_item) {
			$user_id_list[] = $top_item->user_id;
		}

		// фильтруем пользователей в статистике, убирая нечеловеков
		$short_member_list = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list);
		[$top_list, $filtered_user_id_list] = self::_filteredRatingUserList($rating->top_list, $short_member_list);
		[$top_list, $filtered_user_id_list] = self::_filteredDismissalUserList($top_list, $filtered_user_id_list, $short_member_list);
		$rating->top_list = $top_list;

		return [$rating, $filtered_user_id_list];
	}

	/**
	 * Исключаем возможность передачи даты в будущем, отдавая текущую
	 */
	protected static function _preventFutureDate(int $year, int $week, int $month):array {

		// если запрашиваемая дата в будущем - берем последнюю неделю и месяц по времени сервера
		$current_date = new DateTime();
        $current_year = (int) $current_date->format("o");
		$actual_year  = min($year, $current_year);

		$actual_week = match ($year <=> $current_year) {
			1  => (int) $current_date->format("W"),
			0  => min($week, (int) $current_date->format("W")),
			-1 => $week,
		};

		$actual_month = match ($year <=> $current_year) {
			1  => (int) $current_date->format("n"),
			0  => min($month, (int) $current_date->format("n")),
			-1 => $month,
		};

		return [$actual_year, $actual_week, $actual_month];
	}

	/**
	 * фильтруем пользователей в статистике
	 */
	protected static function _filteredRatingUserList(array $top_list, array $short_member_list):array {

		$filtered_user_id_list  = [];
		$not_human_user_id_list = [];

		/** @var \CompassApp\Domain\Member\Struct\Short $short_member */
		foreach ($short_member_list as $short_member) {

			// в статистике нужны только реальные люди
			if (!Type_User_Main::isHuman($short_member->npc_type)) {

				$not_human_user_id_list[] = $short_member->user_id;
				continue;
			}

			$filtered_user_id_list[] = $short_member->user_id;
		}

		$filtered_top_list = [];

		/** @var Struct_Bus_Rating_General_TopItem $top_item */
		foreach ($top_list as $top_item) {

			// убираем из топ-списка нечеловеков
			if (in_array($top_item->user_id, $not_human_user_id_list)) {
				continue;
			}

			// убираем из топ-списка пользователей, которых не нашли в компани_кэше
			// например, когда при получении кэша фильтровали ботов
			if (!in_array($top_item->user_id, $filtered_user_id_list)) {
				continue;
			}

			$filtered_top_list[] = $top_item;
		}

		return [$filtered_top_list, $filtered_user_id_list];
	}

	/**
	 * Находим пользователей, которые были уволены, но не помечены заблокированными в базе данных рейтинга
	 * Убираем их из топ листа возвращаемого клиентам
	 * Чиним их отправляя запросы в базу данных
	 *
	 * @param array $top_list
	 * @param array $user_id_list
	 * @param array $short_member_list
	 *
	 * @return array[]
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _filteredDismissalUserList(array $top_list, array $user_id_list, array $short_member_list):array {

		$filtered_top_list               = [];
		$filtered_user_id_list           = [];
		$kicked_user_id_list             = [];
		$incorrect_disabled_user_id_list = [];

		// получаем список айди пользователей, которые помечены как уволенные
		/** @var \CompassApp\Domain\Member\Struct\Short $short_member */
		foreach ($short_member_list as $short_member) {

			// проверяем что пользователь уволен
			if ($short_member->role == \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT) {
				$kicked_user_id_list[$short_member->user_id] = $short_member->user_id;
			}
		}

		// если уволенные пользователи не помечены is_disabled = 1 чиним их и добавляем список некорректных пользователей
		// в возвращаемый список топ листа, помещаем те записи, где нет сломанных пользователей
		/** @var Struct_Bus_Rating_General_TopItem $top_item */
		foreach ($top_list as $top_item) {

			if (isset($kicked_user_id_list[$top_item->user_id]) && $top_item->is_disabled != 1) {

				// добавляем айди в список некорректных пользователей
				$incorrect_disabled_user_id_list[$top_item->user_id] = $top_item->user_id;

				// чиним уволенного пользователя отключая в рейтинге
				Gateway_Bus_Company_Rating::disableUserInRating($top_item->user_id);

				continue;
			}

			$filtered_top_list[] = $top_item;
		}

		// в список пользователей для actions добавляем пользователей которых нет в списке некорректных пользователей
		foreach ($user_id_list as $user_id) {

			if (isset($incorrect_disabled_user_id_list[$user_id])) {
				continue;
			}

			$filtered_user_id_list[] = $user_id;
		}

		return [$filtered_top_list, $filtered_user_id_list];
	}

	/**
	 * сценарий для получения количества ивентов по типам по пользователю
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_RatingIncorrectYearOrWeeks
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getByUserId(int $user_id, int $year, int $start_week, int $end_week):array {

		// проверяем валидность введеных года и недель
		Domain_Rating_Entity_Validator::assertIncorrectYearOrWeeks($year, $start_week, $end_week);

		// проверяем, существует ли такой пользователь в компании
		$member_info = Gateway_Bus_CompanyCache::getMember($user_id);
		if (!Type_User_Main::isHuman($member_info->npc_type)) {
			throw new cs_IncorrectUserId();
		}

		// получаем список рейтингов у пользователя, разбитый по неделям
		$user_rating_list = Domain_Rating_Action_GetByUserId::do($user_id, $year, $start_week, $end_week, $member_info);

		// загружаем количество участников компании, требуется для определения последней позиции в рейтинге
		$member_count = Domain_User_Action_Config_GetMemberCount::do();

		// форматируем ответ для api
		$formatted_user_rating_list = [];
		foreach ($user_rating_list as $v) {
			$formatted_user_rating_list[] = (object) Apiv1_Format::userRating($v, $member_info->company_joined_at, $member_count);
		}

		return $formatted_user_rating_list;
	}

	/**
	 * сценарий для получения количества ивентов за интервал по его типу
	 *
	 * @throws cs_RatingIncorrectYearOrWeeks
	 * @throws \parseException
	 * @throws paramException
	 * @throws \busException
	 */
	public static function getEventCountByInterval(int $year, int $start_week, int $end_week, string $event):array {

		// проверяем валидность введеных года и недель
		Domain_Rating_Entity_Validator::assertIncorrectYearOrWeeks($year, $start_week, $end_week);

		// получаем количество ивентов за интервал
		return Domain_Rating_Action_GetEventCountByInterval::do($year, $start_week, $end_week, $event);
	}
}
