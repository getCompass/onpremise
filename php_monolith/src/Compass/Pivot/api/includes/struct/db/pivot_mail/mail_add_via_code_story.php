<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail.mail_add_via_code_story
 */
class Struct_Db_PivotMail_MailAddViaCodeStory {

	/**
	 * Struct_Db_PivotMail_MailAddViaCodeStory constructor.
	 *
	 * @param int    $add_mail_story_id
	 * @param string $mail
	 * @param int    $status
	 * @param int    $stage
	 * @param int    $resend_count
	 * @param int    $error_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $next_resend_at
	 * @param string $message_id
	 * @param string $code_hash
	 */
	public function __construct(
		public int $add_mail_story_id,
		public string $mail,
		public int $status,
		public int $stage,
		public int $resend_count,
		public int $error_count,
		public int $created_at,
		public int $updated_at,
		public int $next_resend_at,
		public string $message_id,
		public string $code_hash,
	) {

	}
}