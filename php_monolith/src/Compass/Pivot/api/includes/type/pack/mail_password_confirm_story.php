<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для кодирования/декодирования сущности password_mail_story_map
 */
class Type_Pack_MailPasswordConfirmStory {

	// название упаковываемой сущности
	protected const _MAP_ENTITY_TYPE = "mail_password_confirm_story";

	// текущая версия пакета
	protected const _CURRENT_MAP_VERSION = 1;

	// структура версий пакета
	protected const _PACKET_SCHEMA = [
		1 => [
			"confirm_mail_password_story_id" => "a",
			"type"                           => "b",
			"created_at"                     => "c",
		],
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * метод для запаковки и получения mail_password_confirm_story
	 *
	 * @throws \parseException
	 */
	public static function doPack(int $confirm_mail_password_story_id, int $type, int $created_at):string {

		// получаем скелет текущей версии структуры
		$packet_schema = self::_PACKET_SCHEMA[self::_CURRENT_MAP_VERSION];

		// формируем пакет
		$packet = [
			"confirm_mail_password_story_id" => $confirm_mail_password_story_id,
			"type"                           => $type,
			"created_at"                     => $created_at,
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
	public static function getShardId(string $confirm_mail_password_story_map):string {

		$packet = self::_convertMapToPacket($confirm_mail_password_story_map);
		return $packet["shard_id"];
	}

	/**
	 * получить password_mail_story_id
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getId(string $confirm_mail_password_story_map):string {

		$packet = self::_convertMapToPacket($confirm_mail_password_story_map);
		return $packet["confirm_mail_password_story_id"];
	}

	/**
	 * метод для расшифровки password_mail_story_key пользователя в password_mail_story_map
	 *
	 * @throws \cs_DecryptHasFailed
	 */
	public static function doDecrypt(string $confirm_mail_password_story_key):string {

		if (isset($GLOBALS["map_list"][$confirm_mail_password_story_key])) {
			return $GLOBALS["map_list"][$confirm_mail_password_story_key];
		}

		// расшифровываем
		$decrypt_result = openssl_decrypt($confirm_mail_password_story_key, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		// проверяем наличие ключа password_mail_story_map
		$decrypt_result = fromJson($decrypt_result);
		if (!isset($decrypt_result["confirm_mail_password_story_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем auth_map
		$GLOBALS["map_list"][$confirm_mail_password_story_key] = $decrypt_result["confirm_mail_password_story_map"];
		return $decrypt_result["confirm_mail_password_story_map"];
	}

	/**
	 * метод для зашифровки в password_mail_story_map
	 *
	 */
	public static function doEncrypt(string $confirm_mail_password_story_map):string {

		if (isset($GLOBALS["key_list"][$confirm_mail_password_story_map])) {
			return $GLOBALS["key_list"][$confirm_mail_password_story_map];
		}

		// формируем массив для шифрования
		$arr = [
			"confirm_mail_password_story_map" => $confirm_mail_password_story_map,
		];

		// переводим сформированный password_mail_story_key в JSON
		$json = toJson($arr);

		// зашифровываем данные
		$confirm_mail_password_story_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, substr(ENCRYPT_KEY_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)), 0, substr(ENCRYPT_IV_PIVOT_SESSION, 0, openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD)));

		$GLOBALS["key_list"][$confirm_mail_password_story_map] = $confirm_mail_password_story_key;
		return $confirm_mail_password_story_key;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем map в пакет
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _convertMapToPacket(string $confirm_mail_password_story_map):array {

		if (isset($GLOBALS["packet_list"][$confirm_mail_password_story_map])) {
			return $GLOBALS["packet_list"][$confirm_mail_password_story_map];
		}
		$packet  = self::_unpackPasswordMailStoryMap($confirm_mail_password_story_map);
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
		$output["version"]                                        = $version;
		$GLOBALS["packet_list"][$confirm_mail_password_story_map] = $output;
		return $output;
	}

	/**
	 * распаковываем password_mail_story_map
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _unpackPasswordMailStoryMap(string $confirm_mail_password_story_map):array {

		// получаем пакет из JSON
		$packet = fromJson($confirm_mail_password_story_map);

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
