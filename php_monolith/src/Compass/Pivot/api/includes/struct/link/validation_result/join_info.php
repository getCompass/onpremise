<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Данные для принятия приглашения после валидации.
 */
class Struct_Link_ValidationResult_JoinInfo extends Struct_Default {

	/**
	 * Результат валидации ссылки приглашения.
	 */
	public function __construct(
		public Struct_User_Info $to_join_user_info,
		public Struct_Db_PivotCompany_Company $company,
		public Struct_Db_PivotData_CompanyJoinLinkUserRel|false $invite_link_user_rel_row,
	) {

	}
}
