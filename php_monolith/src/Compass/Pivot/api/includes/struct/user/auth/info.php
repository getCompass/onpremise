<?php

namespace Compass\Pivot;

/**
 * Структура данных об аутентификации
 *
 * Class Struct_User_Auth_SmsInfo
 */
class Struct_User_Auth_Info {

	/**
	 * Struct_User_Auth_SmsInfo constructor.
	 */
	public function __construct(
		public string                                                $auth_map,
		public Struct_Db_PivotAuth_Auth                              $auth,
		protected Domain_User_Entity_AuthStory_MethodHandler_Default $_auth_method_entity,
	) {
	}

	/**
	 * @return Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber
	 */
	public function getAuthPhoneEntity():Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber|Domain_User_Entity_AuthStory_MethodHandler_Default {

		return $this->_auth_method_entity;
	}

	/**
	 * @return Domain_User_Entity_AuthStory_MethodHandler_Mail
	 */
	public function getAuthMailEntity():Domain_User_Entity_AuthStory_MethodHandler_Mail|Domain_User_Entity_AuthStory_MethodHandler_Default {

		return $this->_auth_method_entity;
	}
}