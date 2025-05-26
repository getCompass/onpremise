<?php

namespace BaseFrame\Crypt;

/**
 * * Класс для работы с шифрованием векторов
 * @package BaseFrame\Crypt
 */
class CryptData {

	/**
	 * CryptData constructor.
	 */
	public function __construct(
		private string $_encrypt_key_default,
		private string $_encrypt_iv_default,
	) {
	}

	/**
	 * получаем ключ
	 *
	 * @return string
	 */
	public function key():string {

		return $this->_encrypt_key_default;
	}

	/**
	 * получаем вектор
	 *
	 * @return string
	 */
	public function vector():string {

		return $this->_encrypt_iv_default;
	}
}
