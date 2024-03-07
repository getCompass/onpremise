<?php

namespace Compass\Pivot;

/**
 * данные об аутентификации
 *
 * Class Struct_User_Auth_Story
 */
class Struct_User_Auth_Story {

	public Struct_Db_PivotAuth_Auth      $auth;
	public array                         $auth_method_data;

	/**
	 * Struct_User_Auth_Story constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotAuth_Auth $auth, array $auth_method_data) {

		$this->auth             = $auth;
		$this->auth_method_data = $auth_method_data;
	}
}