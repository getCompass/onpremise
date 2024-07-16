<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы со структурой поля data
 */
class Domain_Jitsi_Entity_Conference_Data {

	// версия упаковщика
	protected const _DATA_VERSION = 1;

	public const CONFERENCE_TYPE_DEFAULT = 0; // дефолтная конференция
	public const CONFERENCE_TYPE_SINGLE  = 1; // сингл звонок

	protected const _ALLOWED_CONFERENCE_TYPE = [
		self::CONFERENCE_TYPE_SINGLE,
		self::CONFERENCE_TYPE_DEFAULT,
	];

	// схема data
	protected const _DATA_SCHEME = [

		1 => [
			"conference_type"    => self::CONFERENCE_TYPE_DEFAULT,
			"opponent_user_id"   => 0,
			"conversation_map"   => "",
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
	 * Является ли сингл звонком
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function getConferenceType(array $data):int {

		$data = self::_getData($data);

		return $data["data"]["conference_type"];
	}

	/**
	 * Является ли сингл звонком
	 *
	 * @param array $data
	 * @param int   $value
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function setConferenceType(array $data, int $value):array {

		if (!in_array($value, self::_ALLOWED_CONFERENCE_TYPE, true)) {
			throw new ParseFatalException("invalid conference type");
		}

		$data                             = self::_getData($data);
		$data["data"]["conference_type"] = $value;

		return $data;
	}

	/**
	 * Получить id оппонента
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public static function getOpponentUserId(array $data):int {

		$data = self::_getData($data);

		return $data["data"]["opponent_user_id"];
	}

	/**
	 * Установить id оппонента
	 *
	 * @param array $data
	 * @param int   $value
	 *
	 * @return array
	 */
	public static function setOpponentUserId(array $data, int $value):array {

		$data                              = self::_getData($data);
		$data["data"]["opponent_user_id"] = $value;

		return $data;
	}

	/**
	 * Получить мапу чата
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public static function getConversationMap(array $data):string {

		$data = self::_getData($data);

		return $data["data"]["conversation_map"];
	}

	/**
	 * Установить мапу чата
	 *
	 * @param array $data
	 * @param int   $value
	 *
	 * @return array
	 */
	public static function setConversationMap(array $data, string $value):array {

		$data                              = self::_getData($data);
		$data["data"]["conversation_map"] = $value;

		return $data;
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function _getData(array $data):array {

		// если версия не совпадает - дополняем её до текущей
		if (!isset($data["version"]) || $data["version"] != self::_DATA_VERSION) {

			$data["data"]    = array_merge(self::_DATA_SCHEME[self::_DATA_VERSION], $data["data"] ?? []);
			$data["version"] = self::_DATA_VERSION;
		}

		return $data;
	}
}