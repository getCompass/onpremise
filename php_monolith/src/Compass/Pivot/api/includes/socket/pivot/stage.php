<?php

namespace Compass\Pivot;

/**
 * Socket методы для работы с сервером STAGE
 */
class Socket_Pivot_Stage extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"registrationUser",
	];

	/**
	 * Регистрируем пользователя
	 * @return array
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_DamagedActionException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function registrationUser():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");

		try {
			[$user_id, $is_exist_user_id] = Domain_Stage_Scenario_Socket::registrationUser($phone_number);
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {
			throw new \BaseFrame\Exception\Request\ParamException("invalid phone number format");
		}

		return $this->ok([
			"user_id"          => (int) $user_id,
			"is_exist_user_id" => (int) $is_exist_user_id,
		]);
	}
}