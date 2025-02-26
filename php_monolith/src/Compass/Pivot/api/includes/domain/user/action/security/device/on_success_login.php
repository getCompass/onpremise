<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Действие при успешной авторизации устройства пользователя
 */
class Domain_User_Action_Security_Device_OnSuccessLogin {

	/**
	 * Выполняем
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $login_type, string $device_name, string $app_version, string $server_version):void {

		// для онпремайз окружения не требуется отправка в чат СП сообщения об успешной авторизации
		if (ServerProvider::isOnPremise()) {
			return;
		}

		$locale = \BaseFrame\System\Locale::getLocale();

		$login_type = match ($login_type) {
			Domain_User_Entity_SessionExtra::SAAS_SMS_LOGIN_TYPE, Domain_User_Entity_SessionExtra::ONPREMISE_SMS_LOGIN_TYPE => "SMS",
			Domain_User_Entity_SessionExtra::ONPREMISE_EMAIL_LOGIN_TYPE => "Email",
			Domain_User_Entity_SessionExtra::ONPREMISE_LDAP_LOGIN_TYPE => "LDAP",
			Domain_User_Entity_SessionExtra::ONPREMISE_SSO_LOGIN_TYPE => "SSO",
			Domain_User_Entity_SessionExtra::SAAS_QRCODE_LOGIN_TYPE, Domain_User_Entity_SessionExtra::ONPREMISE_QRCODE_LOGIN_TYPE =>
			\BaseFrame\System\Locale::getText(getConfig("LOCALE_TEXT"), "local_type", "qr_code", $locale),
			default => \BaseFrame\System\Locale::getText(getConfig("LOCALE_TEXT"), "local_type", "unknown", $locale),
		};

		Type_Phphooker_Main::onSuccessDeviceLogin($user_id, $login_type, $device_name, $app_version, $server_version, $locale);
	}
}