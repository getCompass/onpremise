<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;

/**
 * Методы аутентификации по протоколу LDAP
 */
class Onpremiseweb_Ldap_Auth extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"tryAuthenticate",
	];

	/**
	 * метод для попытки аутентификации по протоколу LDAP
	 *
	 * @return array
	 */
	public function tryAuthenticate():array {

		$username = $this->post(\Formatter::TYPE_STRING, "username");
		$password = $this->post(\Formatter::TYPE_STRING, "password");

		try {
			$ldap_auth_token = Domain_Ldap_Scenario_Api::tryAuthenticate($username, $password);
		} catch (Domain_Ldap_Exception_Auth_BindFailed|Domain_Ldap_Exception_ProtocolError_InvalidCredentials|Domain_Ldap_Exception_ProtocolError_InvalidDnSyntax) {
			return $this->error(1708001, "invalid username or password");
		} catch (Domain_Ldap_Exception_ProtocolError_UnwillingToPerform) {
			return $this->error(1708002, "LDAP provider unwilling to perform this action");
		} catch (Domain_Ldap_Exception_ProtocolError) {
			throw new ParseFatalException("unepxected error");
		} catch (BlockException $e) {

			throw new CaseException(423, "begin method limit exceeded", [
				"expires_at" => $e->getExpire(),
			]);
		}

		return $this->ok([
			"ldap_auth_token" => (string) $ldap_auth_token,
		]);
	}
}