<?php

namespace Compass\Pivot;

/**
 * Класс action для форматирования каталога приложений для клиентов
 */
class Domain_SmartApp_Action_FormatSuggestionListV1 {

	/**
	 * Выполняем действие
	 *
	 * @param array $smart_app_suggested_list
	 * @param array $created_smart_app_list
	 *
	 * @return array
	 * @long
	 */
	public static function do(array $smart_app_suggested_list, array $created_smart_app_list):array {

		// получаем связь catalog_item_id => smart_app_id
		$catalog_item_id_smart_app_id_map = self::_formatSmartAppListToCreatedCatalogIdList($created_smart_app_list);

		$temp = [
			Domain_SmartApp_Entity_SuggestedCatalog::CATEGORY_POPULAR => [],
		];
		foreach ($smart_app_suggested_list as $item) {

			$suggested_item = Struct_Domain_SmartApp_SuggestedItem::rowToStruct($item);
			if (!isset($temp[$suggested_item->catalog_category])) {
				$temp[$suggested_item->catalog_category] = [];
			}

			// добавляем в категорию
			$temp[$suggested_item->catalog_category][] = $suggested_item;

			// добавляем в популярные, если нужно
			if ($suggested_item->is_popular === 1) {
				$temp[Domain_SmartApp_Entity_SuggestedCatalog::CATEGORY_POPULAR][] = $suggested_item;
			}
		}

		// сортируем популярные по убыванию sort_weight
		usort(
			$temp[Domain_SmartApp_Entity_SuggestedCatalog::CATEGORY_POPULAR],
			function(Struct_Domain_SmartApp_SuggestedItem $a, Struct_Domain_SmartApp_SuggestedItem $b) {

				return $b->sort_weight <=> $a->sort_weight;
			}
		);

		$output = [];
		foreach ($temp as $catalog => $list) {

			foreach ($list as $suggested_item) {

				if (!isset($output[$catalog])) {
					$output[$catalog] = [];
				}

				$smart_app_id       = $catalog_item_id_smart_app_id_map[$suggested_item->catalog_item_id] ?? 0;
				$output[$catalog][] = Apiv2_Format::smartAppSuggestedItem(
					$smart_app_id,
					$suggested_item->catalog_item_id,
					$suggested_item->is_popular,
					$suggested_item->catalog_category,
					$suggested_item->title,
					Domain_SmartApp_Entity_SuggestedCatalog::getCatalogSmartAppAvatar($suggested_item->catalog_item_id),
					$suggested_item->url,
					$suggested_item->is_need_custom_user_agent,
					$suggested_item->is_need_show_in_catalog,
				);
			}
		}

		return $output;
	}

	/**
	 * Получаем из smart_app_list связь catalog_id которые уже создали и smart_app_id
	 *
	 * @param array $created_smart_app_list
	 *
	 * @return array
	 */
	protected static function _formatSmartAppListToCreatedCatalogIdList(array $created_smart_app_list):array {

		$output = [];
		foreach ($created_smart_app_list as $smart_app) {

			// если catalog_item_id == 0, значит smart app создавался не из каталога
			if ($smart_app["catalog_item_id"] < 1) {
				continue;
			}

			$output[$smart_app["catalog_item_id"]] = $smart_app["smart_app_id"];
		}

		return $output;
	}
}