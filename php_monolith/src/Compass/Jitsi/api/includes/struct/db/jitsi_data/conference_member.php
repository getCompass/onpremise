<?php

namespace Compass\Jitsi;

/** структура описывающая запись jitsi_data.conference_member_list */
class Struct_Db_JitsiData_ConferenceMember {

	public function __construct(
		public string                                      $conference_id,
		public Domain_Jitsi_Entity_ConferenceMember_Type   $member_type,
		public string                                      $member_id,
		public int                                         $is_moderator,
		public Domain_Jitsi_Entity_ConferenceMember_Status $status,
		public string                                      $ip_address,
		public string                                      $user_agent,
		public int                                         $created_at,
		public int                                         $updated_at,
		public array                                       $data,
	) {
	}

	public static function rowToStruct(array $row):self {

		return new self(
			conference_id: $row["conference_id"],
			member_type: Domain_Jitsi_Entity_ConferenceMember_Type::from($row["member_type"]),
			member_id: $row["member_id"],
			is_moderator: $row["is_moderator"],
			status: Domain_Jitsi_Entity_ConferenceMember_Status::from($row["status"]),
			ip_address: $row["ip_address"],
			user_agent: $row["user_agent"],
			created_at: $row["created_at"],
			updated_at: $row["updated_at"],
			data: fromJson($row["data"]),
		);
	}
}