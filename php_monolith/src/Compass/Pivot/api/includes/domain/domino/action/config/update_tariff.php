<?php

namespace Compass\Pivot;

/**
 * Action для обновления секции конфига с тарифами
 */
class Domain_Domino_Action_Config_UpdateTariff {

	/**
	 * Выполняем
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param Domain_SpaceTariff_Tariff      $tariff
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company,
					  Domain_SpaceTariff_Tariff      $tariff):void {

		// синкаем конфиг
		try {
			$company_config = Domain_Domino_Entity_Config::get($company);
		} catch (Domain_Company_Exception_ConfigNotExist) {
			$company_config = new Struct_Config_Company_Main($company->status, $company->domino_id);
		}

		$tariff_config = Domain_Domino_Entity_Config::makeTariff($tariff->memberCount()->getData());
		$company_config->setTariff($tariff_config);

		Domain_Domino_Entity_Config::update($company->company_id, $company_config);
	}
}