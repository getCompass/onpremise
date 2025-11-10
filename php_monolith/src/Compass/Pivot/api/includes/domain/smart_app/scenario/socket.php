<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * сценарии для сокет методов домена приложений
 */
class Domain_SmartApp_Scenario_Socket {

	/**
	 * получаем дефолтную аватарку приложения
	 *
	 * @return string
	 */
	public static function getDefaultAvatar():string {

		return Domain_SmartApp_Entity_SuggestedCatalog::getDefaultSmartAppAvatar();
	}

	/**
	 * получаем данные о приложении из каталога аватарку приложения
	 *
	 * @param int $catalog_item_id
	 *
	 * @return array
	 * @throws ParamException
	 */
	public static function getSmartAppCatalogItem(int $catalog_item_id):array {

		$smart_app_suggested_list = Domain_SmartApp_Entity_SuggestedCatalog::getSuggestedCatalog();
		$suggested_item           = null;
		foreach ($smart_app_suggested_list as $item) {

			$suggested_item = Struct_Domain_SmartApp_SuggestedItem::rowToStruct($item);
			if ($catalog_item_id === $suggested_item->catalog_item_id) {
				break;
			}
		}

		// если не нашли
		if (is_null($suggested_item)) {
			throw new ParamException("incorrect catalog_item_id");
		}

		return [
			$suggested_item->uniq_name,
			Domain_SmartApp_Entity_SuggestedCatalog::getCatalogSmartAppAvatar($suggested_item->catalog_item_id),
			$suggested_item->url,
		];
	}

	/**
	 * получаем список приложений из каталога
	 *
	 * @param array $catalog_item_id_list
	 *
	 * @return array
	 */
	public static function getSmartAppCatalogList(array $catalog_item_id_list):array {

		$smart_app_suggested_list = Domain_SmartApp_Entity_SuggestedCatalog::getSuggestedCatalog();
		$output                   = [];
		foreach ($smart_app_suggested_list as $item) {

			if (in_array($item["catalog_item_id"], $catalog_item_id_list)) {

				$temp_item                    = $item;
				$temp_item["avatar_file_key"] = Domain_SmartApp_Entity_SuggestedCatalog::getCatalogSmartAppAvatar($item["catalog_item_id"]);
				$output[]                     = $temp_item;
			}
		}

		return $output;
	}
}
