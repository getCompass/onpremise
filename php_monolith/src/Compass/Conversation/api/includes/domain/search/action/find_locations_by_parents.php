<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Поиск локаций, содержащих совпадения для указанного поискового запроса по id родителей.
 */
class Domain_Search_Action_FindLocationsByParents {

	/**
	 * Выполняет поиск локаций указанных типов, содержащих совпадения по id родителей.
	 */
	#[ArrayShape([0 => "Struct_Domain_Search_RawLocation[]", 1 => "bool"])]
	public static function run(int $user_id, array $parent_id_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		$expected_hit_type_list = [
			Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
			Domain_Search_Const::TYPE_THREAD_MESSAGE,
			Domain_Search_Const::TYPE_PREVIEW,
			Domain_Search_Const::TYPE_FILE,
		];

		// получаем данные по локациям из поисковой таблицы
		$location_row_list = Gateway_Search_Main::getLocationsByParentId(
			$user_id, $params->location_type_list, $expected_hit_type_list, $parent_id_list, $params->morphology_query, $params->limit, $params->offset
		);

		// и сразу загружаем связи с сущностями для них
		$search_entity_rel = Domain_Search_Repository_ProxyCache_SearchIdEntity::load(array_column($location_row_list, "search_id"));

		$output = [];

		foreach ($location_row_list as $location_row) {

			// такого вообще не должно быть, но на всякий добавим проверку
			if (!isset($search_entity_rel[$location_row->search_id])) {
				continue;
			}

			$output[] = new Struct_Domain_Search_RawLocation(
				$search_entity_rel[$location_row->search_id]->entity_map,
				$search_entity_rel[$location_row->search_id]->entity_type,
				$location_row->hit_count,
				$location_row->last_hit_at,
				[]
			);
		}

		return [$output, count($location_row_list) >= $params->limit];
	}
}