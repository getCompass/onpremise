<?php

namespace Compass\Pivot;

/**
 * Класс для работы c ключами скриптов обновления компаний.
 * Просто обертка над InputParser.
 */
class Type_Script_CompanyUpdateInputHelper {

	/**
	 * Возвращает значение массив с идентификаторами компаний.
	 *
	 * @return int[]
	 */
	public static function getCompanyIdList():array {

		return static::_parseIds("--company-list");
	}

	/**
	 * Возвращает значение массив с идентификаторами пользователей
	 *
	 * @return int[]
	 */
	public static function getUserIdList():array {

		return static::_parseIds("--user-id-list");
	}

	/**
	 * Возвращает значение массив с идентификаторами компаний.
	 *
	 * @return int[]
	 */
	public static function getExcludeCompanyIdList():array {

		return static::_parseIds("--excluded-company-list");
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
		$list = Type_Script_InputParser::getArgumentValue("--script-data", Type_Script_InputParser::TYPE_ARRAY, required: false);

		return is_array($list) ? $list : [];
	}

	/**
	 * Возвращает имя запускаемого скрипта.
	 *
	 * @return string
	 */
	public static function getScriptName():string {

		// получаем значение
		$name = Type_Script_InputParser::getArgumentValue("--script-name", Type_Script_InputParser::TYPE_STRING);

		if ($name === false) {
			throw new \InvalidArgumentException("script name was not passed, usage: --script-name=my_awesome_script");
		}

		return $name;
	}

	/**
	 * Возвращает список модулей, в которые нужно спроксировать скрипт.
	 * Если скрипт будет спроксирован, то его не нужно отдельно вызывать в company, он будет исполнен хуком.
	 *
	 * @return bool
	 */
	public static function getModuleProxy():array {

		$list = Type_Script_InputParser::getArgumentValue("--module-proxy", Type_Script_InputParser::TYPE_ARRAY, required: false);
		$list = is_array($list) ? $list : [];

		// получаем значение
		return array_values($list);
	}

	/**
	 * Возвращает уровень логирования для скрипта.
	 *
	 * @return bool
	 */
	public static function getLogLevel():int {

		// получаем значение
		return Type_Script_InputParser::getArgumentValue("--log-level", Type_Script_InputParser::TYPE_INT);
	}

	/**
	 * Определяет, является ли вызов асинхронным.
	 * Dry используется для вызова скриптов без каких-либо изменений.
	 *
	 * @return bool
	 */
	public static function isAsync():bool {

		// получаем значение
		$is_dry = Type_Script_InputParser::getArgumentValue("--async", Type_Script_InputParser::TYPE_INT, required: false);
		return $is_dry === 1;
	}

	/**
	 * Возвращает флаг игнорирования ошибок.
	 * Если флаг передан, то скрипт не будет останавливаться при возниконовении ошибки в компании.
	 *
	 * @return bool
	 */
	public static function areErrorIgnored():bool {

		// получаем значение
		return Type_Script_InputParser::getArgumentValue("--ignore-errors", Type_Script_InputParser::TYPE_NONE, required: false);
	}

	/**
	 * Определяет, нужно ли обрабатывать свободные компании.
	 *
	 * @return bool
	 */
	public static function needProcessFreeCompanies():bool {

		// получаем значение
		return Type_Script_InputParser::getArgumentValue("--include-free", Type_Script_InputParser::TYPE_NONE, required: false);
	}

	/**
	 * Определяет, нужно ли подтверждение действия
	 *
	 * @return bool
	 */
	public static function isConfirmed():bool {

		// получаем значение
		return Type_Script_InputParser::getArgumentValue("--y", Type_Script_InputParser::TYPE_NONE, required: false);
	}

	/**
	 * Парсит id
	 *
	 * @param string $key
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected static function _parseIds(string $key):array {

		// получаем значение
		$list = Type_Script_InputParser::getArgumentValue($key, Type_Script_InputParser::TYPE_ARRAY, required: false);
		$list = is_array($list) ? $list : [];

		$output = [];

		foreach ($list as $item) {

			$item = trim($item);

			// если это просто число
			if (is_numeric($item)) {

				$output[] = $item;
				continue;
			}

			$matches = [];

			// если это диапазон
			if (preg_match("/^(\d+)-(\d+)$/", $item, $matches)) {

				$output += range((int) $matches[1], (int) $matches[2]);
				continue;
			}

			throw new \Exception("passed unsupported id");
		}

		return array_unique(arrayValuesInt($output));
	}
}