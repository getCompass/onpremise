<?php

namespace BaseFrame\Domain\ActivityToken\Entity;

use BaseFrame\Domain\ActivityToken\Struct\Header;
use BaseFrame\Domain\ActivityToken\Struct\Payload;
use BaseFrame\Domain\ActivityToken\Exception\DecryptFailed;

/**
 * Основной класс сущности "Токен активности"
 */
class Main {

	protected const _ENCRYPT_ALGORITHM = "SHA256";
	protected const _TOKEN_TYPE        = "JWT";

	public int $user_id;
	public int $expire_time;

	/**
	 * Конструктор класса
	 *
	 * @param string $encrypt_key
	 * @param string $encrypt_iv
	 */
	public function __construct(
		public string $encrypt_key,
		public string $encrypt_iv,

	) {

		// эти параметры необязательны, так как не нужны при расшифровке токена
		$this->user_id     = 0;
		$this->expire_time = 0;
	}

	/**
	 * Сгенерировать токен активности
	 *
	 * @return \BaseFrame\Domain\ActivityToken\Struct\Main
	 */
	public function generate():\BaseFrame\Domain\ActivityToken\Struct\Main {

		$header = new Header(
			self::_ENCRYPT_ALGORITHM,
			self::_TOKEN_TYPE
		);

		$payload = new Payload(
			generateRandomString(12),
			$this->user_id,
			time() + $this->expire_time,
		);

		// создаем подпись
		$signature = self::makeSignature($header, $payload);

		// собираем токен и возвращаем
		return new \BaseFrame\Domain\ActivityToken\Struct\Main(
			$header,
			$payload,
			$signature
		);
	}

	/**
	 * Сформировать подпись
	 *
	 * @param Header  $header
	 * @param Payload $payload
	 *
	 * @return string
	 */
	public function makeSignature(Header $header, Payload $payload):string {

		return hash(self::_ENCRYPT_ALGORITHM, $this->encrypt_key . "." . base64_encode(toJson($header)) . "." . base64_encode(toJson($payload)));
	}

	/**
	 * Сверить, что подпись валидна
	 *
	 * @param \BaseFrame\Domain\ActivityToken\Struct\Main $activity_token
	 *
	 * @return bool
	 */
	public function assertValidSignature(\BaseFrame\Domain\ActivityToken\Struct\Main $activity_token):bool {

		return $this->makeSignature($activity_token->header, $activity_token->payload) === $activity_token->signature;
	}

	/**
	 * Зашифровать токен
	 *
	 * @param \BaseFrame\Domain\ActivityToken\Struct\Main $token
	 *
	 * @return string
	 */
	public function encrypt(\BaseFrame\Domain\ActivityToken\Struct\Main $token):string {

		if (isset($GLOBALS["activity_token_key_list"][$token->payload->token_uniq])) {
			return $GLOBALS["activity_token_key_list"][$token->payload->token_uniq];
		}

		// переводим token в JSON
		$json = toJson(["activity_token" => $token]);

		// зашифровываем данные
		$iv_length          = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv                 = substr($this->encrypt_iv, 0, $iv_length);
		$activity_token_key = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, $this->encrypt_key, 0, $iv);

		$GLOBALS["activity_token_key_list"][$token->payload->token_uniq] = $activity_token_key;
		return $GLOBALS["activity_token_key_list"][$token->payload->token_uniq];
	}

	/**
	 * Расшифровать токен
	 *
	 * @param string $activity_token_key
	 *
	 * @return \BaseFrame\Domain\ActivityToken\Struct\Main
	 * @throws DecryptFailed
	 */
	public function decrypt(string $activity_token_key):\BaseFrame\Domain\ActivityToken\Struct\Main {

		if (isset($GLOBALS["activity_token_list"][$activity_token_key])) {
			return $GLOBALS["activity_token_list"][$activity_token_key];
		}

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr($this->encrypt_iv, 0, $iv_length);
		$decrypt_result = openssl_decrypt($activity_token_key, ENCRYPT_CIPHER_METHOD, $this->encrypt_key, 0, $iv);

		// если расшировка закончилась неудачно
		if ($decrypt_result === false) {
			throw new DecryptFailed("could not decrypt activity key");
		}

		$decrypt_result = fromJson($decrypt_result);

		// проверяем наличие обязательных полей
		if (!isset($decrypt_result["activity_token"])) {
			throw new DecryptFailed("could not decrypt acitivity key");
		}

		$activity_token = $this->_convertToObject($decrypt_result["activity_token"]);

		// проверяем, что подпись токена валидная
		if (!self::assertValidSignature($activity_token)) {
			throw new DecryptFailed("signature of activity token is invalid");
		}

		// возвращаем call_map
		$GLOBALS["activity_token_list"][$activity_token_key] = $activity_token;
		return $activity_token;
	}

	/**
	 * Конвертировать массив в объект
	 *
	 * @param array $activity_token_arr
	 *
	 * @return \BaseFrame\Domain\ActivityToken\Struct\Main
	 */
	protected function _convertToObject(array $activity_token_arr):\BaseFrame\Domain\ActivityToken\Struct\Main {

		return new \BaseFrame\Domain\ActivityToken\Struct\Main(
			new Header(
				$activity_token_arr["header"]["algorithm"],
				$activity_token_arr["header"]["type"],
			),
			new Payload(
				$activity_token_arr["payload"]["token_uniq"],
				$activity_token_arr["payload"]["user_id"],
				$activity_token_arr["payload"]["expires_at"],
			),
			$activity_token_arr["signature"],
		);
	}
}