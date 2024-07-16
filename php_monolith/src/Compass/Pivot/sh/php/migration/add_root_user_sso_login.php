<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Server\ServerProvider;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Добавляем значение sso_login root-пользователю
 *
 * скрипт без dry-run, запускается сразу, безопасен для повторного выполнения
 */
class Migration_Add_Root_User_Sso_Login {

	/**
	 * Стартовая функция скрипта
	 */
	public function start():void {

		if (!ServerProvider::isOnPremise()) {

			console("Для запуска только на on-premise окружении");
			return;
		}

		// проверяем привязан ли к root-пользователю аккаунт SSO
		if (Gateway_Socket_Federation::hasUserRelationship(Domain_User_Entity_OnpremiseRoot::getUserId())) {
			throw new Domain_User_Exception_Security_UserWasRegisteredBySso();
		}

		// получаем параметры
		$sso_login = Type_Script_InputParser::getArgumentValue("--sso_login");

		try {

			$is_need_check_sso_login_phone = false;

			// проверяем формат почты
			$sso_login = (new \BaseFrame\System\Mail($sso_login))->mail();
		} catch (InvalidMail) {
			$is_need_check_sso_login_phone = true;
		}

		// проверяем формат номера телефона, если необходимо
		if ($is_need_check_sso_login_phone) {
			$sso_login = (new \BaseFrame\System\PhoneNumber($sso_login))->number();
		}

		Domain_User_Entity_OnpremiseRoot::setSsoLogin($sso_login);
	}
}

try {
	(new Migration_Add_Root_User_Sso_Login())->start();
} catch (Domain_User_Exception_Security_UserWasRegisteredBySso) {

	console(redText("к root-пользователю уже привязан SSO аккаунт, изменение ранее привязанного SSO аккаунта не требуется"));
	exit(1);
} catch (InvalidPhoneNumber) {

	console(redText("Некорректный формат sso_login"));
	exit(2);
} catch (\Exception|Exception|Error) {

	console(redText("Возникла ошибка в выполнении скрипта"));
	exit(3);
}