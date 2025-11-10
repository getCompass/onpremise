<?php declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер сокет методов для взаимодействия с приложениями
 */
class Socket_SmartApp extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getDefaultAvatar",
		"getSmartAppCatalogItem",
		"getSmartAppCatalogList",
	];

	/**
	 * получаем дефолтную аватарку приложения
	 *
	 * @return array
	 */
	public function getDefaultAvatar():array {

		$avatar_file_key = Domain_SmartApp_Scenario_Socket::getDefaultAvatar();

		return $this->ok([
			"avatar_file_key" => (string) $avatar_file_key,
		]);
	}

	/**
	 * получаем данные о приложении из каталога аватарку приложения
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getSmartAppCatalogItem():array {

		$catalog_item_id = $this->post("?i", "catalog_item_id");

		[$uniq_name, $avatar_file_key, $url] = Domain_SmartApp_Scenario_Socket::getSmartAppCatalogItem($catalog_item_id);

		return $this->ok([
			"uniq_name"       => (string) $uniq_name,
			"avatar_file_key" => (string) $avatar_file_key,
			"url"             => (string) $url,
		]);
	}

	/**
	 * получаем данные о приложении из каталога аватарку приложения
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getSmartAppCatalogList():array {

		$catalog_item_id_list = $this->post("?a", "catalog_item_id_list");

		$smart_app_list = Domain_SmartApp_Scenario_Socket::getSmartAppCatalogList($catalog_item_id_list);

		return $this->ok([
			"smart_app_list" => (array) $smart_app_list,
		]);
	}
}
