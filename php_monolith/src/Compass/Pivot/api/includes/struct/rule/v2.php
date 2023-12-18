<?php

namespace Compass\Pivot;

/**
 * Структура кастомных правил второй версии
 */
class Struct_Rule_V2 {

	public string                        $name; // название правила
	public int                           $type; // тип правила
	public int                           $priority; // приоритет правила
	public Struct_Rule_Main_Restrictions $restrictions; // ограничения правила
	public Struct_Rule_V2_Values         $values; // поля для использования в фиче

	/**
	 * Инициализировать объект
	 *
	 * @param string                        $name
	 * @param int                           $type
	 * @param int                           $priority
	 * @param Struct_Rule_Main_Restrictions $restrictions
	 * @param Struct_Rule_V2_Values         $values
	 *
	 * @return static
	 */
	public static function init(string $name, int $type, int $priority, Struct_Rule_Main_Restrictions $restrictions, Struct_Rule_V2_Values $values):self {

		$instance = new self();

		$instance->name         = $name;
		$instance->type         = $type;
		$instance->priority     = $priority;
		$instance->restrictions = $restrictions;
		$instance->values       = $values;

		return $instance;
	}

	/**
	 * Конструктор из массива
	 *
	 * @param array $row
	 *
	 * @return static
	 * @throws Domain_App_Exception_Rule_InvalidValue
	 */
	public static function fromArray(array $row):self {

		$instance = new self();

		try {

			$instance->name         = $row["name"];
			$instance->type         = $row["type"];
			$instance->priority     = $row["priority"] ?? 0;
			$instance->restrictions = Struct_Rule_Main_Restrictions::fromArray($row["restrictions"]);
			$instance->values       = Struct_Rule_V2_Values::fromArray($row["values"]);
		} catch (\TypeError) {
			throw new Domain_App_Exception_Rule_InvalidValue("wrong value for rule");
		}

		return $instance;
	}

	/**
	 * Вернуть конфиг с именем
	 *
	 * @return array
	 */
	public function toArrayWithName():array {

		return [
			"name"         => $this->name,
			"type"         => $this->type,
			"priority"     => $this->priority,
			"restrictions" => $this->restrictions->toArray(),
			"values"       => $this->values->toArray(),
		];
	}

	/**
	 * Формирование массива для вывода
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"type"         => $this->type,
			"priority"     => $this->priority,
			"restrictions" => $this->restrictions->toArray(),
			"values"       => $this->values->toArray(),
		];
	}
}