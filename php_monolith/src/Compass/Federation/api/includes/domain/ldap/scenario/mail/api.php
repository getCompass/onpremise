<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\System\Mail;

/**
 * класс содержит логику аутентификации по протоколу LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Scenario_Mail_Api {

	/**
	 * Добавляем почту для LDAP
	 *
	 * @param string $mail_confirm_story_map
	 * @param string $mail
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Auth_2faDisabled
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsExpired
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws Domain_Ldap_Exception_Mail_DomainNotAllowed
	 * @throws Domain_Ldap_Exception_Mail_ManualAddDisabled
	 * @throws Domain_Ldap_Exception_Mail_MaxErrorCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_MaxResendCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function add(string $mail_confirm_story_map, string $mail):array {

		// проверяем, что почта верная
		$mail_obj = new Mail($mail);
		$mail     = $mail_obj->mail();

		// проверяем, что двухфакторная авторизация включена и можно привязать почту
		$config_2fa = Domain_Ldap_Entity_2faConfig::instance();
		$config_2fa
			->assertAuthorization2FaEnabled()
			->assertMailManualAddEnabled()
			->assertMailDomainAllowed($mail_obj);

		$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::get($mail_confirm_story_map);

		// проверяем, что попытка все еще активная и мы находимся на правильном этапе
		$mail_confirm_story
			->assertIsActive()
			->assertNotExpired()
			->assertAddMailStage();

		try {

			Domain_Ldap_Entity_Mail_UserRel::getByMail($mail);

			// если запись нашли, значит почта уже занята, отдаем ошибку
			throw new Domain_Ldap_Exception_Mail_IsOccupied();
		} catch (Domain_Ldap_Exception_Mail_UserRelNotFound) {
			// почта свободна, продолжаем
		}
		
		$mail_confirm_via_code_story_list = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::getByMailConfirmStory($mail_confirm_story->mail_confirm_story_id);
		$confirm_counters                 = Domain_Ldap_Entity_Mail_ConfirmViaCodeStoryCounters::collect($mail_confirm_via_code_story_list);
		$confirm_counters->assertMaxErrorCountNotReached($mail_confirm_story->expires_at)->assertMaxResendCountNotReached();

		// создаем попытку отправки кода на почту
		$mail_confirm_via_code_story = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::create($mail_confirm_story->mail_confirm_story_id, $mail);

		// если до этого отправляли код - помечаем попытку проваленной
		if (count($mail_confirm_via_code_story_list) > 0) {
			$mail_confirm_via_code_story_list[0]->setFailed();
		}

		// переезжаем на следующий этап
		if ($mail_confirm_story->stage === Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_ENTER_NEW_MAIL) {
			$mail_confirm_story->moveToNextStage();
		}

		// отправляем код
		$mail_confirm_via_code_story->sendConfirmCode($mail_confirm_story->stage);

		$mail = new Mail($mail_confirm_via_code_story->mail);
		return Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story, $mail, $confirm_counters->code_available_attempts, $mail_confirm_via_code_story->next_resend_at);
	}

	/**
	 * пытаемся аутентифицировать учетную запись в LDAP
	 *
	 * @param string $mail_confirm_story_map
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Auth_2faDisabled
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsExpired
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws Domain_Ldap_Exception_Mail_ManualAddDisabled
	 * @throws Domain_Ldap_Exception_Mail_MaxErrorCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_MaxResendCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function change(string $mail_confirm_story_map):array {

		// проверяем, что двухфакторная авторизация включена и можно привязать почту
		$config_2fa = Domain_Ldap_Entity_2faConfig::instance();
		$config_2fa
			->assertAuthorization2FaEnabled()
			->assertMailManualAddEnabled();

		$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::get($mail_confirm_story_map);

		// проверяем, что попытка все еще активная и мы находимся на правильном этапе
		$mail_confirm_story
			->assertIsActive()
			->assertNotExpired()
			->assertValidStage(Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_CURRENT_MAIL);

		$mail_confirm_via_code_story_list = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::getByMailConfirmStory($mail_confirm_story->mail_confirm_story_id);
		$confirm_counters                 = Domain_Ldap_Entity_Mail_ConfirmViaCodeStoryCounters::collect($mail_confirm_via_code_story_list);
		$confirm_counters->assertMaxErrorCountNotReached($mail_confirm_story->expires_at)->assertMaxResendCountNotReached();
		$mail_confirm_via_code_story = $mail_confirm_via_code_story_list[0];

		// устанавливаем этап смены почты
		$mail_confirm_story->setStage(Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_CHANGING_MAIL);

		// отправляем код на текущую попытку входа
		$mail_confirm_via_code_story->sendConfirmCode($mail_confirm_story->stage);

		$mail = new Mail($mail_confirm_via_code_story->mail);
		return Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story, $mail, $confirm_counters->code_available_attempts, $mail_confirm_via_code_story->next_resend_at);
	}

	/**
	 * Подтвердить почту
	 *
	 * @param string $mail_confirm_story_map
	 * @param string $confirm_code
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Auth_2faDisabled
	 * @throws Domain_Ldap_Exception_Mail_CodeIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsExpired
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws Domain_Ldap_Exception_Mail_MaxErrorCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function confirm(string $mail_confirm_story_map, string $confirm_code):array {

		$mail_confirm_story_id = Type_Pack_MailConfirmStory::getId($mail_confirm_story_map);

		// проверяем, что двухфакторная авторизация включена и можно привязать почту
		$config_2fa = Domain_Ldap_Entity_2faConfig::instance();
		$config_2fa->assertAuthorization2FaEnabled();

		$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::get($mail_confirm_story_map);

		// проверяем, что попытка все еще активная и мы находимся на правильном этапе
		$mail_confirm_story
			->assertIsActive()
			->assertNotExpired()
			->assertConfirmStage();

		$mail_confirm_via_code_story_list = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::getByMailConfirmStory($mail_confirm_story_id);

		$confirm_counters = Domain_Ldap_Entity_Mail_ConfirmViaCodeStoryCounters::collect($mail_confirm_via_code_story_list);
		$confirm_counters->assertMaxErrorCountNotReached($mail_confirm_story->expires_at);

		try {

			$mail_confirm_via_code_story = $mail_confirm_via_code_story_list[0];
			$mail_confirm_via_code_story
				->assertActive()
				->assertValidCode($confirm_code);
		} catch (Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect $e) {

			// добавляем информацию о процессе подтверждения почты
			--$confirm_counters->code_available_attempts;

			$mail = new Mail($mail_confirm_via_code_story->mail);
			$e->makeLdapMailConfirmStoryInfo($mail_confirm_story, $mail, $confirm_counters->code_available_attempts, $mail_confirm_via_code_story->next_resend_at);
			throw $e;
		}

		// обновляем пользователю почту, если подтверждали новую
		if ($mail_confirm_story->stage === Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_NEW_MAIL) {

			Domain_Ldap_Entity_Mail_UserRel::create(
				$mail_confirm_story->uid,
				Domain_Ldap_Entity_Mail_UserRel::MAIL_SOURCE_MANUAL,
				$mail_confirm_via_code_story->mail,
				true
			);
		}

		// переходим на следущий этап
		$mail_confirm_story->moveToNextStage();

		return Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story);
	}

	/**
	 * Переотправить проверочный код
	 *
	 * @param string $mail_confirm_story_map
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Auth_2faDisabled
	 * @throws Domain_Ldap_Exception_Mail_CodeIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmCodeNotFound
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsExpired
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsNotActive
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws Domain_Ldap_Exception_Mail_IsBeforeNextResendAt
	 * @throws Domain_Ldap_Exception_Mail_MaxErrorCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_MaxResendCountIsReached
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function resendConfirmCode(string $mail_confirm_story_map):array {

		$mail_confirm_story_id = Type_Pack_MailConfirmStory::getId($mail_confirm_story_map);

		// проверяем, что двухфакторная авторизация включена и можно привязать почту
		$config_2fa = Domain_Ldap_Entity_2faConfig::instance();
		$config_2fa->assertAuthorization2FaEnabled();

		$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::get($mail_confirm_story_map);

		// проверяем, что попытка все еще активная и мы находимся на правильном этапе
		$mail_confirm_story
			->assertIsActive()
			->assertNotExpired()
			->assertConfirmStage();

		$mail_confirm_via_code_story_list = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::getByMailConfirmStory($mail_confirm_story_id);

		if ($mail_confirm_via_code_story_list === []) {
			throw new Domain_Ldap_Exception_Mail_ConfirmCodeNotFound();
		}

		$last_mail_confirm_story_via_code = $mail_confirm_via_code_story_list[0];
		$last_mail_confirm_story_via_code->assertActive()->assertIsAfterNextResendAt();

		$confirm_counters = Domain_Ldap_Entity_Mail_ConfirmViaCodeStoryCounters::collect($mail_confirm_via_code_story_list);
		$confirm_counters->assertMaxErrorCountNotReached($mail_confirm_story->expires_at)->assertMaxResendCountNotReached();

		// переотправляем код
		$last_mail_confirm_story_via_code->sendConfirmCode($mail_confirm_story->stage);

		$mail = new Mail($last_mail_confirm_story_via_code->mail);
		return Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story, $mail, $confirm_counters->code_available_attempts, $last_mail_confirm_story_via_code->next_resend_at);
	}
}