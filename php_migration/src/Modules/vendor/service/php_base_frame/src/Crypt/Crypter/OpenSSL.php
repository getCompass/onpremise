<?php declare(strict_types=1);

namespace BaseFrame\Crypt\Crypter;

/**
 * Класс шифрования данных с использование OpenSSL.
 * Пришлось сделать еще один класс, потому что дефолтный не поддерживает случайный iv.
 */
class OpenSSL implements \BaseFrame\Crypt\Crypter {

	/** @var bool флаг инициализации шифровальщика */
	protected bool   $_is_initialized = false;
	protected string $_algo           = OpenSSL\Algo::AES256CBC->value;

	/**
	 * Класс шифрования данных с использование OpenSSL.
	 */
	protected function __construct(
		protected ?string $_key,
		protected int     $_options,
		protected string  $_default_iv,
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
	public static function instance(?string $key, int $options = 0, string $default_iv = "", ?\Closure $init_fn = null):static {

		return new static($key, $options, $default_iv, $init_fn);
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
	 * Зашифровывает данные. Можно передать вектор инициализации,
	 * но лучше использовать случайный, создающийся если его не передать.
	 */
	public function encrypt(string $raw, string $iv = ""):string {

		if (!$this->_is_initialized) {
			throw new \RuntimeException("cryptor is not initialized, call init before use");
		}

		$iv_length = openssl_cipher_iv_length($this->_algo);

		if ($iv === "") {

			$iv = $this->_default_iv !== ""
				? $this->_default_iv
				: substr(bin2hex(openssl_random_pseudo_bytes($iv_length)), 0, $iv_length);
		}

		if (strlen($iv) !== $iv_length) {
			throw new \RuntimeException("initialization vector must be of the following length: $iv_length bytes");
		}

		$result = openssl_encrypt($raw, $this->_algo, $this->_key, $this->_options, $iv);

		if ($result === false) {
			throw new \RuntimeException("encryption failed");
		}

		return $iv . $result;
	}

	/**
	 * Расшифровывает данные. Важно — может расшифровать только то, что сам зашифровал —
	 * требует наличия инициализирующего вектора перед зашифрованным значением.
	 */
	public function decrypt(string $encrypted):string {

		if (!$this->_is_initialized) {
			throw new \RuntimeException("cryptor is not initialized, call init before use");
		}

		$iv_length = openssl_cipher_iv_length($this->_algo);

		$iv     = substr($encrypted, 0, $iv_length);
		$result = openssl_decrypt(substr($encrypted, $iv_length), $this->_algo, $this->_key, $this->_options, $iv);

		if ($result === false) {
			throw new \RuntimeException("decryption failed");
		}

		return $result;
	}
}
