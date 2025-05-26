<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use PhoneCarrierLookup\Data\Helper;
use PhoneCarrierLookup\Exception\UnsupportedPhoneNumber;

/**
 * Class Domain_Antispam_Entity_SuspectIp
 */
class Domain_Antispam_Entity_SuspectIp {

	public const EXPIRES_AT           = HOUR1 * 100;
	public const RECENT_IP_CREATED_AT = 60 * 60;

	/**
	 * Добавляем новый ip в базу
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function add($phone_number):Struct_Db_PivotSystem_AntispamSuspectIp {

		// получаем код телефона
		$phone_code = self::_getPhoneCode($phone_number);

		$ip_address        = getIp();
		$time              = time();
		$suspect_ip_struct = new Struct_Db_PivotSystem_AntispamSuspectIp(
			$ip_address,
			$phone_code,
			$time,
			$time + self::EXPIRES_AT,
			$time + Domain_User_Entity_AuthStory_MethodHandler_Default::STORY_LIFE_TIME,
		);

		Gateway_Db_PivotSystem_AntispamSuspectIp::insert($suspect_ip_struct);
		return $suspect_ip_struct;
	}

	/**
	 * Удаляем ip
	 */
	public static function delete():void {

		Gateway_Db_PivotSystem_AntispamSuspectIp::delete(getIp());
	}

	/**
	 * Проверяем что не превысили лимит на капчу
	 *
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 * @throws cs_PlatformNotFound
	 */
	public static function checkIpLimit(string|false $grecaptcha_response, string $phone_number, bool $is_from_web = false, bool $is_already_checked = false):bool {

		// если не нужно проверять, выходим
		if (Type_Antispam_User::needCheckIsBlocked()) {
			return false;
		}

		// если проверяли ранее
		if ($is_already_checked) {
			return true;
		}

		// получаем код телефона
		$phone_code = self::_getPhoneCode($phone_number);

		// получаем число недавно добавленных ip
		$count = Gateway_Db_PivotSystem_AntispamSuspectIp::getRecentCount($phone_code);

		// получаем лимит смс для этого кода страны
		$phone_code_ip_limit = Type_Sms_limit::getSmsLimit($phone_code);

		// если превысили лимит, проверяем капчу
		if ($count >= $phone_code_ip_limit) {
			Type_Captcha_Main::assertCaptcha($grecaptcha_response, $is_from_web);
			return true;
		}

		return false;
	}

	/**
	 * Проверяем сам ip
	 *
	 * @throws ParseFatalException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkIp(string|false $grecaptcha_response, string $phone_number, bool $is_from_web = false, bool $is_already_checked = false):void {

		// если не нужно проверять, выходим
		if (Type_Antispam_User::needCheckIsBlocked()) {
			return;
		}

		// пробуем получить запись
		$ip_address = getIp();
		try {
			$suspect_ip_struct = Gateway_Db_PivotSystem_AntispamSuspectIp::get($ip_address);
		} catch (\cs_RowIsEmpty) {
			return;
		}

		// если пока еще есть время ввести смс - выходим
		if ($suspect_ip_struct->delayed_till > time()) {
			return;
		}

		// получаем код телефона
		$phone_code = self::_getPhoneCode($phone_number);

		// если время на ввод смс уже вышло, проверяем время блокировки ip адреса
		if ($suspect_ip_struct->expires_at < time()) {

			// если и оно вышло, обновляем ip и считаем его снова валидным
			$time = time();
			Gateway_Db_PivotSystem_AntispamSuspectIp::set($suspect_ip_struct->ip_address, [
				"phone_code"   => $phone_code,
				"created_at"   => $time,
				"expires_at"   => $time + self::EXPIRES_AT,
				"delayed_till" => $time + Domain_User_Entity_AuthStory_MethodHandler_Default::STORY_LIFE_TIME,
			]);
			return;
		}

		// если капчу уже проверяли выходим
		if ($is_already_checked) {
			return;
		}

		// иначе проверяем капчу
		Type_Captcha_Main::assertCaptcha($grecaptcha_response, $is_from_web);
	}

	/**
	 * Получаем код из номера, возвращаем дефолтный если не смогли достать
	 * unknown - нужен чтобы не сломать регу если где то не обновили новые коды
	 */
	protected static function _getPhoneCode(string $phone_number):string {

		$phone_number = ltrim($phone_number, "+");

		try {

			$phone_code = Helper::getPhoneCode($phone_number);
		} catch (UnsupportedPhoneNumber) {

			return "unknown";
		}

		return "+" . $phone_code;
	}
}