<?php

namespace BaseFrame\Crypt\Utils;

/**
 * Обертка для функций шифрования, чтобы не копипастить их.
 * Возможно не лучшее место для размещения, но пусть будут тут.
 *
 * Не используй здесь
 */
class CallbackProvider {

	/**
	 * Зашифровать данные json-строку вида: {"v": version, "_": "encrypted_payload"}.
	 * Расшифровывается это дело функций из decodeFromJSON.
	 *
	 * @see CallbackProvider::decodeFromJSON
	 */
	public static function encodeAsJSON(\BaseFrame\Crypt\Crypter $crypter, int $key_version = 1):\Closure {

		return function(array $v) use ($key_version, $crypter):array {
			return ["v" => $key_version, "_" => $crypter->encrypt(toJson($v))];
		};
	}

	/**
	 * Расшифровать данные из json-строки, созданной с использованием encodeAsJSON.
	 * Если передан массив шифровальщиков, то версия шифровальщика соответствует
	 * индексу массива, если передан один шифровальщик, то он обрабатывается как
	 * шифровальщик первой версии.
	 *
	 * @param \BaseFrame\Crypt\Crypter[]|\BaseFrame\Crypt\Crypter $crypters
	 * @return \Closure
	 *
	 * @see CallbackProvider::encodeAsJSON
	 */
	public static function decodeFromJSON(array|\BaseFrame\Crypt\Crypter $crypters):\Closure {

		if (!is_array($crypters)) {
			$crypters = [1 => $crypters];
		}

		return function(string $v) use($crypters):string {

			$decoded = fromJson($v);

			// если не зашифровано, то возвращаем как есть
			if (!isset($decoded["_"]) && !isset($decoded["v"])) {
				return $v;
			}

			if (!isset($crypters[$decoded["v"]])) {
				throw new \RuntimeException("can not resolve crypter with version {$decoded["v"]}");
			}

			return $crypters[$decoded["v"]]->decrypt($decoded["_"]);
		};
	}
}
