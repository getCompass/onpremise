<?php declare(strict_types = 1);

namespace Compass\Announcement;

/** Класс-сущность для таблицы user_announcement */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_AnnouncementUser_UserAnnouncement extends Struct_Default {

	/** @var int Id анонса */
	public int $announcement_id;
	/** @var int Id пользователя */
	public int $user_id;
	/** @var bool Прочитан ли запрос */
	public bool $is_read;
	/** @var int Время создания */
	public int $created_at;
	/** @var int Время редактирования */
	public int $updated_at;
	/** @var int Время следующей отправки */
	public int $next_resend_at;
	/** @var int Время последней отправки */
	public int $resend_attempted_at;
	/** @var array Доп. данные */
	public array $extra;

	/**
	 * Struct_Db_Announcement_UserAnnouncement constructor
	 *
	 * @param int   $announcement_id
	 * @param int   $user_id
	 * @param bool  $is_read
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $next_resend_at
	 * @param int   $resend_attempted_at
	 * @param array $extra
	 */
	public function __construct(int $announcement_id, int $user_id, bool $is_read, int $created_at, int $updated_at, int $next_resend_at, int $resend_attempted_at, array $extra) {

		$this->announcement_id     = $announcement_id;
		$this->user_id             = $user_id;
		$this->is_read             = $is_read;
		$this->created_at          = $created_at;
		$this->updated_at          = $updated_at;
		$this->next_resend_at      = $next_resend_at;
		$this->resend_attempted_at = $resend_attempted_at;
		$this->extra               = $extra;
	}
}