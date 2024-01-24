<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\PaymentRequiredException;
use BaseFrame\Search\Query\MatchBuilder;
use BaseFrame\System\Locale;
use JetBrains\PhpStorm\ArrayShape;

/**
 * API-сценарии для работы с поиском.
 */
class Domain_Search_Scenario_Api {

	/**
	 * Ищет локации, удовлетворяющие поисковому запросу.
	 *
	 * @throws Domain_Search_Exception_InvalidQuery
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Search\Exception\QueryException
	 */
	public static function findLocations(int $user_id, int $space_id, string $raw_query, array $location_type_list, int $limit, int $offset, bool $is_restricted_access):array {

		// проверяем, что поиск доступен в пространстве
		if (!Domain_Search_Config_Access::isSpaceAllowed($space_id)) {
			return [[], false];
		}

		$raw_query = trim($raw_query);

		$query_length = mb_strlen($raw_query);
		if ($query_length < Domain_Search_Const::MIN_SEARCH_STR_LENGTH) {
			throw new Domain_Search_Exception_InvalidQuery("passed incorrect query length");
		}

		// готовим строку с wildcard-подстановками
		$prepared_query = Domain_Search_Helper_Stemmer::stemText($raw_query, [Locale::getLocale(), Locale::LOCALE_ENGLISH]);
		$prepared_query = MatchBuilder::prepareQuery($prepared_query, MatchBuilder::SEARCH_MODE_WILDCARD_PREFIX);

		// получаем локаль пользователя
		$locale = Locale::getLocale();

		// собираем dto структуру с параметрами запроса
		$search_params = Struct_Domain_Search_Dto_SearchRequest::buildFromFindLocation($location_type_list, $raw_query, $prepared_query, $limit, $offset, $locale);

		// если пространство ограниченно
		if ($is_restricted_access) {

			$parent_id_list = Domain_Search_Helper_Location::getRestrictedLocations($user_id, $search_params->location_type_list);
			if (count($parent_id_list) < 1) {
				return [[], false];
			}

			[$raw_location_list, $has_next] = Domain_Search_Action_FindLocationsByParents::run($user_id, $parent_id_list, $search_params);
		} else {

			// находим все локации с совпадениями
			[$raw_location_list, $has_next] = Domain_Search_Action_FindLocations::run($user_id, $search_params);
		}

		$location_list = Domain_Search_Entity_LocationHandler::load($user_id, $raw_location_list, $search_params);
		$location_list = Domain_Conversation_Entity_LegacyTypes::filterFindLocationList($location_list);

		return [Domain_Search_Entity_LocationHandler::toApi($user_id, $location_list), $has_next];
	}

	/**
	 * Ищет совпадения в указанной локации, удовлетворяющие поисковому запросу.
	 *
	 * @throws Domain_Search_Exception_InvalidQuery
	 * @throws PaymentRequiredException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Search\Exception\QueryException
	 * @throws Domain_Search_Exception_LocationDenied
	 * @long
	 */
	#[ArrayShape([0 => "array", 1 => "int", 2 => "bool"])]
	public static function findHits(int $user_id, int $space_id, string $raw_query, int $location_type, string $location_key, int $limit, int $offset, bool $is_restricted_access):array {

		// проверяем, что поиск доступен в пространстве
		if (!Domain_Search_Config_Access::isSpaceAllowed($space_id)) {
			return [[], 0, false];
		}

		// выбрасываем исключение, если прислали ключ legacy диалога
		$location_type === Domain_Search_Entity_Conversation_Location::LOCATION_TYPE && Domain_Conversation_Entity_LegacyTypes::throwIfLegacy($location_key);

		try {

			// проверяем наличие доступа к локации через обработчик локаций
			Domain_Search_Entity_LocationHandler::checkAccess($user_id, $location_type, $location_key, $is_restricted_access);
		} catch (Domain_Search_Exception_LocationDenied $e) {

			throw match ($e->getReasonCode()) {

				// если локация требует оплаты, то откидываем 402
				Domain_Search_Exception_LocationDenied::REASON_MEMBER_PLAN_RESTRICTED
				=> new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, $e->getMessage()),

				default => throw $e
			};
		}

		$ruleset = match ($location_type) {

			Domain_Search_Entity_Conversation_Location::LOCATION_TYPE => Domain_Search_Action_FindHits::_RULESET_LOCATION_CONVERSATION,
			Domain_Search_Entity_Thread_Location::LOCATION_TYPE => Domain_Search_Action_FindHits::_RULESET_LOCATION_THREAD,
			default => Domain_Search_Action_FindHits::_RULESET_SPACE,
		};

		$raw_query = trim($raw_query);

		$query_length = mb_strlen($raw_query);
		if ($query_length < Domain_Search_Const::MIN_SEARCH_STR_LENGTH) {
			throw new Domain_Search_Exception_InvalidQuery("passed incorrect query length");
		}

		// готовим строку с wildcard-подстановками
		$prepared_query = Domain_Search_Helper_Stemmer::stemText($raw_query, [Locale::getLocale(), Locale::LOCALE_ENGLISH]);
		$prepared_query = MatchBuilder::prepareQuery($prepared_query, MatchBuilder::SEARCH_MODE_WILDCARD_PREFIX);

		// фильтруем строку и проверяем, что строка не состоит только из символов
		if (!strpbrk($prepared_query, implode("", Domain_Search_Const::ALLOWED_SEARCH_QUERY_SYMBOLS)) && mb_strlen(filterLetter($prepared_query)) < 1) {
			return [[], 0, false];
		}

		// получаем локаль пользователя
		$locale = Locale::getLocale();

		// собираем dto структуру с параметрами запроса
		$search_params = Struct_Domain_Search_Dto_SearchRequest::buildFromFindHits($location_type, $location_key, $raw_query, $prepared_query, $limit, $offset, $locale);

		// находим все совпадения в локации
		[$raw_hit_list, $total_hit_count, $has_next] = Domain_Search_Action_FindHits::run($user_id, $search_params, $ruleset);
		$hit_list = Domain_Search_Entity_HitHandler::load($user_id, $raw_hit_list, $search_params);

		// записываем метрики
		Domain_Search_Repository_ProxyCache::instance()->writeMetrics();
		Domain_Search_Helper_Stemmer::writeMetrics();
		Domain_Search_Helper_Highlight::writeMetrics();
		Domain_Search_Helper_FormattingCleaner::writeMetrics();

		return [Domain_Search_Entity_HitHandler::toApi($user_id, $hit_list), $total_hit_count, $has_next];
	}

}
