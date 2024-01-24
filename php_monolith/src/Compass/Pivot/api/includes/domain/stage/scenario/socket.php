<?php

namespace Compass\Pivot;

/**
 * сценарий Stage Socket
 */
class Domain_Stage_Scenario_Socket {

	/**
	 * Сценарий регистрации пользователя на STAGE
	 *
	 * @param string $phone_number
	 *
	 * @return array
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_DamagedActionException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function registrationUser(string $phone_number):array {

		// проверяем что запущено не на паблике
		assertNotPublicServer();

		// валидируем номер телефона
		$phone_number = (new \BaseFrame\System\PhoneNumber($phone_number))->number();

		// проверяем зарегистрирован ли уже пользователь с таким номером телефона
		try {

			$user_id          = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
			$is_exist_user_id = 1;
		} catch (cs_PhoneNumberNotFound) {

			// регистрируем
			$user             = Domain_User_Action_Create_Human::do($phone_number, getUa(), getIp(), "", "", []);
			$user_id          = $user->user_id;
			$is_exist_user_id = 0;
		}

		return [
			$user_id,
			$is_exist_user_id,
		];
	}
}
