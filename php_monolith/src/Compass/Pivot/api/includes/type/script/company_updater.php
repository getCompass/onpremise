<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для обновления компаний.
 */
class Type_Script_CompanyUpdater {

	protected const COMPANY_PER_QUERY = 1000;

	/**
	 * Выполняет обновление компаний.
	 *
	 * @long
	 */
	public static function exec(callable $callback, array $allowed_status_list, array $company_id_list = [], array $excluded_company_id_list = []):void {

		// строим разницу, чтобы меньше из бд дергать
		$company_id_list = array_unique(array_diff($company_id_list, $excluded_company_id_list));

		// запрашиваем подтверждение от пользователя
		if (!Type_Script_CompanyUpdateInputHelper::isConfirmed() && !static::_assertConfirm($company_id_list, $excluded_company_id_list, $allowed_status_list)) {

			console("aborted");
			return;
		}

		// количество обновленных компаний
		$updated_company_count = 0;

		// для каждого шарда
		for ($i = 1; $i <= 10; $i++) {

			$offset = 0;

			do {

				[$company_list, $has_next] = count($company_id_list) === 0
					? static::_getCompanyList($i, $allowed_status_list, $offset)
					: static::_getSpecifiedCompanyList($company_id_list, $i, $allowed_status_list, $offset);

				foreach ($company_list as $company_row) {

					if (in_array($company_row["company_id"], $excluded_company_id_list)) {

						console("company {$company_row["company_id"]} was selected, but skipped");
						continue;
					}

					$updated_company_count++;

					// делаем структуру для удобства
					$callback(static::_makeCompanyStruct($company_row));
				}

				$offset += static::COMPANY_PER_QUERY;
			} while ($has_next);
		}

		if ($updated_company_count === 0) {
			console("it seems companies weren't found");
		}
	}

	/**
	 * Проверяет, что пользователь подтверждает обновление.
	 */
	protected static function _assertConfirm(array $company_id_list, array $excluded_company_id_list, array $allowed_status_list):bool {

		if (count($company_id_list) === 0) {

			if (count($excluded_company_id_list) > 0) {
				$message = "press y to update all companies except " . implode(", ", $excluded_company_id_list);
			} else {
				$message = "press y to update all companies";
			}
		} elseif (count($company_id_list) < 20) {
			$message = "press y to update companies " . implode(", ", $company_id_list);
		} else {
			$message = "press y to update " . count($company_id_list) . " companies";
		}

		if (in_array(Domain_Company_Entity_Company::COMPANY_STATUS_VACANT, $allowed_status_list)) {
			$message .= " (free companies will be processed too)";
		}

		return Type_Script_InputHelper::assertConfirm($message);
	}

	/**
	 * Возвращает список компаний.
	 *
	 * @param int $shard
	 * @param int $offset
	 *
	 * @return array
	 */
	protected static function _getCompanyList(int $shard, array $allowed_status_list, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, $allowed_status_list, static::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= static::COMPANY_PER_QUERY];
	}

	/**
	 * Возвращает список конкретных компаний.
	 *
	 * @return array
	 */
	protected static function _getSpecifiedCompanyList(array $company_id_list, int $shard, array $allowed_status_list, int $offset):array {

		$db    = "pivot_company_10m";
		$table = "company_list_{$shard}";

		$query  = "SELECT * FROM `?p` WHERE `status` IN (?a) AND `company_id` IN (?a) LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db)->getAll($query, $table, $allowed_status_list, $company_id_list, static::COMPANY_PER_QUERY, $offset);

		return [$result, count($result) >= static::COMPANY_PER_QUERY];
	}

	/**
	 * Создаем сткруктуру из строки бд
	 *
	 * @noinspection DuplicatedCode
	 */
	protected static function _makeCompanyStruct(array $row):Struct_Db_PivotCompany_Company {

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
}
