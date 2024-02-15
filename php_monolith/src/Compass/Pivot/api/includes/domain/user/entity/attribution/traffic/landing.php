<?php

namespace Compass\Pivot;

/**
 * Класс описывает работу атрибуции для трафика с лендинг страниц – такие пользователи приходят с рекламных кампаний
 */
class Domain_User_Entity_Attribution_Traffic_Landing extends Domain_User_Entity_Attribution_Traffic_Abstract {

	/** @var string тип трафика */
	protected const _TRAFFIC_TYPE = "landing";

	/** @var int процент минимального совпадения, при достижении которого считаем что регистрации относится к конкретному посещению */
	protected const _MIN_MATCHING_PERCENTAGE_THRESHOLD = 60;

	/**
	 * выбираем самое подходящее посещение
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit|null
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function chooseMatchedVisit():null|Struct_Db_PivotAttribution_LandingVisit {

		// пробегаемся по всем записям и ищем наиболее совпадающее по параметром посещение
		$most_matched_visit_log = null;
		$most_matched_percent   = 0;
		foreach ($this->_traffic_filtered_visit_list as $visit_log) {

			// фильтруем ссылки-приглашения, чтобы не вводить в заблуждение и не сохранять в базу такую связь
			if (Domain_Company_Entity_JoinLink_Main::isJoinLink($visit_log->link)) {
				continue;
			}

			$result = $this->_comparator->countMatchingPercent($this->_user_app_registration, $visit_log);

			// если процент меньше порога, то пропускаем
			if ($result->matched_percentage < self::_MIN_MATCHING_PERCENTAGE_THRESHOLD) {
				continue;
			}

			// если процент меньше или совпадает с ранее выбранным посещением, то пропускаем его
			// поскольку в ранее выбранном посещении лежит наиболее свежее посещение – и оно приоритетнее
			if ($result->matched_percentage <= $most_matched_percent) {
				continue;
			}

			// иначе сохраняем
			$most_matched_visit_log = $visit_log;
			$most_matched_percent   = $result->matched_percentage;
		}

		return $most_matched_visit_log;
	}

	/**
	 * Выполняем действие после того как определили посещение
	 */
	protected function _after():void {

		// если найдено совпадение с посещением
		// если это партнерская ссылка, то отправляем событие в партнерскую программу о регистрации пользователя
		if (!is_null($this->_matched_visit) && Domain_Partner_Entity_Link::isPartnerLink($this->_matched_visit->link)) {

			Domain_Partner_Entity_Event_UserUsedPartnerLink::create(
				$this->_user_app_registration->user_id,
				$this->_matched_visit->link,
				$this->_user_app_registration->registered_at
			);
		}
	}
}