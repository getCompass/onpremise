<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Class Domain_User_Entity_Antispam_Auth
 */
class Domain_User_Entity_Antispam_Auth {

	/**
	 * Проверяем блокировки по IP адресу перед началом аутентификации
	 * Используется в аутентификации по почте, etc ...
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkIpAddressBlocksBeforeStartAuth(string|false $grecaptcha_response, bool $is_from_web):array {

		return Type_Antispam_Ip::incrementAndAssertRecaptchaIfBlocked(
			Type_Antispam_Leveled_Ip::getBlockRule(Type_Antispam_Leveled_Ip::getAuthLimitsByServer()),
			$grecaptcha_response,
			$is_from_web
		);
	}

	/**
	 * Проверяем блокировки по IP адресу при вводе пароля
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkIpAddressBlocksOnEnterPassword(string|false $grecaptcha_response, bool $is_from_web):void {

		Type_Antispam_Ip::incrementAndAssertRecaptchaIfBlocked(
			Type_Antispam_Leveled_Ip::getBlockRule(Type_Antispam_Leveled_Ip::getEnterPasswordLimitsByServer()),
			$grecaptcha_response,
			$is_from_web
		);
	}

	/**
	 * Проверяем блокировки перед началом логина
	 *
	 * @throws \blockException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 * @throws cs_AuthIsBlocked
	 */
	public static function checkBlocksBeforeStartLoginByPhoneNumber(string $phone_number, string|false $grecaptcha_response, bool $is_from_web = false):void {

		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number);

		// если потребовалась проверка капчи из-за captcha-list или номер в white-list, то ip больше проверять не требуется
		$is_need_check_ip = !(
			self::_checkCaptchaForCaptchaListIfNeed($phone_number_hash, $grecaptcha_response, $is_from_web) ||
			self::_isPhoneNumberInWhiteList($phone_number_hash)
		);

		self::_authBlocks($phone_number_hash, $phone_number, $is_need_check_ip, $grecaptcha_response, $is_from_web);
	}

	/**
	 * Проверяем блокировки перед началом регистрации
	 *
	 * @throws \blockException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 * @throws cs_ActionNotAvailable
	 * @throws cs_AuthIsBlocked
	 */
	public static function checkBlocksBeforeStartRegisterByPhoneNumber(string $phone_number, string|false $grecaptcha_response, bool $is_from_web = false):void {

		if ($phone_number == IOS_TEST_PHONE ||
			$phone_number == ELECTRON_TEST_PHONE ||
			$phone_number == ANDROID_TEST_PHONE ||
			$phone_number == ANDROID_TEST_PHONE2 ||
			$phone_number == IOS_TEST_PHONE2 ||
			$phone_number == IOS_TEST_PHONE3 ||
			$phone_number == IOS_TEST_PHONE4) {
			return;
		}

		// запись не найдена или же номер телефона не привязан
		// если регистрация разрешена только с офисных IP адресов компании
		if (ONLY_OFFICE_IP && !isOfficeIp()) {
			throw new cs_ActionNotAvailable();
		}

		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number);

		$is_need_check_ip = !(
			self::_checkCaptchaForCaptchaListIfNeed($phone_number_hash, $grecaptcha_response, $is_from_web) ||
			self::_isPhoneNumberInWhiteList($phone_number_hash)
		);

		self::_authBlocks($phone_number_hash, $phone_number, $is_need_check_ip, $grecaptcha_response, $is_from_web);
	}

	/**
	 * Проверяем блокировку перед переотправкой смс
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkBlocksBeforeStartResend(string $phone_number, string|false $grecaptcha_response, bool $is_from_web = false):void {

		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number);

		self::_checkCaptchaForCaptchaListIfNeed($phone_number_hash, $grecaptcha_response, $is_from_web);

		if (self::_isPhoneNumberInWhiteList($phone_number_hash)) {
			return;
		}

		Type_Antispam_Ip::incrementAndAssertRecaptchaIfBlocked(
			Type_Antispam_Leveled_Ip::getBlockRule(Type_Antispam_Leveled_Ip::START_AUTH),
			$grecaptcha_response,
			$is_from_web,
		);
	}

	/**
	 * Уменьшаем блокировки при успешной аутентификации
	 *
	 */
	public static function successAuth(string $phone_number):void {

		Type_Antispam_Ip::decrement(
			Type_Antispam_Leveled_Ip::getBlockRule(Type_Antispam_Leveled_Ip::START_AUTH),
		);
		Type_Antispam_Phone::decrement(
			Type_Hash_PhoneNumber::makeHash($phone_number),
			Type_Antispam_Leveled_Phone::getBlockRule(Type_Antispam_Leveled_Phone::DYNAMIC_AUTH_BLOCK),
		);
		Domain_Antispam_Entity_SuspectIp::delete();
	}

	/**
	 * Проверяем капчу если требуется
	 *
	 * @return bool потребовалась ли проверка капчи
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	private static function _checkCaptchaForCaptchaListIfNeed(string $phone_number_hash, string|false $grecaptcha_response, bool $is_from_web = false):bool {

		if (Type_Antispam_User::needCheckIsBlocked()) {
			return true;
		}

		if (Type_List_CaptchaList::isPhoneHashInCaptchaList($phone_number_hash)) {

			Type_Captcha_Main::assertCaptcha($grecaptcha_response, $is_from_web);

			// капча была проверена
			return true;
		}

		// если подозрительная подсеть
		if (Type_List_CaptchaList::isSuspectSubnet(getIp())) {

			Type_Captcha_Main::assertCaptcha($grecaptcha_response, $is_from_web);

			// капча была проверена
			return true;
		}

		// проверка капчи не потребовалась
		return false;
	}

	/**
	 * Проверяем, в белом ли списке номер телефона (обращая внимание на уровень блокировок)
	 *
	 */
	private static function _isPhoneNumberInWhiteList(string $phone_number_hash):bool {

		if (!Type_Antispam_Leveled_Main::isWarningLevel() && Type_List_WhiteList::isPhoneHashInWhiteList($phone_number_hash)) {
			return true;
		}

		return false;
	}

	/**
	 * Блокировки аутентификации
	 *
	 * @throws ParseFatalException
	 * @throws cs_AuthIsBlocked
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	private static function _authBlocks(string $phone_number_hash, string $phone_number, bool $is_need_check_ip, string|false $grecaptcha_response, bool $is_from_web = false):void {

		// проверяем блокировки по телефону (не инкрементим сразу, чтобы еще проверить капчу, если требуется)
		$auth_dynamic_block_row = Type_Antispam_Phone::check(
			$phone_number_hash,
			Type_Antispam_Leveled_Phone::getBlockRule(Type_Antispam_Leveled_Phone::DYNAMIC_AUTH_BLOCK),
		);
		$auth_static_block_row  = Type_Antispam_Phone::check(
			$phone_number_hash,
			Type_Antispam_Leveled_Phone::getBlockRule(Type_Antispam_Leveled_Phone::STATIC_AUTH_BLOCK),
		);

		// проверяем капчу
		if ($is_need_check_ip) {

			[$_, $is_already_checked] = Type_Antispam_Ip::incrementAndAssertRecaptchaIfBlocked(
				Type_Antispam_Leveled_Ip::getBlockRule(Type_Antispam_Leveled_Ip::getAuthLimitsByServer()),
				$grecaptcha_response,
				$is_from_web
			);
			$is_already_checked = Domain_Antispam_Entity_SuspectIp::checkIpLimit($grecaptcha_response, $phone_number, $is_from_web, $is_already_checked);
			Domain_Antispam_Entity_SuspectIp::checkIp($grecaptcha_response, $phone_number, $is_from_web, $is_already_checked);
		}

		// инкрементим уже проверенные блокировки по телефону
		Type_Antispam_Phone::incrementBlockRow($auth_dynamic_block_row);
		Type_Antispam_Phone::incrementBlockRow($auth_static_block_row);
	}
}