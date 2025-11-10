<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс для валидации данных для приложения
 */
class Domain_SmartApp_Entity_Validator {

	protected const _SIZE_LIST = [
		"s",
		"m",
		"xl",
	];

	/**
	 * проверяем корректность title
	 *
	 * @param string $title
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectTitle
	 */
	public static function assertCorrectTitle(string $title):void {

		if (isEmptyString($title)) {
			throw new Domain_SmartApp_Exception_IncorrectTitle("incorrect title");
		}
	}

	/**
	 * проверяем корректность catalog_item_id
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 */
	public static function assertCorrectCatalogItemId(int|false $catalog_item_id):void {

		if ($catalog_item_id !== false && $catalog_item_id < 1) {
			throw new Domain_SmartApp_Exception_IncorrectParam("incorrect catalog_item_id = {$catalog_item_id}");
		}
	}

	/**
	 * проверяем корректность флагов
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 */
	public static function assertCorrectFlag(int $flag):void {

		if (!in_array($flag, [0, 1])) {
			throw new Domain_SmartApp_Exception_IncorrectParam("incorrect flag = {$flag}");
		}
	}

	/**
	 * проверяем корректность размера
	 *
	 * @param string $size
	 *
	 * @return void
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 */
	public static function assertCorrectSize(string $size):void {

		if (!in_array($size, self::_SIZE_LIST)) {
			throw new Domain_SmartApp_Exception_IncorrectParam("incorrect size = {$size}");
		}
	}

	/**
	 * проверяем корректность smart app name
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectSmartAppUniqName
	 */
	public static function assertCorrectSmartAppUniqName(string $smart_app_name):void {

		if (isEmptyString($smart_app_name)) {
			throw new Domain_SmartApp_Exception_IncorrectSmartAppUniqName("empty smart_app_name");
		}
	}

	/**
	 * проверяем что smart app name уникальный в рамках команды
	 *
	 * @param string $smart_app_uniq_name
	 * @param int    $catalog_item_id
	 *
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_NotUniqSmartAppName
	 * @throws QueryFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function assertUniqSmartAppName(string $smart_app_uniq_name, int $catalog_item_id):void {

		// если создаем из каталога, то может быть не уникальным
		if ($catalog_item_id > 0) {
			return;
		}

		try {
			Gateway_Db_CompanyData_SmartAppList::getBySmartAppUniqName($smart_app_uniq_name);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {

			// если не нашли, значит имя уникальное
			return;
		}

		throw new Domain_SmartApp_Exception_NotUniqSmartAppName("incorrect smart_app_uniq_name");
	}

	/**
	 * проверяем корректность smart app url
	 *
	 * @param string $url
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectUrl
	 */
	public static function assertCorrectUrl(string $url):void {

		if (isEmptyString($url)) {
			throw new Domain_SmartApp_Exception_IncorrectUrl("empty smart_app_url");
		}
	}

	/**
	 * проверяем корректность avatar_file_key
	 *
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 */
	public static function assertCorrectAvatarFileKey(string|false $avatar_file_key):void {

		if ($avatar_file_key !== false && mb_strlen($avatar_file_key) < 1) {
			throw new Domain_SmartApp_Exception_IncorrectParam("incorrect avatar_file_key = {$avatar_file_key}");
		}
	}

	/**
	 * проверяем корректность entity
	 *
	 * @param string|false $entity
	 *
	 * @return bool
	 */
	public static function isCorrectEntity(string|false $entity):bool {

		return in_array($entity, [Domain_SmartApp_Entity_SmartApp::ENTITY_CONVERSATION, Domain_SmartApp_Entity_SmartApp::ENTITY_THREAD]);
	}
}