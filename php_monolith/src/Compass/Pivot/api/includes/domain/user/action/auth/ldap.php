<?php

namespace Compass\Pivot;

/**
 * класс описывает все действия связанные с аутентификацией через LDAP
 * @package Compass\Pivot
 */
class Domain_User_Action_Auth_Ldap {

	/**
	 * создаем попытку аутентификации
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function begin(string $ldap_auth_token, int $auth_user_id):Domain_User_Entity_AuthStory {

		// тип аутентификации
		$auth_type = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_LDAP;

		// создаем все необходимые сущности аутентификации
		$expires_at     = 0;
		$auth_ldap_data = Domain_User_Entity_AuthStory_MethodHandler_Ldap::prepareAuthLdapDataDraft($ldap_auth_token);
		return Domain_User_Entity_AuthStory::create($auth_user_id, $auth_type, $expires_at, $auth_ldap_data);
	}
}