<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс для взаимодействия с smart app
 */
class Domain_SmartApp_Entity_SmartApp {

	public const ENTITY_CONVERSATION = "conversation";
	public const ENTITY_THREAD       = "thread";

	/**
	 * создание записи с ботом
	 *
	 * @param int    $creator_user_id
	 * @param int    $catalog_item_id
	 * @param string $smart_app_uniq_name
	 * @param string $url
	 * @param string $public_key
	 * @param string $private_key
	 *
	 * @return Struct_Db_CompanyData_SmartAppList
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @long
	 */
	public static function create(int    $creator_user_id, int $catalog_item_id, string $smart_app_uniq_name,
						string $url, string $public_key, string $private_key):Struct_Db_CompanyData_SmartAppList {

		$extra = self::initExtra();

		$extra      = self::setUrl($extra, $url);
		$extra      = self::setPublicKey($extra, $public_key);
		$extra      = self::setPrivateKey($extra, $private_key);
		$created_at = time();

		try {
			return Gateway_Db_CompanyData_SmartAppList::getBySmartAppUniqName($smart_app_uniq_name);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {
			$smart_app_id = Gateway_Db_CompanyData_SmartAppList::insert($smart_app_uniq_name, $creator_user_id, $catalog_item_id, $created_at, $extra);
		}

		return new Struct_Db_CompanyData_SmartAppList(
			$smart_app_id,
			$catalog_item_id,
			$creator_user_id,
			$created_at,
			0,
			$smart_app_uniq_name,
			$extra
		);
	}

	/**
	 * удаляем приложение
	 *
	 * @param Struct_Db_CompanyData_SmartAppList $smart_app
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function delete(Struct_Db_CompanyData_SmartAppList $smart_app):void {

		// приложения из каталога не нужно изменять
		if ($smart_app->catalog_item_id > 0) {
			return;
		}

		Gateway_Db_CompanyData_SmartAppList::set($smart_app->smart_app_id, [
			"smart_app_uniq_name" => $smart_app->smart_app_uniq_name,
			"updated_at"          => time(),
		]);
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"url"         => "",
			"public_key"  => "",
			"private_key" => "",
		],
	];

	/**
	 * создаём новую структуру для extra
	 *
	 * @return array
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * установим url приложения
	 */
	public static function setUrl(array $extra, string $url):array {

		$extra                 = self::_getExtra($extra);
		$extra["extra"]["url"] = $url;
		return $extra;
	}

	/**
	 * получим url приложения
	 */
	public static function getUrl(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["url"];
	}

	/**
	 * установим публичный ключ приложения
	 */
	public static function setPublicKey(array $extra, string $public_key):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["public_key"] = $public_key;
		return $extra;
	}

	/**
	 * получим публичный ключ приложения
	 */
	public static function getPublicKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["public_key"];
	}

	/**
	 * установим приватный ключ приложения
	 */
	public static function setPrivateKey(array $extra, string $private_key):array {

		$extra                         = self::_getExtra($extra);
		$extra["extra"]["private_key"] = $private_key;
		return $extra;
	}

	/**
	 * получим приватный ключ приложения
	 */
	public static function getPrivateKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["private_key"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получим актуальную структуру для extra
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}