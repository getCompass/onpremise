<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Выполняет поиск неиспользованных аутентификаций.
 * Неиспользованные — это неиспользованные с просроченным expires at.
 */
class Domain_User_Action_LookForUnusedSmsActions {

	protected const _WARNING_THRESHOLD = 0.1;

	public const DAY_CHECK  = 1;
	public const HOUR_CHECK = 2;

	/**
	 * Выполняет поиск неиспользованных запросов за указанный промежуток времени до указанной даты..
	 * Неиспользованные — это неиспользованные с просроченным expires at.
	 *
	 * @noinspection DuplicatedCode
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function run(int $date_to, int $interval):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// выводим даты для запроса
		[$date_start, $date_end] = static::_calculateRange($interval, $date_to);

		// попытки аутентификации
		$unused_count = Gateway_Db_PivotAuth_AuthList::getUnusedCountPerPeriod($date_start, $date_end);
		$total_count  = Gateway_Db_PivotAuth_AuthList::getTotalCountPerPeriod($date_start, $date_end);

		// попытки смены номера
		$status_list  = [Domain_User_Entity_ChangePhone_Story::STATUS_ACTIVE, Domain_User_Entity_ChangePhone_Story::STATUS_FAIL];
		$unused_count += Gateway_Db_PivotPhone_PhoneChangeStory::getCountWithStatusPerPeriod($date_start, $date_end, $status_list);
		$total_count  += Gateway_Db_PivotPhone_PhoneChangeStory::getCountPerPeriod($date_start, $date_end);

		// запросы 2fa
		$unused_count += Gateway_Db_PivotAuth_TwoFaList::getUnusedCountPerPeriod($date_start, $date_end);
		$total_count  += Gateway_Db_PivotAuth_TwoFaList::getTotalCountPerPeriod($date_start, $date_end);

		if ($total_count > 0 && $unused_count / $total_count > static::_WARNING_THRESHOLD) {
			static::_sendReport($interval, floor(100 * ($unused_count / $total_count)));
		}
	}

	/**
	 * Считаем временной диапазон для выборки
	 */
	protected static function _calculateRange(int $interval, int $time):array {

		if ($time < 0 || $time > time()) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("passed bad time");
		}

		return match ($interval) {
			static::DAY_CHECK  => [$time - DAY1, $time],
			static::HOUR_CHECK => [$time - HOUR1, $time],
			default            => throw new \BaseFrame\Exception\Domain\ReturnFatalException("passed unknown interval")
		};
	}

	/**
	 * Отправляет сообщение о результатах проверки.
	 */
	protected static function _sendReport(int $interval, int $failed_percent):void {

		if ($interval === static::DAY_CHECK) {

			Domain_User_Entity_Alert::onUnusedSmsReportDay($failed_percent);
			return;
		}

		if ($interval === static::HOUR_CHECK) {

			Domain_User_Entity_Alert::onUnusedSmsReportHour($failed_percent);
			return;
		}

		throw new \BaseFrame\Exception\Domain\ReturnFatalException("passed unknown interval");
	}
}