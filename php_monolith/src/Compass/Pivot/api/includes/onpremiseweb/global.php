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
		[
			$is_authorized,
			$user_info,
			$need_fill_profile,
			$dictionary,
			$available_auth_method_list,
			$available_auth_guest_method_list,
			$sso_protocol, $restrictions,
		] = Domain_User_Scenario_OnPremiseWeb::start($this->user_id);

		return $this->ok([
			"is_authorized"                    => (int) $is_authorized,
			"need_fill_profile"                => (int) $need_fill_profile,
			"captcha_public_key"               => (string) Type_Captcha_Main::init()->getPublicCaptchaKey(Type_Api_Platform::PLATFORM_OTHER),
			"captcha_public_data"              => (object) Type_Captcha_Main::getProviderPublicCaptchaData(Type_Api_Platform::PLATFORM_OTHER),
			"available_auth_method_list"       => (array) $available_auth_method_list,
			"available_auth_guest_method_list" => (array) $available_auth_guest_method_list,
			"sso_protocol"                     => (string) $sso_protocol,
			"dictionary"                       => $dictionary,
			"server_version"                   => (string) ONPREMISE_VERSION,
			"user_info"                        => $is_authorized ? Onpremiseweb_Format::userInfo($user_info) : null,
			"restrictions"                     => (array) $restrictions,
			"connect_check_url"                => (string) PUBLIC_CONNECT_CHECK_URL . "/",
			"download_app_url"                 => (string) PUBLIC_ENTRYPOINT_ELECTRON_DOWNLOAD_APP . "/",
		]);
	}
}