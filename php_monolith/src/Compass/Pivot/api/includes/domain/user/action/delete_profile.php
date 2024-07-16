<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Действие для удаления аккаунта пользователя
 */
class Domain_User_Action_DeleteProfile {

	/**
	 * Выполняем.
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_UserNotFound
	 */
	public static function do(int $user_id, Struct_Db_PivotUser_UserSecurity $user_security):void {

		// помечаем пользователя заблокированным
		try {
			$user_info = Domain_User_Action_DisableProfile::do($user_id);
		} catch (cs_UserAlreadyBlocked) {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		}

		// очищаем все сессии пользователя
		Type_Session_Main::clearAllUserPivotAndCompanySessions($user_id);

		// добавляем в историю действие удаления аккаунта пользователем
		Domain_User_Entity_UserActionComment::addDeleteProfileAction($user_id, $user_security->phone_number, $user_security->mail);

		// чистим номер телефона
		if ($user_security->phone_number !== "") {
			self::_clearPhone($user_id, $user_security->phone_number);
		}

		// чистим почту
		if ($user_security->mail !== "") {
			self::_clearMail($user_id, $user_security->mail);
		}

		// очищаем аватарку пользователя на стороне pivot
		Domain_User_Action_ClearAvatar::do($user_id);

		// отправляем запрос на удаление файла-аватарки пользователя
		if (!isEmptyString($user_info->avatar_file_map)) {
			Gateway_Socket_PivotFileBalancer::deleteFiles([$user_info->avatar_file_map]);
		}

		// отправляем таск для выполнения оставшихся действий при удалении аккаунта пользователя
		Type_Phphooker_Main::onProfileDelete($user_id);
		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::DELETED);
	}

	/**
	 * Чистим номер телефона
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _clearPhone(int $user_id, string $phone_number):void {

		// сохраняем в историю изменений запись об очистки номера телефона пользователя
		Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory::insert($user_id, $phone_number, "", "", time(), 0);

		// открепляем пользователя от номера телефона
		Domain_User_Entity_Phone::delete($user_id);
		Gateway_Db_PivotPhone_PhoneUniqList::set(Type_Hash_PhoneNumber::makeHash($phone_number), [
			"user_id"           => 0,
			"has_sso_account"   => 0,
			"last_unbinding_at" => time(),
			"updated_at"        => time(),
		]);
	}

	/**
	 * Чистим почту
	 *
	 * @throws ParseFatalException
	 */
	protected static function _clearMail(int $user_id, string $mail):void {

		Gateway_Db_PivotUser_UserSecurity::delete($user_id);
		Gateway_Db_PivotMail_MailUniqList::set(Type_Hash_Mail::makeHash($mail), [
			"user_id"         => 0,
			"has_sso_account" => 0,
			"password_hash"   => "",
			"updated_at"      => time(),
		]);
	}
}
