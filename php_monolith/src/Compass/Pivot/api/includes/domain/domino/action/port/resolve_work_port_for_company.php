<?php

namespace Compass\Pivot;

/**
 * Действие, которое получает порт для компании.
 * Каждый раз, когда непонятно, на какой порт привязывать компанию, нужно сходить сюда.
 */
class Domain_Domino_Action_Port_ResolveWorkPortForCompany {

	/**
	 * Получает рабочий порт для компании.
	 * Пытается сначала поучить обычный порт, затем резервный.
	 *
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \returnException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompany_Company $company, int $lock_duration):Struct_Db_PivotCompanyService_PortRegistry {

		try {

			// пытаемся получить обычный порт для компании
			$target_port = Domain_Domino_Action_Port_LockForCompany::run(
				$domino, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_COMMON, $lock_duration
			);
		} catch (Domain_Domino_Exception_VoidPortsExhausted) {

			// если обычного порта не оказалось, то испытаем удачу с резервным портом
			$target_port = Domain_Domino_Action_Port_LockForCompany::run(
				$domino, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_RESERVE, $lock_duration
			);
		}

		return $target_port;
	}
}
