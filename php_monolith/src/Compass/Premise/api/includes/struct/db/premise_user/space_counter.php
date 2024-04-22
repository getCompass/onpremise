<?php declare(strict_types=1);

namespace Compass\Premise;

/**
 * Класс структура для таблица premise_user.space_counter
 */
class Struct_Db_PremiseUser_SpaceCounter {

	public function __construct(
		public string $key,
		public int $count,
		public int $created_at,
		public int $updated_at,
	) {
}
}