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

		[$is_authorized, $need_fill_profile] = Domain_User_Scenario_OnPremiseWeb::start($this->user_id);

		return $this->ok([
			"is_authorized"      => (int) $is_authorized,
			"need_fill_profile"  => (int) $need_fill_profile,
			"captcha_public_key" => (string) Type_Captcha_Main::init()->getPublicCaptchaKey(Type_Api_Platform::PLATFORM_OTHER),
		]);
	}
}