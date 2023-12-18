<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * Методы для отправки приглашений
 */
class Socket_Pivot_Security extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"doGenerateTwoFaToken",
		"tryValidateTwoFaToken",
		"setTwoFaTokenAsInactive",
	];

	/**
	 * Сгенерировть 2fa токен
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function doGenerateTwoFaToken():array {

		$action_type = $this->post(\Formatter::TYPE_INT, "action_type");
		$company_id  = $this->post(\Formatter::TYPE_INT, "company_id");

		try {

			$two_fa      = Domain_User_Scenario_Socket::doGenerateTwoFaToken($this->user_id, $company_id, $action_type);
			$two_fa_info = Apiv1_Pivot_Format::twoFaInfo($two_fa);
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		}

		return $this->ok(Type_Pack_Main::replaceMapWithKeys($two_fa_info));
	}

	/**
	 * Валидировать 2fa токен
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 */
	public function tryValidateTwoFaToken():array {

		$action_type = $this->post(\Formatter::TYPE_INT, "action_type");
		$two_fa_key  = $this->post(\Formatter::TYPE_STRING, "two_fa_key");

		try {
			Domain_User_Scenario_Socket::tryValidateTwoFaToken($this->user_id, $this->company_id, $action_type, $two_fa_key);
		} catch (\cs_DecryptHasFailed|cs_TwoFaInvalidUser|cs_TwoFaInvalidCompany|cs_TwoFaTypeIsInvalid|cs_WrongTwoFaKey) {
			return $this->error(2302, "Invalid 2fa key");
		} catch (cs_TwoFaIsFinished) {
			return $this->error(2301, "2fa already finished");
		} catch (cs_TwoFaIsExpired) {
			return $this->error(2300, "2fa is expired");
		} catch (cs_TwoFaIsNotActive) {
			return $this->error(2303, "2fa is not active");
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		}

		return $this->ok();
	}

	/**
	 * Пометить токен как неактивным
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function setTwoFaTokenAsInactive():array {

		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key");
		$two_fa_map = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);

		Domain_User_Scenario_Socket::setTwoFaTokenAsInactive($this->user_id, $two_fa_map);

		return $this->ok();
	}
}
