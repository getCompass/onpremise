<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\AnswerCommandException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы аутентификации по протоколу LDAP
 */
class Onpremiseweb_Ldap_Auth extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getOptions",
		"getToken",
	];

	/**
	 * метод для получения параметров аутентификации по протоколу LDAP
	 *
	 * @return array
	 */
	public function getOptions():array {

		return $this->ok([
			"authentication_timeout" => (int) Domain_Ldap_Entity_Client::DEFAULT_CONNECTION_TIMEOUT,
		]);
	}

	/**
	 * Получить токен авторизации
	 *
	 * @return array
	 * @throws AnswerCommandException
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function getToken():array {

		$username               = $this->post(\Formatter::TYPE_STRING, "username");
		$password               = $this->post(\Formatter::TYPE_STRING, "password");
		$mail_confirm_story_key = $this->post(\Formatter::TYPE_STRING, "mail_confirm_story_key", false);

		try {

			$mail_confirm_story_map = false;
			if ($mail_confirm_story_key) {
				$mail_confirm_story_map = Type_Pack_MailConfirmStory::doDecrypt($mail_confirm_story_key);
			}

			$ldap_auth_token = Domain_Ldap_Scenario_Api::getToken($username, $password, $mail_confirm_story_map);;
		} catch (Domain_Ldap_Exception_Mail_LdapMailNotFound) {
			throw new CaseException(1708015, "ldap mail required");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("mail confirm story key is invalid");
		} catch (Domain_Ldap_Exception_Mail_ConfirmStoryNotFound) {
			throw new CaseException(1708006, "confirm is expired");
		} catch (Domain_Ldap_Exception_Mail_StageIsInvalid) {
			throw new CaseException(1708013, "cant be on that stage");
		} catch (Domain_Ldap_Exception_Auth_BindFailed|Domain_Ldap_Exception_ProtocolError_InvalidCredentials|Domain_Ldap_Exception_ProtocolError_InvalidDnSyntax) {
			return $this->error(1708001, "invalid username or password");
		} catch (Domain_Ldap_Exception_ProtocolError_UnwillingToPerform) {
			return $this->error(1708002, "LDAP provider unwilling to perform this action");
		} catch (Domain_Ldap_Exception_ProtocolError_TimeoutExceeded) {
			return $this->error(1708004, "timeout exceeded");
		} catch (Domain_Ldap_Exception_ProtocolError_FilterError) {
			return $this->error(1708003, "incorrect ldap.user_search_filter");
		} catch (Domain_Ldap_Exception_ProtocolError) {
			throw new ParseFatalException("unexpected error");
		} catch (BlockException $e) {

			throw new CaseException(423, "begin method limit exceeded", [
				"expires_at" => $e->getExpire(),
			]);
		}

		return $this->ok([
			"ldap_auth_token" => $ldap_auth_token,
		]);
	}
}