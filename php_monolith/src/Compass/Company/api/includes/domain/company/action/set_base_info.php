<?php

namespace Compass\Company;

/**
 * Action для изменения основных данных компании
 */
class Domain_Company_Action_SetBaseInfo {

	/**
	 * Изменяем данные компании
	 *
	 * @param int          $user_id
	 * @param string|false $name
	 * @param int|false    $avatar_color_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string|false $name, int|false $avatar_color_id):array {

		// меняем профиль компании в pivot
		[$current_name, $current_avatar_color_id] = Gateway_Socket_Pivot::setCompanyBaseInfo($user_id, $name, $avatar_color_id);

		if ($name !== false) {

			// меняем имя компании в конфиге компании
			$value = ["value" => $name];
			Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_NAME);
		}

		// отправляем ивент об изменении профиля компании
		Gateway_Bus_Sender::companyProfileChanged($current_name, $current_avatar_color_id, false);

		// отмечаем в intercom, что изменилось имя пространства
		if ($name !== false) {
			Gateway_Socket_Intercom::spaceNameChanged($name);
		}

		return [$current_name, $current_avatar_color_id];
	}
}
