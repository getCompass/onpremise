<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * класс-структура для таблицы partner_invite_link.invite_code_list_mirror
 */
class Struct_Db_PartnerInviteLink_InviteCodeMirror {

	public string $invite_code;
	public int    $partner_id;
	public int    $created_at;

	/**
	 * Struct_Db_PartnerInviteLink_InviteCodeMirror constructor.
	 *
	 * @param string $invite_code
	 * @param int    $partner_id
	 * @param int    $created_at
	 */
	public function __construct(string $invite_code,
					    int    $partner_id,
					    int    $created_at) {

		$this->invite_code = $invite_code;
		$this->partner_id  = $partner_id;
		$this->created_at  = $created_at;
	}
}