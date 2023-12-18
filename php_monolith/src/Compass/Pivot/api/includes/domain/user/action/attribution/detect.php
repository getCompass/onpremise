<?php

namespace Compass\Pivot;

/**
 * класс описывает определение источника с которого пользователь пришел в приложение
 *
 * @package Compass\Pivot
 */
class Domain_User_Action_Attribution_Detect {

	/** @var int по умолчанию ищем совпадения за 24 часа */
	protected const _SEARCH_PERIOD = HOUR24;

	/** @var int для /join/ страниц ищем совпадение за 15 минут */
	protected const _SEARCH_JOIN_PAGE_PERIOD = 60 * 15;

	/** @var int процент минимального совпадения, при достижении которого считаем что регистрации относится к конкретному посещению */
	protected const _MIN_MATCHING_PERCENTAGE_THRESHOLD = 60;

	/**
	 * алгоритм сперва пытается найти источник среди посещений /join/ страницы за последние 15 минут
	 * примечательно, что за совпадение будет считаться только 100% матч по параметрам цифровой подписи
	 *
	 * если совпадение среди посещений /join/ страниц не будет найдено, то алгоритм будет искать среди
	 * посещений других страниц
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit|null возвращает объект посещения, для которого найдено совпадение
	 */
	public static function do(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr):Struct_Dto_User_Action_Attribution_Detect_Result {

		// проверяем, что IP адрес пользователя корректен
		Domain_User_Entity_Attribution::assertIpAddress($user_app_registration_attr->ip_address);

		// получаем все посещения за последние N часов
		$visit_list = Gateway_Db_PivotAttribution_LandingVisitLog::getListByPeriod(
			$user_app_registration_attr->registered_at - self::_SEARCH_PERIOD, $user_app_registration_attr->registered_at
		);

		// фильтруем посещения, которые ранее матчились с другими пользователями
		$visit_list = self::_filterPreviouslyMatchedVisits($visit_list);

		// сортируем посещения по убыванию ORDER BY visited_at DESC
		usort($visit_list, function(Struct_Db_PivotAttribution_LandingVisit $a, Struct_Db_PivotAttribution_LandingVisit $b) {

			return $a->visited_at <=> $b->visited_at;
		});

		// пытаемся найти 100% совпадение среди /join/ страниц за последние 15 минут
		/** @var null|Struct_Db_PivotAttribution_LandingVisit $matched_visit */
		[$matched_visit, $matched_percentage] = self::_chooseMostMatchedJoinPageVisit($user_app_registration_attr, $visit_list);

		// если найдено 100% совпадение, то его и возвращаем в ответе
		if (!is_null($matched_visit) && $matched_percentage == 100) {

			// сохраняем связь user_id <-> visit_id
			self::_saveUserVisitRel($user_app_registration_attr, $matched_visit);

			return new Struct_Dto_User_Action_Attribution_Detect_Result($matched_visit, $matched_percentage);
		}

		// иначе пытаемся найти совпадение в остальных посещениях
		// совпадение в 100% здесь уже не обязательно, но не ниже значения константы _MIN_PERCENTAGE_THRESHOLD
		// может вернуться null
		$matched_visit = self::_chooseMostMatchedVisit($user_app_registration_attr, $visit_list);

		// сохраняем связь user_id <-> visit_id
		!is_null($matched_visit) && self::_saveUserVisitRel($user_app_registration_attr, $matched_visit);

		return new Struct_Dto_User_Action_Attribution_Detect_Result($matched_visit, $matched_percentage);
	}

	/**
	 * фильтруем посещения, которые ранее матчились с другими пользователями
	 *
	 * @return array
	 */
	protected static function _filterPreviouslyMatchedVisits(array $landing_visit_list):array {

		if (count($landing_visit_list) < 1) {
			return [];
		}

		$visit_log_id_list = array_column($landing_visit_list, "visit_id");

		$user_campaign_rel_list = Gateway_Db_PivotAttribution_UserCampaignRel::getListByVisitIdList($visit_log_id_list);
		$found_visit_id_list    = array_column($user_campaign_rel_list, "visit_id");

		$filtered_visit_log_list = [];

		/** @var Struct_Db_PivotAttribution_LandingVisit $visit_log */
		foreach ($landing_visit_list as $visit_log) {

			if (in_array($visit_log->visit_id, $found_visit_id_list)) {
				continue;
			}

			$filtered_visit_log_list[] = $visit_log;
		}

		return $filtered_visit_log_list;
	}

	/**
	 * Выбираем посещение /join/ страницы с наибольшим процентом совпадения
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[] $visit_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @long
	 */
	protected static function _chooseMostMatchedJoinPageVisit(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, array $visit_list):array {

		$most_matched_percentage = 0;
		$matched_visit           = null;

		// если фича приглашения в пространство через атрибуцию отключена, то ничего не делаем
		if (!ATTRIBUTION_JOIN_SPACE_ENABLED) {
			return [$matched_visit, $most_matched_percentage];
		}

		// выбираем алгоритм с помощью которого будем подсчитывать совпадения
		$algorithm = Domain_User_Entity_Attribution_DetectAlgorithm_Abstract::chooseAlgorithm($user_app_registration_attr->platform);

		// сюда сложим результаты сравнения параметров среди всех посещений
		$visit_parameters_comparing_result_map = [];

		// пробегаемся по каждому посещению
		foreach ($visit_list as $visit) {

			// если посещение не страницы /join/
			if (!Domain_Company_Entity_JoinLink_Main::isJoinLink($visit->link)) {
				continue;
			}

			// если по времени не подходит
			if ($visit->visited_at < $user_app_registration_attr->registered_at - self::_SEARCH_JOIN_PAGE_PERIOD) {
				continue;
			}

			// определяем совпадение
			$matching_result = $algorithm->countMatchingPercent($user_app_registration_attr, $visit);

			// сохраняем наибольший процент совпадения
			$most_matched_percentage = max($most_matched_percentage, $matching_result->matched_percentage);

			// сохраняем результаты сравнения
			$visit_parameters_comparing_result_map[$visit->visit_id] = $matching_result->parameter_comparing_result_list;

			// если 100% результат, то возвращаем его тут же
			if ($matching_result->matched_percentage == 100) {

				$matched_visit = $visit;
				break;
			}
		}

		// сохраняем аналитику
		Domain_User_Entity_Attribution_JoinSpaceAnalytics::createUserJoinSpaceAnalytics(self::_prepareJoinSpaceAttributAnalytics(
			$user_app_registration_attr, $matched_visit, $most_matched_percentage, $visit_parameters_comparing_result_map
		));

		return [$matched_visit, $most_matched_percentage];
	}

	/**
	 * Подготавливаем аналитику по поиску совпадений среди join-страниц
	 *
	 * @return Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics
	 */
	protected static function _prepareJoinSpaceAttributAnalytics(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, null|Struct_Db_PivotAttribution_LandingVisit $matched_visit, int $most_matched_percentage, array $visit_parameters_comparing_result_map):Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics {

		return new Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics(
			$user_app_registration_attr->user_id,
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::resolveResultByMatchedPercentage($most_matched_percentage),
			!is_null($matched_visit) ? $matched_visit->visit_id : "",
			$most_matched_percentage,
			$visit_parameters_comparing_result_map
		);
	}

	/**
	 * выбираем самый подходящий visit_log
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[] $visit_log_list
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit|null
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _chooseMostMatchedVisit(Struct_Db_PivotAttribution_UserAppRegistration $registration_log, array $visit_log_list):null|Struct_Db_PivotAttribution_LandingVisit {

		// выбираем алгоритм с помощью которого будем подсчитывать совпадения
		$algorithm = Domain_User_Entity_Attribution_DetectAlgorithm_Abstract::chooseAlgorithm($registration_log->platform);

		// пробегаемся по всем записям и ищем наиболее совпадающее по параметром посещение
		$most_matched_visit_log = null;
		$most_matched_percent   = 0;
		foreach ($visit_log_list as $visit_log) {

			// фильтруем ссылки-приглашения, чтобы не вводить в заблуждение и не сохранять в базу такую связь
			if (Domain_Company_Entity_JoinLink_Main::isJoinLink($visit_log->link)) {
				continue;
			}

			$result = $algorithm->countMatchingPercent($registration_log, $visit_log);

			// если процент меньше порога, то пропускаем
			if ($result->matched_percentage < self::_MIN_MATCHING_PERCENTAGE_THRESHOLD) {
				continue;
			}

			// если процент меньше ранее выбранного посещения, то пропускаем
			if ($result->matched_percentage < $most_matched_percent) {
				continue;
			}

			// иначе сохраняем
			$most_matched_visit_log = $visit_log;
			$most_matched_percent   = $result->matched_percentage;
		}

		return $most_matched_visit_log;
	}

	/**
	 * Создаем связь пользователь <-> посещение
	 */
	protected static function _saveUserVisitRel(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, Struct_Db_PivotAttribution_LandingVisit $matched_visit):void {

		Gateway_Db_PivotAttribution_UserCampaignRel::insert(
			new Struct_Db_PivotAttribution_UserCampaignRel(
				$user_app_registration_attr->user_id,
				$matched_visit->visit_id,
				$matched_visit->utm_tag,
				$matched_visit->source_id,
				$matched_visit->link,
				true,
				time()
			)
		);
	}

}