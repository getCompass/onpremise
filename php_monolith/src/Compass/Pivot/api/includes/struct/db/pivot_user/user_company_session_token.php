<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_company_session_token_list_{1}
 */
class Struct_Db_PivotUser_UserCompanySessionToken {

	public string $user_company_session_token;
	public int    $user_id;
	public string $session_uniq;
	public int    $status;
	public int    $company_id;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_PivotHistoryLogs_UserAuthStory constructor.
	 *
	 */
	public function __construct(string $user_company_session_token, int $user_id, string $session_uniq, int $status, int $company_id, int $created_at) {

		$this->user_company_session_token = $user_company_session_token;
		$this->user_id                    = $user_id;
		$this->session_uniq               = $session_uniq;
		$this->status                     = $status;
		$this->company_id                 = $company_id;
		$this->created_at                 = $created_at;
	}
}