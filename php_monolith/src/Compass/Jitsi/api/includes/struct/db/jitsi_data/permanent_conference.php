<?php

namespace Compass\Jitsi;

/** структура описывающая запись jitsi_data.permanent_conference_list */
class Struct_Db_JitsiData_PermanentConference {

	/**
	 * __construct
	 */
	public function __construct(
		public string $conference_id = "",
		public int    $space_id = 0,
		public bool   $is_deleted = false,
		public int    $creator_user_id = 0,
		public string $conference_url_custom_name = "",
		public int    $created_at = 0,
		public int    $updated_at = 0,
	) {
	}

	/**
	 * rowToStruct
	 */
	public static function rowToStruct(array $row):self {

		return new self(
			conference_id: (string) $row["conference_id"],
			space_id: (int) $row["space_id"],
			is_deleted: (bool) $row["is_deleted"],
			creator_user_id: (int) $row["creator_user_id"],
			conference_url_custom_name: (string) $row["conference_url_custom_name"],
			created_at: (int) $row["created_at"],
			updated_at: (int) $row["updated_at"],
		);
	}
}