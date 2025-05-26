<?php

namespace Compass\Migration;

/**
 * Сценарии для cli скриптов
 */
class Domain_Domino_Scenario_Cli {

	const MIGRATION_TYPE_UP           = 1;
	const MIGRATION_TYPE_LEGACY_CLEAN = 2;

	/**
	 * поднимаем миграции
	 *
	 * @param array $company_id_list
	 * @param int   $migration_type
	 *
	 * @throws \returnException
	 */
	public static function migrate(array $company_id_list, int $migration_type):void {

		if (count($company_id_list) < 1) {
			$company_id_list = Gateway_Socket_Pivot::getActiveDominoCompanyIdList(DOMINO_ID);
		}

		$migration_options = Gateway_Socket_Pivot::getDominoMigrationOptions();

		foreach ($company_id_list as $company_id) {

			$need_port = 0;

			try {
				Gateway_Bus_DatabaseController::getCompanyPort($company_id);
			} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

				$need_port = 1;

				// если пробуждать не нужно - то и не пробуждаем
				if (!$migration_options["need_wakeup"]) {

					Type_System_Log::doErrorLog("Пропустили компанию: " . $company_id . " потому что не нужно пробуждать");
					continue;
				}
			}

			// если компания занята - пропускаем ее
			try {
				Gateway_Socket_Pivot::lockBeforeMigration($company_id, $need_port);
			} catch (Gateway_Socket_Exception_CompanyIsBusy) {

				Type_System_Log::doErrorLog("Пропустили компанию: " . $company_id . " потому что она в данный момент занята");
				continue;
			}

			// запускаем в компании миграцию
			self::_migrateCompany($company_id, $migration_type, $migration_options);

			// установить, что компания больше не занята
			Gateway_Socket_Pivot::unlockAfterMigration($company_id, $need_port);
		}
	}

	/**
	 * Запускаем миграцию на определенную компанию
	 *
	 * @param int   $company_idd
	 * @param int   $migration_type
	 * @param array $migration_options
	 *
	 * @return void
	 */
	protected static function _migrateCompany(int $company_id, int $migration_type, array $migration_options):void {

		try {
			// создаем бэкап, если нужно
			if ($migration_options["need_backup"]) {
				Gateway_Bus_DatabaseController::createMysqlBackup($company_id);
			}

			// накатываем миграции
			match ($migration_type) {
				self::MIGRATION_TYPE_UP => Gateway_Bus_DatabaseController::migrateUp($company_id),
				self::MIGRATION_TYPE_LEGACY_CLEAN => Gateway_Bus_DatabaseController::migrateLegacyClean($company_id),
			};
		} catch (\Exception $e) {

			console("Отвалилась задача миграции на компании $company_id на домино " . DOMINO_ID .
				" сервер " . PIVOT_DOMAIN . ":" . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
		}
	}
}