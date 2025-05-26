<?php

/**
 * класс для валидации структур
 */
class Entity_Validator_Structure {

	public const TYPE_INT    = Formatter::TYPE_INT;
	public const TYPE_STRING = Formatter::TYPE_STRING;
	public const TYPE_BOOL   = Formatter::TYPE_BOOL;
	public const TYPE_FLOAT  = Formatter::TYPE_FLOAT;
	public const TYPE_ARRAY  = Formatter::TYPE_ARRAY;
	public const TYPE_OBJECT = "?o";

	protected const _ALLOWED_DATA_TYPE = [
		self::TYPE_INT,
		self::TYPE_STRING,
		self::TYPE_BOOL,
		self::TYPE_FLOAT,
		self::TYPE_ARRAY,
		self::TYPE_OBJECT,
	];

	/**
	 * проверяем, валидна ли переданная структура
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function isExpectedStructure(mixed $data, array $expected_structure):bool {

		// если проверяемая структура не содержит ожидаемые ключи
		if (!self::_isDataContainExpectedKeys($data, $expected_structure)) {
			return false;
		}

		// пробегаемся по каждому ожидаемому полю
		$output = true;
		foreach ($expected_structure as $expected_key => $expected_field_data_type) {

			// если поле не обязательное и его нет в проверяемой структуре, то пропускаем
			$temp = (array) $data;
			if (!self::_isRequiredField($expected_key) && !isset($temp[$expected_key])) {
				continue;
			}

			// данные, которые будем проверять
			$data_field_value = $temp[$expected_key];

			// результат итерации
			$result = true;

			// если в ожидаемой стурктуре это строка, то это сравнение с типой данных (?i, ?s, ?o, ?a ...)
			if (is_string($expected_field_data_type)) {
				$result &= self::_isExpectedScalarData($data_field_value, $expected_field_data_type);
			}

			// если в ожидаемой структуре массив, то значит там вложенность
			if (is_array($expected_field_data_type)) {
				$result &= self::isExpectedStructure($data_field_value, $expected_field_data_type);
			}

			//
			$output &= $result;
		}

		return $output;
	}

	/**
	 * проверяем, что структура содержит все ожидаемые поля
	 *
	 * @return bool
	 */
	protected static function _isDataContainExpectedKeys(mixed $data, array $expected_structure):bool {

		// если это объект, то конвертим в ассоциативный массив
		if (is_object($data)) {
			$data = (array) $data;
		}

		foreach ($expected_structure as $key => $_) {

			// если ключ не обязательный, то пропускаем
			if (!self::_isRequiredField($key)) {
				continue;
			}

			if (!isset($data[$key])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * проверяем, что поле обязательное
	 *
	 * @return bool
	 */
	protected static function _isRequiredField(string $expected_field_name):bool {

		return !inHtml($expected_field_name, "?");
	}

	/**
	 * проверяем, что поле содержит данные ожидаемого типа
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _isExpectedScalarData(mixed $data_field_value, string $expected_field_data_type):bool {

		self::_assertValidDataType($expected_field_data_type);

		// получаем тип данных в переданной структуре
		$data_type = gettype($data_field_value);

		return match ($expected_field_data_type) {
			self::TYPE_INT, self::TYPE_BOOL => $data_type == "integer",
			self::TYPE_STRING => $data_type == "string",
			self::TYPE_OBJECT => $data_type == "object",
			self::TYPE_ARRAY => $data_type == "array",
			self::TYPE_FLOAT => $data_type == "double",
			default => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected behaviour"),
		};
	}

	/**
	 * проверяем, что указали корректный ожидаемый тип данных
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _assertValidDataType(string $data_type):void {

		if (!in_array($data_type, self::_ALLOWED_DATA_TYPE)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected data type");
		}
	}
}