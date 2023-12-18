<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Вспомогательный класс для написания скриптов
 * Нужен для того чтобы не писать один и тот же код много раз
 * !!! использовать можно ТОЛЬКО В СКРИПТАХ
 */
class Type_Script_Helper {

	/**
	 * получает базы данных по паттерну из конфига sharding.php
	 * пример pattern: #^pivot_auth_\d{4}$#si
	 *
	 * @throws \returnException
	 */
	public static function getDatabaseList(string $pattern):array {

		$conf = getConfig("SHARDING_MYSQL");

		// пробегаемся и оставляем только те базы которые соответствуют паттерну
		$output = [];
		foreach ($conf as $k => $_) {

			if (!preg_match($pattern, $k)) {
				continue;
			}

			// проверяем что база существует
			if (!self::_isDbExist($k)) {

				console("!!! DATABASE {$k} NOT EXISTS - SKIP");
				continue;
			}
			$output[] = $k;
		}

		return $output;
	}

	// проверяем что база существует
	protected static function _isDbExist(string $sharding_key):bool {

		try {
			ShardingGateway::database($sharding_key);
		} catch (\PDOException $e) {

			// SQLSTATE[HY000] [1049] Unknown database '%s'
			if ($e->getCode() == 1049) {
				return false;
			}

			throw new ReturnFatalException("Got not 1049 error_code from MySQL in " . __METHOD__ . "\n{$e->getMessage()}");
		}

		return true;
	}

	// получаем таблицы соответствующие паттерну из одной базы
	// пример pattern: #^meta_\d{1,2}$#si
	public static function getOneDbTableList(string $sharding_key, string $pattern):array {

		// получаем список таблиц в базе
		$pdo  = ShardingGateway::database($sharding_key);
		$list = $pdo->query("SHOW TABLES FROM `{$sharding_key}`")->fetchAll(\PDO::FETCH_COLUMN);

		// проходимся и оставляем только соответствующие паттерну
		$output = [];
		foreach ($list as $item) {

			if (!preg_match($pattern, $item)) {
				continue;
			}
			$output[] = $item;
		}

		return $output;
	}
}
