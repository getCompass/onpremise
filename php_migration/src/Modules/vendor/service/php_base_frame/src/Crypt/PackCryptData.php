<?php

namespace BaseFrame\Crypt;

/**
 * * Класс для работы с шифрованием
 * @package BaseFrame\Crypt
 */
class PackCryptData {

	public function __construct(
		private array $_salt_version_list,
		private CryptData $_vector,
	) {
	}

	public function salt(int $v):string {

		return $this->_salt_version_list[$v];
	}

	public function vector():CryptData {

		return $this->_vector;
	}
}