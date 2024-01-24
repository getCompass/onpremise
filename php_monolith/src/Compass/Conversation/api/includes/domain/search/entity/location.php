<?php

namespace Compass\Conversation;

/**
 * Базовый класс локации.
 * Нужен в основном для типизации и удобного рефакторинга.
 */
abstract class Domain_Search_Entity_Location {

	public const LOCATION_TYPE     = 0;
	public const API_LOCATION_TYPE = "";

	/**
	 * Проверяет наличие доступа к указанной локации.
	 */
	abstract public static function checkAccess(int $user_id, string $key, bool $is_restricted_access):void;

	/**
	 * Возвращает список локаций, доступных пользователю.
	 * На вход получает результат поиска в виде массива ключей локаций.
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawLocation[]     $raw_location_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 *
	 * @return array
	 */
	abstract public static function loadSuitable(int $user_id, array $raw_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array;

	/**
	 * Формирует локацию из совпадения.
	 *
	 * @param int                                         $user_id
	 * @param Struct_Domain_Search_RawHitNestedLocation[] $raw_hit_nested_location_list
	 * @param Struct_Domain_Search_Dto_SearchRequest      $params
	 *
	 * @return Struct_Domain_Search_Location_Thread[]
	 */
	abstract public static function loadNested(int $user_id, array $raw_hit_nested_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array;


	/**
	 * Конвертирует внешний ключ локации во внутренний.
	 * @throws Domain_Search_Exception_IncorrectLocation
	 */
	abstract public static function fromApi(string $key):string;
}