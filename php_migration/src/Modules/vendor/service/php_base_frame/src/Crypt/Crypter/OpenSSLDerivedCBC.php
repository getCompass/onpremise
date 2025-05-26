<?php

namespace BaseFrame\Crypt\Crypter;

/**
 * Шифровальщик, использующий CBC алгоритм. Вектор шифрования и ключ шифрования получаются
 * из pbkdf2 исходного ключа шифрования, что совместимо с консольным OpenSSL.
 */
class OpenSSLDerivedCBC implements \BaseFrame\Crypt\Crypter {

	/** @var bool флаг инициализации шифровальщика */
	protected bool   $_is_initialized = false;
	protected string $_algo           = \BaseFrame\Crypt\Crypter\OpenSSL\Algo::AES256CBC->value;

	/**
	 * Класс шифрования данных с использование OpenSSL.
	 */
	protected function __construct(
		protected ?string $_key,
		protected int     $_options,
		protected mixed   $_init_fn,
	) {

		// не делаем никаких проверок, если есть функция инициализации
		if (!is_null($_init_fn)) {
			return;
		}

		// проверяем, что указанная длина ключа подходит для шифрования выбранным алгоритмом,
		// чтобы не было ситуаций, когда в php недостающие байты нулями заполнились, а в go все сломалось
		if (is_null($_key) || strlen($_key) !== openssl_cipher_key_length($this->_algo)) {
			throw new \RuntimeException("passed incorrect key");
		}

		// поскольку функция инициализации не передана,
		// то шифровальщик считаем проинициализированным
		$this->_is_initialized = true;
	}

	/**
	 * Создает экземпляр класса шифрования.
	 */
	public static function instance(?string $key, int $options = OPENSSL_RAW_DATA | OPENSSL_DONT_ZERO_PAD_KEY, ?\Closure $init_fn = null):static {

		return new static($key, $options, $init_fn);
	}

	/**
	 * Функция инициализации, чтобы не дергать по 10 раз какую-то сложную логику
	 */
	public function init():static {

		if ($this->_is_initialized) {
			return $this;
		}

		$this->_is_initialized = true;

		if (!is_null($this->_init_fn)) {
			$this->_init_fn->call($this);
		}

		// проверим, что после инициализации ключ у нас не пустой,
		// поскольку изначально мы допускаем возможность передать
		// пустой ключ при наличии функции инициализации
		if (is_null($this->_key) || strlen($this->_key) !== openssl_cipher_key_length($this->_algo)) {
			throw new \RuntimeException("key is incorrect after initialization");
		}

		return $this;
	}

	/**
	 * Зашифровывает данные. Работает аналогично команде:
	 * echo <in> | openssl aes-256-cbc -pbkdf2 -k <key> -out <out>
	 */
	public function encrypt(string $raw):string {

		if (!$this->_is_initialized) {
			throw new \RuntimeException("cryptor is not initialized, call init before use");
		}

		// для CBC размер блока и длина вектора инициализации
		// совпадают, для остальных, увы, нужно считать
		$block_length = openssl_cipher_iv_length($this->_algo);

		// первые 8 байт — просто байты, будем вставлять строку Salted__
		$salt = random_bytes($block_length - strlen("Salted__"));
		$pb   = openssl_pbkdf2($this->_key, $salt, 48, 10000, "sha256");

		// получаем ключ, которым будем шифровать и вектор инициализации,
		// вот так сложно все, но это так работает openssl через терминал
		$pass = substr($pb, 0, 32);
		$iv   = substr($pb, 32, 16);

		// соль добавляем в результат шифрования в качестве префикса
		$result = "Salted__" . $salt . openssl_encrypt($raw, $this->_algo, $pass, $this->_options, $iv);

		if ($result === false) {
			throw new \RuntimeException("encryption failed");
		}

		// важно, что эта штука вернет байты, а не строку, смотри не сломай ничего
		return $result;
	}

	/**
	 * Расшифровывает данные. Работает аналогично команде:
	 * openssl aes-256-cbc -pbkdf2 -d -k <key> -in <in> -out <out>
	 */
	public function decrypt(string $encrypted):string {

		if (!$this->_is_initialized) {
			throw new \RuntimeException("cryptor is not initialized, call init before use");
		}

		// первые 8 байт — просто байты, вторые 8 байт — соль
		// остальное _ зашифрованные данные
		$salt      = substr($encrypted, 8, 8);
		$encrypted = substr($encrypted, 16);

		// есть ключ, есть соль, повторяем логику как при шифровании
		$pb   = openssl_pbkdf2($this->_key, $salt, 48, 10000, "sha256");
		$pass = substr($pb, 0, 32);
		$iv   = substr($pb, 32, 16);

		$result = openssl_decrypt($encrypted, $this->_algo, $pass, $this->_options, $iv);

		if ($result === false) {
			throw new \RuntimeException("decryption failed");
		}

		return $result;
	}
}