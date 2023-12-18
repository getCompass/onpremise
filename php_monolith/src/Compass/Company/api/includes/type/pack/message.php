<?php

namespace Compass\Company;

/*
 * управляющий класс-роутер для сообщения из треда/диалога
 * работает только с обязательным полем "?" в map сообщения
 * с его помощью можно узнать к какой сущности относится сообщение
 * doEncrypt/doDecrypt так же через него
 */

/**
 * Class Type_Pack_Message
 */
class Type_Pack_Message {

	// message_map из диалога?
	public static function isFromConversation(string $message_map):bool {

		$arr = fromJson($message_map);
		return isset($arr["?"]) && $arr["?"] == "conversation_message";
	}

	// message_map из треда?
	public static function isFromThread(string $message_map):bool {

		$arr = fromJson($message_map);
		return isset($arr["?"]) && $arr["?"] == "thread_message";
	}

	// превратить map в key
	public static function doEncrypt(string $message_map):string {

		// преобразуем message_map в json
		$json = self::_getEncryptJson($message_map);

		// зашифровываем данные
		$iv_length = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv        = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		return openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);
	}

	// преобразуем message_map в json
	protected static function _getEncryptJson(string $message_map):string {

		// формируем массив для зашифровки
		$arr = [
			"message_map" => $message_map,
		];

		// переводим сформированный message_map в JSON
		return toJson($arr);
	}

	// превратить key в map
	public static function doDecrypt(string $message_key):string {

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$decrypt_result = openssl_decrypt($message_key, ENCRYPT_CIPHER_METHOD, ENCRYPT_KEY_DEFAULT, 0, $iv);

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new \cs_DecryptHasFailed();
		}

		$decrypt_result = fromJson($decrypt_result);

		// проверяем наличие обязательных полей
		if (!isset($decrypt_result["message_map"])) {
			throw new \cs_DecryptHasFailed();
		}

		// возвращаем message_map
		return $decrypt_result["message_map"];
	}
}