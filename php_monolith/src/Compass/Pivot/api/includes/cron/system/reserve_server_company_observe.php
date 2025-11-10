<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;
use BaseFrame\System\File;

/**
 * Крон для выполнения observe компаний для резервных серверов
 */
class Cron_System_ReserveServerCompanyObserve extends \Cron_Default {

	public function __construct(array $config = []) {

		parent::__construct($config);
	}

	/**
	 * Выполняем задачу
	 */
	public function work():void {

		if (mb_strlen(SERVICE_LABEL) < 1 || !ServerProvider::isOnPremise()) {
			return;
		}

		// получаем доминошку
		$domino_id_list = array_column(Gateway_Db_PivotCompanyService_DominoRegistry::getAll(), "domino_id");
		if (count($domino_id_list) < 1) {
			return;
		}
		$domino_id = $domino_id_list[0];

		// инициализируем файл конфига компаний резервных серверов
		$companies_relationship_file = File::init(self::_getPivotConfigPath(), COMPANIES_RELATIONSHIP_FILE);

		// если конфиг отсутствует - создаём
		if (!$companies_relationship_file->isExists()) {

			// пишем пустой файл
			$companies_relationship_file->write("{}");
			$companies_relationship_file->chmod(0600);
		}

		$companies_relationship_config = fromJson($companies_relationship_file->read());

		$port_list = Gateway_Db_PivotCompanyService_PortRegistry::getAllWithCompany($domino_id);

		$active_port_list = [];
		/** @var Struct_Db_PivotCompanyService_PortRegistry $port */
		foreach ($port_list as $port) {

			// если порт не для активной компании - пропускаем
			if ($port->status == Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE) {
				$active_port_list[] = $port;
			}
		}

		// если для текущего сервера отсутствует данные в конфиге
		// или количество компаний меньше количества портов активных компаний
		if (!isset($companies_relationship_config[SERVICE_LABEL])
			|| !isset($companies_relationship_config[SERVICE_LABEL]["company_list"])
			|| count($companies_relationship_config[SERVICE_LABEL]["company_list"]) != count($active_port_list)
		) {

			self::_createCompaniesRelationshipConfig($companies_relationship_file, $domino_id);
			$companies_relationship_config = fromJson($companies_relationship_file->read());
		}

		// запоминаем старую версию конфига для дальнейшего сравнения
		$old_companies_relationship_config = $companies_relationship_config;

		$companies_relationship_config = self::_actualizeReserveCompanies($companies_relationship_config, $domino_id);

		// если изменения отсутствуют
		if ($old_companies_relationship_config == $companies_relationship_config) {
			return;
		}

		$companies_relationship_file->write(toJson($companies_relationship_config));
	}

	/**
	 * получаем путь к конфигам
	 *
	 * @return string
	 */
	protected static function _getPivotConfigPath():string {

		return sprintf("%s", DOMINO_CONFIG_PATH);
	}

	/**
	 * создаём конфиг компаний текущего сервера
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _createCompaniesRelationshipConfig(File $companies_relationship_file, string $domino_id):void {

		// получаем порты компаний
		$port_list = Gateway_Db_PivotCompanyService_PortRegistry::getAllWithCompany($domino_id);

		$companies_relationship_config = fromJson($companies_relationship_file->read());

		// готовим конфиг для текущего сервера
		$config_struct = self::_makeConfig(HOST_IP);

		/** @var Struct_Db_PivotCompanyService_PortRegistry $port */
		foreach ($port_list as $port) {

			// если порт не для активной компании - пропускаем
			if ($port->status != Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE) {
				continue;
			}

			$mysql_host = $port->host !== "" ? $port->host : $domino_id . "-" . $port->port;

			$config_struct->company_list[$port->company_id] = [
				"mysql_host" => $mysql_host,
				"mysql_port" => $port->port,
			];
		}

		$current_server_config_data                   = $companies_relationship_config[SERVICE_LABEL] ?? [];
		$companies_relationship_config[SERVICE_LABEL] = array_merge($current_server_config_data, (array) $config_struct);

		// записываем в конфиг данные списка компаний
		$companies_relationship_file->write(toJson($companies_relationship_config));
	}

	/**
	 * готовим конфиг для связи компаний и резервных серверов
	 */
	protected static function _makeConfig(string $server_host_ip):Struct_Config_Reserve_Main {

		return new Struct_Config_Reserve_Main($server_host_ip, []);
	}

	/**
	 * актуализируем компании резерва
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long
	 */
	protected static function _actualizeReserveCompanies(array $companies_relationship_config, string $domino_id):array {

		// получаем список компаний текущего сервера
		$current_server_company_list = $companies_relationship_config[SERVICE_LABEL]["company_list"];

		foreach ($companies_relationship_config as $service_label => $companies_relationship_data) {

			// если это компании текущего сервера - пропускаем
			if ($service_label == SERVICE_LABEL) {
				continue;
			}

			// если это мастер, то сверяем количество активных компаний
			// проверяем, нужно ли анбиндить порты для компаний
			if (isset($companies_relationship_data["master"]) && $companies_relationship_data["master"] == true
				&& count($current_server_company_list) > count($companies_relationship_data["company_list"])) {

				$company_id_list_for_unbind = array_diff_key($current_server_company_list, $companies_relationship_data["company_list"]);
				self::_unbindReserveCompaniesPorts($company_id_list_for_unbind, $domino_id);
				foreach ($company_id_list_for_unbind as $company_id => $_) {
					unset($current_server_company_list[$company_id]);
				}
			}

			foreach ($companies_relationship_data["company_list"] as $company_id => $mysql_data) {

				// если такая компания имеется в списке - пропускаем
				if (isset($current_server_company_list[$company_id])) {
					continue;
				}

				$port = $mysql_data["mysql_port"];

				// создаём компанию для текущего сервера
				try {
					$mysql_config_data = Domain_System_Action_CreateReserveServerCompany::do($company_id, $domino_id, $port, "");
				} catch (\Exception $e) {
					Type_System_Admin::log("reserve_server_companies_observe", [
						"company_id"    => $company_id,
						"domino_id"     => $domino_id,
						"service_label" => SERVICE_LABEL,
						"error_message" => $e->getMessage(),
					]);
					continue;
				}
				if (is_null($mysql_config_data)) {
					continue;
				}
				$mysql_host = $mysql_config_data[0];
				$mysql_port = $mysql_config_data[1];

				$current_server_company_list[$company_id] = [
					"mysql_host" => $mysql_host,
					"mysql_port" => $mysql_port,
				];
			}
		}

		$companies_relationship_config[SERVICE_LABEL]["company_list"] = $current_server_company_list;

		return $companies_relationship_config;
	}

	/**
	 * анбиндим компанейские порты для резервных серверов
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _unbindReserveCompaniesPorts(array $company_id_list_for_unbind, string $domino_id):void {

		foreach ($company_id_list_for_unbind as $company_id => $_) {

			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
			$company_row         = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

			// запускаем отвязываем компании от порта
			Domain_Domino_Action_Config_UpdateMysql::do($company_row, $domino_registry_row, need_force_update: true);
			Domain_Domino_Action_WaitConfigSync::do($company_row, $domino_registry_row);
			Domain_Domino_Action_StopCompany::run($domino_registry_row, $company_row, "deleteCompany");
		}
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}

	// нужно ли запускать работу крона
	protected static function _isNeedWork():bool {

		return true;
	}
}