<?php

namespace Compass\Speaker;

/**
 * класс для кодирования/декодирования сущности call_map
 * все взаимодействие с call_map происходит в рамках этого класса
 * за его пределами call_map существует только как обычная строка
 */
class Type_Pack_Call {

	// название упаковываемой сущности
	protected const _ENTITY = "call";

	// текущая версия пакета
	protected const _VERSION = 1;

	// структура версий пакета
	protected const _STRUCTURES = [
		1 => [
			"shard_id" => "a",
			"table_id" => "b",
			"meta_id"  => "c",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// метод для запаковки и получения call_map
	public static function doPack(string $shard_id, int $table_id, int $meta_id):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_STRUCTURES[self::_VERSION];

		// формируем пакет
		$packet = [
			"shard_id" => $shard_id,
			"table_id" => $table_id,
			"meta_id"  => $meta_id,
		];

		// упаковываем входящий массив
		$packed_data = self::_doPack($packet, $packet_schema);

		// переводим в JSON
		$call_map = toJson($packed_data);

		return $call_map;
	}

	// упаковываем данные
	protected static function _doPack(array $packet, array $packet_schema):array {

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
		$output["_"] = self::_VERSION;

		// добавляем название сущности
		$output["?"] = self::_ENTITY;

		// получаем подпись
		$output["z"] = self::_getSignature($output);

		return $output;
	}

	// получить shard_id
	public static function getShardId(string $call_map):string {

		$pack = self::_doUnpack($call_map);
		return $pack["shard_id"];
	}

	// получить table_id
	public static function getTableId(string $call_map):int {

		$pack = self::_doUnpack($call_map);
		return $pack["table_id"];
	}

	// получить meta_id
	public static function getMetaId(string $call_map):int {

		$pack = self::_doUnpack($call_map);
		return $pack["meta_id"];
	}

	// получить shard_id из времени
	public static function getShardIdByTime(int $time):string {

		return date("Y_n", $time);
	}

	// получить table_id из времени
	public static function getTableIdByTime(int $time):int {

		return date("j", $time);
	}

	// превратить map в key
	public static function doEncrypt(string $call_map):string {

		if (isset($GLOBALS["key_list"][$call_map])) {
			return $GLOBALS["key_list"][$call_map];
		}

		// формируем массив для зашифровки
		$arr = [
			"call_map" => $call_map,
		];

		// переводим сформированный call_key в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$iv_length = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv        = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$call_key  = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);

		$GLOBALS["key_list"][$call_map] = $call_key;
		return $GLOBALS["key_list"][$call_map];
	}

	// превратить key в map
	public static function doDecrypt(string $call_key):string {

		if (isset($GLOBALS["map_list"][$call_key])) {
			return $GLOBALS["map_list"][$call_key];
		}

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$decrypt_result = openssl_decrypt($call_key, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);

		// если расшировка закончилась неудачно
		if (!$decrypt_result) {
			throw new \cs_DecryptHasFailed();
		}

		$decrypt_result = fromJson($decrypt_result);

		// проверяем наличие обязательных полей
		if (!isset($decrypt_result["call_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем call_map
		$GLOBALS["map_list"][$call_key] = $decrypt_result["call_map"];
		return $decrypt_result["call_map"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// распаковываем информацию из call_map
	protected static function _doUnpack(string $call_map):array {

		if (isset($GLOBALS["packet_list"][$call_map])) {
			return $GLOBALS["packet_list"][$call_map];
		}

		// получаем пакет из JSON
		$packet = fromJson($call_map);
		self::_throwIfIncorrectPacket($packet);

		// проверяем что передали ожидаемую сущность
		self::_throwIfIncorrectEntity($packet);

		// получаем версию пакета
		$version = $packet["_"];

		// проверяем существование такой версии
		self::_throwIfIncorrectVersion($version);

		// получаем сигнатуру, проверяем, что подпись корректна
		$passed_sign = $packet["z"];
		unset($packet["z"]);
		self::_throwIfIncorrectSignature($packet, $passed_sign);

		$output                            = self::_doUnpackPacket($packet, $version);
		$output["version"]                 = $version;
		$GLOBALS["packet_list"][$call_map] = $output;
		return $output;
	}

	// выбрасываем исключение, если некорректный пакет
	protected static function _throwIfIncorrectPacket(array $packet):void {

		if (!isset($packet["_"], $packet["?"])) {
			throw new \cs_UnpackHasFailed();
		}
	}

	// выбрасываем исключение, если некорректная сущность
	protected static function _throwIfIncorrectEntity(array $packet):void {

		if ($packet["?"] != self::_ENTITY) {
			throw new \cs_UnpackHasFailed();
		}
	}

	// выбрасываем исключение, если некорректная версия
	protected static function _throwIfIncorrectVersion(int $version):void {

		if (!isset(self::_STRUCTURES[$version])) {
			throw new \cs_UnpackHasFailed();
		}
	}

	// выбрасываем исключение, если некорректная подпись
	protected static function _throwIfIncorrectSignature(array $packet, string $passed_sign):void {

		// получаем подпись и сверяем с пришедшей
		$sign = self::_getSignature($packet);

		// если подпись не совпала
		if ($sign != $passed_sign) {
			throw new \cs_UnpackHasFailed();
		}
	}

	// распаковываем пакет
	protected static function _doUnpackPacket(array $packet, int $version):array {

		// убираем добавляемые поля
		unset($packet["_"]);
		unset($packet["?"]);

		// распаковываем пакет
		$output                = [];
		$flipped_packet_schema = array_flip(self::_STRUCTURES[$version]);

		foreach ($packet as $key => $item) {

			if (!isset($flipped_packet_schema[$key])) {
				throw new \cs_UnpackHasFailed();
			}

			$output[$flipped_packet_schema[$key]] = $item;
		}

		return $output;
	}

	// получает подпись для пакета
	protected static function _getSignature(array $packet):string {

		// получаем версию пакета
		$version = $packet["_"];

		// сортируем массив пакета по ключам
		ksort($packet);

		// формируем подпись
		$string_for_sign = implode(",", $packet);

		$sign     = hash_hmac("sha1", $string_for_sign, SALT_PACK_CALL[$version]);
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
}