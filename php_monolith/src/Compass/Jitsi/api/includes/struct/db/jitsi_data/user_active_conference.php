<?php

namespace Compass\Jitsi;

/** структура описывающая запись jitsi_data.user_active_conference_rel */
class Struct_Db_JitsiData_UserActiveConference {

	public function __construct(
		public int    $user_id,
		public string $active_conference_id,
		public int    $created_at,
		public int    $updated_at,
	) {
	}

	public static function rowToStruct(array $row):self {

		return new self(
			user_id: $row["user_id"],
			active_conference_id: $row["active_conference_id"],
			created_at: $row["created_at"],
			updated_at: $row["updated_at"],
		);
	}
}