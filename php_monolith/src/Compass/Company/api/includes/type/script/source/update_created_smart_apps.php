<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Скрипт для обновления созданных приложений из каталога
 *
 * Безопасен для повторного исполнения.
 *
 * @since 24.06.25
 */
class Type_Script_Source_UpdateCreatedSmartApps extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Выполняем скрипт
	 *
	 * @param array $data
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @long
	 */
	public function exec(array $data):void {

		if (count($data) < 1) {

			$this->_error("Не передан список catalog_item_id которые необходимо обновить");
			return;
		}

		$catalog_item_id_list = [];
		foreach ($data as $item) {
			$catalog_item_id_list[] = (int) $item;
		}

		// получаем список приложений созданных из каталога
		$smart_app_list = Gateway_Db_CompanyData_SmartAppList::getListCreatedFromCatalog();
		if (count($smart_app_list) < 1) {
			return;
		}

		// dry-run
		if ($this::_isDry()) {

			$this->_log("DRY-RUN - Обновили приложения, company_id = " . COMPANY_ID);
			return;
		}

		// получаем каталог с pivot
		$smart_app_catalog_list = Gateway_Socket_Pivot::getSmartAppCatalogList($catalog_item_id_list);
		$smart_app_catalog_list = self::_getAsocCatalogList($smart_app_catalog_list);

		foreach ($smart_app_list as $smart_app) {

			// если приложение не менялось, то пропускаем
			if (!isset($smart_app_catalog_list[$smart_app->catalog_item_id])) {
				continue;
			}

			$smart_app_catalog_item = $smart_app_catalog_list[$smart_app->catalog_item_id];

			$smart_app_set = [];
			if ($smart_app->smart_app_uniq_name !== $smart_app_catalog_item["uniq_name"]) {
				$smart_app_set["smart_app_uniq_name"] = $smart_app_catalog_item["uniq_name"];
			}
			if (Domain_SmartApp_Entity_SmartApp::getUrl($smart_app->extra) !== $smart_app_catalog_item["url"]) {

				$smart_app->extra       = Domain_SmartApp_Entity_SmartApp::setUrl($smart_app->extra, $smart_app_catalog_item["url"]);
				$smart_app_set["extra"] = $smart_app->extra;
			}

			if (count($smart_app_set) > 0) {
				Gateway_Db_CompanyData_SmartAppList::set($smart_app->smart_app_id, $smart_app_set);
			}

			$user_rel_list = Gateway_Db_CompanyData_SmartAppUserRel::getListBySmartAppId($smart_app->smart_app_id);
			foreach ($user_rel_list as $user_rel) {

				$user_rel_set = [];
				if (Domain_SmartApp_Entity_SmartAppUserRel::getTitle($user_rel->extra) !== $smart_app_catalog_item["title"]) {

					$user_rel->extra = Domain_SmartApp_Entity_SmartAppUserRel::setTitle($user_rel->extra, $smart_app_catalog_item["title"]);;
					$user_rel_set["extra"] = $user_rel->extra;
				}

				if (Domain_SmartApp_Entity_SmartAppUserRel::getAvatarFileKey($user_rel->extra) !== $smart_app_catalog_item["avatar_file_key"]) {

					$user_rel->extra       = Domain_SmartApp_Entity_SmartAppUserRel::setAvatarFileKey($user_rel->extra, $smart_app_catalog_item["avatar_file_key"]);
					$user_rel_set["extra"] = $user_rel->extra;
				}

				if (count($user_rel_set) > 0) {
					Gateway_Db_CompanyData_SmartAppUserRel::set($smart_app->smart_app_id, $user_rel->user_id, $user_rel_set);
				}
			}
		}

		$this->_log("Обновили приложения, company_id = " . COMPANY_ID);
	}

	/**
	 * Форматируем каталог
	 *
	 * @param array $catalog_list
	 *
	 * @return array
	 */
	protected static function _getAsocCatalogList(array $catalog_list):array {

		$output = [];
		foreach ($catalog_list as $item) {
			$output[$item["catalog_item_id"]] = $item;
		}

		return $output;
	}
}