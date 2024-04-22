<?php

namespace Compass\Premise;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для актвиации сервера
 */
class Server_Activate {

	/**
	 * Запускаем работу скрипта
	 */
	public function run():void {

		try {
			Domain_Premise_Action_Register::do();
		} catch (Domain_Premise_Exception_ServerCountExceeded) {

			console(redText("На данном сервере было произведено слишком много установок. Дальнейшая активация серверов на нем невозможна"));
			exit(1);
		} catch (Domain_Premise_Exception_ServerAlreadyRegistered) {

			console(yellowText("Данный сервер уже был активирован"));
			exit(1);
		} catch (Domain_Premise_Exception_ServerIsNotAvailable) {

			console(redText("Сервер недоступен по адресу " . PUBLIC_ENTRYPOINT_PREMISE . ", проверьте доступность, и попробуйте еще раз"));
			exit(1);
		}
	}
}

try {
	(new Server_Activate())->run();
} catch (\Exception $e) {

	console($e->getMessage());
	console($e->getTraceAsString());
	console(redText("Не смогли зарегистировать сервер"));
	exit(1);
}
