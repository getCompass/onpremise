<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для получения списка всех активных пользователей
 */
class Migration_Get_Users {

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

			$user_name = $user->full_name;
			if ($user_name == "") {
				$user_name = "compass_user";
			}

			console($user_name);
			console("User ID: {$user->user_id}\n");
		}
	}
}

// запускаем
Migration_Get_Users::doWork();