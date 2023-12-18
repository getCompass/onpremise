<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use BaseFrame\System\Locale;

/**
 * Класс для очистки данных
 */
class Domain_Member_Entity_EmployeeCard {

	/**
	 * Первоначальные значения бейджа для различных ролей/позиций в пространстве
	 */
	protected const _GUEST_INITIAL_BADGE_COLOR_ID   = 5;
	protected const _GUEST_INITIAL_BADGE_CONTENT    = "Guest";
	protected const _MEMBER_INITIAL_BADGE_COLOR_ID  = 1;
	protected const _MEMBER_INITIAL_BADGE_CONTENT   = "Member";
	protected const _CREATOR_INITIAL_BADGE_COLOR_ID = 3;
	protected const _CREATOR_INITIAL_BADGE_CONTENT  = "Admin";

	/**
	 * вычисляет время работы сотрудника в компании как отношения отработанного времени к годам
	 *
	 * @param int $join_company_at_timestamp timestamp когда сотрудник вступил в компанию
	 * @param int $target_timestamp          timestamp даты исключения из компании/текущей даты
	 *
	 * @return float
	 * @throws cs_DatesWrongOrder
	 */
	public static function calculateTotalWorkedTime(int $join_company_at_timestamp, int $target_timestamp):float {

		// получаем timestamp начала дня, когда сотрудник вступил в компанию
		$timezone                                = new \DateTimeZone("UTC");

		$join_day_company_at_timestamp_start_day = (new \DateTime())->setTimezone($timezone)
			->setTimestamp($join_company_at_timestamp)->modify("today");

		$target_timestamp_start_day_plus_second  = (new \DateTime())->setTimezone($timezone)
				->setTimestamp($target_timestamp + 1)->modify("today");

		// находим разницу между целевым временем и тем, когда сотрудник вступил в компанию
		$diff_join_company_target_datetime = self::_getDiffBetweenDates($join_day_company_at_timestamp_start_day, $target_timestamp_start_day_plus_second);

		// если время вступления в компанию позже, чем целевое, это исключительно, поэтому Exception
		if ($diff_join_company_target_datetime->invert) {
			throw new cs_DatesWrongOrder("Join company datetime is less than target datetime");
		}

		// получаем количество месяцев в компании для сотрудника
		$months_in_company = $diff_join_company_target_datetime->m;
		// делим отработанное число месяцев на 12, чтобы получить отношение отработанных месяцев к году работы
		$ratio_months_to_year = $months_in_company / 12;

		// округляем дробь до первого знака после запятой
		$ratio_months_to_year = round($ratio_months_to_year * 10) / 10;
		return (float) $diff_join_company_target_datetime->y + $ratio_months_to_year;
	}

	/**
	 * находит разницу между двумя timestamp
	 *
	 * @param \DateTime $origin_datetime
	 * @param \DateTime $target_datetime
	 *
	 * @return \DateInterval
	 */
	protected static function _getDiffBetweenDates(\DateTime $origin_datetime, \DateTime $target_datetime):\DateInterval {

		return $origin_datetime->diff($target_datetime);
	}

	/**
	 * Получаем первоначальные данные вступаемого участника
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 */
	public static function getJoiningInitialData(int $role, bool $is_creator, string $locale):array {

		// получаем описание пользователя
		$short_description = match ($role) {
			Member::ROLE_GUEST => Locale::getText(getConfig("LOCALE_TEXT"), "guest", "default_description", $locale),
			default => Locale::getText(getConfig("LOCALE_TEXT"), "member", "default_description", $locale),
		};

		// получаем настройки бейджа:
		if ($is_creator) {

			// если создатель
			[$badge_color_id, $badge_content] = [self::_CREATOR_INITIAL_BADGE_COLOR_ID, self::_CREATOR_INITIAL_BADGE_CONTENT];
		} else {

			// если другие роли
			[$badge_color_id, $badge_content] = match ($role) {
				Member::ROLE_GUEST => [self::_GUEST_INITIAL_BADGE_COLOR_ID, self::_GUEST_INITIAL_BADGE_CONTENT],
				Member::ROLE_MEMBER => [self::_MEMBER_INITIAL_BADGE_COLOR_ID, self::_MEMBER_INITIAL_BADGE_CONTENT],
			};
		}

		return [$short_description, $badge_color_id, $badge_content];
	}
}