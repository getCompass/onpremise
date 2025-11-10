<?php

namespace Compass\Company;

/**
 * Сокеты для работы с приложениями
 */
class Socket_Smartapp extends \BaseFrame\Controller\Socket {

	/** @var string[] поддерживаемые методы */
	public const ALLOW_METHODS = [
		"getCreatedSmartAppListByUser",
		"getCreatedSmartAppsStatFromSuggestionList",
		"getCreatedPersonalSmartApps",
	];

	/**
	 * Получаем список приложений созданных пользователем
	 */
	public function getCreatedSmartAppListByUser():array {

		$created_smart_app_list = Domain_SmartApp_Scenario_Socket::getCreatedSmartAppListByUser($this->user_id);

		return $this->ok([
			"created_smart_app_list" => (array) $created_smart_app_list,
		]);
	}

	/**
	 * Получаем статистику по количеству созданных приложений из каталога
	 */
	public function getCreatedSmartAppsStatFromSuggestionList():array {

		$suggestion_stat_list = Domain_SmartApp_Scenario_Socket::getCreatedSmartAppsStatFromSuggestionList();

		return $this->ok([
			"suggestion_stat_list" => (array) $suggestion_stat_list,
		]);
	}

	/**
	 * Получаем созданныt приложения НЕ из каталога
	 */
	public function getCreatedPersonalSmartApps():array {

		$smart_app_list = Domain_SmartApp_Scenario_Socket::getCreatedPersonalSmartApps();

		return $this->ok([
			"smart_app_list" => (array) $smart_app_list,
		]);
	}
}