<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Контроллер для методов рейтинга
 */
class Apiv1_Company_Rating extends \BaseFrame\Controller\Api {

	private const _DEFAULT_TOP_LIST_COUNT = 50; // количестов людей в топе по дефолту

	public const ALLOW_METHODS = [
		"get",
		"getByUserId",
		"getEventCountByInterval",
		"getGeneralEventCountByInterval",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Метод для получения количества ивентов по типам
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function get():array {

		$year            = $this->post(\Formatter::TYPE_INT, "year", (int) date("o"));
		$week            = $this->post(\Formatter::TYPE_INT, "week", (int) date("W"));
		$period_type     = $this->post(\Formatter::TYPE_INT, "period_type", Domain_Rating_Entity_Rating::PERIOD_WEEK_TYPE);
		$month           = $this->post(\Formatter::TYPE_INT, "month", (int) date("n"));
		$event           = $this->post(\Formatter::TYPE_STRING, "event", "general");
		$top_list_offset = $this->post(\Formatter::TYPE_INT, "top_list_offset", 0);
		$top_list_count  = $this->post(\Formatter::TYPE_INT, "top_list_count", self::_DEFAULT_TOP_LIST_COUNT);

		try {

			[$rating, $user_id_list] = Domain_Rating_Scenario_Api::get(
				$this->user_id, $this->role, $this->method_version, $event, $period_type, $year, $month, $week, $top_list_offset, $top_list_count
			);
		} catch (cs_RatingIncorrectOffset) {
			return $this->error(657, "Incorrect offset");
		} catch (cs_RatingIncorrectLimit) {
			return $this->error(658, "Incorrect limit");
		} catch (cs_RatingIncorrectPeriodType) {
			throw new ParamException("Invalid period type");
		} catch (cs_RatingIncorrectDateParams) {
			throw new ParamException("Invalid date params");
		} catch (cs_RatingIncorrectEvent) {
			throw new ParamException("Invalid event type");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed|\CompassApp\Domain\Member\Exception\UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		$this->action->users($user_id_list);
		return $this->ok([
			"rating" => (object) Apiv1_Format::rating($rating),
		]);
	}

	/**
	 * метод для получения количества ивентов по типам по пользователю
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \busException
	 */
	public function getByUserId():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$year       = $this->post(\Formatter::TYPE_INT, "year");
		$start_week = $this->post(\Formatter::TYPE_INT, "start_week");
		$end_week   = $this->post(\Formatter::TYPE_INT, "end_week");

		try {
			$formatted_user_rating_list = Domain_Rating_Scenario_Api::getByUserId($user_id, $year, $start_week, $end_week);
		} catch (\cs_RowIsEmpty) {
			return $this->error(654, "User is not in company");
		} catch (cs_RatingIncorrectYearOrWeeks) {
			throw new ParamException("Invalid year or weeks");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("User is not human");
		}

		return $this->ok([
			"user_rating_list" => (array) $formatted_user_rating_list,
		]);
	}

	/**
	 * метод для получения количества ивентов за интервал по его типу
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \busException
	 */
	public function getEventCountByInterval():array {

		$year       = $this->post(\Formatter::TYPE_INT, "year");
		$start_week = $this->post(\Formatter::TYPE_INT, "start_week");
		$end_week   = $this->post(\Formatter::TYPE_INT, "end_week");
		$event      = $this->post(\Formatter::TYPE_STRING, "event");

		// получаем количество ивентов из go_rating
		try {
			$event_count_list = Domain_Rating_Scenario_Api::getEventCountByInterval($year, $start_week, $end_week, $event);
		} catch (cs_RatingIncorrectYearOrWeeks) {
			throw new ParamException("Invalid year or weeks");
		}

		$formatted_event_count_list = [];
		foreach ($event_count_list as $v) {
			$formatted_event_count_list[] = (object) Apiv1_Format::eventCount($v);
		}
		return $this->ok([
			"event_count_list" => (array) $formatted_event_count_list,
		]);
	}

	/**
	 * метод для получения общего количества ивентов за интервал
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \busException
	 */
	public function getGeneralEventCountByInterval():array {

		$year       = $this->post(\Formatter::TYPE_INT, "year");
		$start_week = $this->post(\Formatter::TYPE_INT, "start_week");
		$end_week   = $this->post(\Formatter::TYPE_INT, "end_week");
		$event      = $this->post(\Formatter::TYPE_STRING, "event", Domain_Rating_Entity_Rating::GENERAL);

		// получаем количество ивентов из go_rating
		try {
			$event_count_list = Domain_Rating_Scenario_Api::getEventCountByInterval($year, $start_week, $end_week, $event);
		} catch (cs_RatingIncorrectYearOrWeeks) {
			throw new ParamException("Invalid year or weeks");
		}
		$formatted_event_count_list = [];
		foreach ($event_count_list as $v) {
			$formatted_event_count_list[] = (object) Apiv1_Format::eventCount($v);
		}
		return $this->ok([
			"event_count_list" => (array) $formatted_event_count_list,
		]);
	}
}
