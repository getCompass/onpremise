<?php


use BaseFrame\Exception\Request\ParamException;
/**
 * класс дял форматирования пост параметров
 */
class Formatter {

	public const TYPE_INT          = "?i";
	public const TYPE_STRING       = "?s";
	public const TYPE_HASH         = "?h";
	public const TYPE_JSON         = "?j";
	public const TYPE_ARRAY        = "?a";
	public const TYPE_ARRAY_INT    = "?ai";
	public const TYPE_FLOAT        = "?f";
	public const TYPE_UUID         = "?uuid";
	public const TYPE_BOOL         = "?b";

	// разрешенные для использования в форматтере типы
	protected const _ALLOWED_SCALAR_TYPES = [
		self::TYPE_INT,
		self::TYPE_STRING,
		self::TYPE_HASH,
		self::TYPE_JSON,
		self::TYPE_FLOAT,
		self::TYPE_UUID,
		self::TYPE_BOOL,
	];

	// разрешенные для использования в форматтере типы для массивов
	protected const _ALLOWED_ARRAY_TYPES = [
		self::TYPE_ARRAY,
		self::TYPE_ARRAY_INT,
	];

	/**
	 * возвращает параметр который пришел от пользователя
	 *
	 * @param array  $post_data
	 * @param string $type
	 * @param string $key
	 * @param null   $default
	 *
	 * @return array|false|float|int|mixed|string
	 * @throws paramException
	 * @mixed
	 */
	public static function post(array $post_data, string $type, string $key, $default = null) {

		if (!isset($post_data[$key])) {

			if (!is_null($default)) {

				return $default;
			}
			throw new ParamException("There is no required parameter ({$key}) in request, or developer did not passed default value for it.");
		}

		$value = $post_data[$key];
		if (is_scalar($value)) {

			return self::_formatScalar($type, $value);
		}

		if (!is_array($value)) {

			$type = gettype($value);
			throw new ParamException("passed unknown param type ({$type})");
		}

		return self::_formatArray($type, $value);
	}

	/**
	 * форматируем скалярный тип
	 *
	 * @param string $type
	 * @param mixed  $value
	 *
	 * @return mixed
	 * @throws paramException
	 * @mixed
	 */
	protected static function _formatScalar(string $type, mixed $value):mixed {

		return match ($type) {

			self::TYPE_STRING => formatString($value, false),
			self::TYPE_FLOAT => self::_getFloat($value),
			self::TYPE_INT => self::_getInt($value),
			self::TYPE_HASH => self::_getHash($value),
			self::TYPE_UUID => self::_getUuid($value),
			self::TYPE_JSON => fromJson($value),
			self::TYPE_BOOL => (bool) $value,
			default => throw new ParamException("Developer passed not existed type ({$type}) of expecting parameter! Use types ". implode(", ",self::_ALLOWED_SCALAR_TYPES)),
		};
	}

	/**
	 * Получаем число
	 *
	 * @param string $value
	 *
	 * @return int
	 * @throws paramException
	 * @mixed
	 */
	protected static function _getInt(string $value):int {

		// пытаемся извлечь из параметра число
		if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
			return (int) $value;
		}

		throw new ParamException("Expecting value ({$value}) to be int, but got string");
	}

	/**
	 * Получаем число с плавающей запятой
	 *
	 * @param string $value
	 *
	 * @return float
	 * @throws paramException
	 * @mixed
	 */
	protected static function _getFloat(string $value):float {

		// пытаемся извлечь из параметра число с плавающей запятой
		if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
			return (float) $value;
		}

		throw new ParamException("Expecting value ({$value}) to be float, but got string");
	}

	/**
	 * получаем хэш
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws paramException
	 * @mixed
	 */
	protected static function _getHash(string $value):string {

		// получаем хэш
		$value = formatHash($value);

		// выбрасываем ошибку, если получили некорректный хэш
		if (strlen($value) != 32 && strlen($value) != 40) {

			$length = strlen($value);
			throw new ParamException("Expecting hash ({$value}), but its length ({$length}) not equal 32 nor 40 characters!");
		}

		return $value;
	}

	/**
	 * получаем и проверяем uuid
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws paramException
	 * @mixed
	 */
	protected static function _getUuid(string $value):string {

		// сразу приводим к lower case - для единообразия
		$string_value = mb_strtolower(formatString($value));

		if (!isUuidValid($string_value)) {

			throw new ParamException("not a valid uuid");
		}

		return $string_value;
	}

	/**
	 * Форматирование массива
	 *
	 * @param string $type
	 * @param array  $value
	 *
	 * @return array|int[]
	 * @throws paramException
	 * @mixed
	 */
	protected static function _formatArray(string $type, array $value):array {

		return match ($type) {

			self::TYPE_ARRAY => self::_getArray($value),
			self::TYPE_ARRAY_INT => self::_getArrayOfInt($value),
			default => throw new ParamException("Developer passed not existed type ({$type}) of expecting parameter! Use types ". implode(", ", self::_ALLOWED_ARRAY_TYPES)),
		};
	}

	/**
	 * получаем массива с автоматическим приведением типов
	 *
	 * @param array $value
	 *
	 * @return array
	 * @throws paramException
	 */
	protected static function _getArray(array $value):array {

		foreach ($value as $k => $v) {

			if (is_scalar($v)) {

				$value[$k] = self::_formatScalar(self::_resolveType($v), $v);
				continue;
			}

			$value[$k] = self::_getArray($v);
		}
		return $value;
	}

	/**
	 * получение массива целочисленных значений
	 *
	 * @param array $value
	 *
	 * @return int[]
	 * @throws paramException
	 * @mixed
	 */
	protected static function _getArrayOfInt(array $value):array {

		$output = [];
		foreach ($value as $k => $v) {

			if (!is_int($k) && !is_scalar($v)) {

				throw new ParamException("not a valid array int");
			}

			$output[] = self::_formatScalar(self::TYPE_INT, $v);
		}

		// для всех элементов пробегаемся и преобразуем в число
		return $output;
	}

	/**
	 * автоматически определяем тип значения
	 *
	 * @param mixed $value
	 *
	 * @return string
	 * @throws paramException
	 */
	protected static function _resolveType(mixed $value):string {

		$type = gettype($value);

		return match ($type) {

			"boolean" => self::TYPE_BOOL,
			"integer" => self::TYPE_INT,
			"double" => self::TYPE_FLOAT,
			"string", "NULL" => self::TYPE_STRING,
			"array", "object" => self::TYPE_ARRAY,
			default => throw new ParamException("Developer passed not existed type ({$type}) of expecting parameter!"),
		};
	}
}