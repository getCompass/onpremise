<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

/**
 * Абстрактный класс подтверждения действий
 */
abstract class Domain_User_Entity_Confirmation_Main {

	public const CONFIRMATION_CHANGE_PIN_TYPE     = 1; // тип изменения пинкода
	public const CONFIRMATION_SELF_DISMISSAL_TYPE = 2; // тип покидание компании (самоувольнение)
	public const CONFIRMATION_DELETE_COMPANY      = 3; // тип удаления компании
	public const CONFIRMATION_DELETE_PROFILE      = 4; // тип удаления аккаунта пользователя

	protected const _GROUP_NAME_BY_TYPE = [
		self::CONFIRMATION_CHANGE_PIN_TYPE     => "auth",
		self::CONFIRMATION_SELF_DISMISSAL_TYPE => "self_dismissal",
		self::CONFIRMATION_DELETE_COMPANY      => "delete_company",
		self::CONFIRMATION_DELETE_PROFILE      => "delete_profile",
	];

	protected const _ACTION_TYPES = [
		self::CONFIRMATION_CHANGE_PIN_TYPE,
		self::CONFIRMATION_SELF_DISMISSAL_TYPE,
		self::CONFIRMATION_DELETE_COMPANY,
		self::CONFIRMATION_DELETE_PROFILE,
	];

	// приоритет способов подтверждения
	protected const _AUTH_METHOD_ORDER = [
		Domain_User_Entity_Auth_Method::METHOD_MAIL,
		Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER,
	];

	/**
	 * Проверить, совпадают ли типы
	 *
	 * @throws cs_TwoFaTypeIsInvalid
	 */
	public static function assertTypeIsValid(int $action_type):void {

		if (!in_array($action_type, self::_ACTION_TYPES)) {
			throw new cs_TwoFaTypeIsInvalid("Type invalid");
		}
	}

	/**
	 * Получим название группы для метода, который иницировал создание 2fa токена
	 *
	 * @param int $action_type
	 *
	 * @return string
	 *
	 * @throws cs_TwoFaTypeIsInvalid
	 */
	public static function getGroupNameByActionType(int $action_type):string {

		if (!isset(self::_GROUP_NAME_BY_TYPE[$action_type])) {
			throw new cs_TwoFaTypeIsInvalid();
		}

		return self::_GROUP_NAME_BY_TYPE[$action_type];
	}

	/**
	 * Обработать действие подтверждения
	 *
	 * @param Struct_Db_PivotUser_UserSecurity $user_security
	 * @param string                           $session_uniq
	 * @param int                              $action_type
	 * @param string|false                     $two_fa_key
	 * @param string|false                     $mail_confirmation_key
	 * @param int                              $company_id
	 *
	 * @return void
	 * @throws BlockException
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidUser
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 * @throws Domain_User_Exception_Confirmation_Mail_IsInvalidType
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotConfirmed
	 * @throws Domain_User_Exception_Confirmation_Mail_NotSuccess
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaInvalidCompany
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaIsNotActive
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws cs_WrongTwoFaKey
	 * @throws cs_blockException|Domain_User_Exception_Mail_NotFound|cs_UserPhoneSecurityNotFound
	 */
	public static function handle(Struct_Db_PivotUser_UserSecurity $user_security, string $session_uniq, int $action_type, string|false $two_fa_key, string|false $mail_confirmation_key, int $company_id = 0):void {

		// получаем доступные в приложении виды авторизации
		$available_user_method_list = [];
		$available_method_list      = Domain_User_Entity_Auth_Method::getAvailableMethodList();
		$available_guest_method_list = Domain_User_Entity_Auth_Method::getAvailableGuestMethodList();

		// если у пользователя есть телефон - то он может подтвердить по нему действие
		if ($user_security->phone_number !== "") {
			$available_user_method_list[] = Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER;
		}

		// если есть мыло - тоже добавляем в способы подтверждения
		if ($user_security->mail !== "") {
			$available_user_method_list[] = Domain_User_Entity_Auth_Method::METHOD_MAIL;
		}

		// выясняем, какие методы нам доступны и выбираем первый по приоритету
		$confirmation_method_list = array_intersect(self::_AUTH_METHOD_ORDER, $available_method_list, $available_user_method_list);

		// если не нашли ни один - скорее всего гость - его способы будут в $available_user_method_list, проверяем их
		if ($confirmation_method_list === []) {
			$confirmation_method_list = array_intersect(self::_AUTH_METHOD_ORDER, $available_guest_method_list, $available_user_method_list);
		}

		if ($confirmation_method_list === []) {

			if (Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL)) {
				Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);
			}

			if (Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)) {
				Domain_User_Entity_Phone::assertAlreadyExistPhoneNumber($user_security);
			}

			// в конце проверяем, возможно это гость и у него есть или почта или номер, fatal не кидаем
			if (Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL)
				|| Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)) {

				try {
					Domain_User_Entity_Phone::assertAlreadyExistPhoneNumber($user_security);
				} catch (cs_UserPhoneSecurityNotFound) {
					Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);
				}
			}

			throw new Domain_User_Exception_Mail_NotFoundOnSso("sso user");
		}

		$confirmation_method = reset($confirmation_method_list);

		match ($confirmation_method) {
			Domain_User_Entity_Auth_Method::METHOD_MAIL => Domain_User_Entity_Confirmation_Mail_Mail::handle(
				$user_security->user_id, $session_uniq, $action_type, $mail_confirmation_key),
			Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER => Domain_User_Entity_Confirmation_TwoFa_TwoFa::handle(
				$user_security->user_id, $action_type, $two_fa_key, $company_id)
		};
	}

}