<?php

namespace BaseFrame\String;

/**
 * Класс для работы с base58
 */
class Base58 {

	/** @var string используемые символы */
	protected const _ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

	/**
	 * Кодируем строку в base58
	 */
	public static function encode(string $string):string|false {

		$base = strlen(static::_ALPHABET);

		if (strlen($string) === 0) {
			return "";
		}

		$bytes = array_values(unpack("C*", $string));

		$decimal = $bytes[0];

		for ($i = 1, $l = count($bytes); $i < $l; $i++) {

			$decimal = bcmul($decimal, 256);
			$decimal = bcadd($decimal, $bytes[$i]);
		}

		$output = "";
		while ($decimal >= $base) {

			$div     = bcdiv($decimal, $base, 0);
			$mod     = (int) bcmod($decimal, $base);
			$output  .= static::_ALPHABET[$mod];
			$decimal = $div;
		}

		if ($decimal > 0) {
			$output .= static::_ALPHABET[$decimal];
		}

		$output = strrev($output);

		foreach ($bytes as $byte) {

			if ($byte === 0) {

				$output = static::_ALPHABET[0] . $output;
				continue;
			}

			break;
		}

		return $output;
	}

	/**
	 * Декодируем base58 в строку
	 */
	public static function decode(string $base58):string|false {

		$base = strlen(static::_ALPHABET);

		if (strlen($base58) === 0) {
			return "";
		}

		$indexes = array_flip(str_split(static::_ALPHABET));
		$chars   = str_split($base58);

		foreach ($chars as $char) {

			if (isset($indexes[$char]) === false) {
				return false;
			}
		}

		$decimal = $indexes[$chars[0]];

		for ($i = 1, $l = count($chars); $i < $l; $i++) {

			$decimal = bcmul($decimal, $base);
			$decimal = bcadd($decimal, $indexes[$chars[$i]]);
		}

		$output = '';
		while ($decimal > 0) {

			$byte    = (int) bcmod($decimal, 256);
			$output  = pack("C", $byte) . $output;
			$decimal = bcdiv($decimal, 256, 0);
		}

		foreach ($chars as $char) {

			if ($indexes[$char] === 0) {
				$output = "\x00" . $output;
				continue;
			}

			break;
		}

		return $output;
	}
}
