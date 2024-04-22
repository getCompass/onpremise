<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Регистрация onpremise сервера
 */
class Domain_Premise_Action_Register {

	/**
	 * Выполняем
	 *
	 * @return string
	 * @throws Domain_Premise_Exception_ServerAlreadyRegistered
	 * @throws Domain_Premise_Exception_ServerCountExceeded
	 * @throws Domain_Premise_Exception_ServerIsNotAvailable
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do():string {

		try {

			$secret_key = Gateway_Premise_License::register(PUBLIC_ENTRYPOINT_PREMISE, SERVER_UID);
			Domain_Config_Entity_Main::set(Domain_Config_Entity_Main::SECRET_KEY, [
				"secret_key" => $secret_key,
			]);

		} catch (Gateway_Premise_Exception_ServerAlreadyRegistered) {
			throw new Domain_Premise_Exception_ServerAlreadyRegistered("server already registered");
		} catch (Gateway_Premise_Exception_ServerIsNotAvailable) {
			throw new Domain_Premise_Exception_ServerIsNotAvailable("server is not available");
		} catch (Gateway_Premise_Exception_ServerCountExceeded) {
			throw new Domain_Premise_Exception_ServerCountExceeded("server count exceeded");
		}

		// отправляем всем ws, что сервер активирован
		Gateway_Bus_SenderBalancer::serverActivated();

		return $secret_key;
	}
}