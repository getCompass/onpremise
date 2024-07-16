<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для кодирования/декодирования сущности pivot_session_map
 */
class Type_Pack_PivotSession {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "pivot_session";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 1;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"session_uniq" => "a",
			"shard_id"     => "b",
			"table_id"     => "c",
			"created_at"   => "d",
			"status"       => "e",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// метод для запаковки и получения session_map
	public static function doPack(string $session_uniq, string $shard_id, string $table_id, int $created_at, int $status):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"session_uniq" => $session_uniq,
			"shard_id"     => $shard_id,
			"table_id"     => $table_id,
			"created_at"   => $created_at,
			"status"       => $status,
		];

		return self::_convertPacketToMap($packet, $packet_schema);
	}

	// конвертируем пакет в map
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

	// получить session_uniq
	public static function getSessionUniq(string $session_map):string {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["session_uniq"];
	}

	// получить shard_id
	public static function getShardId(string $session_map):string {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["shard_id"];
	}

	// получить table_id
	public static function getTableId(string $session_map):string {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["table_id"];
	}

	// получить created_at
	public static function getCreateTime(string $session_map):int {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["created_at"];
	}

	// получить status
	public static function getStatus(string $session_map):int {

		$packet = self::_convertMapToPacket($session_map);
		return $packet["status"];
	}

	// получить shard_id из user_id
	public static function getShardIdByUserId(int $user_id):string {

		return ceil($user_id / 10000000) . "0m";
	}

	// получить table_id из user_id
	public static function getTableIdByUserId(int $user_id):string {

		return ceil($user_id / 1000000);
	}

	// метод для расшифровки session_key пользователя в session_map
	public static function doDecrypt(string $session_key):string {

		if (isset($GLOBALS["map_list"][$session_key])) {
			return $GLOBALS["map_list"][$session_key];
		}

		// расшифровываем
		$decrypt_result = openssl_decrypt($session_key, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		// проверяем наличие ключа session_map
		$decrypt_result = fromJson($decrypt_result);
		if (!isset($decrypt_result["session_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем session_map
		$GLOBALS["map_list"][$session_key] = $decrypt_result["session_map"];
		return $decrypt_result["session_map"];
	}

	// метод для зашифровки пользовательской сессии в session_key
	public static function doEncrypt(string $session_map):string {

		if (isset($GLOBALS["key_list"][$session_map])) {
			return $GLOBALS["key_list"][$session_map];
		}

		// формируем массив для шифрования
		$arr = [
			"session_map" => $session_map,
		];

		// переводим сформированный session_key в JSON
		$json = toJson($arr);

		$passphrase = substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD));
		$iv         = substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD));

		// зашифровываем данные
		$session_key = openssl_encrypt(
			$json, ENCRYPT_CIPHER_METHOD, $passphrase, 0, $iv
		);

		$GLOBALS["key_list"][$session_map] = $session_key;
		return $session_key;
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

		$sign     = hash_hmac("sha1", $string_for_sign, SALT_PACK_SESSION[$version]);
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