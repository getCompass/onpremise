<?php

namespace Compass\Jitsi;

/** структура api-сущности описывающей конференцию */
class Struct_Api_Conference_Data {

	// типы конференций для клиентов
	protected const _CONFERENCE_TYPE_SCHEMA = [
		Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_DEFAULT   => "default",
		Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE    => "single",
		Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_PERMANENT => "permanent",
	];

	// схема для статусов
	protected const _STATUS_SCHEMA = [
		Domain_Jitsi_Entity_Conference::STATUS_NEW      => "new",
		Domain_Jitsi_Entity_Conference::STATUS_WAITING  => "new",
		Domain_Jitsi_Entity_Conference::STATUS_ACTIVE   => "active",
		Domain_Jitsi_Entity_Conference::STATUS_FINISHED => "finished",
	];

	public function __construct(
		public string $conference_id,
		public int    $space_id,
		public string $status,
		public string $link,
		public int    $created_at,
		public bool   $is_private,
		public bool   $is_lobby,
		public string $conference_type,
		public string $description,
		public string $conference_url_custom_name,
	) {
	}

	/** создаем структуру с помощью записи полученной из БД */
	public static function buildFromDB(Struct_Db_JitsiData_Conference $conference):self {

		$conference_type = Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data);

		return new self(
			conference_id: $conference->conference_id,
			space_id: $conference->space_id,
			status: self::_STATUS_SCHEMA[$conference->status],
			link: Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByConference($conference)::prepareLandingConferenceLink($conference),
			created_at: $conference->created_at,
			is_private: boolval($conference->is_private),
			is_lobby: boolval($conference->is_lobby),
			conference_type: self::_CONFERENCE_TYPE_SCHEMA[$conference_type],
			description: $conference->description,
			conference_url_custom_name: $conference->conference_url_custom_name,
		);
	}

	/** форматируем сущность для ответа */
	public function format():array {

		return [
			"conference_id"              => (string) $this->conference_id,
			"space_id"                   => (int) $this->space_id,
			"status"                     => (string) $this->status,
			"link"                       => (string) $this->link,
			"created_at"                 => (int) $this->created_at,
			"is_private"                 => (int) $this->is_private,
			"is_lobby"                   => (int) $this->is_lobby,
			"conference_type"            => (string) $this->conference_type,
			"description"                => (string) $this->description,
			"conference_url_custom_name" => (string) $this->conference_url_custom_name,
		];
	}
}
