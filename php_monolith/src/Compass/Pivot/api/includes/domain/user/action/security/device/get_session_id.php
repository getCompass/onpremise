<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Crypt\CryptProvider;

/**
 * Получить session_id для устройства пользователя
 */
class Domain_User_Action_Security_Device_GetSessionId {

	/**
	 * Получить публичный session_id для клиента
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function doEncrypt(string $session_uniq):string {

		return openssl_encrypt(
			$session_uniq, ENCRYPT_CIPHER_METHOD, CryptProvider::default()->key(), 0, self::_getCryptLength()
		);
	}

	/**
	 * Получить session_uniq из публичного session_id
	 *
	 * @throws Domain_User_Exception_Security_Device_IncorrectSessionId
	 * @throws ParseFatalException
	 */
	public static function doDecrypt(string $public_session_id):string {

		$decrypt_result = openssl_decrypt(
			$public_session_id, ENCRYPT_CIPHER_METHOD, CryptProvider::default()->key(), 0, self::_getCryptLength()
		);

		if ($decrypt_result === false) {
			throw new Domain_User_Exception_Security_Device_IncorrectSessionId("incorrect session_id");
		}

		return $decrypt_result;
	}

	/**
	 * Получить длину аутентификации для публичного session_id.
	 *
	 * @throws ParseFatalException
	 */
	protected static function _getCryptLength():string {

		$iv_length = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		return substr(CryptProvider::default()->vector(), 0, $iv_length);
	}
}