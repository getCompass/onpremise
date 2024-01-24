<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.company_inbox_{1}
 */
class Struct_Db_PivotUser_CompanyInbox {

	public int $user_id;
	public int $company_id;
	public int $messages_unread_count;
	public int $inbox_unread_count;
	public int $created_at;
	public int $updated_at;

	public function __construct(
		int $user_id,
		int $company_id,
		int $messages_unread_count,
		int $inbox_unread_count,
		int $created_at,
		int $updated_at,

	) {

		$this->user_id               = $user_id;
		$this->company_id            = $company_id;
		$this->messages_unread_count = $messages_unread_count;
		$this->inbox_unread_count    = $inbox_unread_count;
		$this->created_at            = $created_at;
		$this->updated_at            = $updated_at;
	}
}