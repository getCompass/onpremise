<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для добавления участников пространства в чат Спасибо во всех пространствах
 */
class Join_Users_To_Respect_Conversation_In_Spaces {

	protected const SPACE_PER_QUERY = 1000;

	/** @var bool драй ран */
	protected bool $_is_dry_run = true;

	/**
	 * @param array $space_id_list для каких пространств необходимо провернуть действие
	 *
	 * @long
	 */
	public function run(array $space_id_list = [], bool $is_dry_run = true):void {

		$this->_is_dry_run = $is_dry_run;

		console(count($space_id_list) === 0 ? "update all spaces" : "update spaces " . implode(", ", $space_id_list));

		$is_space_exists = false;

		// для каждого шарда
		for ($i = 1; $i <= 10; $i++) {

			$offset = 0;

			do {

				[$space_list, $has_next] = count($space_id_list) === 0
					? $this->_getSpaceList($i, $offset)
					: $this->_getSpecifiedSpaceList($space_id_list, $i, $offset);

				foreach ($space_list as $space_row) {

					$is_space_exists = true;

					// делаем структуру для удобства
					$space = $this->_makeSpaceStruct($space_row);

					try {

						$this->_updateSpace($space);
						console(greenText("Успешно добавил пользователей чат Спасибо в пространстве {$space->company_id}"));
					} catch (\Exception $e) {
						console("can't update company, reason: {$e->getMessage()}");
					}
				}

				$offset += $this::SPACE_PER_QUERY;
			} while ($has_next);
		}

		if (!$is_space_exists) {
			console("it seems spaces weren't found");
		}
	}

	/**
	 * Получаем список пространств
	 *
	 * @param int $shard
	 * @param int $offset
	 *
	 * @return array
	 */
	protected function _getSpaceList(int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $this::SPACE_PER_QUERY, $offset);

		return [$result, count($result) >= $this::SPACE_PER_QUERY];
	}

	/**
	 * Получаем только определенные пространства
	 *
	 * @param array $space_id_list
	 * @param int   $shard
	 * @param int   $offset
	 *
	 * @return array
	 */
	protected function _getSpecifiedSpaceList(array $space_id_list, int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$status = Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE;
		$query  = "SELECT * FROM `?p` WHERE `status` = ?i AND `company_id` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, $status, $space_id_list, $this::SPACE_PER_QUERY, $offset);

		return [$result, count($result) >= $this::SPACE_PER_QUERY];
	}

	/**
	 * Обновляем пространства
	 *
	 * @param Struct_Db_PivotCompany_Company $space
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	protected function _updateSpace(Struct_Db_PivotCompany_Company $space):void {

		$this->_is_dry_run && console("dry run, doing nothing");

		if ($this->_is_dry_run === false) {

			Gateway_Socket_Conversation::addMembersToRespectConversation($space->company_id, $space->domino_id,
				Domain_Company_Entity_Company::getPrivateKey($space->extra));
		}
	}

	/**
	 * Создаем сткруктуру из строки бд
	 *
	 * @noinspection DuplicatedCode
	 */
	protected function _makeSpaceStruct(array $row):Struct_Db_PivotCompany_Company {

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
	 * Получаем список пространств из консоли
	 *
	 * @return array
	 */
	public static function getSpaceListFromCli():array {

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

(new Join_Users_To_Respect_Conversation_In_Spaces())->run(Join_Users_To_Respect_Conversation_In_Spaces::getSpaceListFromCli(), isDryRun());
