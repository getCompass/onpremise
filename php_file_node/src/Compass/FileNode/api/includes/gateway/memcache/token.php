<?php

namespace Compass\FileNode;

/**
 * Класс для взаимодействия с токеном, по которому грузится файл
 */
class Gateway_Memcache_Token {

	protected const _TOKEN_EXPIRE_TIME = 60 * 60;

	// типы токенов
	public const UPLOAD_TOKEN_TYPE = 0;
	public const CROP_TOKEN_TYPE   = 1;

	// версии схемы
	public const UPLOAD_VERSION = 2;
	public const CROP_VERSION   = 2;

	// структура версий загрузки
	protected const _UPLOAD_SCHEMA = [
		1 => [
			"user_id"     => 0,
			"file_source" => "",
		],
		2 => [
			"user_id"     => 0,
			"file_source" => "",
			"company_url" => "",
		],
	];

	// структура версий кропа
	protected const _CROP_SCHEMA = [
		1 => [
			"user_id"     => 0,
			"file_key"    => "",
			"file_url"    => "",
			"file_name"   => "",
			"file_width"  => 0,
			"file_height" => 0,
		],
		2 => [
			"user_id"     => 0,
			"file_key"    => "",
			"file_url"    => "",
			"file_name"   => "",
			"file_width"  => 0,
			"file_height" => 0,
			"company_url" => "",
			"node_id"     => 0,
			"company_id"  => 0,
		],
	];

	// записать токен в память
	public static function addToken(string $token, int $user_id, int $file_source, int $company_id, string $company_url):void {

		$value = self::_makeUploadCacheStructure($user_id, $file_source, $company_id, $company_url);
		\mCache::Init()->set(self::_getKey($token), $value, self::_TOKEN_EXPIRE_TIME);
	}

	// делаем данные upload для кеша
	protected static function _makeUploadCacheStructure(int $user_id, string $file_source, int $company_id, string $company_url):string {

		$upload_data                = self::_UPLOAD_SCHEMA[self::UPLOAD_VERSION];
		$upload_data["user_id"]     = $user_id;
		$upload_data["file_source"] = $file_source;
		$upload_data["company_id"]  = $company_id;
		$upload_data["company_url"]   = $company_url;

		$output = [
			"data"    => $upload_data,
			"type"    => self::UPLOAD_TOKEN_TYPE,
			"version" => self::UPLOAD_VERSION,
		];

		return toJson($output);
	}

	// записать токен для кропа в память
	public static function addTokenForCrop(string $token, int $user_id, string $file_key, string $file_url, string $file_name, int $file_width, int $file_height, int $company_id, string $company_url):void {

		$value = self::_makeCropCacheStructure($user_id, $file_key, $file_url, $file_name, $file_width, $file_height, $company_id, $company_url);
		\mCache::Init()->set(self::_getKey($token), $value, self::_TOKEN_EXPIRE_TIME);
	}

	// делаем данные Crop для кеша
	protected static function _makeCropCacheStructure(int $user_id, string $file_key, string $file_url, string $file_name, int $file_width, int $file_height, int $company_id, string $company_url):string {

		$crop_data                = self::_CROP_SCHEMA[self::CROP_VERSION];
		$crop_data["user_id"]     = $user_id;
		$crop_data["file_key"]    = $file_key;
		$crop_data["file_url"]    = $file_url;
		$crop_data["file_name"]   = $file_name;
		$crop_data["file_width"]  = $file_width;
		$crop_data["file_height"] = $file_height;
		$crop_data["company_id"]  = $company_id;
		$crop_data["company_url"] = $company_url;
		$crop_data["node_id"]     = NODE_ID;

		$output = [
			"data"    => $crop_data,
			"type"    => self::CROP_TOKEN_TYPE,
			"version" => self::CROP_VERSION,
		];

		return toJson($output);
	}

	/**
	 * возвращает данные по токену
	 */
	public static function getDataByToken(string $token, int $token_type):mixed {

		$txt  = \mCache::Init()->get(self::_getKey($token));
		$temp = fromJson($txt);

		// если токена не существует или типы не совпали то отдаем false
		if (!isset($temp["type"]) || $temp["type"] !== $token_type) {
			return false;
		}

		return $temp["data"];
	}

	// удаляет токен
	public static function removeToken(string $token):void {

		\mCache::Init()->delete(self::_getKey($token));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить ключ для mCache
	protected static function _getKey(string $token):string {

		return __CLASS__ . $token;
	}
}