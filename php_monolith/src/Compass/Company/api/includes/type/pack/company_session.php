<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для кодирования/декодирования сущности company_session_map
 * все взаимодействие с company_session_map происходит в рамках этого класса
 * за его пределами company_session_map существует только как обычная строка
 */
class Type_Pack_CompanySession {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "company_session";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 1;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"session_uniq" => "a",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// метод для запаковки и получения company_session_map
	public static function doPack(string $session_uniq):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"session_uniq" => $session_uniq,
		];

		return self::_convertPacketToMap($packet, $packet_schema);
	}

	/**
	 * конвертируем пакет в map
	 *
	 * @param array $packet
	 * @param array $packet_schema
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _convertPacketToMap(array $packet, array $packet_schema):string {

		// упаковываем входящий массив
		$output = [];
		foreach ($packet as $key => $item) {

			// если во входящей структуре имеется некорректный ключ
			if (!isset($packet_schema[$key])) {
				throw new ParseFatalException("Passed incorrect packet schema in " . __METHOD__);
			}

			// добавляем ключ
			$output[$packet_schema[$key]] = $item;
		}

		// добавляем версию пакета
		$output["_"] = self::_CURRENT_MAP_VERSION;

		// добавляем название сущности
		$output["?"] = self::_MAP_ENTITY_TYPE;

		// получаем подпись
		$output["z"] = self::_getSignature($output);

		return toJson($output);
	}

	// получить session_uniq
	public static function getSessionUniq(string $session_map):string {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["session_uniq"];
	}

	// превратить map в key
	public static function doEncrypt(string $session_map):string {

		if (isset($GLOBALS["key_list"][$session_map])) {
			return $GLOBALS["key_list"][$session_map];
		}

		// формируем массив для зашифровки
		$arr = [
			"company_session_map" => $session_map,
		];

		// переводим сформированный company_session_map в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$iv_length   = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv          = substr(ENCRYPT_IV_COMPANY_SESSION, 0, $iv_length);
		$session_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_COMPANY_SESSION, 0, $iv);

		$GLOBALS["key_list"][$session_map] = $session_key;
		return $GLOBALS["key_list"][$session_map];
	}

	// превратить key в map
	public static function doDecrypt(string $session_key):string {

		if (isset($GLOBALS["map_list"][$session_key])) {
			return $GLOBALS["map_list"][$session_key];
		}

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(ENCRYPT_IV_COMPANY_SESSION, 0, $iv_length);
		$decrypt_result = openssl_decrypt($session_key, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_COMPANY_SESSION, 0, $iv);

		// если расшировка закончилась неудачно
		if (!$decrypt_result) {
			throw new \cs_DecryptHasFailed();
		}

		$decrypt_result = fromJson($decrypt_result);

		// проверяем наличие обязательных полей
		if (!isset($decrypt_result["company_session_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем company_session_map
		$GLOBALS["map_list"][$session_key] = $decrypt_result["company_session_map"];
		return $decrypt_result["company_session_map"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// конвертируем map в пакет
	protected static function _convertMapToPacket(string $session_map):array {

		if (isset($GLOBALS["packet_list"][$session_map])) {
			return $GLOBALS["packet_list"][$session_map];
		}
		$packet  = self::_unpackSessionMap($session_map);
		$version = self::_throwIfUnsupportedVersion($packet);

		$passed_sign = $packet["z"];
		unset($packet["z"]);

		self::_checkSignature($passed_sign, $packet);

		// убираем добавляемые поля
		unset($packet["_"]);
		unset($packet["?"]);

		// распаковываем пакет
		$output                = [];
		$flipped_packet_schema = array_flip(self::_PACKET_SCHEMA[$version]);

		foreach ($packet as $key => $item) {

			self::_throwIfReplacementKeyNotSet($flipped_packet_schema, $key);
			$output[$flipped_packet_schema[$key]] = $item;
		}
		$output["version"]                    = $version;
		$GLOBALS["packet_list"][$session_map] = $output;
		return $output;
	}

	// распаковываем session_map
	protected static function _unpackSessionMap(string $session_map):array {

		// получаем пакет из JSON
		$packet = fromJson($session_map);

		if (!isset($packet["_"], $packet["?"])) {
			throw new \cs_UnpackHasFailed();
		}

		// проверяем что передали ожидаемую сущность
		if ($packet["?"] != self::_MAP_ENTITY_TYPE) {
			throw new \cs_UnpackHasFailed();
		}

		return $packet;
	}

	// получаем версию пакета
	protected static function _throwIfUnsupportedVersion(array $packet):int {

		// получаем версию пакета
		$version = $packet["_"];

		// проверяем существование такой версии
		if (!isset(self::_PACKET_SCHEMA[$version])) {
			throw new \cs_UnpackHasFailed();
		}

		return $version;
	}

	// проверяем пришедшую подпись на подлинность
	protected static function _checkSignature(string $passed_sign, array $packet):void {

		// получаем подпись и сверяем с пришедшей
		$sign = self::_getSignature($packet);

		// если подпись не совпала
		if ($sign != $passed_sign) {
			throw new \cs_UnpackHasFailed();
		}
	}

	// получает подпись для пакета
	protected static function _getSignature(array $packet):string {

		// получаем версию пакета
		$version = $packet["_"];

		// сортируем массив пакета по ключам
		ksort($packet);

		// формируем подпись
		$string_for_sign = implode(",", $packet);

		$sign     = hash_hmac("sha1", $string_for_sign, SALT_PACK_COMPANY_SESSION[$version]);
		$sign_len = mb_strlen($sign);

		// получаем ее короткую версию (каждый 5 символ из подписи)
		$short_sign = "";
		for ($i = 1; $i <= $sign_len; $i++) {

			if ($i % 5 == 0) {
				$short_sign .= $sign[$i - 1];
			}
		}

		return $short_sign;
	}

	// выбрасываем ошибку, если заменяемый ключ отсутствует в пакете
	protected static function _throwIfReplacementKeyNotSet(array $flipped_packet_schema, string $key):void {

		if (!isset($flipped_packet_schema[$key])) {
			throw new \cs_UnpackHasFailed();
		}
	}
}