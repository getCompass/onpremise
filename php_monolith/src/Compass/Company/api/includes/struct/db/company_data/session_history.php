<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.session_history_list
 */
class Struct_Db_CompanyData_SessionHistory {

	public string $session_uniq;
	public int    $user_id;
	public string $user_company_session_token;
	public int    $status;
	public int    $created_at;
	public int    $login_at;
	public int    $logout_at;
	public string $ip_address;
	public string $user_agent;
	public array  $extra;

	/**
	 * Struct_Db_CompanyData_SessionHistory constructor.
	 */
	public function __construct(string $session_uniq, int $user_id, string $user_company_session_token, int $status, int $created_at, int $login_at, int $logout_at, string $ip_address, string $user_agent, array $extra) {

		$this->session_uniq               = $session_uniq;
		$this->user_id                    = $user_id;
		$this->user_company_session_token = $user_company_session_token;
		$this->status                     = $status;
		$this->created_at                 = $created_at;
		$this->login_at                   = $login_at;
		$this->logout_at                  = $logout_at;
		$this->ip_address                 = $ip_address;
		$this->user_agent                 = $user_agent;
		$this->extra                      = $extra;
	}
}