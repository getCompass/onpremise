<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Действие для обновления данных сессии, если требуется
 */
class Domain_User_Action_Security_Device_UpdateSessionIfNeed {

	/**
	 * Выполняем
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @long
	 */
	public static function do(int $user_id, string $session_uniq, string $client_app_version):void {

		try {
			$active_session = Gateway_Db_PivotUser_SessionActiveList::getOne($user_id, $session_uniq);
		} catch (\cs_RowIsEmpty) {
			return;
		}

		// проверяем, можем информация в активной сессии уже обновлена
		$login_type     = Domain_User_Entity_SessionExtra::getLoginType($active_session->extra);
		$last_online_at = $active_session->last_online_at;
		$device_id      = Domain_User_Entity_SessionExtra::getDeviceId($active_session->extra);
		$server_version = Domain_User_Entity_SessionExtra::getServerVersion($active_session->extra);
		$app_version    = Domain_User_Entity_SessionExtra::getAppVersion($active_session->extra);
		$device_name    = Domain_User_Entity_SessionExtra::getDeviceName($active_session->extra);

		try {
			$current_device_name = Type_Api_Platform::getDeviceName(getUa());
		} catch (cs_PlatformNotFound) {
			$current_device_name = "";
		}

		$new_server_version = ServerProvider::isSaas() ? SAAS_VERSION : ONPREMISE_VERSION;

		if ($current_device_name == $device_name && $login_type != 0 && $last_online_at != 0 && mb_strlen($device_id) > 0
			&& mb_strlen($server_version) > 0 && $server_version == $new_server_version
			&& mb_strlen($app_version) > 0 && $app_version == $client_app_version) {

			return;
		}

		$extra = $active_session->extra;
		if (ServerProvider::isSaas()) {
			$extra = Domain_User_Entity_SessionExtra::setLoginType($extra, Domain_User_Entity_SessionExtra::SAAS_SMS_LOGIN_TYPE);
		}
		$extra = Domain_User_Entity_SessionExtra::setServerVersion($extra, ServerProvider::isSaas() ? SAAS_VERSION : ONPREMISE_VERSION);
		$extra = Domain_User_Entity_SessionExtra::setDeviceId($extra, getDeviceId());
		$extra = Domain_User_Entity_SessionExtra::setAppVersion($extra, $client_app_version);

		try {

			$device_type = match (Type_Api_Platform::getPlatform(getUa())) {
				Type_Api_Platform::PLATFORM_ELECTRON                                 => "desktop",
				Type_Api_Platform::PLATFORM_ANDROID, Type_Api_Platform::PLATFORM_IOS => "mobile",
				Type_Api_Platform::PLATFORM_OPENAPI                                  => "open_api",
				default                                                              => "unknown",
			};
		} catch (cs_PlatformNotFound) {
			$device_type = "unknown";
		}
		$extra = Domain_User_Entity_SessionExtra::setOutputDeviceType($extra, $device_type);
		$extra = Domain_User_Entity_SessionExtra::setDeviceName($extra, $current_device_name);

		$set = [
			"extra"          => $extra,
			"last_online_at" => time(),
			"updated_at"     => time(),
		];
		Gateway_Db_PivotUser_SessionActiveList::set($user_id, $session_uniq, $set);
	}
}