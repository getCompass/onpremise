<?php

namespace Compass\Jitsi;

/**
 * класс описывающий действие по генерации jwt токена для гостя
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_JoinAsGuest {

	/**
	 * выполняем действие
	 *
	 * @return string
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 */
	public static function do(string $guest_id, Struct_Db_JitsiData_Conference $conference):string {

		// имя гостя по умолчанию
		$guest_name = "Гость";

		// формируем контекст пользователя для токена
		$user_context = Domain_Jitsi_Entity_Authentication_Jwt_UserContext::createForGuest($guest_name, $guest_id);

		return Domain_Jitsi_Entity_Authentication_Jwt::create(
			$conference,
			$user_context,
			false,
			Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain)
		);
	}
}