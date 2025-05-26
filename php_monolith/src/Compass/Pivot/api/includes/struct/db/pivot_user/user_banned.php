<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_banned
 */
class Struct_Db_PivotUser_UserBanned {

	public int    $user_id;
	public string $comment;
	public int    $created_at;

	public function __construct(
		int    $user_id,
		string $comment,
		int    $created_at
	) {

		$this->user_id    = $user_id;
		$this->comment    = $comment;
		$this->created_at = $created_at;
	}
}