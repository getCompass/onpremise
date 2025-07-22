<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Socket методы для работы с LDAP
 */
class Socket_Pivot_Ldap extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"blockUserAuthentication",
		"kickUserFromAllCompanies",
		"unblockUserAuthentication",
		"isLdapAuthAvailable",
		"getUserInfo",
		"actualizeProfileData",
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

		$is_ldap_auth_available = (
				Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO)
				|| Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO)
			) && Domain_User_Entity_Auth_Config::getSsoProtocol() == Domain_User_Entity_Auth_Method::SSO_PROTOCOL_LDAP;

		return $this->ok([
			"is_available" => (int) $is_ldap_auth_available,
		]);
	}

	/**
	 * Получаем данные по пользователю для мапинга
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	public function getUserInfo():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			return $this->error(1315001, "user not found");
		}

		return $this->ok([
			"user_info" => (array) $user_info,
		]);
	}

	/**
	 * Актуализируем данные пользователя
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws \busException
	 * @throws \cs_CurlError
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_FileIsNotImage
	 */
	public function actualizeProfileData():array {

		$user_id           = $this->post(\Formatter::TYPE_INT, "user_id");
		$ldap_account_data = $this->post(\Formatter::TYPE_ARRAY, "ldap_account_data");

		Domain_User_Scenario_Socket::actualizeProfileData($user_id, Struct_User_Auth_Ldap_AccountData::arrayToStruct($ldap_account_data));

		return $this->ok();
	}
}