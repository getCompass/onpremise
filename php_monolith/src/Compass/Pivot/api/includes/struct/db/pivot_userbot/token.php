<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_userbot.token_list
 */
class Struct_Db_PivotUserbot_Token {

	public string $token;
	public string $userbot_id;
	public int    $created_at;
	public int    $updated_at;
	public array  $extra;

	/**
	 * Struct_Db_PivotUserbot_Token constructor
	 *
	 */
	public function __construct(string $token, string $userbot_id, int $created_at, int $updated_at, array $extra) {

		$this->token      = $token;
		$this->userbot_id = $userbot_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}