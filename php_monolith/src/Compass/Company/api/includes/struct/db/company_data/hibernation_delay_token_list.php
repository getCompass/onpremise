<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.activity_token_list
 */
class Struct_Db_CompanyData_HibernationDelayTokenList {

	public string $token_uniq;
	public int    $user_id;
	public int    $hibernation_delayed_till;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_CompanyData_ActivityToken constructor.
	 */
	public function __construct(
		string $token_uniq,
		int    $user_id,
		int    $hibernation_delayed_till,
		int    $created_at,
		int    $updated_at
	) {

		$this->token_uniq               = $token_uniq;
		$this->user_id                  = $user_id;
		$this->hibernation_delayed_till = $hibernation_delayed_till;
		$this->created_at               = $created_at;
		$this->updated_at               = $updated_at;
	}
}
