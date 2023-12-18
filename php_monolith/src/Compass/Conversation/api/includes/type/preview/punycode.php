<?php

namespace Compass\Conversation;

/**
 * класс для работы с кириллическими url адресами
 * преобразование utf-8 доменов в punycode и обратно
 */
class Type_Preview_Punycode {

	// дефолтные параметры
	protected const _BASE         = 36;
	protected const _TMIN         = 1;
	protected const _TMAX         = 26;
	protected const _SKEW         = 38;
	protected const _DAMP         = 700;
	protected const _INITIAL_BIAS = 72;
	protected const _INITIAL_N    = 128;
	protected const _PREFIX       = "xn--";
	protected const _DELIMITER    = "-";

	// максимальная длина домена
	protected const _MAX_DOMAIN_LENGTH      = 255;
	protected const _MAX_DOMAIN_PART_LENGTH = 63;

	// минимальная длина домена
	protected const _MIN_DOMAIN_LENGTH = 1;

	// таблица символов для encode
	protected const _ENCODE_TABLE = [
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l",
		"m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x",
		"y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
	];

	// таблица символов для decode
	protected const _DECODE_TABLE = [
		"a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4, "f" => 5,
		"g" => 6, "h" => 7, "i" => 8, "j" => 9, "k" => 10, "l" => 11,
		"m" => 12, "n" => 13, "o" => 14, "p" => 15, "q" => 16, "r" => 17,
		"s" => 18, "t" => 19, "u" => 20, "v" => 21, "w" => 22, "x" => 23,
		"y" => 24, "z" => 25, "0" => 26, "1" => 27, "2" => 28, "3" => 29,
		"4" => 30, "5" => 31, "6" => 32, "7" => 33, "8" => 34, "9" => 35,
	];

	/**
	 * перекодирует utf-8 домен по алгоритму punycode
	 *
	 */
	public static function encode(string $input, string $encoding = "utf-8"):string|false {

		// переводим домен в нижний регистр и разбиваем на части
		$input = mb_strtolower($input, $encoding);
		$parts = explode(".", $input);

		// проходимся по каждой части домена
		foreach ($parts as &$v) {

			// если длина меньше минимальной - возвращаем false
			$length = mb_strlen($v);
			if ($length < self::_MIN_DOMAIN_LENGTH) {
				return false;
			}

			// преобразуем части домена в punycode
			$v = self::_encodePart($v, $encoding);
		}

		// объединяем части домена по точке
		$encoded_url = implode(".", $parts);

		// если домен больше максимальной длины - возвращаем false
		$length = mb_strlen($encoded_url);
		if ($length > self::_MAX_DOMAIN_LENGTH) {
			return false;
		}

		return $encoded_url;
	}

	/**
	 * перекодирует домен из кодировки punycode в utf-8
	 *
	 */
	public static function decode(string $input, string $encoding = "utf-8"):string|false {

		// переводим домен в нижний регистр и разбиваем на части
		$input = strtolower($input);
		$parts = explode(".", $input);

		// проходимся по каждой части домена
		foreach ($parts as &$v) {

			// если часть домена больше максимальной или меньше минимальной - возвращаем false
			$length = mb_strlen($v);
			if ($length > self::_MAX_DOMAIN_PART_LENGTH || $length < self::_MIN_DOMAIN_LENGTH) {
				return false;
			}

			// если в начале части нет префикса - пропускаем
			if (strpos($v, self::_PREFIX) !== 0) {
				continue;
			}

			// считаем длину части домена и декодируем
			$v = substr($v, mb_strlen(self::_PREFIX));
			$v = self::_decodePart($v, $encoding);
		}

		// объединяем части домена по точке
		$decoded_url = implode(".", $parts);

		// если длина домена больше максимальной - возвращаем false
		$length = mb_strlen($decoded_url);
		if ($length > self::_MAX_DOMAIN_LENGTH) {
			return false;
		}

		return $decoded_url;
	}

	// -------------------------------------------------
	// PROTECTED
	// -------------------------------------------------

	// перекодирует часть utf-8 домена
	// @long
	protected static function _encodePart(string $input, string $encoding):string|false {

		$code_points = self::_listCodePoints($input, $encoding);

		$n     = self::_INITIAL_N;
		$bias  = self::_INITIAL_BIAS;
		$delta = 0;
		$h     = $b     = count($code_points["basic"]);

		$output = "";
		foreach ($code_points["basic"] as $v) {
			$output .= self::_codePointToChar($v);
		}

		if ($input === $output) {
			return $output;
		}

		if ($b > 0) {
			$output .= self::_DELIMITER;
		}

		$code_points["non_basic"] = array_unique($code_points["non_basic"]);
		sort($code_points["non_basic"]);

		$i      = 0;
		$length = mb_strlen($input, $encoding);
		while ($h < $length) {

			$m = $code_points["non_basic"][$i++];
			$delta += ($m - $n) * ($h + 1);
			$n = $m;

			foreach ($code_points["all"] as $c) {

				if ($c < $n || $c < self::_INITIAL_N) {
					$delta++;
				}

				if ($c === $n) {

					$q = $delta;
					for ($k = self::_BASE; ; $k += self::_BASE) {

						$t = self::_calculateThreshold($k, $bias);
						if ($q < $t) {
							break;
						}

						$code = $t + (($q - $t) % (self::_BASE - $t));
						$output .= self::_ENCODE_TABLE[$code];

						$q = ($q - $t) / (self::_BASE - $t);
					}

					$output .= self::_ENCODE_TABLE[$q];
					$bias  = self::_adapt($delta, $h + 1, ($h === $b));
					$delta = 0;
					$h++;
				}
			}

			$delta++;
			$n++;
		}

		$encoded_part = self::_PREFIX . $output;

		$length = mb_strlen($encoded_part);
		if ($length > self::_MAX_DOMAIN_PART_LENGTH || $length < self::_MIN_DOMAIN_LENGTH) {
			return false;
		}

		return $encoded_part;
	}

	// перекодирует часть домена из кодировки punycode
	// @long
	protected static function _decodePart(string $input, string $encoding):string {

		$n      = self::_INITIAL_N;
		$i      = 0;
		$bias   = self::_INITIAL_BIAS;
		$output = "";

		$pos = strrpos($input, self::_DELIMITER);
		if ($pos !== false) {
			$output = substr($input, 0, $pos++);
		} else {
			$pos = 0;
		}

		$output_length = mb_strlen($output);
		$input_length  = mb_strlen($input);
		while ($pos < $input_length) {

			$output_length++;
			$oldi = $i;
			$w    = 1;

			for ($k = self::_BASE; ; $k += self::_BASE) {

				$digit = self::_DECODE_TABLE[$input[$pos++]];
				$i += ($digit * $w);
				$t = self::_calculateThreshold($k, $bias);

				if ($digit < $t) {
					break;
				}

				$w = $w * (self::_BASE - $t);
			}

			$bias   = self::_adapt($i - $oldi, $output_length, ($oldi === 0));
			$n      = $n + (int) ($i / $output_length);
			$i      = $i % ($output_length);
			$output = mb_substr($output, 0, $i, $encoding) . self::_codePointToChar($n) . mb_substr($output, $i, $output_length - 1, $encoding);

			$i++;
		}

		return $output;
	}

	// функция для подсчета смещения между tmin(_TMIN) и tmax(_TMAX)
	protected static function _calculateThreshold(int $k, int $bias):int {

		if ($k <= $bias + self::_TMIN) {
			return self::_TMIN;
		} elseif ($k >= $bias + self::_TMAX) {
			return self::_TMAX;
		}

		return $k - $bias;
	}

	// функция для адаптации смещения
	protected static function _adapt(int $delta, int $num_points, bool $first_time):int {

		$delta = (int) (($first_time) ? $delta / self::_DAMP : $delta / 2);

		$delta += (int) ($delta / $num_points);

		$k = 0;
		while ($delta > ((self::_BASE - self::_TMIN) * self::_TMAX) / 2) {

			$delta = (int) ($delta / (self::_BASE - self::_TMIN));
			$k += self::_BASE;
		}

		$k += (int) (((self::_BASE - self::_TMIN + 1) * $delta) / ($delta + self::_SKEW));

		return $k;
	}

	// функция для получения массива из кодов символов переданного домена
	protected static function _listCodePoints(string $input, string $encoding):array {

		$code_points = [
			"all"       => [],
			"basic"     => [],
			"non_basic" => [],
		];

		$length = mb_strlen($input, $encoding);
		for ($i = 0; $i < $length; $i++) {

			$char = mb_substr($input, $i, 1, $encoding);
			$code = self::_charToCodePoint($char);
			if ($code < 128) {
				$code_points["all"][] = $code_points["basic"][] = $code;
			} else {
				$code_points["all"][] = $code_points["non_basic"][] = $code;
			}
		}

		return $code_points;
	}

	// функция для получения кода символа
	protected static function _charToCodePoint(string $char):int {

		$code = ord($char[0]);
		if ($code < 128) {
			return $code;
		} elseif ($code < 224) {
			return (($code - 192) * 64) + (ord($char[1]) - 128);
		} elseif ($code < 240) {
			return (($code - 224) * 4096) + ((ord($char[1]) - 128) * 64) + (ord($char[2]) - 128);
		} else {
			return (($code - 240) * 262144) + ((ord($char[1]) - 128) * 4096) + ((ord($char[2]) - 128) * 64) + (ord($char[3]) - 128);
		}
	}

	// функция для получения символа по его коду
	protected static function _codePointToChar(int $code):string {

		if ($code <= 0x7F) {
			return chr($code);
		} elseif ($code <= 0x7FF) {
			return chr(($code >> 6) + 192) . chr(($code & 63) + 128);
		} elseif ($code <= 0xFFFF) {
			return chr(($code >> 12) + 224) . chr((($code >> 6) & 63) + 128) . chr(($code & 63) + 128);
		} else {
			return chr(($code >> 18) + 240) . chr((($code >> 12) & 63) + 128) . chr((($code >> 6) & 63) + 128) . chr(($code & 63) + 128);
		}
	}
}