<?php

namespace Compass\Pivot;

/**
 * Структура кастомных правил второй версии
 */
class Struct_Rule_Main_Restrictions {

	public string $app_version; // версия приложения
	public array  $user_list; // список пользователей

	/**
	 * Инициализировать объект
	 *
	 * @param string $app_version
	 * @param array  $user_list
	 *
	 * @return static
	 */
	public static function init(string $app_version, array $user_list):self {

		$instance = new self();

		$instance->app_version = $app_version;
		$instance->user_list   = $user_list;

		return $instance;
	}

	/**
	 * Конструктор из массива
	 *
	 * @param array $row
	 *
	 * @return Struct_Rule_Main_Restrictions
	 */
	public static function fromArray(array $row):self {

		$instance = new self();

		$instance->app_version = $row["app_version"] ?? "";
		$instance->user_list   = isset($row["user_list"]) ? arrayValuesInt($row["user_list"]) : [];

		return $instance;
	}

	/**
	 * Формирование массива для вывода
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"app_version" => $this->app_version,
			"user_list"   => $this->user_list,
		];
	}
}