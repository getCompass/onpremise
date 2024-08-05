<?php

namespace Compass\Federation;

/**
 * класс реализует легкий уровень блокировки пользователя Compass:
 * у связанного пользователя Compass закрываются все активные сессии, блокируется доступ к приложению
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_UserBlocker_Light extends Domain_Ldap_Entity_UserBlocker_Abstract {

	/**
	 * функция для основной логики блокировки пользователя
	 */
	protected static function _block(int $user_id):void {

		// блокируем пользователю возможность аутентифицироваться в приложении
		Gateway_Socket_Pivot::blockUserAuthentication($user_id);
	}
}