<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для работы с SSO
 */
class Socket_Sso extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"hasUserRelationship",
		"deleteUserRelationship",
	];

	/**
	 * проверяем, что связь «SSO аккаунт (по любому из протоколов – oidc, ldap, ...)» – «Пользователь Compass» существует
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function hasUserRelationship():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$has_user_relationship = Domain_Oidc_Scenario_Socket_Auth::hasUserRelationship($user_id);
		$has_user_relationship = $has_user_relationship ?: Domain_Ldap_Scenario_Socket::hasUserRelationship($user_id);

		return $this->ok([
			"has_user_relationship" => (int) intval($has_user_relationship),
		]);
	}

	/**
	 * проверяем, что связь «SSO аккаунт (по любому из протоколов – oidc, ldap, ...)» – «Пользователь Compass» существует
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function deleteUserRelationship():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Domain_Oidc_Scenario_Socket_Auth::deleteUserRelationship($user_id);
		Domain_Ldap_Scenario_Socket::deleteUserRelationship($user_id);

		return $this->ok();
	}
}