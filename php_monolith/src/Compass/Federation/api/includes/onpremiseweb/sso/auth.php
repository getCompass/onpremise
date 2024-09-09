<?php

namespace Compass\Federation;

use BaseFrame\Exception\Request\ParamException;

/**
 * Методы аутентификации через SSO по протоколу OpenID Connect
 */
class Onpremiseweb_Sso_Auth extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"begin",
		"getStatus",
	];

	/**
	 * метод запускает попытку аутентификации через sso
	 *
	 * @return array
	 */
	public function begin():array {

		$redirect_url = $this->post(\Formatter::TYPE_STRING, "redirect_url", false);

		$sso_auth = Domain_Oidc_Scenario_OnPremiseWeb_Auth::begin($redirect_url);

		return $this->ok([
			"link"           => (string) $sso_auth->link,
			"sso_auth_token" => (string) $sso_auth->sso_auth_token,
			"signature"      => (string) $sso_auth->signature,
		]);
	}

	/**
	 * определяем статус
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getStatus():array {

		$sso_auth_token = $this->post(\Formatter::TYPE_STRING, "sso_auth_token");
		$signature      = $this->post(\Formatter::TYPE_STRING, "signature");

		try {
			$status = Domain_Oidc_Scenario_OnPremiseWeb_Auth::getStatus($sso_auth_token, $signature);
		} catch (Domain_Oidc_Exception_Auth_TokenNotFound|Domain_Oidc_Exception_Auth_SignatureMismatch) {
			throw new ParamException("incorrect data");
		}

		return $this->ok([
			"status" => (string) $status,
		]);
	}
}