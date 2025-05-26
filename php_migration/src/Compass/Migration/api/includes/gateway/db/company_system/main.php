<?php

namespace Compass\Migration;

/**
 * класс-интерфейс для базы данных company_system
 */
class Gateway_Db_CompanySystem_Main {

	protected const _DB_KEY = "company_system";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод открывает транзакцию
	public static function beginTransaction():bool {

		return customSharding::pdo(self::_DB_KEY)->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction():void {

		if (!customSharding::pdo(self::_DB_KEY)->commit()) {

			customSharding::pdo(self::_DB_KEY)->rollback();
			throw new \returnException("Transaction commit failed");
		}
	}

	// метод откатывает транзакцию
	public static function rollback():bool {

		return customSharding::pdo(self::_DB_KEY)->rollback();
	}

	/**
	 * Создаем базу если ее нет
	 *
	 * @throws paramException
	 * @throws parseException
	 * @long
	 */
	public static function createIfNotExist():void {

		$results = customSharding::pdoWithoutDb()->query("SHOW DATABASES;")->fetchAll();

		foreach ($results as $row) {

			if ($row["Database"] == self::_DB_KEY) {

				Type_System_Log::doInfoLog("База для работы миграций существует");
				return;
			}
		}

		// накатываем init бд
		try {

			customSharding::pdoWithoutDb()->query("CREATE SCHEMA IF NOT EXISTS `" . self::_DB_KEY . "` DEFAULT CHARACTER SET utf8;");
		} catch (PDOException $e) {

			Type_System_Log::doErrorLog("Не смогли создать базу для миграций");
			throw $e;
		}

		$sql = file_get_contents(PATH_SQL . "/release/company_system/1_init.up.sql");

		if ($sql === false || mb_strlen($sql) < 1) {

			Type_System_Log::doErrorLog("Стартовая миграция повреждена, не смогли накатить");
			throw new parseException("start migration invalid");
		}

		// накатываем init миграцию
		try {

			customSharding::pdo(self::_DB_KEY)->query($sql);
		} catch (PDOException $e) {

			Type_System_Log::doErrorLog("Не смогли создать таблицу для миграция");
			throw $e;
		}
		Type_System_Log::doCompleteLog("Создали бд с миграциями");
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем шард ключ для базы данных
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}