<?php

namespace Compass\Jitsi;

/** структура описывающая запись jitsi_data.conference_list */
class Struct_Db_JitsiData_Conference {

	public function __construct(
		public string $conference_id = "",
		public int    $space_id = 0,
		public int    $status = Domain_Jitsi_Entity_Conference::STATUS_NEW,
		public bool   $is_private = false,
		public bool   $is_lobby = false,
		public int    $creator_user_id = 0,
		public string $conference_url_custom_name = "",
		public string $description = "",
		public string $password = "",
		public string $jitsi_instance_domain = "",
		public int    $created_at = 0,
		public int    $updated_at = 0,
		public array  $data = [],
	) {
	}

	public static function rowToStruct(array $row):self {

		$data = fromJson($row["data"]);

		return new self(
			conference_id: (string) $row["conference_id"],
			space_id: (int) $row["space_id"],
			status: (int) $row["status"],
			is_private: boolval($row["is_private"]),
			is_lobby: boolval($row["is_lobby"]),
			creator_user_id: (int) $row["creator_user_id"],
			conference_url_custom_name: (string) $row["conference_url_custom_name"],
			description: (string) $row["description"],
			password: (string) $row["password"],
			jitsi_instance_domain: (string) $row["jitsi_instance_domain"],
			created_at: (int) $row["created_at"],
			updated_at: (int) $row["updated_at"],
			data: $data !== [] ? $data : Domain_Jitsi_Entity_Conference_Data::initData()
		);
	}
}