<?php

namespace Compass\Company;

/**
 * Action для изменения имени компании
 */
class Domain_Company_Action_SetName {

	/**
	 * Изменяем имя компании
	 *
	 * @param int    $user_id
	 * @param string $name
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $name):void {

		// меняем имя компании в pivot
		Gateway_Socket_Pivot::setName($user_id, $name);

		// меняем имя компании в конфиге компании
		$value = ["value" => $name];
		Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_NAME);

		// отправляем ивент об изменении имени компании
		Gateway_Bus_Sender::companyProfileChanged($name, false, false);

		// отмечаем в intercom, что изменилось имя пространства
		Gateway_Socket_Intercom::spaceNameChanged($name);
	}
}
