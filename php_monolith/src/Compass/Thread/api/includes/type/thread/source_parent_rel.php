<?php

namespace Compass\Thread;

/*
 *
 * методы для работы с JSON полем source_parent_rel
 * содержит указатель на сущность, на основе которой тред
 * актуализирует информацию
 *
 * структура source_parent_rel
 * array
 * (
 *    [version] => 1
 *    [type] => 1
 *    [map] => conversation_map
 * )
 */

class Type_Thread_SourceParentRel {

	protected const _META_REL_VERSION = 2;
	protected const _META_REL_SCHEMA  = [
		1 => [
			"type"                          => 0,
			"map"                           => "",
		],
		2 => [
			"type"                          => 0,
			"map"                           => "",
		],
	];

	// создаем структуру для source_parent_rel
	public static function create(string $source_parent_map , int $type):array {

		// получаем текущую схему meta_rel
		$meta_rel_schema = self::_META_REL_SCHEMA[self::_META_REL_VERSION];

		// устанавливаем персональные параметры
		$meta_rel_schema["map"]  = $source_parent_map ;
		$meta_rel_schema["type"] = $type;

		// устанавливаем текущую версию
		$meta_rel_schema["version"] = self::_META_REL_VERSION;

		return $meta_rel_schema;
	}

	// получить тип сущности source_parent_rel
	public static function getType(array $source_parent_rel):int {

		$meta_rel_schema = self::_getMetaRelSchema($source_parent_rel);
		return $meta_rel_schema["type"];
	}

	// получить map указатель на сущность source_parent_rel
	public static function getMap(array $source_parent_rel):string {

		$meta_rel_schema = self::_getMetaRelSchema($source_parent_rel);
		return $meta_rel_schema["map"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить актуальную структуру для source_parent_rel
	protected static function _getMetaRelSchema(array $source_parent_rel):array {

		// если версия не совпадает - дополняем её до текущей
		if ($source_parent_rel[ "version"] != self::_META_REL_VERSION) {

			$source_parent_rel            = array_merge(self::_META_REL_SCHEMA, $source_parent_rel);
			$source_parent_rel[ "version"] = self::_META_REL_VERSION;
		}

		return $source_parent_rel;
	}
}