<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use Exception;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для миграции добавления пользователей
 */
class Migrations_1AddUsers {

	protected const _LIMIT              = 10000;                    // лимит пользователей для получения из базы
	protected const _CONFIG_ROOT_ID_KEY = "ONPREMISE_ROOT_USER_ID"; // ключ конфига для получения root_user_id

	// данные таблицы для получения конфига с пивота
	protected const _PIVOT_DB_KEY    = "pivot_data";
	protected const _PIVOT_TABLE_KEY = "pivot_config";

	/**
	 * Запускаем работу скрипта
	 *
	 * @throws ParseFatalException
	 * @long
	 */
	public function run():void {

		// узнаём рута
		$pivot_config = self::_getPivotConfig(self::_CONFIG_ROOT_ID_KEY);
		$root_user_id = $pivot_config["value"];

		// получаем пользователей
		$all_user_list = [];
		$offset        = 0;
		do {

			$user_list     = Gateway_Db_PivotUser_UserList::getAll(self::_LIMIT, $offset);
			$all_user_list = array_merge($all_user_list, $user_list);

			$offset += self::_LIMIT;
		} while (count($user_list) > 0);

		// права для пользователей, которые те будут иметь
		$member_premise_permissions = Domain_User_Entity_Permissions::DEFAULT;
		$root_premise_permissions   = Domain_User_Entity_Permissions::addPermissionListToMask(
			Domain_User_Entity_Permissions::DEFAULT, [Domain_User_Entity_Permissions::SERVER_ADMINISTRATOR, Domain_User_Entity_Permissions::ACCOUNTANT]
		);

		$insert_user_list = [];
		/** @var Struct_Db_PivotUser_User $user */
		foreach ($all_user_list as $user) {

			// пропускаем тех, у кого заблочен аккаунт
			if (Type_User_Main::isDisabledProfile($user->extra)) {
				continue;
			}

			$insert_user_list[] = new Struct_Db_PremiseUser_User(
				$user->user_id,
				$user->npc_type,
				Domain_Premise_Entity_Space::NOT_EXIST_SPACE_STATUS,
				($user->user_id == $root_user_id ? 1 : 0),
				($user->user_id == $root_user_id ? $root_premise_permissions : $member_premise_permissions),
				$user->created_at,
				0,
				"",
				"",
				"",
				[],
				[]
			);
		}

		Gateway_Db_PremiseUser_UserList::insertList($insert_user_list);
	}

	/**
	 * Получить конфиг из пивота по выбранному ключу.
	 */
	protected static function _getPivotConfig(string $key):array {

		$row = \sharding::pdoConnect(
			MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SSL, self::_PIVOT_DB_KEY
		)->getOne("SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i", self::_PIVOT_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			return [];
		}

		return fromJson($row["value"]);
	}
}

try {
	(new Migrations_1AddUsers())->run();
} catch (Exception $e) {

	console($e->getMessage());
	console($e->getTraceAsString());
	console(redText("Не смогли добавить пользователей"));
	exit(1);
}
