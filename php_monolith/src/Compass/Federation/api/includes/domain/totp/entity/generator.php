<?php

namespace Compass\Federation;

/**
 * Работаем с totp_seed
 */
class Domain_Totp_Entity_Generator
{
	/** длина генерируемого секрета в байтах до base32-кодирования */
	private const _SECRET_BYTE_LENGTH = 20;

	/** алфавит base32 без паддинга */
	private const _BASE32_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

	/**
	 * Генерируем новый случайный TOTP-секрет в base32
	 */
	public static function generateSecret(): string
	{

		$bytes  = random_bytes(self::_SECRET_BYTE_LENGTH);
		$secret = "";

		// кодируем в base32
		$n      = strlen($bytes);
		$buffer = 0;
		$bits   = 0;

		for ($i = 0; $i < $n; $i++) {

			$buffer = ($buffer << 8) | ord($bytes[$i]);
			$bits += 8;

			while ($bits >= 5) {
				$bits -= 5;
				$secret .= self::_BASE32_CHARS[($buffer >> $bits) & 0x1F];
			}
		}

		if ($bits > 0) {
			$secret .= self::_BASE32_CHARS[($buffer << (5 - $bits)) & 0x1F];
		}

		return $secret;
	}

	/**
	 * Получаем код из totp_seed + time
	 */
	public static function getCode(string $totp_seed, int $code_len = 6, int $time = null): string
	{

		if ($time === null) {
			$time = floor(time() / 30);
		}

		$secret_key = self::_base32Decode($totp_seed);

		// упаковываем время в бинарную строку
		$time = chr(0) . chr(0) . chr(0) . chr(0) . pack("N*", $time);

		// хешируем с использованием секретного ключа пользователя
		$hm = hash_hmac("SHA1", $time, $secret_key, true);

		// используем последний полубайт результата как индекс/смещение
		$offset = ord(substr($hm, -1)) & 0x0F;

		// берем 4 байта из результата
		$hash_part = substr($hm, $offset, 4);

		// распаковываем бинарное значение
		$value = unpack("N", $hash_part);
		$value = $value[1];

		// Оставляем только 32 бита
		$value = $value & 0x7FFFFFFF;

		$modulo = pow(10, $code_len);

		return str_pad($value % $modulo, $code_len, "0", STR_PAD_LEFT);
	}

	/**
	 * Декодируем base32
	 */
	protected static function _base32Decode(string $secret): string
	{

		if (empty($secret)) {
			return "";
		}

		$base32_chars         = self::_getBase32LookupTable();
		$base32_chars_flipped = array_flip($base32_chars);

		$padding_char_count = substr_count($secret, $base32_chars[32]);
		$allowed_values     = [6, 4, 3, 1, 0];

		if (!in_array($padding_char_count, $allowed_values)) {
			return "";
		}

		for ($i = 0; $i < 4; ++$i) {
			if ($padding_char_count == $allowed_values[$i] && substr($secret, -($allowed_values[$i])) != str_repeat($base32_chars[32], $allowed_values[$i])) {
				return "";
			}
		}

		$secret        = str_replace("=", "", $secret);
		$secret        = str_split($secret);
		$binary_string = "";

		for ($i = 0; $i < count($secret); $i = $i + 8) {

			$x = "";

			if (!in_array($secret[$i], $base32_chars)) {
				return "";
			}

			for ($j = 0; $j < 8; ++$j) {
				$x .= str_pad(
					base_convert(@$base32_chars_flipped[@$secret[$i + $j]], 10, 2),
					5,
					"0",
					STR_PAD_LEFT
				);
			}

			$eight_bits = str_split($x, 8);

			for ($z = 0; $z < count($eight_bits); ++$z) {
				$binary_string .= (($y = chr(base_convert($eight_bits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
			}
		}

		return $binary_string;
	}

	/**
	 * Получить массив со всеми 32 символами для декодирования/кодирования base32
	 * @return string[]
	 */
	protected static function _getBase32LookupTable(): array
	{

		return [
			"A", "B", "C", "D", "E", "F", "G", "H", //  7
			"I", "J", "K", "L", "M", "N", "O", "P", // 15
			"Q", "R", "S", "T", "U", "V", "W", "X", // 23
			"Y", "Z", "2", "3", "4", "5", "6", "7", // 31
			"=",  // символ паддинга
		];
	}
}
