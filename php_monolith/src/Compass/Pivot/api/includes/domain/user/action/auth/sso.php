<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\System\Mail;
use BaseFrame\System\PhoneNumber;

/**
 * класс описывает все действия связанные с аутентификацией через SSO
 * @package Compass\Pivot
 */
class Domain_User_Action_Auth_Sso {

	/**
	 * Получаем user_id по почте
	 *
	 * @return int
	 */
	public static function resolveUser(int $sso_account_user_id_rel, Struct_User_Auth_Sso_AccountData $sso_account_data):array {

		// если вернулась связь «SSO аккаунт» – «Compass пользователь», то ориентируемся на это
		if ($sso_account_user_id_rel > 0) {
			return [$sso_account_user_id_rel, true];
		}

		// ниже случай, когда пользователь впервые прошел аутентификацию через SSO, а значит событие может развиваться так:
		// – либо в учетной записи SSO аккаунта пользователя указан mail/phone_number который ранее был зарегистрирован в Compass,
		// в таком случае авторизуем пользователя под этим существующим пользователем
		// – либо зарегистрируем нового пользователя Compass

		// проверяем почту
		[$user_id, $has_sso_account] = mb_strlen($sso_account_data->mail) > 0 ? self::_resolveUserByMail($sso_account_data->mail) : [0, false];
		if ($user_id > 0) {

			// нашли существующего пользователя с такой почтой
			return [$user_id, $has_sso_account];
		}

		// проверяем номер телефона
		[$user_id, $has_sso_account] = mb_strlen($sso_account_data->phone_number) > 0 ? self::_resolveUserByPhoneNumber($sso_account_data->phone_number) : [0, false];
		if ($user_id > 0) {

			// нашли существующего пользователя с таким номером
			return [$user_id, $has_sso_account];
		}

		// не нашли существующего пользователя – будем регистрировать нового
		return [0, false];
	}

	/**
	 * получаем user_id зарегистрированного пользователя по почте
	 *
	 * @return int
	 */
	protected static function _resolveUserByMail(string $mail):array {

		try {

			$tt   = new Mail($mail);
			$mail = $tt->mail();
		} catch (InvalidMail) {
			return 0;
		}

		return Domain_User_Action_Auth_Mail::resolveUser($mail);
	}

	/**
	 * получаем user_id зарегистрированного пользователя по номеру телефона
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 */
	protected static function _resolveUserByPhoneNumber(string $phone_number):array {

		try {

			$tt           = new PhoneNumber($phone_number);
			$phone_number = $tt->number();
		} catch (InvalidMail) {
			return 0;
		}

		return Domain_User_Action_Auth_PhoneNumber::resolveUser($phone_number);
	}

	/**
	 * создаем попытку аутентификации
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function begin(string $sso_auth_token, int $auth_user_id):Domain_User_Entity_AuthStory {

		// тип аутентификации
		$auth_type = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO;

		// создаем все необходимые сущности аутентификации
		$expires_at    = 0;
		$auth_sso_data = Domain_User_Entity_AuthStory_MethodHandler_Sso::prepareAuthSsoDataDraft($sso_auth_token);
		return Domain_User_Entity_AuthStory::create($auth_user_id, $auth_type, $expires_at, $auth_sso_data);
	}

	/**
	 * Обновляем флаг has_sso_account, после успешной аутентификации пользователя через SSO
	 *
	 * @throws \parseException
	 */
	public static function updateHasSsoAccountFlag(int $user_id, Struct_User_Auth_Sso_AccountData $sso_account_data):void {

		// обновляем has_sso_account для почты только если почта указанная в учетной записи sso привязана за пользователем в рамках приложения
		$mail = self::prepareMail($sso_account_data->mail);
		if ($mail !== "") {

			Gateway_Db_PivotMail_MailUniqList::setByMailAndUserId(Type_Hash_Mail::makeHash($mail), $user_id, [
				"has_sso_account" => 1,
				"updated_at"      => time(),
			]);
		}

		// обновляем has_sso_account для номера телефона только если номер указанный в учетной записи sso привязан за пользователем в рамках приложения
		$phone_number = self::preparePhoneNumber($sso_account_data->phone_number);
		if ($phone_number !== "") {

			Gateway_Db_PivotPhone_PhoneUniqList::setByPhoneAndUserId(Type_Hash_PhoneNumber::makeHash($phone_number), $user_id, [
				"has_sso_account" => 1,
				"updated_at"      => time(),
			]);
		}
	}

	/**
	 * подготавливаем номер телефона после его получения из SSO
	 *
	 * @return string
	 */
	public static function preparePhoneNumber(string $sso_phone_number):string {

		// если номер телефона не указан, то ничего не делаем
		if ($sso_phone_number === "") {
			return "";
		}

		// если некорректный номер телефона, то ничего не делаем
		try {
			$temp = new PhoneNumber($sso_phone_number);
		} catch (InvalidPhoneNumber) {
			return "";
		}

		return $temp->number();
	}

	/**
	 * подготавливаем почту полученную из SSO
	 *
	 * @return string
	 */
	public static function prepareMail(string $sso_mail):string {

		// если почта не указан, то ничего не делаем
		if ($sso_mail === "") {
			return "";
		}

		// если некорректная почта, то ничего не делаем
		try {
			$temp = new Mail($sso_mail);
		} catch (InvalidMail) {
			return "";
		}

		return $temp->mail();
	}

}