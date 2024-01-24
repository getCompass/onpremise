<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\BlockException;

/**
 * Class Apiv1_Company_Auth
 */
class Apiv1_Auth extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"tryLogin",
		"doLogout",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"tryLogin",
		"doLogout",
	];

	/**
	 * Начинаем процесс авторизации в компании
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \busException
	 * @throws cs_PlatformNotFound
	 * @throws \cs_SessionNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function tryLogin():array {

		$user_id                    = $this->post(\Formatter::TYPE_INT, "user_id");
		$user_company_session_token = $this->post(\Formatter::TYPE_STRING, "user_company_session_token");

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row42");
			Domain_User_Scenario_Api::tryLoginInCompany($this->user_id, $user_id, $user_company_session_token);
		} catch (cs_InvalidUserCompanySessionToken) {

			Gateway_Bus_CollectorAgent::init()->inc("row43");
			throw new ParamException("passed unknown token");
		} catch (cs_IsNotEqualsPinCode) {

			Gateway_Bus_CollectorAgent::init()->inc("row44");
			return $this->error(1003);
		} catch (\cs_UserIsNotMember) {

			Gateway_Bus_CollectorAgent::init()->inc("row45");
			return $this->error(1002);
		} catch (BlockException $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row46");
			return $this->error(1006, "company auth blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {

			Gateway_Bus_CollectorAgent::init()->inc("row47");
			return $this->ok();
		} catch (cs_CompanyIsDeleted) {

			// exception выбросится если еще не успели отбиндить mysql
			return $this->error(2502, "company is deleted");
		} catch (cs_CompanyIsNotExist) {
			throw new CompanyNotServedException("company does not exist, probably subdomain is wrong");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("passed incorrect user_id");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row47");
		return $this->ok();
	}

	/**
	 * Разлогиниваемся из компании
	 */
	public function doLogout():array {

		Gateway_Bus_CollectorAgent::init()->inc("row48");
		Domain_User_Scenario_Api::doLogout($this->user_id);
		Gateway_Bus_CollectorAgent::init()->inc("row49");

		return $this->ok();
	}
}