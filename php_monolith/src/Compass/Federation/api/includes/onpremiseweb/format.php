<?php

namespace Compass\Federation;

use BaseFrame\System\Mail;

/**
 * Методы аутентификации по протоколу LDAP
 */
class Onpremiseweb_Format
{
	/**
	 * Информация о подтверждении почты
	 */
	public static function ldapMailConfirmStoryInfo(Domain_Ldap_Entity_Mail_ConfirmStory $mail_confirm_story, ?Mail $mail = null, int $code_available_attempts = 0, int $next_resend_at = 0): array
	{

		$formatted_mail = "";

		if (!is_null($mail)) {
			$formatted_mail = $mail_confirm_story->stage === Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_NEW_MAIL ? $mail->mail() : $mail->obfuscate();
		}
		
		return [
			"mail_confirm_story_key" => Type_Pack_MailConfirmStory::doEncrypt($mail_confirm_story->mail_confirm_story_map),
			"scenario"               => "default_confirm",
			"scenario_data"          => [
				"is_manual_add_enabled"   => intval(Domain_Ldap_Entity_2faConfig::instance()->mail_mapped_field === ""),
				"stage"                   => $mail_confirm_story->getFormattedStage(),
				"mail_mask"               => $formatted_mail,
				"next_resend_at"          => $next_resend_at,
				"code_available_attempts" => $code_available_attempts,
				"expires_at"              => $mail_confirm_story->expires_at,
			],
		];
	}
}
