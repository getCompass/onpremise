<?php

namespace Compass\Company;

/**
 * Action для изменения информации компании
 */
class Domain_Company_Action_ChangeInfo {

	/**
	 * Изменяем информацию компании
	 */
	public static function do(int $user_id, string|false $name, string|false $avatar_file_key):array {

		// меняем профиль компании в pivot
		[$current_name, $avatar_file_key] = Gateway_Socket_Pivot::changeInfoCompany($user_id, $name, $avatar_file_key);

		if ($name !== false) {

			// меняем имя компании в конфиге компании
			$value = ["value" => $name];
			Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_NAME);
		}

		// отправляем ивент об изменении профиля компании
		Gateway_Bus_Sender::companyProfileChanged($current_name, false, $avatar_file_key);

		// отмечаем в intercom, что изменилось имя пространства
		if ($name !== false) {
			Gateway_Socket_Intercom::spaceNameChanged($name);
		}

		return [$current_name, $avatar_file_key];
	}
}
