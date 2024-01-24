<?php

namespace Compass\Pivot;

/**
 * Структура фичи второй версии
 */
class Struct_Feature_v2 {

	public string $name; // название фичи
	public int    $current_version; // текущая версия фичи
	public array  $supported_version_list; // поддерживаемые версии фичи

	public array $rule_name_list = []; // названия кастомных правил

	public function __construct() {
	}

	/**
	 * Сформировать объект из массива
	 *
	 * @param array $row
	 *
	 * @return static
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 */
	public static function fromArray(array $row):self {

		$instance = new self();

		try {

			$instance->supported_version_list = (array) arrayValuesInt($row["supported_version_list"]);
			$instance->current_version        = (int) $row["current_version"];
			$instance->name                   = (string) $row["name"];

			// если передали кастомные правила - добавляем
			if (isset($row["rule_name_list"])) {

				$row["rule_name_list"]    = array_values($row["rule_name_list"]);
				$instance->rule_name_list = (array) $row["rule_name_list"];
			}
		} catch (\TypeError) {
			throw new Domain_App_Exception_Feature_InvalidValue("passed wrong value");
		}

		return $instance;
	}

	/**
	 * Добавить названия правил в список
	 *
	 * @param array $rule_name_list
	 *
	 * @return $this
	 */
	public function addRuleNameList(array $rule_name_list):self {

		$this->rule_name_list = array_merge($this->rule_name_list, $rule_name_list);

		return $this;
	}

	/**
	 * Формирование массива для вывода
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"current_version"        => $this->current_version,
			"supported_version_list" => $this->supported_version_list,
			"rule_name_list"         => $this->rule_name_list,
		];
	}

	/**
	 * Вернуть конфиг с именем
	 *
	 * @return array
	 */
	public function toArrayFull():array {

		return [
			"name"                   => $this->name,
			"current_version"        => $this->current_version,
			"supported_version_list" => $this->supported_version_list,
			"rule_name_list"         => $this->rule_name_list,
		];
	}

	/**
	 * Формирование массива для вывода
	 *
	 * @return array
	 */
	public function toArrayForUser():array {

		return [
			"current_version"        => $this->current_version,
			"supported_version_list" => $this->supported_version_list,
		];
	}
}