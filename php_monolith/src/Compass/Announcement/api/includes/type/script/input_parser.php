<?php

namespace Compass\Announcement;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для работы с входными данными из консоли.
 *
 * Допустимые значения для парсера:
 * — строки (без пробелов) --hello=world, , --no-args, ,
 * — числа (целые и с плавающей точкой) --value=1, --value=1.05
 * — одномерные массивы --hello=[who: world] --company-list=[1, 2, 3, 4, 5]
 */
class Type_Script_InputParser {

	public const TYPE_NONE   = 0;
	public const TYPE_STRING = 1;
	public const TYPE_INT    = 2;
	public const TYPE_NUMBER = 3;
	public const TYPE_ARRAY  = 4;

	/** @var array|null переданные скрипту данные через консоль */
	protected static array|null $_args = null;

	/**
	 * Возвращает значение, связанное с указанным параметром.
	 *
	 * Если значение не передано, то вернет пустую строку.
	 * Если параметр не передан, вернет false.
	 *
	 * @param string      $asked_param
	 * @param int         $expected_type
	 * @param mixed|false $default
	 * @param bool        $required
	 *
	 * @return mixed
	 * @throws \paramException
	 */
	public static function getArgumentValue(string $asked_param, int $expected_type = self::TYPE_STRING, mixed $default = false, bool $required = true):mixed {

		// докидываем -- при необходимости
		$asked_param = mb_substr($asked_param, 0, 2) === "--" ? $asked_param : "--{$asked_param}";

		foreach (static::_getArgs($required) as $passed_param) {

			$tmp              = explode("=", $passed_param);
			$passed_param_key = $tmp[0];

			if ($asked_param === $passed_param_key) {
				return static::_parseParamValue($tmp[1] ?? "", $expected_type);
			}
		}

		if ($default !== false) {
			return $default;
		}
		if ($required) {
			throw new ParamException("args $asked_param not parsed");
		}
		return false;
	}

	/**
	 * Возвращает все ключи, переданные в скрипт.
	 */
	public static function getPassedKeys():array {

		$output = [];

		foreach (static::_getArgs(true) as $passed_param) {

			$tmp      = explode("=", $passed_param);
			$output[] = $tmp[0];
		}

		return $output;
	}

	/**
	 * Парсит значение переданного параметра.
	 *
	 * @param string $value
	 * @param int    $expected_type
	 *
	 * @return mixed
	 */
	protected static function _parseParamValue(string $value, int $expected_type):mixed {

		if ($expected_type === static::TYPE_NONE) {
			return true;
		}

		return match ($expected_type) {
			static::TYPE_STRING => $value,
			static::TYPE_NUMBER => is_numeric($value) ? floatval($value) : throw new \InvalidArgumentException("passed non-numeric value"),
			static::TYPE_INT    => is_numeric($value) ? intval($value) : throw new \InvalidArgumentException("passed non-integer value"),
			static::TYPE_ARRAY  => static::_parseArrayValue($value),
			default             => throw new \InvalidArgumentException("passed unknown type")
		};
	}

	/**
	 * Парсит массив из строки параметров.
	 *
	 * @param string $value
	 *
	 * @return array
	 */
	protected static function _parseArrayValue(string $value):array {

		if (!preg_match("#\[.*]#u", $value)) {
			throw new \InvalidArgumentException("passed non-array value");
		}

		$value = mb_substr($value, 1, mb_strlen($value) - 2);

		$output = [];

		foreach (explode(",", $value) as $v) {

			$tmp = explode(":", $v, 2);
			$tmp = array_map(static fn(string $el) => trim($el), $tmp);

			if (count($tmp) === 2) {
				$output[$tmp[0]] = $tmp[1];
			} else {
				$output[] = $tmp[0];
			}
		}

		return $output;
	}

	/**
	 * Возвращает параметры вызова скрипта.
	 *
	 * @param bool $required
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected static function _getArgs(bool $required):array {

		// доступно только для запуска из консоли
		if (!isCLi()) {
			throw new \RuntimeException("access to argv is allowed in CLI mode only");
		}

		if (is_null(static::$_args)) {

			global $argv;

			$slice = array_slice($argv, 1);

			// склеиваем аргументы, чтобы перепарсить их по регулярке
			$args_string = implode(" ", $slice);
			$args_string = preg_replace_callback("/(\[.*?])/", fn(array $el) => str_replace(" ", "", $el[0]), $args_string);
			$args_string = preg_replace("/\s+/", " ", $args_string);

			$matches = [];

			// регулярка, парсит выражения вида --hello=world, --hello=[who: world], --no-args, --value=1, --company-list=[1, 2, 3, 4, 5]
			preg_match_all("#--[\w-]*(=([\w.-]+|\[[^[]*]))?#u", $args_string, $matches);

			// если вдруг передали аргумент в неподдерживаемом виде
			if (count($matches[0]) !== count(explode(" ", $args_string))) {

				if ($required) {
					throw new \Exception("passed incorrect argument list");
				}
				return [];
			}

			static::$_args = $matches[0];
		}

		return static::$_args;
	}
}