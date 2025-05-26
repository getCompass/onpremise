<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_phone.phone_banned
 */
class Struct_Db_PivotPhone_PhoneBanned {

	public string $phone_number_hash;
	public string $comment;
	public int    $created_at;

	public function __construct(
		string $phone_number_hash,
		string $comment,
		int    $created_at
	) {

		$this->phone_number_hash = $phone_number_hash;
		$this->comment           = $comment;
		$this->created_at        = $created_at;
	}
}