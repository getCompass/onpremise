<?php

namespace Compass\Pivot;

/**
 * Action для обновления секции конфига с mysql
 */
class Domain_Domino_Action_Config_UpdateMysql {

	/**
	 * Выполняем
	 *
	 * @param Struct_Db_PivotCompany_Company                  $company
	 * @param Struct_Db_PivotCompanyService_DominoRegistry    $domino
	 * @param Struct_Db_PivotCompanyService_PortRegistry|null $port
	 * @param bool                                            $need_force_update
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function do(Struct_Db_PivotCompany_Company               $company,
					  Struct_Db_PivotCompanyService_DominoRegistry $domino,
					  ?Struct_Db_PivotCompanyService_PortRegistry  $port = null,
					  bool                                         $need_force_update = false):void {

		// синкаем конфиг
		try {

			$company_config            = Domain_Domino_Entity_Config::get($company);
			$company_config->status    = $company->status;
			$company_config->domino_id = $company->domino_id;
		} catch (Domain_Company_Exception_ConfigNotExist) {
			$company_config = new Struct_Config_Company_Main($company->status, $company->domino_id);
		}

		$mysql_config = Domain_Domino_Entity_Config::makeMysql($company->status, $company, $domino, $port);
		$company_config->setMysql($mysql_config);

		Domain_Domino_Entity_Config::update($company->company_id, $company_config, $need_force_update);
	}

}