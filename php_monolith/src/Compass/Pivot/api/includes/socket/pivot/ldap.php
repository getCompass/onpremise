<?php

namespace Compass\Pivot;

/**
 * Socket методы для работы с LDAP
 */
class Socket_Pivot_Ldap extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"blockUserAuthentication",
		"kickUserFromAllCompanies",
		"unblockUserAuthentication",
		"isLdapAuthAvailable",
	];

	/**
	 * Блокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function blockUserAuthentication():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			Domain_User_Scenario_Socket::blockUserAuthentication($user_id);
		} catch (cs_UserAlreadyBlocked) {
			// если пользователь уже заблокирован, то ничего не делаем
		}

		return $this->ok();
	}

	/**
	 * Исключаем пользователя из всех команд
	 * @return array
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_DamagedActionException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function kickUserFromAllCompanies():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Phphooker_Main::kickUserFromAllCompanies($user_id);

		return $this->ok();
	}

	/**
	 * Разблокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function unblockUserAuthentication():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Domain_User_Scenario_Socket::unblockUserAuthentication($user_id);

		return $this->ok();
	}

	/**
	 * Включена ли возможность авторизации через LDAP
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function isLdapAuthAvailable():array {

		$is_ldap_auth_available = Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO) &&
			Domain_User_Entity_Auth_Config::getSsoProtocol() == Domain_User_Entity_Auth_Method::SSO_PROTOCOL_LDAP;

		return $this->ok([
			"is_available" => (int) $is_ldap_auth_available,
		]);
	}
}