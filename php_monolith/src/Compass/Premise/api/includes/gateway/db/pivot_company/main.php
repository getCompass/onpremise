<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы данных pivot_user
 */
class Gateway_Db_PivotCompany_Main {

	protected const _DB_KEY = "pivot_company";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Открываем транзакцию
	 *
	 * @param int $company_id
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function beginTransaction(int $company_id):bool {

		return ShardingGateway::database(self::_getDbKey($company_id))->beginTransaction();
	}

	/**
	 * Коммитим транзакцию
	 *
	 * @param int $company_id
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function commitTransaction(int $company_id):void {

		if (!ShardingGateway::database(self::_getDbKey($company_id))->commit()) {

			ShardingGateway::database(self::_getDbKey($company_id))->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Откатываем транзакцию
	 *
	 * @param int $company_id
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function rollback(int $company_id):bool {

		return ShardingGateway::database(self::_getDbKey($company_id))->rollback();
	}

	/**
	 * Получить список существующих шардов
	 *
	 * @return array
	 */
	public static function getExistingShardList():array {

		$sharding_key_list          = [];
		$pivot_company_shard_regexp = "/^pivot_company_[\dm]+$/";

		$conf = getConfig("SHARDING_MYSQL");

		foreach ($conf as $k => $_) {

			if (preg_match($pivot_company_shard_regexp, $k)) {
				$sharding_key_list[] = $k;
			}
		}

		return $sharding_key_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить шард базы
	 *
	 * @param int $company_id
	 *
	 * @return string
	 */
	protected static function _getDbKey(int $company_id):string {

		return self::_DB_KEY . "_" . ceil($company_id / 10000000) . "0m";
	}
}