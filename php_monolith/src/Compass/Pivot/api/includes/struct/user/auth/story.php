<?php

namespace Compass\Pivot;

/**
 * данные об аутентификации
 *
 * Class Struct_User_Auth_Story
 */
class Struct_User_Auth_Story {

	public Struct_Db_PivotAuth_Auth      $auth;
	public Struct_Db_PivotAuth_AuthPhone $auth_phone;

	/**
	 * Struct_User_Auth_Story constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotAuth_Auth $auth, Struct_Db_PivotAuth_AuthPhone $auth_phone) {

		$this->auth       = $auth;
		$this->auth_phone = $auth_phone;
	}
}