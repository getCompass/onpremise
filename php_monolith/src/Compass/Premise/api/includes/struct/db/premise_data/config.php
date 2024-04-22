<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Класс структура для таблица premise_Data.premise_config
 */
class Struct_Db_PremiseData_Config {

	public function __construct(
		public string $key,
		public int    $created_at,
		public int    $updated_at,
		public array  $value,
	) {
	}
}