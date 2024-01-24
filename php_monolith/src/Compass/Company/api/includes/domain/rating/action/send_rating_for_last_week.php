<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для отправки рейтинга за последнюю неделю
 */
class Domain_Rating_Action_SendRatingForLastWeek {

	/**
	 * отправляем рейтинг за неделю всем пользователям
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $year, int $week):bool {

		// получаем timestamp начала недели
		$from_date_at = Type_Rating_Helper::weekBeginAtByYearAndWeek($year, $week);

		// получаем timestamp конца недели
		$to_date_at = Type_Rating_Helper::weekEndAtByYearAndWeek($year, $week);
		$to_date_at = $to_date_at < $from_date_at ? $from_date_at : $to_date_at;

		$rating = Gateway_Bus_Company_Rating::get("general", $from_date_at, $to_date_at, 0, 10);

		// не шлем рейтинг активности компании если было совершено меньше минимального количества действий
		if ($rating->count < Domain_Rating_Entity_Rating::MIN_RATING_EVENT_COUNT) {
			return false;
		}

		$config       = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME);
		$company_name = $config["value"];

		// получаем чат в который шлем сообщение с рейтингом
		$conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		$conversation_map    = $conversation_config["value"];

		// пушим событие о фиксации общего числа действий в статистике
		Gateway_Event_Dispatcher::dispatch(Type_Event_CompanyRating_ActionTotalFixed::create($conversation_map, $year, $week, $rating->count, $company_name), true);
		return true;
	}
}