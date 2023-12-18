<?php

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * DTO структура со всеми параметрами поисковых запросов:
 * @see Apiv2_Search::findLocations
 * @see Apiv2_Search::findHits
 */
class Struct_Domain_Search_Dto_SearchRequest {

	/**
	 * @param string[] $location_type_list
	 * @param string   $location_type
	 * @param string   $location_key
	 * @param string   $raw_query
	 * @param string   $morphology_query
	 * @param int      $limit
	 * @param int      $offset
	 */
	public function __construct(
		public array  $location_type_list = [],
		public string $location_type = "",
		public string $location_key = "",
		public string $raw_query = "",
		public string $morphology_query = "",
		public int    $limit = 0,
		public int    $offset = 0,
		public string $user_locale = Locale::LOCALE_RUSSIAN,
	) {
	}

	/**
	 * собираем структуру из параметров api-метода search.findLocation
	 *
	 * @return Struct_Domain_Search_Dto_SearchRequest
	 */
	public static function buildFromFindLocation(array $location_type_list, string $raw_query, string $prepared_query, int $limit, int $offset, string $locale):self {

		return new Struct_Domain_Search_Dto_SearchRequest(
			location_type_list: $location_type_list,
			raw_query: $raw_query,
			morphology_query: $prepared_query,
			limit: $limit,
			offset: $offset,
			user_locale: $locale,
		);
	}

	/**
	 * собираем структуру из параметров api-метода search.findHits
	 *
	 * @return Struct_Domain_Search_Dto_SearchRequest
	 */
	public static function buildFromFindHits(string $location_type, string $location_key, string $raw_query, string $prepared_query, int $limit, int $offset, string $locale):self {

		return new Struct_Domain_Search_Dto_SearchRequest(
			location_type: $location_type,
			location_key: $location_key,
			raw_query: $raw_query,
			morphology_query: $prepared_query,
			limit: $limit,
			offset: $offset,
			user_locale: $locale
		);
	}
}