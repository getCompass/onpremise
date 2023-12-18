<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Онбординги пользователей
 */
class Apiv2_User_Onboarding extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"finish",
		"start",
	];

	/**
	 * Завершаем онбординг
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public function finish():array {

		$type = $this->post(\Formatter::TYPE_STRING, "type");

		try {
			Domain_User_Scenario_Api::finishOnboarding($this->user_id, $type);
		} catch (Domain_User_Exception_Onboarding_NotAllowedStatusStep) {
			throw new CaseException(1208003, "onboarding cant be finished");
		} catch (Domain_User_Exception_Onboarding_NotAllowedType) {
			throw new ParamException("invalid type");
		}

		return $this->ok();
	}

	/**
	 * Стартуем выбранный онбординг
	 *
	 * @throws CaseException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 * @throws \busException
	 */
	public function start():array {

		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");
		$type     = $this->post(\Formatter::TYPE_STRING, "type", "space_creator_lite");

		try {
			Domain_User_Scenario_Api::startOnboarding($this->user_id, $type, $space_id);
		} catch (Domain_User_Exception_Onboarding_NotAllowedStatusStep) {
			throw new CaseException(1208003, "onboarding cant be started");
		} catch (Domain_User_Exception_Onboarding_NotAllowedType) {
			throw new ParamException("invalid type");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("incorrect space_id {$space_id}");
		} catch (cs_CompanyNotExist) {
			throw new ParamException("space {$space_id} is not exist");
		} catch (cs_UserIsNotCreatorOfCompany) {
			throw new ParamException("user is not creator of space {$space_id}");
		}

		return $this->ok();
	}
}