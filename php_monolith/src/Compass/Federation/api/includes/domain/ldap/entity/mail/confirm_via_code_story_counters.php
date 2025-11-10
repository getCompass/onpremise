<?php

namespace Compass\Federation;

/**
 * класс для суммирования и проверки счетчиков в истории
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Mail_ConfirmViaCodeStoryCounters {

	public int $code_available_attempts;
	public int $total_error_count;
	public int $total_resend_count;

	private function __construct() {
	}

	/**
	 * Собираем счетчики
	 *
	 * @param array $mail_confirm_via_code_story_list
	 *
	 * @return self
	 */
	public static function collect(array $mail_confirm_via_code_story_list):self {

		$mail_confirmed_counters = new self();

		[
			$mail_confirmed_counters->total_error_count,
			$mail_confirmed_counters->code_available_attempts,
			$mail_confirmed_counters->total_resend_count,
		] =
			self::_getConfirmCodeCounters($mail_confirm_via_code_story_list);

		return $mail_confirmed_counters;
	}

	/**
	 * Проверить, что не достигли максимального количества ошибок
	 *
	 * @return $this
	 * @throws Domain_Ldap_Exception_Mail_MaxErrorCountIsReached
	 */
	public function assertMaxErrorCountNotReached(int $expires_at):self {

		if ($this->total_error_count >= Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::MAX_ERROR_COUNT) {
			throw new Domain_Ldap_Exception_Mail_MaxErrorCountIsReached($expires_at);
		}

		return $this;
	}

	/**
	 * Проверить, что не достиггли максимального количества переотправок
	 * @return $this
	 * @throws Domain_Ldap_Exception_Mail_MaxResendCountIsReached
	 */
	public function assertMaxResendCountNotReached():self {

		if ($this->total_resend_count >= Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::MAX_RESEND_COUNT) {
			throw new Domain_Ldap_Exception_Mail_MaxResendCountIsReached();
		}

		return $this;
	}

	/**
	 * Получить счетчики ошибок и переотправки для каждой почты в попытке
	 *
	 * @param array $mail_confirm_via_code_story_list
	 *
	 * @return array
	 */
	protected static function _getConfirmCodeCounters(array $mail_confirm_via_code_story_list):array {

		$total_error_count  = 0;
		$total_resend_count = 0;
		foreach ($mail_confirm_via_code_story_list as $mail_confirm_via_code_story) {

			$total_error_count  += $mail_confirm_via_code_story->error_count;
			$total_resend_count += $mail_confirm_via_code_story->resend_count;
		}

		return [
			$total_error_count,
			Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::MAX_ERROR_COUNT - $total_error_count,
			$total_resend_count,
		];
	}
}