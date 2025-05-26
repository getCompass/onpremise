<?php

namespace Compass\Pivot;

/**
 * класс
 * @package Compass\Pivot
 */
class Domain_User_Entity_Attribution_Traffic_TypeDetector {

	/** @var int За какой промежуток выбираем посещения, среди которых будем искать совпадения */
	private const _FETCH_VISIT_PERIOD = HOUR24;

	/**
	 * Определяем тип трафика по которому зарегистрировался пользователь
	 *
	 * @return Domain_User_Entity_Attribution_Traffic_Abstract
	 */
	public static function  detect(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration):Domain_User_Entity_Attribution_Traffic_Abstract {

		// проверяем, что IP адрес пользователя корректен
		Domain_User_Entity_Attribution::assertIpAddress($user_app_registration->ip_address);

		// достаем список посещений, с которыми будем работать
		$visit_list = self::_fetchVisitList($user_app_registration->registered_at - self::_FETCH_VISIT_PERIOD, $user_app_registration->registered_at);

		// разделяем посещения по типу трафика
		[$join_link_visit_list, $landing_visit_list] = self::_separateByTrafficType($visit_list);

		// проверяем, есть ли совпадения по IP адресу среди join-посещений
		if (self::_hasIpMatchedJoinVisits($user_app_registration, $join_link_visit_list)) {
			return self::_detectTypeIfHasIpMatchedJoinVisits($user_app_registration, $join_link_visit_list, $landing_visit_list);
		}

		return self::_detectTypeByOtherCases($user_app_registration, $join_link_visit_list, $landing_visit_list);
	}

	/**
	 * достаем список посещений, с которыми будем работать
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	private static function _fetchVisitList(int $start_at, int $end_at):array {

		$visit_list = Gateway_Db_PivotAttribution_LandingVisitLog::getListByPeriod($start_at, $end_at);

		// оставляем посещения, которые ранее не матчились с другими регистрациями
		$visit_list = static::_filterPreviouslyMatchedVisits($visit_list);

		// сортируем посещения по убыванию ORDER BY visited_at DESC
		usort($visit_list, function(Struct_Db_PivotAttribution_LandingVisit $a, Struct_Db_PivotAttribution_LandingVisit $b) {

			return $b->visited_at <=> $a->visited_at;
		});

		return $visit_list;
	}

	/**
	 * фильтруем посещения, которые ранее матчились с другими пользователями
	 *
	 * @return array
	 */
	private static function _filterPreviouslyMatchedVisits(array $visit_list):array {

		if (count($visit_list) < 1) {
			return [];
		}

		$visit_log_id_list = array_column($visit_list, "visit_id");

		$user_campaign_rel_list = Gateway_Db_PivotAttribution_UserCampaignRel::getListByVisitIdList($visit_log_id_list);
		$found_visit_id_map     = array_column($user_campaign_rel_list, null, "visit_id");

		$filtered_visit_log_list = [];

		/** @var Struct_Db_PivotAttribution_LandingVisit $visit_log */
		foreach ($visit_list as $visit_log) {

			if (isset($found_visit_id_map[$visit_log->visit_id])) {
				continue;
			}

			$filtered_visit_log_list[] = $visit_log;
		}

		return $filtered_visit_log_list;
	}

	/**
	 * Разделяем посещения по типу трафика:
	 * – Посещения лендинг-страниц – трафик откуда приходят собственники
	 * – Посещения /join/ страниц – трафик откуда приходят участники
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[] $visit_list
	 *
	 * @return array
	 */
	private static function _separateByTrafficType(array $visit_list):array {

		$join_link_visit_list = [];
		$landing_visit_list   = [];

		foreach ($visit_list as $visit) {

			if (Domain_Company_Entity_JoinLink_Main::isJoinLink($visit->link)) {
				$join_link_visit_list[] = $visit;
			} else {
				$landing_visit_list[] = $visit;
			}
		}

		return [$join_link_visit_list, $landing_visit_list];
	}

	/**
	 * Проверяем, есть ли у зарегистрированного пользователя совпадения по IP адресу среди посещений /join/ страниц
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[] $join_link_visit_list
	 *
	 * @return bool
	 */
	private static function _hasIpMatchedJoinVisits(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, array $join_link_visit_list):bool {

		return Domain_User_Entity_Attribution_VisitFilter::anySatisfy($user_app_registration, $join_link_visit_list, [
			Domain_User_Entity_Attribution_VisitFilter_Rule_ParameterMatching::create(Domain_User_Entity_Attribution_Comparator_Abstract::PARAMETER_IP_ADDRESS),
		]);
	}

	/**
	 * Определяем тип трафика в случае когда среди посещений /join/ страниц есть совпадение по IP адресу. Действуем по алгоритму:
	 * – Сперва ищем 100% совпадение среди посещений /join/ страниц. Если такое есть то join-трафик
	 * – Затем ищем 100% совпадение среди остальных посещений. Если такое есть то рекламный трафик
	 * – Иначе join-трафик
	 *
	 * @return Domain_User_Entity_Attribution_Traffic_Abstract
	 */
	private static function _detectTypeIfHasIpMatchedJoinVisits(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, array $join_link_visit_list, array $landing_visit_list):Domain_User_Entity_Attribution_Traffic_Abstract {

		// ищем 100% совпадения среди посещений join страницы
		$join_filtered_list = Domain_User_Entity_Attribution_VisitFilter::run($user_app_registration, $join_link_visit_list, [
			Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::create(
				Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::OPERATOR_EQUALS, 100
			),
		]);

		$comparator = Domain_User_Entity_Attribution_Comparator_Abstract::choose($user_app_registration->platform);

		// если такие совпадения есть, то это трафик вступления в команду
		if (count($join_filtered_list) > 0) {
			return new Domain_User_Entity_Attribution_Traffic_JoinSpace($user_app_registration, $comparator, $join_link_visit_list);
		}

		// ищем 100% совпадения среди посещений landing страницы
		$landing_filtered_list = Domain_User_Entity_Attribution_VisitFilter::run($user_app_registration, $landing_visit_list, [
			Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::create(
				Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::OPERATOR_EQUALS, 100
			),
		]);

		// если такие совпадения есть, то это трафик лендинга
		if (count($landing_filtered_list) > 0) {
			return new Domain_User_Entity_Attribution_Traffic_Landing($user_app_registration, $comparator, $landing_filtered_list);
		}

		// иначе считаем что трафик с join страниц
		return new Domain_User_Entity_Attribution_Traffic_JoinSpace($user_app_registration, $comparator, $join_link_visit_list);
	}

	/**
	 * Определяем тип трафика в случае когда среди посещений /join/ страниц НЕТ совпадения по IP адресу. Действуем по алгоритму:
	 * – Сперва ищем 100% совпадение среди посещений /join/ страниц. Если такое есть то join-трафик
	 * – Затем ищем 100% совпадение среди остальных посещений. Если такое есть то рекламный трафик
	 * – Иначе join-трафик
	 *
	 * @return Domain_User_Entity_Attribution_Traffic_Abstract
	 */
	private static function _detectTypeByOtherCases(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, array $join_link_visit_list, array $landing_visit_list):Domain_User_Entity_Attribution_Traffic_Abstract {

		// ищем >= 60% совпадения среди посещений landing страниц
		$landing_filtered_list = Domain_User_Entity_Attribution_VisitFilter::run($user_app_registration, $landing_visit_list, [
			Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::create(
				Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::OPERATOR_GREATER_THAN_OR_EQUAL, 60
			),
		]);

		$comparator = Domain_User_Entity_Attribution_Comparator_Abstract::choose($user_app_registration->platform);

		// если такие совпадения есть, то это трафик лендинга
		if (count($landing_filtered_list) > 0) {
			return new Domain_User_Entity_Attribution_Traffic_Landing($user_app_registration, $comparator, $landing_filtered_list);
		}

		// иначе ищем >= 40% совпадения среди посещений join страниц
		$join_filtered_list = Domain_User_Entity_Attribution_VisitFilter::run($user_app_registration, $join_link_visit_list, [
			Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::create(
				Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching::OPERATOR_GREATER_THAN_OR_EQUAL, 40
			),
		]);

		// если такие совпадения есть, то это трафик join страниц
		if (count($join_filtered_list) > 0) {
			return new Domain_User_Entity_Attribution_Traffic_JoinSpace($user_app_registration, $comparator, $join_filtered_list);
		}

		// в остальных случаях считаем органическим трафиком
		return new Domain_User_Entity_Attribution_Traffic_Organic($user_app_registration, $comparator, $landing_filtered_list);
	}
}