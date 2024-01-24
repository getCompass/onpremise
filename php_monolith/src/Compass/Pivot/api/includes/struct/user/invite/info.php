<?php

namespace Compass\Pivot;

/**
 * структура информации об отправленном приглашении
 */
class Struct_User_Invite_Info {

	public string $phone_number;
	public string $full_name;
	public int    $invite_id;
	public int    $type;

	/**
	 * Struct_User_Invite_Info constructor.
	 *
	 */
	public function __construct(string $phone_number, string $full_name, int $invite_id, int $type) {

		$this->phone_number = $phone_number;
		$this->full_name    = $full_name;
		$this->invite_id    = $invite_id;
		$this->type         = $type;
	}
}