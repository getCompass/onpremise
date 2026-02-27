<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$company_id  = Type_Script_InputParser::getArgumentValue("company-id", Type_Script_InputParser::TYPE_INT);
$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
$domino_row  = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);

console(blueText("Инвалидирую компанию"));
Type_System_Admin::log("start_company_process", "--- Инвалидируем компанию {$company_row->company_id}");

Gateway_Db_PivotCompanyService_CompanyRegistry::set($company_row->domino_id, $company_row->company_id, [
	"is_busy"        => 1,
	"is_hibernated"  => 0,
	"is_mysql_alive" => 0,
]);

// помечаем компанию как недоступную, чтобы никто не попал в нее
$company_row->status = Domain_Company_Entity_Company::COMPANY_STATUS_INVALID;
Gateway_Db_PivotCompany_CompanyList::set($company_row->company_id, [
	"status"     => $company_row->status,
	"updated_at" => time(),
]);

console(blueText("Пробуем остановить компанию"));
Type_System_Admin::log("start_company_process", "Останавливаем mysql и компанию {$company_row->company_id}");
try {

	// генерим обновленный конфиг для компании
	Domain_Domino_Action_Config_UpdateMysql::do($company_row, $domino_row, need_force_update: true);
	Domain_Domino_Action_StopCompany::run($domino_row, $company_row, "repair_company");

	Type_Phphooker_Main::onCountCompany(time());
} catch (\Exception $e) {
	console(redText("Не удалось остановить компанию. Ошибка: {$e->getMessage()}"));
	Type_System_Admin::log("start_company_process", "Error: не удалось остановить компанию {$company_row->company_id}. Error: {$e->getMessage()}");
}

// ждем перед пробуждением
console(blueText("Ждем перед стартом компании"));
sleep(2);
console(blueText("Пробую стартануть компанию"));

// запускаем компанию
Type_System_Admin::log("start_company_process", "Выполняем старт компании {$company_row->company_id}");
if (ServerProvider::isReserveServer()) {
	[$company_row] = Domain_Domino_Action_StartReserveCompany::run($domino_row, $company_row);
} else {
	[$company_row] = Domain_Domino_Action_StartCompany::run($domino_row, $company_row);
}

// конфиг обновляется каждые 2 секунды - нужно дождаться, когда компания станет доступной
sleep(2);

// будем компанию пока неразбудим в течении таймаута
Type_System_Admin::log("start_company_process", "Выполняем пробуждение компании {$company_row->company_id}");
$awake_timeout_timestamp = time() + 10;
$e                       = null;
do {

	sleep(1);

	try {

		Gateway_Socket_Company::awake(
			$company_row->company_id,
			time() + 60 * 60,
			time(),
			$company_row->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company_row->extra),
			[],
			[]
		);
		$e = null;
		break;
	} catch (cs_CompanyIsHibernate $e) {

		// пишем в лог и пробуем снова если не вышло время
		Type_System_Admin::log("awake_error_repair_company", "Не смогли пробудить компанию {$company_row->company_id}");
	}
} while ($awake_timeout_timestamp > time());
console(blueText("Успешно разбудил компанию {$company_row->company_id}"));

// если не смогли разбудить компанию
if (!is_null($e)) {
	throw $e;
}
Type_System_Admin::log("start_company_process", "Успешно разбудили компании {$company_row->company_id}");

Gateway_Bus_SenderBalancer::companyAwoke($company_row->company_id, Domain_Company_Action_GetUserIdList::do($company_row));

Type_Phphooker_Main::onCountCompany(time());

console(blueText("Стартанул компанию"));

// актуализируем запись в company_tier_observe
// специально делаем delete + insert, на случай если записи раньше не было или она неактуальная
Gateway_Db_PivotCompany_CompanyTierObserve::delete($company_row->company_id);
Gateway_Db_PivotCompany_CompanyTierObserve::insert($company_row->company_id, $domino_row->tier, 0, Domain_Company_Entity_Tier::initExtra());

// разблокируем компанию
Gateway_Db_PivotCompanyService_CompanyRegistry::set($company_row->domino_id, $company_row->company_id, ["is_busy" => 0]);

console(blueText("Разблокировал компанию"));
Type_System_Admin::log("start_company_process", "Восстановление компании {$company_row->company_id} завершено");
