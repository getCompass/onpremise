<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс-интерфейс для базы данных pivot_user
 */
class Gateway_Db_PivotCompany_Main {

	protected const _DB_KEY = "pivot_company";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 *
	 */
	public static function beginTransaction(int $company_id):bool {

		return ShardingGateway::database(self::_getDbKey($company_id))->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @param int $company_id
	 *
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 */
	public static function commitTransaction(int $company_id):void {

		if (!ShardingGateway::database(self::_getDbKey($company_id))->commit()) {

			ShardingGateway::database(self::_getDbKey($company_id))->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 *
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
	 * Получаем шард ключ для базы данных
	 */
	protected static function _getDbKey(int $company_id):string {

		return self::_DB_KEY . "_" . ceil($company_id / 10000000) . "0m";
	}

	/**
	 * проверяем шард
	 *
	 * @param string $table_shard
	 * @param int    $company_id
	 *
	 * @throws ParamException
	 */
	protected static function _checkExistShard(string $table_shard, int $company_id):void {

		$conf = getConfig("SHARDING_MYSQL");
		if (!isset($conf[self::_getDbKey($company_id)])) {
			throw new ParamException("incorrect company_id");
		}
		$pivot_company_conf = $conf[self::_getDbKey($company_id)];

		if (!isset($pivot_company_conf["schemas"]["tables"][$table_shard])) {
			throw new ParamException("incorrect company_id");
		}
	}
}