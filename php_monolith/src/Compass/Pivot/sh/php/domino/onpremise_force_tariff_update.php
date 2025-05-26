<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Скрипт для отмены тарифа на онпремайзе
 */
class Domino_OnpremiseForceTariffUpdate {

	protected const _INITIAL_TARIFF_MEMBER_LIMIT  = 1000;

	protected string $_company_name;

	/**
	 * Запускаем работу скрипта
	 */
	public function run():void {

        // ни в коем случае не запускать на saas
        if (!ServerProvider::isOnPremise()) {
            return;
        }

        // получаем список всех команд
        $company_list = Gateway_Db_PivotCompany_CompanyList::getActiveList();

        // получаем рутового пользователя
        $user_id =  Domain_User_Entity_OnpremiseRoot::getUserId();
		
		if ($user_id < 1) {
			return;
		}

        // для каждой команды
		foreach ($company_list as $company) {

			$tariff = Domain_SpaceTariff_Repository_Tariff::get($company->company_id);

			// если у компании уже установлено бесконечное действия тарифа - пропускаем ее
			if ($tariff->memberCount()->getActiveTill() == 0 && $tariff->memberCount()->getRestrictedAccessFrom() == 0) {
				continue;
			}

            // создаем альтерацию с бесконечным сроком действия
            $alteration = \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount(self::_INITIAL_TARIFF_MEMBER_LIMIT)
			->setActions(\Tariff\Plan\BaseAlteration::PROLONG, \Tariff\Plan\BaseAlteration::CHANGE, \Tariff\Plan\BaseAlteration::ACTIVATE)
			->setProlongation(\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_INFINITE)
			->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_REASON_REQUIRED));

            // применяем ее на команду
			Domain_SpaceTariff_Action_AlterMemberCount::run($user_id, $company->company_id, \Tariff\Plan\BaseAction::METHOD_FORCE, $alteration);
        }
	}
}

try {
	(new Domino_OnpremiseForceTariffUpdate())->run();
} catch (\Exception $e) {

	console(redText("Не смогли обновить тариф, ошибка " . $e->getMessage()));
	exit(1);
}
