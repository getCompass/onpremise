<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * класс-структура для таблицы partner_invite_link.invite_code_list_mirror
 */
class Struct_Confirmation_Mail_Main {

	public int $confirm_mail_password_story_id;

	public int    $user_id;
	public int    $status;
	public int    $type;
	public int    $stage;
	public int    $error_count;
	public int    $created_at;
	public int    $updated_at;
	public int    $expires_at;
	public string $session_uniq;
	public string $confirm_mail_password_story_map;

	/**
	 * Struct_Db_PartnerInviteLink_InviteCodeMirror constructor.
	 *
	 * @param Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story
	 *
	 * @throws \parseException
	 */
	public function __construct(Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story) {

		$this->confirm_mail_password_story_id = $mail_password_confirm_story->confirm_mail_password_story_id;
		$this->status                         = $mail_password_confirm_story->status;
		$this->type                           = $mail_password_confirm_story->type;
		$this->stage                          = $mail_password_confirm_story->stage;
		$this->error_count                    = $mail_password_confirm_story->error_count;
		$this->created_at                     = $mail_password_confirm_story->created_at;
		$this->updated_at                     = $mail_password_confirm_story->updated_at;
		$this->expires_at                     = $mail_password_confirm_story->expires_at;
		$this->session_uniq                   = $mail_password_confirm_story->session_uniq;

		$this->confirm_mail_password_story_map = Type_Pack_MailPasswordConfirmStory::doPack($this->confirm_mail_password_story_id, $this->type, $this->created_at);
	}
}