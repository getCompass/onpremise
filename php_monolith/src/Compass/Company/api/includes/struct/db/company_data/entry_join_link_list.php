<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.entry_join_link_list
 */
class Struct_Db_CompanyData_EntryJoinLinkList {

	public int    $entry_id;
	public string $join_link_uniq;
	public int    $hiring_request_id;
	public int    $inviter_user_id;
	public int    $created_at;

	/**
	 * Struct_Db_CompanyData_EntryJoinLinkList constructor.
	 *
	 * @param int    $entry_id
	 * @param string $join_link_uniq
	 * @param int    $inviter_user_id
	 * @param int    $created_at
	 */
	public function __construct(int $entry_id, string $join_link_uniq, int $inviter_user_id, int $created_at) {

		$this->entry_id        = $entry_id;
		$this->join_link_uniq  = $join_link_uniq;
		$this->inviter_user_id = $inviter_user_id;
		$this->created_at      = $created_at;
	}

}