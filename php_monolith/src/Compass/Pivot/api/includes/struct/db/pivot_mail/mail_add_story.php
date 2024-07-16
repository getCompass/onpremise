<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail.mail_add_story
 */
class Struct_Db_PivotMail_MailAddStory {

	/**
	 * Struct_Db_PivotMail_MailAddStory constructor.
	 *
	 * @param int|null $add_mail_story_id
	 * @param int      $user_id
	 * @param int      $status
	 * @param int      $stage
	 * @param int      $created_at
	 * @param int      $updated_at
	 * @param int      $error_count
	 * @param int      $expires_at
	 * @param string   $session_uniq
	 */
	public function __construct(
		public ?int $add_mail_story_id,
		public int $user_id,
		public int $status,
		public int $stage,
		public int $created_at,
		public int $updated_at,
		public int $error_count,
		public int $expires_at,
		public string $session_uniq,
	) {

	}
}
