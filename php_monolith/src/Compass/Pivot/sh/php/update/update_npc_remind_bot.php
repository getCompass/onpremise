<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для обновления npc бота Напоминаний
 */
class Update_Npc_Remind_Bot {

	protected const COMPANY_PER_QUERY = 1000;

	/** @var bool драй ран */
	protected bool $_is_dry_run = true;

	/**
	 * @param array $company_id_list для каких компаний необходимо провернуть действие
	 *
	 * @throws \parseException
	 * @long
	 */
	public function run(array $company_id_list = [], bool $is_dry_run = true):void {

		$this->_is_dry_run = $is_dry_run;

		console(count($company_id_list) === 0 ? "update all companies" : "update companies " . implode(", ", $company_id_list));

		$is_company_exists = false;

		// апдейтим бота на пивоте
		$this->_updatePivotUser();

		// после чего апдейтим в каждой компании
		// для каждого шарда
		for ($i = 1; $i <= 10; $i++) {

			$offset = 0;

			do {

				[$company_list, $has_next] = count($company_id_list) === 0
					? $this->_getCompanyList($i, $offset)
					: $this->_getSpecifiedCompanyList($company_id_list, $i, $offset);

				foreach ($company_list as $company_row) {

					$is_company_exists = true;

					// делаем структуру для удобства
					$company = $this->_makeCompanyStruct($company_row);

					try {
						$this->_updateInCompany($company);
					} catch (\Exception $e) {

						console("can't update company, reason: {$e->getMessage()}");
					}
				}

				$offset += $this::COMPANY_PER_QUERY;
			} while ($has_next);
		}

		if (!$is_company_exists) {
			console("it seems companies weren't found");
		}
	}

	/**
	 * получаем записи компаний
	 */
	protected function _getCompanyList(int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $this::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::COMPANY_PER_QUERY];
	}

	/**
	 * получаем определённые компании
	 */
	protected function _getSpecifiedCompanyList(array $company_id_list, int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i AND `company_id` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $company_id_list, $this::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::COMPANY_PER_QUERY];
	}

	/**
	 * Обновляем компании
	 */
	protected function _updateInCompany(Struct_Db_PivotCompany_Company $company):void {

		$this->_is_dry_run && console("dry run, doing nothing");

		if (!defined("REMIND_BOT_USER_ID") || REMIND_BOT_USER_ID < 1) {

			console(redText("ERROR! REMIND_BOT_USER_ID NOT FOUND"));
			exit(1);
		}

		// изменения ТОЛЬКО ВНЕ режима DRY-RUN
		if ($this->_is_dry_run === false) {

			$bot_info = [
				"user_id"  => REMIND_BOT_USER_ID,
				"npc_type" => Type_User_Main::NPC_TYPE_SYSTEM_BOT_REMIND,
			];

			Gateway_Socket_Company::updateSystemBot($bot_info, $company->company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));
		}
	}

	/**
	 * апдейтим бота на пивоте
	 */
	protected function _updatePivotUser():void {

		$user_id = REMIND_BOT_USER_ID; // выбираем бота Напоминаний для изменения

		console(greenText("обновили данные на пивоте"));

		// изменения ТОЛЬКО ВНЕ режима DRY-RUN
		if ($this->_is_dry_run === false) {

			// формируем массив на обновление
			$set = [
				"npc_type"   => Type_User_Main::NPC_TYPE_SYSTEM_BOT_REMIND,
				"updated_at" => time(),
			];

			// обновляем пользователя и скидываем кеш
			Gateway_Db_PivotUser_UserList::set($user_id, $set);
			Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
		}
	}

	/**
	 * Создаем сткруктуру из строки бд
	 *
	 * @noinspection DuplicatedCode
	 */
	protected function _makeCompanyStruct(array $row):Struct_Db_PivotCompany_Company {

		$extra = fromJson($row["extra"]);

		return new Struct_Db_PivotCompany_Company(
			$row["company_id"],
			$row["is_deleted"],
			$row["status"],
			$row["created_at"],
			$row["updated_at"],
			$row["deleted_at"],
			$row["avatar_color_id"],
			$row["created_by_user_id"],
			$row["partner_id"],
			$row["domino_id"],
			$row["name"],
			$row["url"],
			$row["avatar_file_map"],
			$extra,
		);
	}

	/**
	 * получаем компании из консоли
	 */
	public static function getCompanyListFromCli():array {

		global $argv;

		$arr = array_slice($argv, 1);

		$output = [];

		foreach ($arr as $v) {

			if (is_numeric($v)) {
				$output[] = intval($v);
			}
		}

		return $output;
	}
}

(new Update_Npc_Remind_Bot())->run(Update_Npc_Remind_Bot::getCompanyListFromCli(), isDryRun());
