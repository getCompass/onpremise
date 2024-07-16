<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для кодирования/декодирования сущности change_mail_story_map
 */
class Type_Pack_ChangeMailStory {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "change_mail_story";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 1;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"change_mail_story_id" => "a",
			"created_at"           => "b",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * Метод для запаковки и получения change_mail_story_map
	 *
	 * @throws ParseFatalException
	 */
	public static function doPack(int $change_mail_story_id, int $created_at):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"change_mail_story_id" => $change_mail_story_id,
			"created_at"           => $created_at,
		];

		return self::_convertPacketToMap($packet, $packet_schema);
	}

	/**
	 * Конвертируем пакет в map
	 *
	 * @throws ParseFatalException
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
	 * Получить change_mail_story_id
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getId(string $change_mail_story_map):string {

		$packet = self::_convertMapToPacket($change_mail_story_map);
		return $packet["change_mail_story_id"];
	}

	/**
	 * Метод для расшифровки change_mail_story_key пользователя в change_mail_story_map
	 *
	 * @throws \cs_DecryptHasFailed
	 */
	public static function doDecrypt(string $change_mail_story_key):string {

		if (isset($GLOBALS["map_list"][$change_mail_story_key])) {
			return $GLOBALS["map_list"][$change_mail_story_key];
		}

		// расшифровываем
		$decrypt_result = openssl_decrypt($change_mail_story_key, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		// проверяем наличие ключа change_mail_story_map
		$decrypt_result = fromJson($decrypt_result);
		if (!isset($decrypt_result["change_mail_story_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем auth_map
		$GLOBALS["map_list"][$change_mail_story_key] = $decrypt_result["change_mail_story_map"];
		return $decrypt_result["change_mail_story_map"];
	}

	/**
	 * Метод для зашифровки в change_mail_story_map
	 */
	public static function doEncrypt(string $change_mail_story_map):string {

		if (isset($GLOBALS["key_list"][$change_mail_story_map])) {
			return $GLOBALS["key_list"][$change_mail_story_map];
		}

		// формируем массив для шифрования
		$arr = [
			"change_mail_story_map" => $change_mail_story_map,
		];

		// переводим сформированный change_mail_story_key в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$change_mail_story_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		$GLOBALS["key_list"][$change_mail_story_map] = $change_mail_story_key;
		return $change_mail_story_key;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Конвертируем map в пакет
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _convertMapToPacket(string $change_mail_story_map):array {

		if (isset($GLOBALS["packet_list"][$change_mail_story_map])) {
			return $GLOBALS["packet_list"][$change_mail_story_map];
		}
		$packet  = self::_unpackChangeMailStoryMap($change_mail_story_map);
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
		$output["version"]                              = $version;
		$GLOBALS["packet_list"][$change_mail_story_map] = $output;
		return $output;
	}

	/**
	 * Распаковываем change_mail_story_map
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _unpackChangeMailStoryMap(string $change_mail_story_map):array {

		// получаем пакет из JSON
		$packet = fromJson($change_mail_story_map);

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
	 * Получаем версию пакета
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
	 * Проверяем пришедшую подпись на подлинность
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
	 * Получает подпись для пакета
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
	 * Выбрасываем ошибку, если заменяемый ключ отсутствует в пакете
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _throwIfReplacementKeyNotSet(array $flipped_packet_schema, string $key):void {

		if (!isset($flipped_packet_schema[$key])) {
			throw new \cs_UnpackHasFailed();
		}
	}
}
