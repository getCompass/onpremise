<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;
use parseException;

/**
 * Обновить secret_key onpremise сервера
 */
class Domain_Premise_Action_UpdateSecretKey
{
	/**
	 * Выполняем действие.
	 * !!! Только для тестового/стейдж сервера
	 *
	 * @throws ParseException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \queryException
	 */
	public static function do(): void
	{

		if (!ServerProvider::isTest() && !ServerProvider::isStage()) {
			throw new \ParseException("called is not test or stage server");
		}

		$secret_key = Gateway_Premise_License::updateServerKey(PUBLIC_ENTRYPOINT_PIVOT, SERVER_UID);

		Domain_Config_Entity_Main::set(Domain_Config_Entity_Main::SECRET_KEY, [
			"secret_key" => $secret_key
		]);
	}
}
