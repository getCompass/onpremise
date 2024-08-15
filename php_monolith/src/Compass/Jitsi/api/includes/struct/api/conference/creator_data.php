<?php

namespace Compass\Jitsi;

use BaseFrame\Domain\User\Avatar;

/** структура api-сущности описывающей конкретного создателя конференции */
class Struct_Api_Conference_CreatorData {

	public function __construct(
		public int                                      $creator_user_id,
		public string                                   $full_name,
		public Struct_Api_Conference_CreatorData_Avatar $avatar
	) {
	}

	/** создаем структуру с помощью записи полученной из кеша */
	public static function buildFromCache(Struct_Db_PivotUser_User $conference_creator):self {

		return new self(
			creator_user_id: $conference_creator->user_id,
			full_name: $conference_creator->full_name,
			avatar: new Struct_Api_Conference_CreatorData_Avatar(
				mb_strlen($conference_creator->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($conference_creator->avatar_file_map) : "",
				Avatar::getColorOutput(Avatar::getColorByUserId($conference_creator->user_id))
			)
		);
	}

	/** форматируем сущность для ответа */
	public function format():array {

		return [
			"creator_user_id" => (int) $this->creator_user_id,
			"full_name"       => $this->full_name,
			"avatar"          => $this->avatar->format(),
		];
	}
}
