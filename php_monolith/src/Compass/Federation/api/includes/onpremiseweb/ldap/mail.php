<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы аутентификации по протоколу LDAP
 */
class Onpremiseweb_Ldap_Mail extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"add",
		"change",
		"confirm",
		"resendConfirmCode",
	];

	/**
	 * метод для добавления почты
	 *
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function add():array {

		$mail_confirm_story_key = $this->post(\Formatter::TYPE_STRING, "mail_confirm_story_key");
		$mail                   = $this->post(\Formatter::TYPE_STRING, "mail");
		try {

			$mail_confirm_story_map       = Type_Pack_MailConfirmStory::doDecrypt($mail_confirm_story_key);
			$ldap_mail_confirm_story_info = Domain_Ldap_Scenario_Mail_Api::add($mail_confirm_story_map, $mail);
		} catch (\cs_DecryptHasFailed|InvalidMail) {
			throw new ParamException("invalid mail confirm story key");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsExpired|Domain_Ldap_Exception_Mail_ConfirmStoryNotFound) {
			throw new CaseException(1708006, "confirm is expired");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsNotActive) {
			throw new CaseException(1708008, "confirm is not active");
		} catch (Domain_Ldap_Exception_Mail_MaxErrorCountIsReached $e) {

			throw new CaseException(1708009, "max error count is reached", [
				"expires_at" => $e->getExpiresAt(),
			]);
		} catch (Domain_Ldap_Exception_Mail_StageIsInvalid) {
			throw new CaseException(1708013, "cant be on that stage");
		} catch (Domain_Ldap_Exception_Mail_DomainNotAllowed) {

			throw new CaseException(1708011, "mail domain is not allowed", [
				"mail_allowed_domains" => Domain_Ldap_Entity_2faConfig::instance()->mail_allowed_domains,
			]);
		} catch (Domain_Ldap_Exception_Mail_ManualAddDisabled) {
			throw new CaseException(1708012, "mail manual add disabled");
		} catch (Domain_Ldap_Exception_Mail_MaxResendCountIsReached) {
			throw new CaseException(1708010, "max resend count is reached");
		} catch (Domain_Ldap_Exception_Auth_2faDisabled) {
			throw new CaseException(1708016, "2fa is disabled");
		} catch (Domain_Ldap_Exception_Mail_IsOccupied) {
			throw new CaseException(1708017, "mail is occupied");
		}

		return $this->ok([
			"ldap_mail_confirm_story_info" => (object) $ldap_mail_confirm_story_info,
		]);
	}

	/**
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function resendConfirmCode():array {

		$mail_confirm_story_key = $this->post(\Formatter::TYPE_STRING, "mail_confirm_story_key");
		try {

			$mail_confirm_story_map       = Type_Pack_MailConfirmStory::doDecrypt($mail_confirm_story_key);
			$ldap_mail_confirm_story_info = Domain_Ldap_Scenario_Mail_Api::resendConfirmCode($mail_confirm_story_map);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid mail confirm story key");
		} catch (Domain_Ldap_Exception_Mail_CodeIsNotActive|Domain_Ldap_Exception_Mail_ConfirmCodeNotFound) {
			throw new CaseException(1708005, "code is not active");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsExpired|Domain_Ldap_Exception_Mail_ConfirmStoryNotFound) {
			throw new CaseException(1708006, "confirm is expired");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsNotActive) {
			throw new CaseException(1708008, "confirm is not active");
		} catch (Domain_Ldap_Exception_Mail_MaxErrorCountIsReached $e) {

			throw new CaseException(1708009, "max error count is reached", [
				"expires_at" => $e->getExpiresAt(),
			]);
		} catch (Domain_Ldap_Exception_Mail_MaxResendCountIsReached) {
			throw new CaseException(1708010, "max resend count is reached");
		} catch (Domain_Ldap_Exception_Mail_StageIsInvalid) {
			throw new CaseException(1708013, "cant be on that stage");
		} catch (Domain_Ldap_Exception_Mail_IsBeforeNextResendAt) {
			throw new CaseException(1708014, "cant send confirm code now");
		} catch (Domain_Ldap_Exception_Auth_2faDisabled) {
			throw new CaseException(1708016, "2fa is disabled");
		}

		return $this->ok([
			"ldap_mail_confirm_story_info" => (object) $ldap_mail_confirm_story_info,
		]);
	}

	/**
	 * Подтвердить почту
	 *
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public function confirm():array {

		$mail_confirm_story_key = $this->post(\Formatter::TYPE_STRING, "mail_confirm_story_key");
		$confirm_code           = $this->post(\Formatter::TYPE_STRING, "confirm_code");

		try {

			$mail_confirm_story_map = Type_Pack_MailConfirmStory::doDecrypt($mail_confirm_story_key);

			$ldap_mail_confirm_story_info = Domain_Ldap_Scenario_Mail_Api::confirm(
				$mail_confirm_story_map,
				$confirm_code,
			);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid mail confirm story key");
		} catch (Domain_Ldap_Exception_Mail_CodeIsNotActive) {
			throw new CaseException(1708005, "code is not active");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsExpired|Domain_Ldap_Exception_Mail_ConfirmStoryNotFound) {
			throw new CaseException(1708006, "confirm is expired");
		} catch (Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect $e) {

			throw new CaseException(1708007, "confirm code is incorrect", [
				"ldap_mail_confirm_story_info" => (object) $e->ldap_mail_confirm_story_info,
			]);
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsNotActive) {
			throw new CaseException(1708008, "confirm is not active");
		} catch (Domain_Ldap_Exception_Mail_MaxErrorCountIsReached $e) {

			throw new CaseException(1708009, "max error count is reached", [
				"expires_at" => $e->getExpiresAt(),
			]);
		} catch (Domain_Ldap_Exception_Mail_StageIsInvalid) {
			throw new CaseException(1708013, "cant be on that stage");
		} catch (Domain_Ldap_Exception_Auth_2faDisabled) {
			throw new CaseException(1708016, "2fa is disabled");
		}

		return $this->ok([
			"ldap_mail_confirm_story_info" => (object) $ldap_mail_confirm_story_info,
		]);
	}

	/**
	 * Изменить почту
	 *
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function change():array {

		$mail_confirm_story_key = $this->post(\Formatter::TYPE_STRING, "mail_confirm_story_key");

		try {

			$mail_confirm_story_map       = Type_Pack_MailConfirmStory::doDecrypt($mail_confirm_story_key);
			$ldap_mail_confirm_story_info = Domain_Ldap_Scenario_Mail_Api::change($mail_confirm_story_map);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid mail confirm story key");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsExpired|Domain_Ldap_Exception_Mail_ConfirmStoryNotFound) {
			throw new CaseException(1708006, "confirm is expired");
		} catch (Domain_Ldap_Exception_Mail_ConfirmIsNotActive) {
			throw new CaseException(1708008, "confirm is not active");
		} catch (Domain_Ldap_Exception_Mail_MaxErrorCountIsReached $e) {

			throw new CaseException(1708009, "max error count is reached" , [
				"expires_at" => $e->getExpiresAt()
			]);
		} catch (Domain_Ldap_Exception_Mail_StageIsInvalid) {
			throw new CaseException(1708013, "cant be on that stage");
		} catch (Domain_Ldap_Exception_Auth_2faDisabled) {
			throw new CaseException(1708016, "2fa is disabled");
		} catch (Domain_Ldap_Exception_Mail_ManualAddDisabled) {
			throw new CaseException(1708012, "mail manual add disabled");
		} catch (Domain_Ldap_Exception_Mail_MaxResendCountIsReached) {
			throw new CaseException(1708010, "max resend count is reached");
		}

		return $this->ok([
			"ldap_mail_confirm_story_info" => (object) $ldap_mail_confirm_story_info,
		]);
	}
}