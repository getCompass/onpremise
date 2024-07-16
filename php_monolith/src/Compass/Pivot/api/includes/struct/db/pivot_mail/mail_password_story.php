<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail.mail_password_story
 */
class Struct_Db_PivotMail_MailPasswordStory {

	/**
	 * Struct_Db_PivotMail_MailPasswordStory constructor.
	 *
	 * @param int|null $password_mail_story_id
	 * @param int      $user_id
	 * @param int      $status
	 * @param int      $type
	 * @param int      $stage
	 * @param int      $created_at
	 * @param int      $updated_at
	 * @param int      $error_count
	 * @param int      $expires_at
	 * @param string   $session_uniq
	 */
	public function __construct(
		public ?int $password_mail_story_id,
		public int $user_id,
		public int $status,
		public int $type,
		public int $stage,
		public int $created_at,
		public int $updated_at,
		public int $error_count,
		public int $expires_at,
		public string $session_uniq,
	) {

	}
}
