<?php

namespace Compass\Conversation;

/**
 * Класс-структура для содержимого таблицы space_search.entity_search_id_rel_{f}.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_SpaceSearch_EntitySearchIdRel {

	/**
	 * Класс-структура для содержимого таблицы space_search.entity_search_id_rel_{f}.
	 */
	public function __construct(
		public string $entity_id,
		public int    $search_id,
		public int    $entity_type,
		public string $entity_map
	) {

	}
}
