<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для добавления бота Службы поддержки во все компании
 */
class Add_Support_Bot_To_Companies {

	protected const COMPANY_PER_QUERY = 1000;

	/** @var bool драй ран */
	protected bool $_is_dry_run = true;

	/**
	 * @param array $company_id_list для каких компаний необходимо провернуть действие
	 *
	 * @long
	 */
	public function run(array $company_id_list = [], bool $is_dry_run = true):void {

		$this->_is_dry_run = $is_dry_run;

		console(count($company_id_list) === 0 ? "update all companies" : "update companies " . implode(", ", $company_id_list));

		$is_company_exists = false;

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

						$this->_updateCompany($company);
						console(greenText("Успешно добавил в компанию {$company->company_id}"));
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
	 * Получаем список компаний
	 *
	 * @param int $shard
	 * @param int $offset
	 *
	 * @return array
	 */
	protected function _getCompanyList(int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $this::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::COMPANY_PER_QUERY];
	}

	/**
	 * Получаем только определенные компании
	 *
	 * @param array $company_id_list
	 * @param int   $shard
	 * @param int   $offset
	 *
	 * @return array
	 */
	protected function _getSpecifiedCompanyList(array $company_id_list, int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$status = Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE;
		$query  = "SELECT * FROM `?p` WHERE `status` = ?i AND `company_id` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, $status, $company_id_list, $this::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::COMPANY_PER_QUERY];
	}

	/**
	 * Обновляем компании
	 */
	protected function _updateCompany(Struct_Db_PivotCompany_Company $company):void {

		$this->_is_dry_run && console("dry run, doing nothing");

		if ($this->_is_dry_run === false) {

			if (!defined("SUPPORT_BOT_USER_ID") || SUPPORT_BOT_USER_ID < 1) {

				console(redText("ERROR! SUPPORT_BOT_USER_ID NOT FOUND"));
				exit(1);
			}

			$bot_info = Gateway_Bus_PivotCache::getUserInfo(SUPPORT_BOT_USER_ID);

			$bot_info = [
				"user_id"         => $bot_info->user_id,
				"full_name"       => $bot_info->full_name,
				"avatar_file_key" => mb_strlen($bot_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($bot_info->avatar_file_map) : "",
				"npc_type"        => $bot_info->npc_type,
			];

			Gateway_Socket_Company::addSystemBot(
				$bot_info, $company->company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));
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
	 * Получаем список компаний из консоли
	 *
	 * @return array
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

(new Add_Support_Bot_To_Companies())->run(Add_Support_Bot_To_Companies::getCompanyListFromCli(), isDryRun());
