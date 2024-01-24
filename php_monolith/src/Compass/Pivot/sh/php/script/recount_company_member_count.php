<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт пересчета числа участников в компании
 */
class Recount_Company_Member_Count {

	protected const _COMPANY_PER_QUERY = 1000;

	/** @var bool драй ран */
	protected bool $_is_dry_run = true;

	/**
	 * @param array $company_id_list для каких компаний необходимо провернуть действие
	 */
	public function run(array $company_id_list = [], bool $is_dry_run = true):void {

		$this->_is_dry_run = $is_dry_run;

		if (!$this->_is_dry_run) {

			console("Скрипт запущен в dry-run");
			console("Чтобы выполнить его на самом деле небходимо вызвать следующим образом: php recount_company_member_count.php dry-run");
			console("---");
		}

		console(count($company_id_list) === 0 ? "Обновляем все компании" : "Обновляем только переданные компании " . implode(", ", $company_id_list));
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
						$this->_run($company);
					} catch (\Exception $e) {

						console("Упали с ошибкой на компании {$company->company_id} по причине: {$e->getMessage()}");
					}
				}

				$offset += $this::_COMPANY_PER_QUERY;
			} while ($has_next);
		}

		if (!$is_company_exists) {
			console("Все очень плохо - не нашли ни одной компании");
		}
	}

	/**
	 * получаем список компаний
	 */
	protected function _getCompanyList(int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $this::_COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::_COMPANY_PER_QUERY];
	}

	/**
	 * получаем компании
	 */
	protected function _getSpecifiedCompanyList(array $company_id_list, int $shard, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` = ?i AND `company_id` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, $company_id_list, $this::_COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= $this::_COMPANY_PER_QUERY];
	}

	/**
	 * для каждой компании
	 */
	protected function _run(Struct_Db_PivotCompany_Company $company):void {

		// проверяем на всякий компанию
		Domain_Company_Entity_Validator::assertActiveCompany($company);

		// получаем количество пользователей из самой компании сокетом (там должно быть всегда актуально)
		$member_count_from_company = Domain_Company_Action_GetMemberCount::do($company);

		// достаем число пользователей которое храним на пивоте
		$member_count_from_pivot = Domain_Company_Entity_Company::getMemberCount($company->extra);

		// елси число одинаковое, просто скипаем
		if ($member_count_from_company == $member_count_from_pivot) {
			return;
		} else {
			console("В компании id = {$company->company_id} : {$member_count_from_company} пользователей, а на пивоте в счетчике {$member_count_from_pivot}");
		}

		// если драй ран, то выходим
		if (!$this->_is_dry_run) {
			return;
		}

		// перезаписали количество пользователей на пивоте в сущности компании
		$updated_company = $this->setCompanyMemberCount($company->company_id, $member_count_from_company);
		console("Перезаписали в компании id = {$updated_company->company_id} количество пользователей с: {$member_count_from_pivot} на {$member_count_from_company}");
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
			$row["deleted_at"],
			$row["updated_at"],
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
	 * Получаем список id компаний с консоли если передали
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

	/**
	 * Пишем число участников в компании
	 */
	public static function setCompanyMemberCount(int $company_id, int $member_count_from_company):Struct_Db_PivotCompany_Company {

		Gateway_Db_PivotCompany_CompanyList::beginTransaction($company_id);
		try {

			$company = Gateway_Db_PivotCompany_CompanyList::getForUpdate($company_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotCompany_CompanyList::rollback($company_id);
			throw new ReturnFatalException("row not found");
		}

		// берем количество участников что получили из компании
		$member_count = $member_count_from_company;

		// пишем в бд
		$company->extra = Domain_Company_Entity_Company::setMemberCount($company->extra, $member_count);
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"extra" => $company->extra,
		]);
		Gateway_Db_PivotCompany_CompanyList::commitTransaction($company_id);

		return $company;
	}
}

(new Recount_Company_Member_Count())->run(Recount_Company_Member_Count::getCompanyListFromCli(), isDryRun());
