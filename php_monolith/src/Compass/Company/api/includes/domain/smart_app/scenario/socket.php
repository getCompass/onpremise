<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Сценарии для сокетов приложений
 */
class Domain_SmartApp_Scenario_Socket {

	/**
	 * Получаем список приложений созданных пользователем
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getCreatedSmartAppListByUser(int $user_id):array {

		$smart_app_user_rel_count = Gateway_Db_CompanyData_SmartAppUserRel::getActiveCountByUserId($user_id);
		$smart_app_user_rel_list  = Gateway_Db_CompanyData_SmartAppUserRel::getActiveListByUserId($user_id, $smart_app_user_rel_count);

		$smart_app_id_list = [];
		foreach ($smart_app_user_rel_list as $smart_app_user_rel) {
			$smart_app_id_list[] = $smart_app_user_rel->smart_app_id;
		}

		return Gateway_Db_CompanyData_SmartAppList::getList($smart_app_id_list);
	}

	/**
	 * Получаем статистику по количеству созданных приложений из каталога
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getCreatedSmartAppsStatFromSuggestionList():array {

		// получаем список приложений созданных из каталога
		$smart_app_list = Gateway_Db_CompanyData_SmartAppList::getListCreatedFromCatalog();
		if (count($smart_app_list) < 1) {
			return [];
		}

		$output = [];
		foreach ($smart_app_list as $smart_app) {

			// фильтруем старые некорректные данные от клиентов
			if ($smart_app->catalog_item_id < 0) {
				continue;
			}

			$active_count                        = Gateway_Db_CompanyData_SmartAppUserRel::getActiveCountBySmartAppId($smart_app->smart_app_id);
			$output[$smart_app->catalog_item_id] = $active_count;
		}

		return $output;
	}

	/**
	 * Получаем созданные приложения НЕ из каталога
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getCreatedPersonalSmartApps():array {

		// получаем список приложений созданных НЕ из каталога
		$smart_app_list = Gateway_Db_CompanyData_SmartAppList::getListCreatedNotFromCatalog();
		if (count($smart_app_list) < 1) {
			return [];
		}

		$output = [];
		foreach ($smart_app_list as $smart_app) {

			try {
				$smart_app_user_rel = Gateway_Db_CompanyData_SmartAppUserRel::getOne($smart_app->smart_app_id, $smart_app->creator_user_id);
			} catch (Domain_SmartApp_Exception_SmartAppNotFound) {
				continue;
			}

			// формируем ответ
			$output[] = [
				"title"               => (string) Domain_SmartApp_Entity_SmartAppUserRel::getTitle($smart_app_user_rel->extra),
				"smart_app_uniq_name" => (string) $smart_app->smart_app_uniq_name,
				"url"                 => (string) Domain_SmartApp_Entity_SmartApp::getUrl($smart_app->extra),
				"creator_user_id"     => (int) $smart_app_user_rel->user_id,
				"space_id"            => (int) COMPANY_ID,
				"is_deleted"          => (int) $smart_app_user_rel->status !== Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE,
			];
		}

		return $output;
	}
}