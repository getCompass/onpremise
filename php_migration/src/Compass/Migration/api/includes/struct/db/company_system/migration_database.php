<?php

namespace Compass\Migration;

/**
 * Структуруа для таблиц company_system.migration_release_database_list и company_system.migration_cleaning_database_list
 */
class Struct_Db_CompanySystem_MigrationDatabase extends Gateway_Db_CompanySystem_Main {

	/**
	 * constructor.
	 *
	 * @param string $full_database_name
	 * @param string $database_name
	 * @param int    $is_completed
	 * @param int    $current_version
	 * @param int    $previous_version
	 * @param int    $expected_version
	 * @param int    $highest_version
	 * @param int    $last_migrated_type
	 * @param int    $last_migrated_at
	 * @param int    $created_at
	 * @param string $last_migrated_file
	 */
	public function __construct(
		public string $full_database_name,
		public string $database_name,
		public int    $is_completed,
		public int    $current_version,
		public int    $previous_version,
		public int    $expected_version,
		public int    $highest_version,
		public int    $last_migrated_type,
		public int    $last_migrated_at,
		public string $last_migrated_file,
		public int    $created_at
	) {
	}
}
