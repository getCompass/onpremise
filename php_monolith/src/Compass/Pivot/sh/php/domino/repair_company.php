<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$company_id  = Type_Script_InputParser::getArgumentValue("company-id", Type_Script_InputParser::TYPE_INT);
$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
$domino_row  = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);

console(blueText("Инвалидирую компанию"));

Gateway_Db_PivotCompanyService_CompanyRegistry::set($company_row->domino_id, $company_row->company_id, [
	"is_busy"        => 1,
	"is_hibernated"  => 0,
	"is_mysql_alive" => 0,
]);

// помечаем компанию как недоступную, чтобы никто не попал в нее
Gateway_Db_PivotCompany_CompanyList::set($company_row->company_id, [
	"status"     => Domain_Company_Entity_Company::COMPANY_STATUS_INVALID,
	"updated_at" => time(),
]);

console(blueText("Пробуем остановить компанию"));
try {

	// генерим обновленный конфиг для компании
	Domain_Domino_Action_Config_UpdateMysql::do($company_row, $domino_row, need_force_update: true);
	Domain_Domino_Action_StopCompany::run($domino_row, $company_row, "repair_company");

	Type_Phphooker_Main::onCountCompany(time());
} catch (\Exception) {
	console(redText("Не удалось остановить компанию"));
}

// ждем перед пробуждением
console(blueText("Ждем перед стартом компании"));
sleep(2);
console(blueText("Пробую стартануть компанию"));

// запускаем компанию
[$company_row] = Domain_Domino_Action_StartCompany::run($domino_row, $company_row);

// конфиг обновляется каждые 2 секунды - нужно дождаться, когда компания станет доступной
sleep(2);

// будем компанию пока неразбудим в течении таймаута
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

// если не смогли разбудить компанию
if (!is_null($e)) {
	throw $e;
}

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