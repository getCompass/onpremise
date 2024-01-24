<?php

namespace Compass\Speaker;

/**
 * класс для кодирования/декодирования сущности invite_map
 * все взаимодействие с invite_map происходит в рамках этого класса
 * за его пределами invite_map существует только как обычная строка
 */
class Type_Pack_Invite {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "invite";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 1;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"shard_id" => "a",
			"meta_id"  => "b",
			"type"     => "c",
			"uniq"     => "d",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// метод для запаковки и получения invite_map
	public static function doPack(string $shard_id, int $meta_id, int $type):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"shard_id" => $shard_id,
			"meta_id"  => $meta_id,
			"type"     => $type,
			"uniq"     => COMPANY_ID . "_" . time(),
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
				throw new \parseException("Passed incorrect packet schema in " . __METHOD__);
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

	// получить версию invite_map
	public static function getVersion(string $invite_map):int {

		$packet = self::_unpackInviteMap($invite_map);
		return $packet["_"];
	}

	// получить shard_id
	public static function getShardId(string $invite_map):string {

		$packet = self::_convertMapToPacket($invite_map);
		return $packet["shard_id"];
	}

	// получить meta_id
	public static function getMetaId(string $invite_map):int {

		$packet = self::_convertMapToPacket($invite_map);
		return $packet["meta_id"];
	}

	// получить type
	public static function getType(string $invite_map):int {

		$packet = self::_convertMapToPacket($invite_map);
		return $packet["type"];
	}

	// получить company_id
	public static function getCompanyId(string $invite_map):int {

		$packet = self::_convertMapToPacket($invite_map);
		$result = explode("_", $packet["uniq"]);
		if (count($result) !== 2) {
			throw new \parseException("incorrect value for uniq parameter of packet");
		}

		return $result[0];
	}

	// получить shard_id из времени
	public static function getShardIdByTime(int $time):string {

		return date("Y", $time);
	}

	// превратить map в key
	public static function doEncrypt(string $invite_map):string {

		if (isset($GLOBALS["key_list"][$invite_map])) {
			return $GLOBALS["key_list"][$invite_map];
		}

		// формируем массив для зашифровки
		$arr = [
			"invite_map" => $invite_map,
		];

		// переводим сформированный invite_key в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$iv_length  = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv         = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$invite_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);

		$GLOBALS["key_list"][$invite_map] = $invite_key;
		return $GLOBALS["key_list"][$invite_map];
	}

	// превратить key в map
	public static function doDecrypt(string $invite_key):string {

		if (isset($GLOBALS["map_list"][$invite_key])) {
			return $GLOBALS["map_list"][$invite_key];
		}

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$decrypt_result = openssl_decrypt($invite_key, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		$decrypt_result = fromJson($decrypt_result);

		// проверяем наличие обязательных полей
		if (!isset($decrypt_result["invite_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем invite_map
		$GLOBALS["map_list"][$invite_key] = $decrypt_result["invite_map"];
		return $decrypt_result["invite_map"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// конвертируем map в пакет
	protected static function _convertMapToPacket(string $invite_map):array {

		if (isset($GLOBALS["packet_list"][$invite_map])) {
			return $GLOBALS["packet_list"][$invite_map];
		}
		$packet  = self::_unpackInviteMap($invite_map);
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
		$GLOBALS["packet_list"][$invite_map] = $output;
		return $output;
	}

	// распаковываем invite_map
	protected static function _unpackInviteMap(string $invite_map):array {

		// получаем пакет из JSON
		$packet = fromJson($invite_map);

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

		$sign     = hash_hmac("sha1", $string_for_sign, SALT_PACK_INVITE[$version]);
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