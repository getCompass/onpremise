<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Действие по получению подписи
 */
class Domain_User_Action_GetSignature {

	/**
	 * Выполняем
	 *
	 * @param int $user_id
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \Random\RandomException
	 */
	public static function do(int $user_id):string {

		$secret_key_config = Domain_Config_Entity_Main::get(Domain_Config_Entity_Main::SECRET_KEY);

		// если конфиг пустой - значит нет ключа у сервера, значит и подпись пустая
		if ($secret_key_config->value === []) {
			return "";
		}

		$secret_key = base64_decode($secret_key_config->value["secret_key"]);

		$signature_arr = [
			"user_id"    => $user_id,
			"expires_at" => time() + DAY1,
		];

		$signature_json = toJson($signature_arr);

		// варим рандомный iv и приклеиваем в конец строки
		// рандомный iv нужен, чтобы избежать возможности выявить какие-либо последовательности в шифровании
		// его можно передать вместе с зашифрованным текстом
		// длина IV ровно 16 байтов!
		$iv         = random_bytes(16);
		$ciphertext = $iv . openssl_encrypt($signature_json, ENCRYPT_CIPHER_METHOD, $secret_key, OPENSSL_RAW_DATA, $iv);

		return base64_encode($ciphertext);
	}
}
