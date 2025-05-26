<?php

namespace Compass\Migration;

/**
 * Класс-структура записи БД domino_service.port_registry
 */
class Struct_Db_DominoService_PortRegistry {

	public function __construct(
		public int $port,
		public int $status,
		public int $type,
		public int $locked_till,
		public int $created_at,
		public int $updated_at,
		public int $company_id,
		public array $extra,
	) {}

}