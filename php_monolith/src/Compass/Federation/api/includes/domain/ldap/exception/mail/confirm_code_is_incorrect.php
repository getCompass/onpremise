<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;
use BaseFrame\System\Mail;

/**
 * Исключение, когда код подтверждения неверный
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect extends DomainException {

    public array $ldap_mail_confirm_story_info = [];

	public function __construct(string $message = "confirm code is incorrect") {

		parent::__construct($message);
	}

    /**
     * Добавить информацию о процессе подтверждения
     *
     * @param Domain_Ldap_Entity_Mail_ConfirmStory $mail_confirm_story
     * @param int $code_available_attempts
     * @param int $next_resend_at
     * @return void
     */
    public function makeLdapMailConfirmStoryInfo(Domain_Ldap_Entity_Mail_ConfirmStory $mail_confirm_story, ?Mail $mail, int $code_available_attempts, int $next_resend_at) {

        $this->ldap_mail_confirm_story_info = Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story, $mail, $code_available_attempts, $next_resend_at);
    }
}