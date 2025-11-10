<?php

namespace Compass\Federation;

class Domain_Ldap_Entity_AuthToken_Data {

	/** @var int версия упаковщика */
	protected const _DATA_VERSION = 1;

	/** @var array[] схема data */
	protected const _DATA_SCHEME = [

		1 => [
			"entry" => [],
		],
	];

	/**
	 * Создать новую структуру data
	 *
	 * @return array
	 */
	public static function initData():array {

		return [
			"version" => self::_DATA_VERSION,
			"data"    => self::_DATA_SCHEME[self::_DATA_VERSION],
		];
	}

	/**
	 * Установить entry
	 *
	 * @param array $data
	 * @param array $entry
	 *
	 * @return array
	 */
	public static function setEntry(array $data, array $entry):array {

		$data                  = self::_actualizeData($data);
		$data["data"]["entry"] = $entry;

		return $data;
	}

    /**
     * Получаем entry
     *
     * @param array $data
     * @return array
     */
	public static function getEntry(array $data):array {

		$data                  = self::_actualizeData($data);
		return $data["data"]["entry"];
	}

	/**
	 * Получить актуальную структуру data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function _actualizeData(array $data):array {

		// если версия не совпадает - дополняем её до текущей
		if (!isset($data["version"]) || $data["version"] != self::_DATA_VERSION) {

			$data["data"]    = array_merge(self::_DATA_SCHEME[self::_DATA_VERSION], $data["data"] ?? []);
			$data["version"] = self::_DATA_VERSION;
		}

		return $data;
	}
}
