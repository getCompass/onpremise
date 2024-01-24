<?php

namespace Compass\Pivot;

/**
 * Выделяет слот под новую пустую компанию.
 * Пустые компании нужны для моментального создания компаний по запросу пользователя.
 */
class Domain_Domino_Action_CreateVacantCompany {

	/** @var int время блокировки порта */
	protected const _PORT_LOCK_DURATION = HOUR1;

	/**
	 * Выделяет новую компанию для быстрого создания.
	 * Такая компания мгновенно создается по запросу пользователя.
	 *
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompanyService_DominoRegistry $domino_row):int {

		// добавляем в базу запись для новой компании
		$company = static::_create($domino_row);

		// блокируем порт для компании
		$port_row = Domain_Domino_Action_Port_LockForCompany::run($domino_row, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_COMMON, static::_PORT_LOCK_DURATION);

		// занимаем порт на доминошке
		Domain_Domino_Action_Port_Bind::run($domino_row, $port_row, $company->company_id, Domain_Domino_Action_Port_Bind::POLICY_CREATING);
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_mysql_alive" => 1]);

		// поднимаем актуальную миграцию
		Gateway_Bus_DatabaseController::migrateUp($domino_row, $company->company_id);

		// добавляем компанию в очередь свободных
		$company->status = Domain_Company_Entity_Company::COMPANY_STATUS_VACANT;
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, ["status" => $company->status]);

		// создаем конфиг для компании

		$port_registry_row = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino_row->domino_id, $port_row->port);
		Domain_Domino_Action_Config_UpdateMysql::do($company, $domino_row, $port_registry_row, true);
		Domain_Domino_Action_WaitConfigSync::do($company, $domino_row);

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_Main::beginTransaction();

		$company_registry       = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getForUpdate($company->company_id);
		$company_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyCreateSuccessLog($company_registry->logs);

		Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company->company_id, [
			"is_vacant"            => 1,
			"became_vacant_at"     => time(),
			"creating_finished_at" => time(),
			"logs"                 => $company_registry->logs,
		]);

		Gateway_Db_PivotCompanyService_Main::commitTransaction();
		/** конец транзакции */

		return $company->company_id;
	}

	/**
	 * Создание записей со свободной компанией.
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_row
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \queryException
	 * @throws \returnException
	 * @long мало действий, записи данных в столбик для читаемости
	 */
	public static function _create(Struct_Db_PivotCompanyService_DominoRegistry $domino_row):Struct_Db_PivotCompany_Company {

		// получаем инкрементальный идентификатор компании
		$company_id = Type_Autoincrement_Pivot::getNextId(Type_Autoincrement_Pivot::COMPANY_ID_KEY);

		$company_registry = new Struct_Db_PivotCompanyService_CompanyInitRegistry(
			$company_id,
			0,
			0,
			0,
			time(),
			0,
			0,
			0,
			0,
			0,
			0,
			time(),
			0,
			0,
			0,
			[],
			[],
		);
		Gateway_Db_PivotCompanyService_CompanyInitRegistry::insert($company_registry);

		// определяем данные для экстры
		$extra = Domain_Company_Entity_Company::initExtra();

		$company = new Struct_Db_PivotCompany_Company(
			$company_id,
			0,
			Domain_Company_Entity_Company::COMPANY_STATUS_CREATING,
			time(),
			0,
			0,
			1,
			0,
			0,
			$domino_row->domino_id,
			"",
			self::_makeCompanyUrl($company_id, $domino_row),
			"",
			$extra
		);

		// вставляем новую запись для компании
		// изначально помечаем ее как неактивную, активируем потом
		Gateway_Db_PivotCompany_CompanyList::insert(
			$company->company_id,
			$company->status,
			$company->created_at,
			$company->updated_at,
			$company->avatar_color_id,
			$company->created_by_user_id,
			$company->domino_id,
			$company->name,
			$company->url,
			$company->extra
		);

		$company_registry = new Struct_Db_PivotCompanyService_CompanyRegistry($company->company_id, 0, 0, 0, time(), 0);
		Gateway_Db_PivotCompanyService_CompanyRegistry::insert($company->domino_id, $company_registry);
		return $company;
	}

	/**
	 * Возвращает строку с адресом,
	 * по которому можно будет достучаться до компании извне.
	 */
	public static function _makeCompanyUrl(int $company_id, Struct_Db_PivotCompanyService_DominoRegistry $domino_row):string {

		$url = Domain_Domino_Entity_Registry_Extra::getUrl($domino_row->extra);
		return "c$company_id-$url";
	}
}
