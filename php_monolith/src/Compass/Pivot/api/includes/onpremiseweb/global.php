<?php

namespace Compass\Pivot;

/**
 * Методы инициализации для сайта on-premise.
 */
class Onpremiseweb_Global extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"start",
	];

	/**
	 * Выполняет проверку наличия сессии и заполненности профиля.
	 */
	public function start():array {

		/** @var Struct_Db_PivotUser_User $user_info */
		[$is_authorized, $user_info, $need_fill_profile, $dictionary, $available_auth_method_list, $restrictions] = Domain_User_Scenario_OnPremiseWeb::start($this->user_id);

		return $this->ok([
			"is_authorized"              => (int) $is_authorized,
			"need_fill_profile"          => (int) $need_fill_profile,
			"captcha_public_key"         => (string) Type_Captcha_Main::init()->getPublicCaptchaKey(Type_Api_Platform::PLATFORM_OTHER),
			"available_auth_method_list" => (array) $available_auth_method_list,
			"dictionary"                 => $dictionary,
			"user_info"                  => $is_authorized ? Onpremiseweb_Format::userInfo($user_info) : null,
			"restrictions"               => (array) $restrictions,
		]);
	}
}