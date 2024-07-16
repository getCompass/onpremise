<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Server\ServerProvider;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Отвязка пользователей от почты и номера телефона привязанных при аутентификации через SSO
 *
 * скрипт без dry-run, запускается сразу, безопасен для повторного выполнения
 */
class Migration_Reform_Sso {

	// лимит для основного sql запроса
	protected const _QUERY_LIMIT = 200000;

	/**
	 * стартовая функция скрипта
	 */
	public static function run():void {

		if (!ServerProvider::isOnPremise()) {

			console("Для запуска только на on-premise окружении");
			return;
		}

		$user_list = Gateway_Db_PivotUser_UserList::getAll(self::_QUERY_LIMIT, 0);

		foreach ($user_list as $user) {

			// если аккаунт уже удален
			if (Type_User_Main::isDisabledProfile($user->extra)) {
				continue;
			}

			if (Gateway_Socket_Federation::hasUserRelationship($user->user_id)) {
				self::_doWork($user->user_id);
			}
		}
	}

	/**
	 * выполняем основную часть скрипта
	 */
	protected static function _doWork(int $user_id):void {

		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			return;
		}

		// если надо, отвязываем почту
		try {
			self::_unbindMailIfNeed($user_id, $user_security);
		} catch (RowNotFoundException $e) {
			Type_System_Admin::log("reform_sso_exception", ["message" => "mail_uniq_list not found to user_id = {$user_id}", "exception" => $e->getMessage()]);
		}

		// если надо, отвязываем телефон
		try {
			self::_unbindPhoneNumberIfNeed($user_id, $user_security);
		} catch (RowNotFoundException $e) {
			Type_System_Admin::log("reform_sso_exception", ["message" => "phone_uniq_list not found to user_id = {$user_id}", "exception" => $e->getMessage()]);
		}
	}

	/**
	 * если надо, отвязываем почту
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException|RowNotFoundException
	 */
	protected static function _unbindMailIfNeed(int $user_id, Struct_Db_PivotUser_UserSecurity $user_security):void {

		// если есть почта
		if (Domain_User_Entity_Mail::hasMail($user_security)) {

			$mail_row = Gateway_Db_PivotMail_MailUniqList::getOne(Type_Hash_Mail::makeHash($user_security->mail));

			// проверяем что добавлена из данных SSO, если да - отвязываем
			if ($mail_row->has_sso_account) {

				Gateway_Db_PivotMail_MailUniqList::set(Type_Hash_Mail::makeHash($user_security->mail), [
					"user_id"         => 0,
					"has_sso_account" => 0,
					"password_hash"   => "",
					"updated_at"      => time(),
				]);

				Gateway_Db_PivotUser_UserSecurity::set($user_id, [
					"mail"       => "",
					"updated_at" => time(),
				]);

				Type_System_Admin::log("reform_sso_unbind", ["message" => "mail = {$user_security->mail} unbind user_id = {$user_id}"]);
			}
		}
	}

	/**
	 * если надо, отвязываем телефон
	 *
	 * @throws \parseException|RowNotFoundException
	 */
	protected static function _unbindPhoneNumberIfNeed(int $user_id, Struct_Db_PivotUser_UserSecurity $user_security):void {

		// если есть номер телефона
		if (Domain_User_Entity_Phone::hasPhoneNumber($user_security)) {

			$phone_row = Gateway_Db_PivotPhone_PhoneUniqList::getOne(Type_Hash_PhoneNumber::makeHash($user_security->phone_number));

			// проверяем что добавлена из данных SSO, если да - отвязываем
			if ($phone_row->has_sso_account) {

				Gateway_Db_PivotPhone_PhoneUniqList::set(Type_Hash_PhoneNumber::makeHash($user_security->phone_number), [
					"user_id"           => 0,
					"has_sso_account"   => 0,
					"last_unbinding_at" => time(),
					"updated_at"        => time(),
				]);

				Gateway_Db_PivotUser_UserSecurity::set($user_id, [
					"phone_number" => "",
					"updated_at"   => time(),
				]);

				Type_System_Admin::log("reform_sso_unbind", ["message" => "phone = {$user_security->phone_number} unbind user_id = {$user_id}"]);
			}
		}
	}
}

try {
	(new Migration_Reform_Sso())->run();
} catch (\Exception|Exception|Error $e) {

	console($e->getMessage());
	console($e->getTraceAsString());
	console(redText("Не смогли актуализировать связь с SSO"));
	exit(1);
}