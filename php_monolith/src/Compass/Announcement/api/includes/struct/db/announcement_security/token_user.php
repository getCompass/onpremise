<?php declare(strict_types = 1);

namespace Compass\Announcement;

/** Класс-сущность для таблицы token_user */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_AnnouncementSecurity_TokenUser extends Struct_Default {

	/** @var string токен */
	public string $token;
	/** @var string Ключ сессии */
	public string $bound_session_key;
	/** @var int Id пользователя */
	public int $user_id;
	/** @var int Время создания */
	public int $created_at;
	/** @var int Время редактирования */
	public int $updated_at;
	/** @var int Время истечения */
	public int $expires_at;

	/**
	 * Struct_Db_AnnouncementSecurity_TokenUser constructor
	 *
	 * @param string $token
	 * @param int    $user_id
	 * @param string $bound_session_key
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $expires_at
	 */
	public function __construct(string $token, int $user_id, string $bound_session_key, int $created_at, int $updated_at, int $expires_at) {

		$this->token             = $token;
		$this->user_id           = $user_id;
		$this->bound_session_key = $bound_session_key;
		$this->created_at        = $created_at;
		$this->updated_at        = $updated_at;
		$this->expires_at        = $expires_at;
	}
}