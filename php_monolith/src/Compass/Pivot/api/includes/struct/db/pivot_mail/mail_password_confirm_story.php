<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail.mail_password_confirm_story
 */
class Struct_Db_PivotMail_MailPasswordConfirmStory {

	/**
	 * Struct_Db_PivotMail_MailPasswordConfirmStory constructor.
	 *
	 * @param int|null $confirm_mail_password_story_id
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
		public ?int   $confirm_mail_password_story_id,
		public int    $user_id,
		public int    $status,
		public int    $type,
		public int    $stage,
		public int    $error_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $expires_at,
		public string $session_uniq,
	) {

	}

	/**
	 * Составить объект из массива
	 *
	 * @param array $array
	 *
	 * @return Struct_Db_PivotMail_MailPasswordConfirmStory
	 */
	public static function fromArray(array $array):self {

		return new self(
			$array["confirm_mail_password_story_id"],
			$array["user_id"],
			$array["status"],
			$array["type"],
			$array["stage"],
			$array["error_count"],
			$array["created_at"],
			$array["updated_at"],
			$array["expires_at"],
			$array["session_uniq"],
		);
	}
}
