<?php

namespace Compass\Pivot;

/**
 * класс описывает определение источника с которого пользователь пришел в приложение
 *
 * @package Compass\Pivot
 */
class Domain_User_Action_Attribution_Detect {

	/** @var int все возможные кейсы атрибуции связанные с переходом по ссылке-приглашению при регистрации */
	public const JOIN_SPACE_CASE_OPEN_DASHBOARD     = 1;
	public const JOIN_SPACE_CASE_OPEN_ENTERING_LINK = 2;
	public const JOIN_SPACE_CASE_OPEN_JOIN_LINK     = 3;

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

			return $b->visited_at <=> $a->visited_at;
		});

		// пытаемся найти 100% совпадение среди /join/ страниц за последние 15 минут
		/** @var null|Struct_Db_PivotAttribution_LandingVisit $matched_visit */
		[$join_space_case, $most_matched_visit] = self::_chooseMostMatchedJoinPageVisit($user_app_registration_attr, $visit_list);

		// если найдено 100% совпадение, то его и возвращаем в ответе
		if ($join_space_case === self::JOIN_SPACE_CASE_OPEN_JOIN_LINK) {

			// сохраняем связь user_id <-> visit_id
			Domain_User_Entity_Attribution::saveUserVisitRel($user_app_registration_attr, $most_matched_visit);

			return new Struct_Dto_User_Action_Attribution_Detect_Result(self::JOIN_SPACE_CASE_OPEN_JOIN_LINK, $most_matched_visit);
		}

		// иначе пытаемся найти совпадение в остальных посещениях
		// совпадение в 100% здесь уже не обязательно, но не ниже значения константы _MIN_PERCENTAGE_THRESHOLD
		// может вернуться null
		$matched_visit = self::_chooseMostMatchedVisit($user_app_registration_attr, $visit_list);

		// сохраняем связь user_id <-> visit_id
		!is_null($matched_visit) && Domain_User_Entity_Attribution::saveUserVisitRel($user_app_registration_attr, $matched_visit);

		return new Struct_Dto_User_Action_Attribution_Detect_Result($join_space_case, $matched_visit);
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

		// здесь храним наиболее совпавшее посещение и процент его совпадения
		$most_matched_visit      = null;
		$most_matched_percentage = 0;

		// если фича приглашения в пространство через атрибуцию отключена, то ничего не делаем
		if (!ATTRIBUTION_JOIN_SPACE_ENABLED) {
			return [self::JOIN_SPACE_CASE_OPEN_DASHBOARD, $most_matched_visit];
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

			// сохраняем результаты сравнения
			$visit_parameters_comparing_result_map[$visit->visit_id] = $matching_result->parameter_comparing_result_list;

			// если ранее не было выбрано 100% совпадение (чтобы не перезаписывать ранее совпавшее на 100% приглашение)
			// и результат сравнения лучше чем у прошлых
			if ($most_matched_percentage !== 100 && $matching_result->matched_percentage > $most_matched_percentage) {

				$most_matched_percentage = $matching_result->matched_percentage;
				$most_matched_visit      = $visit;
			}
		}

		// определяем какой кейс
		$join_space_case = self::_resolveJoinSpaceCase($most_matched_percentage, $user_app_registration_attr, $most_matched_visit);

		// сохраняем аналитику
		Domain_User_Entity_Attribution_JoinSpaceAnalytics::createUserJoinSpaceAnalytics(self::_prepareJoinSpaceAttributAnalytics(
			$user_app_registration_attr, $most_matched_percentage === 100 ? $most_matched_visit : null, $join_space_case, $most_matched_percentage, $visit_parameters_comparing_result_map
		));

		return [$join_space_case, $most_matched_visit];
	}

	/**
	 * Определяем кейс join_space
	 *
	 * @return int
	 */
	protected static function _resolveJoinSpaceCase(int $most_matched_percentage, Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, null|Struct_Db_PivotAttribution_LandingVisit $most_matched_visit):int {

		// если результат совпадения – 100%
		if ($most_matched_percentage === 100) {
			return self::JOIN_SPACE_CASE_OPEN_JOIN_LINK;
		}

		// проверим самое точное совпадение на кейс с открытием ввода ссылки приглашения
		if (!is_null($most_matched_visit) && self::_isOpenEnteringLinkCase($user_app_registration_attr, $most_matched_visit)) {
			return self::JOIN_SPACE_CASE_OPEN_ENTERING_LINK;
		}

		// иначе % совпадений мал
		return self::JOIN_SPACE_CASE_OPEN_DASHBOARD;
	}

	/**
	 * Подготавливаем аналитику по поиску совпадений среди join-страниц
	 *
	 * @return Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics
	 */
	protected static function _prepareJoinSpaceAttributAnalytics(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, null|Struct_Db_PivotAttribution_LandingVisit $matched_visit, int $join_space_case, int $most_matched_percentage, array $visit_parameters_comparing_result_map):Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics {

		return new Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics(
			$user_app_registration_attr->user_id,
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::resolveResultByJoinCase($join_space_case),
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
	 * Если это кейс открытия окна ввода ссылки-приглашения
	 *
	 * @return bool
	 */
	protected static function _isOpenEnteringLinkCase(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, ?Struct_Db_PivotAttribution_LandingVisit $matched_visit):bool {

		// выбираем алгоритм с помощью которого будем подсчитывать совпадения
		$algorithm = Domain_User_Entity_Attribution_DetectAlgorithm_Abstract::chooseAlgorithm($user_app_registration_attr->platform);

		// определяем совпадение
		$matching_result = $algorithm->countMatchingPercent($user_app_registration_attr, $matched_visit);

		// совпал ли ip-адрес
		$ip_is_equal = false;

		// количество совпавших параметров за исключением ip-адреса
		$matched_parameter_count = 0;

		// пробегаемся по всем сравниваемым параметрам
		foreach ($matching_result->parameter_comparing_result_list as $parameter_comparing_result) {

			// если параметр не совпал, то пропускаем
			if (!$parameter_comparing_result->is_equal) {
				continue;
			}

			// если это ip-адрес
			if ($parameter_comparing_result->parameter_name === Domain_User_Entity_Attribution_DetectAlgorithm_Abstract::PARAMETER_IP_ADDRESS) {
				$ip_is_equal = true;
			} else {

				// иначе считаем кол-во совпавших параметров
				$matched_parameter_count++;
			}
		}

		// если совпал ip-адрес и 4 других параметра
		return $ip_is_equal && $matched_parameter_count >= 4;
	}

}