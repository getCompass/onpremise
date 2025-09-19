<?php

namespace Compass\Pivot;

/**
 * Выполняет запуск компании на домино.
 */
class Domain_Domino_Action_StartCompany {

	/** @var float|int время, на которое блокируется порт */
	protected const _PORT_LOCK_DURATION = 60;

	/**
	 * Выполняет запуск компании на домино.
	 * <h2>Это действие пересоздает конфиг компании.</h2>
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 * @param Struct_Db_PivotCompany_Company               $company
	 *
	 * @return array
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \returnException
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => Struct_Db_PivotCompany_Company::class, 1 => Struct_Db_PivotCompanyService_PortRegistry::class])]
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompany_Company $company):array {

		try {

			// пытаемся получить активный порт, чтобы не пытаться поднять живую компанию
			$active_port = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($domino->domino_id, $company->company_id);

			// проверяем, не на сервисном ли порту развернута компания
			// сервисные гасить нужно там, где они поднимались
			if (Domain_Domino_Entity_Port_Registry::isService($active_port)) {
				throw new Domain_Domino_Exception_CompanyInOnMaintenance("company bound to service port $active_port->port, maintenance probably");
			}

			// если там не сервисные работы, значит компания активна в рядовом режиме
			throw new Domain_Domino_Exception_CompanyIsBound("company is already bound to port $active_port->port");
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// это нормально, просто проверили, что компания не привязана к порту
		}

		// получаем рабочий порт, на котором будет подниматься компания и привязываем ее
		Type_System_Admin::log("start_company_process", "Выполняем привязку к порту для компании {$company->company_id}");
		$target_port = Domain_Domino_Action_Port_ResolveWorkPortForCompany::run($domino, $company, static::_PORT_LOCK_DURATION);
		Type_System_Admin::log("start_company_process", "Выбрали порт {$target_port->port} для привязки компании {$company->company_id}");
		$target_port = Domain_Domino_Action_Port_Bind::run($domino, $target_port, $company->company_id, Domain_Domino_Action_Port_Bind::POLICY_WAKING_UP);
		Type_System_Admin::log("start_company_process", "Привязка к порту успешно выполнена для компании {$company->company_id}");

		// поднимаем актуальную миграцию
		console("поднимаем актуальную миграцию для компании {$company->company_id}");
		Gateway_Bus_DatabaseController::migrateUp($domino, $company->company_id);
		Type_System_Admin::log("start_company_process", "Актуализировали миграцию для компании {$company->company_id}");
		console("актуализировали миграцию для компании {$company->company_id}");

		// обновляем данные компании
		$company = static::_updateCompany($company, $domino);
		Type_System_Admin::log("start_company_process", "Обновили данные компании {$company->company_id}");
		console("обновили данные компании {$company->company_id}");

		// генерим конфиг тарифа
		$tariff = Domain_SpaceTariff_Repository_Tariff::get($company->company_id);
		Domain_Domino_Action_Config_UpdateTariff::do($company, $tariff);
		Type_System_Admin::log("start_company_process", "Сгенерировали файл-конфиг тарифа для компании {$company->company_id}");
		console("сгенерировали файл-конфиг тарифа для компании {$company->company_id}");

		// генерим конфиг мускула для компании в активном состоянии
		Domain_Domino_Action_Config_UpdateMysql::do($company, $domino, $target_port, true);
		Type_System_Admin::log("start_company_process", "Обновили файл-конфиг mysql для компании {$company->company_id}");
		console("обновляем файл-конфиг mysql для компании {$company->company_id}");

		// ждем, пока компания не подтвердит готовность
		Domain_Domino_Action_WaitConfigSync::do($company, $domino);
		Type_System_Admin::log("start_company_process", "Успешно синхронизированы конфиг-файлы между pivot и компанией {$company->company_id}");
		console("успешно синхронизированы конфиг-файлы между pivot и компанией {$company->company_id}");

		// обновляем данные премиума для компании
		console("обновляем дополнительные данные после успешного старта компании {$company->company_id}");
		return [static::_afterStart($company), $target_port];
	}

	/**
	 * Обновляет данные компании
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	protected static function _updateCompany(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $domino):Struct_Db_PivotCompany_Company {

		// меняем статус компании
		$company->status    = Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE;
		$company->url       = Domain_Domino_Entity_Registry_Main::makeCompanyUrl($company->company_id, $domino);
		$company->domino_id = $domino->domino_id;

		// меняем статус компании
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"status"     => $company->status,
			"url"        => $company->url,
			"domino_id"  => $domino->domino_id,
			"updated_at" => $company->updated_at,
		]);

		// меняем флаг в реестре компаний
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, [
			"is_mysql_alive" => 1,
			"is_hibernated"  => 0, // не знаю, насколько правильно тут этот флаг менять, но по идее он всегда должен в 0 обращаться
		]);

		return $company;
	}

	/**
	 * Выполняет какие-то действия после успешного запуска компании.
	 */
	protected static function _afterStart(Struct_Db_PivotCompany_Company $company):Struct_Db_PivotCompany_Company {

		// обновляем данные премиума для компании
		Gateway_Socket_Company::updatePermissions($company);

		console("обновление компании {$company->company_id} завершено");
		Type_System_Admin::log("start_company_process", "Обновление компании {$company->company_id} завершено");

		return $company;
	}
}
