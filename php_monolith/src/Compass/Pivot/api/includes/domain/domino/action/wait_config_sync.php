<?php

namespace Compass\Pivot;

/**
 * Экшн для ожидания конфига компании
 */
class Domain_Domino_Action_WaitConfigSync {

	// необходимое время ожидания конфига
	protected const _NECESSARY_TIMEOUT = 2;

	/** @var int таймаут ожидания готовности компании */
	protected const _COMPANY_READINESS_TIMEOUT = 10;

	/**
	 * Ждет, пока конфиг не засинкается.
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $domino):void {

		$necessary_timeout = self::_NECESSARY_TIMEOUT;
		if (defined("PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START") && $company->status == Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			$necessary_timeout = PORT_EXPECTED_CONFIG_SYNC_TIMEOUT_ON_COMPANY_START;
		}

		sleep($necessary_timeout);

		// таймаут ожидания начала обслуживания компании
		$awake_timeout_timestamp = time() + self::_COMPANY_READINESS_TIMEOUT;

		// в цикле ожидаем, пока компания подтвердит ожидаемый статус
		$company_status = -1;
		while ($awake_timeout_timestamp > time()) {

			// ждем не дольше ожидаемого таймаута
			$company_status = Gateway_Socket_Company::getCompanyConfigStatus($company, $domino);
			if ($company_status === $company->status) {

				// даем еще капельку времени на синк микросервисов
				// тут вообще отдельный метод нужен, скорее всего,
				// но он сложный для реализации в рамках фичи
				sleep(1);
				return;
			}
		}

		throw new \BaseFrame\Exception\Domain\ReturnFatalException("company {$company->company_id} still ready after timeout, status: {$company_status}");
	}
}