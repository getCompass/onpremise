<?php

namespace Compass\Jitsi;

/** структура api-сущности описывающей конкретного участника конференции */
class Struct_Api_Conference_MemberData {

	public function __construct(
		public bool                                        $is_moderator,
		public Domain_Jitsi_Entity_ConferenceMember_Status $status,
	) {
	}

	/** создаем структуру с помощью записи полученной из БД */
	public static function buildFromDB(Struct_Db_JitsiData_ConferenceMember $conference_member):self {

		return new self(
			is_moderator: boolval($conference_member->is_moderator),
			status: $conference_member->status
		);
	}

	/** форматируем сущность для ответа */
	public function format():array {

		return [
			"is_moderator" => (int) $this->is_moderator,
			"status"       => (string) mb_strtolower($this->status->name),
		];
	}
}
