<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Результат валидации ссылки приглашения.
 */
class Struct_Link_ValidationResult extends Struct_Default {

	/**
	 * Результат валидации ссылки приглашения.
	 */
	public function __construct(
		public Struct_Db_PivotData_CompanyJoinLinkRel           $invite_link_rel,
		public Struct_Db_PivotData_CompanyJoinLinkUserRel|false $user_invite_link_rel,
		public Struct_Db_PivotCompany_Company                   $company,
		public Struct_Db_PivotUser_User                         $inviter_user_info,
		public int                                              $entry_option,
		public bool                                             $is_postmoderation = false,
		public bool                                             $is_waiting_for_postmoderation = false,
		public bool                                             $is_exit_status_in_progress = false,
		public bool                                             $was_member = false,
	) {

	}
}
