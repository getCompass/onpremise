<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовый класс для работы с локациями поиска.
 */
class Domain_Search_Entity_LocationHandler {

	/** @var Domain_Search_Entity_Location[] список известных типов локаций */
	protected const _KNOWN_LOCATION_TYPE_LIST = [
		Domain_Search_Entity_Conversation_Location::class,
		Domain_Search_Entity_Thread_Location::class,
	];

	/**
	 * Загружает указанный список локаций.
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawLocation[]     $raw_location_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 *
	 * @noinspection DuplicatedCode
	 * @return array
	 */
	public static function load(int $user_id, array $raw_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		// снимаем метрику производительности
		$execution_time_metric = \BaseFrame\Monitor\Core::metric("load_locations_execution_time_ms")->since();

		$raw_location_list_grouped_by_type = [];

		foreach ($raw_location_list as $raw_location) {
			$raw_location_list_grouped_by_type[$raw_location->type][] = $raw_location;
		}

		$output = [];

		// проходимся по всем известным типа локаций и
		// пытаемся загрузить соответствующие локации
		foreach (static::_KNOWN_LOCATION_TYPE_LIST as $location_class) {

			if (!isset($raw_location_list_grouped_by_type[$location_class::LOCATION_TYPE])) {
				continue;
			}

			$output = $location_class::loadSuitable($user_id, $raw_location_list, $params);
		}

		// фиксируем и закрываем метрику
		$execution_time_metric->since()->seal();
		return array_filter($output);
	}

	/**
	 * Загружает указанный вложенных список локаций.
	 *
	 * @param int                                         $user_id
	 * @param Struct_Domain_Search_RawHitNestedLocation[] $raw_location_nested_location_list
	 * @param Struct_Domain_Search_Dto_SearchRequest      $params
	 *
	 * @noinspection DuplicatedCode
	 * @return array
	 */
	public static function loadNested(int $user_id, array $raw_location_nested_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		$raw_location_list_grouped_by_type = [];

		foreach ($raw_location_nested_location_list as $raw_location_nested_location) {
			$raw_location_list_grouped_by_type[$raw_location_nested_location->location->entity_rel->entity_type][] = $raw_location_nested_location;
		}

		$output = [];

		// проходимся по всем известным типа локаций и
		// пытаемся загрузить соответствующие локации
		foreach (static::_KNOWN_LOCATION_TYPE_LIST as $location_class) {

			if (!isset($raw_location_list_grouped_by_type[$location_class::LOCATION_TYPE])) {
				continue;
			}

			$output = $location_class::loadNested($user_id, $raw_location_nested_location_list, $params);
		}

		return array_filter($output);
	}

	/**
	 * Проверяет наличие доступа для определенной локации.
	 * @throws Domain_Search_Exception_LocationDenied
	 */
	public static function checkAccess(int $user_id, int $type, string $key, bool $is_restricted_access):void {

		foreach (static::_KNOWN_LOCATION_TYPE_LIST as $location_class) {

			if ($location_class::LOCATION_TYPE === $type) {

				$location_class::checkAccess($user_id, $key, $is_restricted_access);
				return;
			}
		}

		throw new Domain_Search_Exception_LocationDenied("passed incorrect location type");
	}

	/**
	 * Проверяет локацию на корректность.
	 * @throws Domain_Search_Exception_IncorrectLocation
	 */
	public static function convertTypes(array $type_list):array {

		$output = [];

		foreach ($type_list as $type) {

			$is_known = false;

			foreach (static::_KNOWN_LOCATION_TYPE_LIST as $location_class) {

				if ($location_class::API_LOCATION_TYPE === $type) {

					$output[] = $location_class::LOCATION_TYPE;
					$is_known = true;

					break;
				}
			}

			!$is_known && throw new Domain_Search_Exception_IncorrectLocation("passed incorrect location type");
		}

		return $output;
	}

	/**
	 * Конвертирует данные локации, пришедшие извне.
	 * @throws Domain_Search_Exception_IncorrectLocation
	 */
	public static function fromApi(int $type, string $key):string {

		foreach (static::_KNOWN_LOCATION_TYPE_LIST as $location_class) {

			if ($location_class::LOCATION_TYPE === $type) {
				return $location_class::fromApi($key);
			}
		}

		throw new Domain_Search_Exception_IncorrectLocation("passed incorrect location");
	}

	/**
	 * Готовит локации для отдачи на клиент.
	 */
	public static function toApi(int $user_id, array $location_list):array {

		$output = [];

		foreach ($location_list as $hit) {

			$output[] = match ($hit::class) {

				Struct_Domain_Search_Location_Conversation::class => Domain_Search_Entity_Conversation_Location::toApi($hit, $user_id),
				Struct_Domain_Search_Location_Thread::class       => Domain_Search_Entity_Thread_Location::toApi($hit, $user_id),
				default                                           => throw new ParseFatalException("passed unknown object type")
			};
		}

		return $output;
	}
}
