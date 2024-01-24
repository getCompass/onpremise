<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для кодирования/декодирования сущности 2fa_map
 */
class Type_Pack_TwoFa {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "2fa";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 2;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"2fa_uniq"   => "a",
			"shard_id"   => "b",
			"created_at" => "d",
		],
		2 => [
			"2fa_uniq"   => "a",
			"shard_id"   => "b",
			"table_id"   => "c",
			"created_at" => "d",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * метод для запаковки и получения two_fa_map
	 *
	 * @throws \parseException
	 */
	public static function doPack(string $two_fa_uniq, string $shard_id, string $table_id, int $created_at):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"2fa_uniq"   => $two_fa_uniq,
			"shard_id"   => $shard_id,
			"table_id"   => $table_id,
			"created_at" => $created_at,
		];

		return self::_convertPacketToMap($packet, $packet_schema);
	}

	/**
	 * конвертируем пакет в map
	 *
	 * @throws \parseException
	 */
	protected static function _convertPacketToMap(array $packet, array $packet_schema):string {

		// упаковываем входящий массив
		$output = [];
		foreach ($packet as $key => $item) {

			// если во входящей структуре имеется некорректный ключ
			if (!isset($packet_schema[$key])) {
				throw new ParseFatalException("Passed incorrect packet_schema in " . __METHOD__);
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

	/**
	 * получить shard_id
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getShardId(string $two_fa_map):string {

		$packet = self::_convertMapToPacket($two_fa_map);
		return $packet["shard_id"];
	}

	/**
	 * получить shard_id
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getTwoFaUniq(string $two_fa_map):string {

		$packet = self::_convertMapToPacket($two_fa_map);
		return $packet["2fa_uniq"];
	}

	// получить table_id
	public static function getTableId(string $two_fa_map):string {

		$packet = self::_convertMapToPacket($two_fa_map);
		return $packet["table_id"];
	}

	/**
	 * получить shard_id из времени
	 *
	 */
	public static function getShardIdByTime(int $time):string {

		return date("Y", $time);
	}

	/**
	 * получить table_id из времени
	 *
	 */
	public static function getTableIdByTime(int $time):string {

		return date("n", $time);
	}

	/**
	 * метод для расшифровки two_fa_key пользователя в two_fa_map
	 *
	 * @throws \cs_DecryptHasFailed
	 */
	public static function doDecrypt(string $two_fa_key):string {

		if (isset($GLOBALS["map_list"][$two_fa_key])) {
			return $GLOBALS["map_list"][$two_fa_key];
		}

		// расшифровываем
		$decrypt_result = openssl_decrypt($two_fa_key, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		// проверяем наличие ключа auth_map
		$decrypt_result = fromJson($decrypt_result);
		if (!isset($decrypt_result["two_fa_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем auth_map
		$GLOBALS["map_list"][$two_fa_key] = $decrypt_result["two_fa_map"];
		return $decrypt_result["two_fa_map"];
	}

	/**
	 * метод для зашифровки пользовательской сессии в two_fa_map
	 *
	 */
	public static function doEncrypt(string $two_fa_map):string {

		if (isset($GLOBALS["key_list"][$two_fa_map])) {
			return $GLOBALS["key_list"][$two_fa_map];
		}

		// формируем массив для шифрования
		$arr = [
			"two_fa_map" => $two_fa_map,
		];

		// переводим сформированный two_fa_key в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$two_fa_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		$GLOBALS["key_list"][$two_fa_map] = $two_fa_key;
		return $two_fa_key;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем map в пакет
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _convertMapToPacket(string $two_fa_map):array {

		if (isset($GLOBALS["packet_list"][$two_fa_map])) {
			return $GLOBALS["packet_list"][$two_fa_map];
		}
		$packet  = self::_unpackSessionMap($two_fa_map);
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
		$output["version"]                   = $version;
		$GLOBALS["packet_list"][$two_fa_map] = $output;
		return $output;
	}

	/**
	 * распаковываем auth_map
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _unpackSessionMap(string $two_fa_map):array {

		// получаем пакет из JSON
		$packet = fromJson($two_fa_map);

		if (!isset($packet["_"], $packet["?"])) {
			throw new \cs_UnpackHasFailed();
		}

		// проверяем что передали ожидаемую сущность
		if ($packet["?"] != self::_MAP_ENTITY_TYPE) {
			throw new \cs_UnpackHasFailed();
		}

		return $packet;
	}

	/**
	 * получаем версию пакета
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _throwIfUnsupportedVersion(array $packet):int {

		// получаем версию пакета
		$version = $packet["_"];

		// проверяем существование такой версии
		if (!isset(self::_PACKET_SCHEMA[$version])) {
			throw new \cs_UnpackHasFailed();
		}

		return $version;
	}

	/**
	 * проверяем пришедшую подпись на подлинность
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _checkSignature(string $passed_sign, array $packet):void {

		// получаем подпись и сверяем с пришедшей
		$sign = self::_getSignature($packet);

		// если подпись не совпала
		if ($sign != $passed_sign) {
			throw new \cs_UnpackHasFailed();
		}
	}

	/**
	 * получает подпись для пакета
	 *
	 */
	protected static function _getSignature(array $packet):string {

		// получаем версию пакета
		$version = $packet["_"];

		// сортируем массив пакета по ключам
		ksort($packet);

		// формируем подпись
		$string_for_sign = implode(",", $packet);

		$sign     = hash_hmac("sha1", $string_for_sign, SALT_PACK_AUTH[$version]);
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

	/**
	 * выбрасываем ошибку, если заменяемый ключ отсутствует в пакете
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _throwIfReplacementKeyNotSet(array $flipped_packet_schema, string $key):void {

		if (!isset($flipped_packet_schema[$key])) {
			throw new \cs_UnpackHasFailed();
		}
	}
}
