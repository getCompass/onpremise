<?php

namespace Compass\Pivot;

/**
 * Действие привязки порта домино к компании.
 * !!! Не пересоздает конфигурационный файл для компании, оно лишь связывает порт и компанию.
 */
class Domain_Domino_Action_Port_Bind {

	// политики поведения при отсутствии директорий с данными компании
	protected const _NON_EXISTING_DATA_DIR_POLICY_ALLOW    = 1; // разрешено работать с несуществующими директориями
	protected const _NON_EXISTING_DATA_DIR_POLICY_DISALLOW = 2; // работать без директории нельзя

	// политики поведения при возникновении конфликта имен директорий с данными компании
	protected const _DUPLICATE_DATA_DIR_POLICY_REPLACE = 1; // удалять и замещать директорию
	protected const _DUPLICATE_DATA_DIR_POLICY_COPY    = 2; // создавать копию директории перед замещение
	protected const _DUPLICATE_DATA_DIR_POLICY_IGNORE  = 3; // игнорировать и использовать существующую
	protected const _DUPLICATE_DATA_DIR_POLICY_FORBID  = 4; // запрещать действия при наличии существующей директории

	// политики создания
	public const POLICY_CREATING = [
		"policy"                       => "creating",
		"non_existing_data_dir_policy" => self::_NON_EXISTING_DATA_DIR_POLICY_ALLOW,
		"duplicate_data_dir_policy"    => self::_DUPLICATE_DATA_DIR_POLICY_FORBID,
	];

	// политики для релокации — копирование данных
	public const POLICY_RELOCATE_COPYING = [
		"policy"                       => "relocation_data_copying",
		"non_existing_data_dir_policy" => self::_NON_EXISTING_DATA_DIR_POLICY_DISALLOW,
		"duplicate_data_dir_policy"    => self::_DUPLICATE_DATA_DIR_POLICY_IGNORE,
	];

	// политики для релокации — применение данных
	public const POLICY_RELOCATE_APPLYING = [
		"policy"                       => "relocation_data_applying",
		"non_existing_data_dir_policy" => self::_NON_EXISTING_DATA_DIR_POLICY_ALLOW,
		"duplicate_data_dir_policy"    => self::_DUPLICATE_DATA_DIR_POLICY_COPY,
	];

	// политики для выхода из гибернации
	public const POLICY_WAKING_UP = [
		"policy"                       => "waking_up",
		"non_existing_data_dir_policy" => self::_NON_EXISTING_DATA_DIR_POLICY_DISALLOW,
		"duplicate_data_dir_policy"    => self::_DUPLICATE_DATA_DIR_POLICY_IGNORE,
	];

	// политики для миграций
	public const POLICY_MIGRATING = [
		"policy"                       => "migrating",
		"non_existing_data_dir_policy" => self::_NON_EXISTING_DATA_DIR_POLICY_DISALLOW,
		"duplicate_data_dir_policy"    => self::_DUPLICATE_DATA_DIR_POLICY_IGNORE,
	];

	/**
	 * Выполняет привязку компании к порту на домино.
	 * Возвращает обновленный порт.
	 *
	 * !!! Не пересоздает конфигурационный файл для компании, оно лишь связывает порт и компанию.
	 *
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, int $company_id, array $policy_list):Struct_Db_PivotCompanyService_PortRegistry {

		console("биндю порт {$port->port} для компании {$company_id}");
		$port_to_bound = static::_makeLocalBinding($domino, $port->port, $company_id);
		static::_makeRemoteBinding($domino, $port->port, $company_id, $policy_list);

		return $port_to_bound;
	}

	/**
	 * Выполняет привязку порта и компании на локальном сервере.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \returnException
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 */
	protected static function _makeLocalBinding(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port_value, int $company_id):Struct_Db_PivotCompanyService_PortRegistry {

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_Main::beginTransaction();

		try {

			$port_to_bound = Gateway_Db_PivotCompanyService_PortRegistry::getForUpdate($domino->domino_id, $port_value);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw new \BaseFrame\Exception\Domain\ParseFatalException("passed non-existing port {$port_value}");
		}

		// проверяем, что этот порт можно связать с этой компаний;
		// проверка делается здесь, а не в сценарии как делается обычно, для того
		// чтобы точно не рассыпались какие-то данные, поскольку работа с портами очень критичный аспект логики
		if (!Domain_Domino_Entity_Port_Registry::canBeBound($port_to_bound)) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw new Domain_Domino_Exception_PortBindingIsNotAllowed("port {$port_value} can't be bound because of status {$port_to_bound->status}");
		} elseif (!Domain_Domino_Entity_Port_Registry::canBeBoundWithCompany($port_to_bound, $company_id)) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw new Domain_Domino_Exception_PortBindingIsNotAllowed("port {$port_value} can't be bound, company {$port_to_bound->company_id} already bound");
		}

		try {

			// обновляем данные для порта
			$port_to_bound = static::_update($port_to_bound, $company_id, $domino->domino_id);
		} catch (\Exception $e) {

			Gateway_Db_PivotCompanyService_Main::rollback();

			// если что-то пошло не так, то нужно вызвать инвалидацию порта
			Domain_Domino_Action_Port_Invalidate::run($domino, $port_value, "error on bind update");
			throw $e;
		}

		Gateway_Db_PivotCompanyService_Main::commitTransaction();
		/** конец транзакции */

		// изменяем количество портов нужного типа на домино
		Domain_Domino_Action_DoActivePortCountDelta::doPortCountDelta(1, $domino->domino_id, $port_to_bound->type);

		return $port_to_bound;
	}

	/**
	 * Выполняет привязку порта и компании на удаленном сервере.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	protected static function _makeRemoteBinding(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port_value, int $company_id, array $policy_list):void {

		try {

			// выполняем привязку порта на домино и сращу накатываем миграции
			Gateway_Bus_DatabaseController::bindPort($domino, $port_value, $company_id, $policy_list);
		} catch (\Exception $e) {

			// если что-то пошло не так, то нужно вызвать инвалидацию порта
			Domain_Domino_Action_Port_Invalidate::run($domino, $port_value, "error on remote bind");
			throw $e;
		}
	}

	/**
	 * Обновляет данные порта.
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _update(Struct_Db_PivotCompanyService_PortRegistry $port, int $company_id, string $domino_id):Struct_Db_PivotCompanyService_PortRegistry {

		// обновляем данные для порта
		$port->status     = Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE;
		$port->company_id = $company_id;
		$port->updated_at = time();

		Gateway_Db_PivotCompanyService_PortRegistry::set($domino_id, $port->port, [
			"status"     => $port->status,
			"company_id" => $port->company_id,
			"updated_at" => $port->updated_at,
		]);

		// логируем изменение статуса порта
		Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
			"status"     => $port->status,
			"port"       => $port->port,
			"company_id" => $port->company_id,
			"action"     => __CLASS__,
		]);

		return $port;
	}
}
