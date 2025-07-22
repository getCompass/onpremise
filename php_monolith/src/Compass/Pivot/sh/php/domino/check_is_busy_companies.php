<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для проверки наличия is_busy = 1 компаний
 */
class Domino_CheckIsBusyCompanies {

	/**
	 * Принимаем параметры
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @long
	 */
	public function start():void {

		$domino_list = Gateway_Db_PivotCompanyService_DominoRegistry::getAll();
		foreach ($domino_list as $domino) {

			$is_busy_count = Gateway_Db_PivotCompanyService_CompanyRegistry::getIsBusyCount($domino->domino_id);
			if ($is_busy_count > 0) {

				console(redText("На домино {$domino->domino_id} есть занятые компании, количество: {$is_busy_count}"));
				exit(1);
			}
		}
	}
}

try {
	(new Domino_CheckIsBusyCompanies())->start();
} catch (Error|Exception) {

	console(redText("Не смогли проверить занятые компании"));
	exit(1);
}