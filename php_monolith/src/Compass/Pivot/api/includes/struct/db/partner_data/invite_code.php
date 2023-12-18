<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * класс-структура для таблицы partner_data.invite_code_list
 */
class Struct_Db_PartnerData_InviteCode {

	public string $invite_code_hash;
	public string $invite_code;
	public int    $partner_id;
	public int    $discount;
	public int    $can_reuse_after;
	public int    $expires_at;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_PartnerData_InviteCode constructor.
	 *
	 * @param string $invite_code_hash
	 * @param string $invite_code
	 * @param int    $partner_id
	 * @param int    $discount
	 * @param int    $can_reuse_after
	 * @param int    $expires_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 */
	public function __construct(string $invite_code_hash,
					    string $invite_code,
					    int    $partner_id,
					    int    $discount,
					    int    $can_reuse_after,
					    int    $expires_at,
					    int    $created_at,
					    int    $updated_at) {

		$this->invite_code_hash = $invite_code_hash;
		$this->invite_code      = $invite_code;
		$this->partner_id       = $partner_id;
		$this->discount         = $discount;
		$this->can_reuse_after  = $can_reuse_after;
		$this->expires_at       = $expires_at;
		$this->created_at       = $created_at;
		$this->updated_at       = $updated_at;
	}

}