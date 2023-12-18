<?php

namespace Compass\Thread;

/**
 * методы для работы с JSON полем parent_rel
 * содержит указатель на родительскую сущность треда
 *
 * структура parent_rel
 * array
 * (
 *    [version] => 1
 *    [type] => 1
 *    [creator_user_id] => 1
 *    [created_at] => 1581049503
 *    [map] => {"a":1,"b":"2018_7","c":9,"d":7,"_":1,"?":"conversation","z":"b8e353d1"}
 *    [is_deleted] => 0
 * )
 *
 */
class Type_Thread_ParentRel {

	protected const _PARENT_REL_VERSION = 4;
	protected const _PARENT_REL_SCHEMA  = [
		1 => [
			"type"            => 0,
			"creator_user_id" => 0,
			"map"             => "",
		],

		2 => [
			"type"            => 0,
			"creator_user_id" => 0,
			"created_at"      => 0,
			"map"             => "",
		],

		3 => [
			"type"            => 0,
			"creator_user_id" => 0,
			"created_at"      => 0,
			"map"             => "",
			"is_deleted"      => 0,
		],

		self::_PARENT_REL_VERSION => [
			"type"                        => 0,
			"creator_user_id"             => 0,
			"created_at"                  => 0,
			"map"                         => "",
			"is_deleted"                  => 0,
			"message_hidden_by_user_list" => [],
		],
	];

	// создаем структуру для parent_rel
	public static function create(int $type, int $creator_user_id, string $parent_entity_map, int $created_at, array $message_hidden_by_user_list):array {

		// получаем текущую схему parent_rel
		$parent_rel_schema = self::_PARENT_REL_SCHEMA[self::_PARENT_REL_VERSION];

		// устанавливаем персональные параметры
		$parent_rel_schema["type"]                        = $type;
		$parent_rel_schema["creator_user_id"]             = $creator_user_id;
		$parent_rel_schema["created_at"]                  = $created_at;
		$parent_rel_schema["map"]                         = $parent_entity_map;
		$parent_rel_schema["is_deleted"]                  = 0;
		$parent_rel_schema["message_hidden_by_user_list"] = $message_hidden_by_user_list;

		// устанавливаем текущую версию
		$parent_rel_schema["version"] = self::_PARENT_REL_VERSION;

		return $parent_rel_schema;
	}

	// получить тип сущности parent_rel
	public static function getType(array $parent_rel):int {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return $parent_rel_schema["type"];
	}

	// получить map указатель на сущность parent_rel
	public static function getMap(array $parent_rel):string {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return $parent_rel_schema["map"];
	}

	// получить creator_user_id сущности parent_rel
	public static function getCreatorUserId(array $parent_rel):int {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return $parent_rel_schema["creator_user_id"];
	}

	// получить юзеров, которые скрыли сообщение из сущности parent_rel
	public static function getMessageHiddenByUserId(array $parent_rel):array {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return (array) $parent_rel_schema["message_hidden_by_user_list"];
	}

	// true - скрыть пользователям тред, false - открыть пользователям тред
	public static function setMessageHiddenOrUnhiddenByUserId(array $parent_rel, int $user_id, bool $true_if_hidden = true):array {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);

		if ($true_if_hidden) {
			$parent_rel_schema["message_hidden_by_user_list"][] = $user_id;
		} else {

			$key = array_search($user_id, $parent_rel_schema["message_hidden_by_user_list"]);
			if ($key !== false) {
				unset($parent_rel_schema["message_hidden_by_user_list"][$key]);
			}
		}

		$parent_rel_schema["message_hidden_by_user_list"] = array_unique($parent_rel_schema["message_hidden_by_user_list"]);

		return (array) $parent_rel_schema;
	}

	// проверить юзера на скрытость сообщения из сущности parent_rel
	public static function isMessageHiddenByUserId(array $parent_rel, int $user_id):bool {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return in_array($user_id, $parent_rel_schema["message_hidden_by_user_list"]);
	}

	// получить created_at сущности parent_rel
	public static function getCreatedAt(array $parent_rel):int {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return $parent_rel_schema["created_at"] == 0 ? time() : $parent_rel_schema["created_at"];
	}

	// получаем is_deleted родительского сообщения
	public static function getIsDeleted(array $parent_rel):bool {

		$parent_rel_schema = self::_getParentRelSchema($parent_rel);
		return $parent_rel_schema["is_deleted"] == 1;
	}

	// устанавливаем is_deleted
	public static function setIsDeleted(array $parent_rel, bool $is_deleted):array {

		$parent_rel_schema               = self::_getParentRelSchema($parent_rel);
		$parent_rel_schema["is_deleted"] = $is_deleted ? 1 : 0;

		return $parent_rel_schema;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить актуальную структуру для parent_rel
	protected static function _getParentRelSchema(array $parent_rel):array {

		// если версия не совпадает - дополняем её до текущей
		if ($parent_rel["version"] != self::_PARENT_REL_VERSION) {

			$parent_rel            = array_merge(self::_PARENT_REL_SCHEMA[self::_PARENT_REL_VERSION], $parent_rel);
			$parent_rel["version"] = self::_PARENT_REL_VERSION;
		}

		return $parent_rel;
	}
}