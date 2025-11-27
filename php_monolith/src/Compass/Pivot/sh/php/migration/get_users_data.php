<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;
use Compass\Federation\Gateway_Db_LdapData_LdapAccountUserRel;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для получения данных всех активных пользователей
 */
class Migration_Get_Users_Data {

	protected const _USER_COUNT_PER_CHUNK = 1000;

	/**
	 * выполняем
	 */
	public static function doWork():void {

		// получаем всех пользователей в приложении

		$offset = 0;
		do {

			$user_list = Gateway_Db_PivotUser_UserList::getAll(self::_USER_COUNT_PER_CHUNK, $offset);
			$offset    += self::_USER_COUNT_PER_CHUNK;
			self::_doWorkChunk($user_list);
		} while (count($user_list) > 0);
	}

	/**
	 * Работаем с чанком
	 *
	 * @param Struct_Db_PivotUser_User[] $user_list
	 *
	 * @return void
	 */
	protected static function _doWorkChunk(array $user_list):void {

		// проходимся по всем пользователям
		foreach ($user_list as $user) {

			// если не пользователь - пропускаем
			if (!Type_User_Main::isHuman($user->npc_type)) {
				continue;
			}

			// если аккаунт уже удален - пропускаем
			if (Type_User_Main::isDisabledProfile($user->extra)) {
				continue;
			}

			$mail         = "";
			$phone_number = "";
			try {

				$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user->user_id);
				$mail          = $user_security->mail;
				$phone_number  = $user_security->phone_number;
			} catch (\cs_RowIsEmpty) {
			}

			$ldap_uid      = "";
			$ldap_username = "";
			try {

				$ldap_account_user_rel = Gateway_Db_LdapData_LdapAccountUserRel::getOneByUserID($user->user_id);
				$ldap_uid              = $ldap_account_user_rel->uid;
				$ldap_username         = $ldap_account_user_rel->username;
			} catch (RowNotFoundException) {
			}

			$user_name = $user->full_name;
			if ($user_name == "") {
				$user_name = "compass_user";
			}

			console("------------------------------------");
			console($user_name);
			console("User ID: {$user->user_id}");
			console("Email: {$mail}");
			console("Phone number: {$phone_number}");
			console("Ldap uid: {$ldap_uid}");
			console("Ldap username: {$ldap_username}\n");
		}
	}
}

// запускаем
Migration_Get_Users_Data::doWork();