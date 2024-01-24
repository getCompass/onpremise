<?php

namespace Compass\Pivot;

/**
 * Структура кастомных правил второй версии
 */
class Struct_Rule_V2_Values {

	public int   $current_version; // текущая версия фичи
	public array $supported_version_list; // поддержимваемый список фич)

	/**
	 * Инициализировать объект
	 *
	 * @param string $current_version
	 * @param array  $supported_version_list
	 *
	 * @return static
	 */
	public static function init(string $current_version, array $supported_version_list):self {

		$instance = new self();

		$instance->current_version        = $current_version;
		$instance->supported_version_list = $supported_version_list;

		return $instance;
	}

	/**
	 * Конструктор из массива
	 *
	 * @param array $row
	 *
	 * @return Struct_Rule_V2_Values
	 */
	public static function fromArray(array $row):self {

		$instance = new self();

		$instance->current_version        = $row["current_version"];
		$instance->supported_version_list = arrayValuesInt($row["supported_version_list"]);

		return $instance;
	}

	/**
	 * Вернуть массив
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"current_version"        => $this->current_version,
			"supported_version_list" => $this->supported_version_list,
		];
	}

}