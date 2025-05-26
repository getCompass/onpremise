<?php

namespace Compass\Migration;

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
	 * Ожидает подтверждение со стороны пользователя.
	 *
	 * @param string $message
	 * @param string $start_param
	 *
	 * @return bool
	 */
	public static function isConfirmScript(string $message, string $start_param):bool {

		if ($start_param === "y") {
			return true;
		}

		console($message);

		$input = readline();
		return mb_strtolower($input) === "y";
	}

	/**
	 * Определяет, является ли вызов dry.
	 * Dry используется для вызова скриптов без каких-либо изменений.
	 *
	 * @param bool $is_required
	 *
	 * @return bool
	 * @throws paramException
	 */
	public static function isDry(bool $is_required = true):bool {

		// старый вариант dry вызова
		if (isDryRun()) {

			$command_text = greenText("--dry");
			console(yellowText("deprecated dry-run call, use {$command_text} instead"));

			return true;
		}

		// получаем значение
		$is_dry = static::getArgumentValue("--dry", static::TYPE_INT);

		if ($is_required && ($is_dry === false)) {
			throw new InvalidArgumentException("dry flag is required, usage: --dry=1/0");
		}

		return $is_dry === 1;
	}

	/**
	 * Возвращает имя запускаемого скрипта.
	 *
	 * @return string
	 */
	public static function getScriptName():string {

		// получаем значение
		$name = static::getArgumentValue("--script-name", static::TYPE_STRING);

		if ($name === false) {
			throw new InvalidArgumentException("script name was not passed, usage: --script-name=my_awesome_script");
		}

		return $name;
	}

	/**
	 * Возвращает значение массив с идентификаторами компаний.
	 *
	 * @return int[]
	 */
	public static function getCompanyIdList():array {

		// получаем значение
		$list = static::getArgumentValue("--company-list", static::TYPE_ARRAY);
		$list = is_array($list) ? $list : [];

		return arrayValuesInt($list);
	}

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
	 * @throws paramException
	 */
	public static function getArgumentValue(string $asked_param, int $expected_type = self::TYPE_STRING, mixed $default = false, bool $required = true):mixed {

		// докидываем -- при необходимости
		$asked_param = mb_substr($asked_param, 0, 2) === "--" ? $asked_param : "--{$asked_param}";

		foreach (static::_getArgs() as $passed_param) {

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
			throw new paramException("args $asked_param not parsed");
		}
		return false;
	}

	/**
	 * Возвращает значение, связанное с указанным параметром.
	 *
	 * Если значение не передано, то вернет пустую строку.
	 * Если параметр не передан, вернет false.
	 *
	 * @return mixed
	 */
	public static function getScriptData():array {

		// получаем значение
		$list = static::getArgumentValue("--script-data", static::TYPE_ARRAY);

		return is_array($list) ? $list : [];
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
			static::TYPE_NUMBER => is_numeric($value) ? floatval($value) : throw new InvalidArgumentException("passed non-numeric value"),
			static::TYPE_INT => is_numeric($value) ? intval($value) : throw new InvalidArgumentException("passed non-integer value"),
			static::TYPE_ARRAY => static::_parseArrayValue($value),
			default => throw new InvalidArgumentException("passed unknown type")
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
			throw new InvalidArgumentException("passed non-array value");
		}

		$value = mb_substr($value, 1, mb_strlen($value) - 2);

		$output = [];

		foreach (explode(",", $value) as $k => $v) {

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
	 * @return array
	 */
	protected static function _getArgs():array {

		// доступно только для запуска из консоли
		if (!isCLi()) {
			throw new RuntimeException("access to argv is allowed in CLI mode only");
		}

		if (is_null(static::$_args)) {

			global $argv;

			// склеиваем аргументы, чтобы перепарсить их по регулярке
			$args_string = implode(" ", array_slice($argv, 1));
			$matches     = [];

			// регулярка, парсит выражения вида --hello=world, --hello=[who: world], --no-args, --value=1, --company-list=[1, 2, 3, 4, 5]
			preg_match_all("#--[\w-]*(=([\w\d.]+|\[[^[]*]))?#u", $args_string, $matches);

			static::$_args = $matches[0];
		}

		return static::$_args;
	}
}