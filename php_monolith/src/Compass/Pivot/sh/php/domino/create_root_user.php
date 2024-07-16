<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use TheSeer\Tokenizer\Exception;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для создания root пользователя
 */
class Domino_CreateRootUser {

	protected string $_phone_number;
	protected string $_full_name;
	protected string $_mail;
	protected string $_password;
	protected string $_sso_login;

	/**
	 * Запускаем работу скрипта
	 * @long
	 */
	public function run(string $full_name, string $phone_number, string $mail, string $password, string $sso_login):void {

		// если здесь будет true, то в результате функции ничего не выполнится, самое то для dry-run режима
		$is_dry_run = Type_Script_InputHelper::isDry();

		// если мы пытаемся создать root пользователя - проверяем, что такого не существует
		$root_user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

		if ($root_user_id !== -1) {

			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($root_user_id);
			$message       = "Root пользователь уже существует, id {$root_user_id}, ";
			$message       .= $user_security->phone_number != "" ? "номер телефона {$user_security->phone_number}, " : "";
			$message       .= $user_security->mail != "" ? "почта {$user_security->mail}, " : "";
			console(yellowText($message));
			exit(0);
		}

		// проверяем, что такой пользователь уже не был зарегистрирован
		$found_user_id = $this->_findUser($phone_number, $mail);
		if ($found_user_id === 0) {

			// создаём нового пользователя
			$this->_createUser($full_name, $phone_number, $mail, $password, $sso_login, $is_dry_run);
			exit(0);
		}

		// отдаем существующего пользователя
		$found_user_info = Gateway_Bus_PivotCache::getUserInfo($found_user_id);
		$found_full_name = $found_user_info->full_name;
		$found_user_id   = $found_user_info->user_id;

		if ($phone_number !== "" && $mail !== "") {

			console(redText("По номеру телефона {$phone_number} и почте {$mail} уже зарегистрирован пользователь с именем {$found_full_name}, user_id {$found_user_id}"));
			exit(0);
		}

		if ($phone_number === "" && $mail !== "") {

			console(redText("По почте {$mail} уже зарегистрирован пользователь с именем {$found_full_name}, user_id {$found_user_id}"));
			exit(0);
		}

		console(redText("По номеру телефона {$phone_number} уже зарегистрирован пользователь с именем {$found_full_name}, user_id {$found_user_id}"));
		exit(0);
	}

	/**
	 * пробуем получить пользователя, если тот существует
	 */
	protected function _findUser(string $phone_number, string $mail):int {

		if ($phone_number !== "") {

			try {
				return Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
			} catch (cs_PhoneNumberNotFound) {
			}
		}

		if ($mail !== "") {

			try {
				return Domain_User_Entity_Mail::get($mail)->user_id;
			} catch (Domain_User_Exception_Mail_NotFound) {
			}
		}

		return 0;
	}

	/**
	 * создаём пользователя
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_DamagedActionException
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @long
	 */
	protected function _createUser(string $full_name, string $phone_number, string $mail, string $password, string $sso_login, bool $is_dry_run):void {

		if ($is_dry_run) {

			$info_message = "Хотим зарегистрировать пользователя с именем {$full_name}";
			$info_message .= $mail != "" ? ", почтой {$mail}" : "";
			$info_message .= $phone_number != "" ? " и номером телефона {$phone_number}" : "";
			$info_message .= $sso_login != "" ? " и SSO логином  {$sso_login}" : "";
			console($info_message);
			exit(0);
		}

		// создаём нового пользователя
		$user = Domain_User_Action_Create_Human::do(
			$phone_number,
			$mail,
			$password != "" ? Domain_User_Entity_Password::makeHash($password) : "",
			\BaseFrame\System\UserAgent::getUserAgent(),
			getIp(),
			$full_name,
			"",
			[],
			is_root: 1
		);
		Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

		Domain_User_Entity_OnpremiseRoot::setUserId($user->user_id);

		if ($sso_login !== "") {
			Domain_User_Entity_OnpremiseRoot::setSsoLogin($sso_login);
		}

		$success_message = "Успешно создали пользователя с именем {$full_name}, ";
		$success_message .= $phone_number != "" ? "номером телефона {$phone_number}, " : "";
		$success_message .= $mail != "" ? "почтой {$mail}, " : "";
		$success_message .= $sso_login != "" ? "и SSO логином  {$sso_login} " : "";
		$success_message .= "user_id {$user->user_id}";
		console($success_message);
	}

	/**
	 * Принимаем параметры
	 *
	 * @return void
	 * @throws \paramException
	 * @long
	 */
	public function start():void {

		// если прислали аргумент --help
		if (Type_Script_InputHelper::needShowUsage()) {

			console("Данный скрипт создает первого пользователя админа на переданном окружении ");
			console("Запустите скрипт без флага --help, чтобы начать");
			console("Скрипт поддерживает флаг --dry-run – в таком случае работа скрипта не проделает никаких write-операций");

			exit(0);
		}

		$input_full_name    = Type_Script_InputParser::getArgumentValue("--full-name", default: "", required: false);
		$input_phone_number = Type_Script_InputParser::getArgumentValue("--phone-number", default: "", required: false);
		$input_mail         = Type_Script_InputParser::getArgumentValue("--mail", default: "", required: false);
		$input_password     = Type_Script_InputParser::getArgumentValue("--password", default: "", required: false);
		$input_sso_login    = Type_Script_InputParser::getArgumentValue("--sso_login", default: "", required: false);

		// проверяем, что передали корректные параметры:
		try {

			if (!isset($this->_full_name)) {

				$full_name = $input_full_name !== ""
					? $input_full_name
					: readline("Введите имя создаваемого пользователя (Например: Ivan Ivanov): ");

				// проверяем имя пользователя
				$full_name = Domain_User_Entity_Sanitizer::sanitizeProfileName($full_name);
				Domain_User_Entity_Validator::assertValidProfileName($full_name);

				$this->_full_name = $full_name;
			}
		} catch (\cs_InvalidProfileName) {

			console(redText("Передано некорректное имя пользователя"));
			$input_full_name ? exit(1) : $this->start();
			return;
		}

		try {

			if (!isset($this->_phone_number)) {

				// проверяем номер телефона
				if ($input_phone_number !== "") {
					$input_phone_number = (new \BaseFrame\System\PhoneNumber($input_phone_number))->number();
				}

				$this->_phone_number = $input_phone_number;
			}
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

			console(redText("Передан некорректный номер телефона"));
			$input_phone_number ? exit(1) : $this->start();
			$this->start();
			return;
		}

		try {

			if (!isset($this->_mail)) {

				// проверяем почту пользователя
				if ($input_mail !== "") {
					$input_mail = (new \BaseFrame\System\Mail($input_mail))->mail();
				}

				$this->_mail = $input_mail;
			}
		} catch (InvalidMail) {

			console(redText("Передана некорректная почта"));
			$input_mail ? exit(1) : $this->start();
			$this->start();
			return;
		}

		try {

			if (!isset($this->_password)) {

				// проверяем пароль для почты
				if ($input_password !== "") {
					Domain_User_Entity_Password::throwIfIncorrect($input_password);
				}

				$this->_password = $input_password;
			}
		} catch (Domain_User_Exception_Password_Incorrect) {

			console(redText("Передан некорректный пароль для почты"));
			$input_password ? exit(1) : $this->start();
			$this->start();
			return;
		}

		try {

			if (!isset($this->_mail)) {

				// проверяем почту пользователя
				if ($input_mail !== "") {
					$input_mail = (new \BaseFrame\System\Mail($input_mail))->mail();
				}

				$this->_mail = $input_mail;
			}
		} catch (InvalidMail) {

			console(redText("Передана некорректная почта"));
			$input_mail ? exit(1) : $this->start();
			$this->start();
			return;
		}

		try {

			if (!isset($this->_sso_login)) {

				try {

					$is_need_check_sso_login_phone = false;

					// проверяем номер телефона
					if ($input_sso_login !== "") {
						$input_sso_login = (new \BaseFrame\System\Mail($input_sso_login))->mail();
					}
				} catch (InvalidMail) {
					$is_need_check_sso_login_phone = true;
				}

				// проверяем номер телефона
				if ($input_sso_login !== "" && $is_need_check_sso_login_phone) {
					$input_sso_login = (new \BaseFrame\System\PhoneNumber($input_sso_login))->number();
				}

				$this->_sso_login = $input_sso_login;
			}
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

			console(redText("Передан некорректный логин от SSO"));
			$input_sso_login ? exit(1) : $this->start();
			$this->start();
			return;
		}

		if ($this->_phone_number == "" && $this->_mail == "" && $this->_sso_login == "") {

			console(redText("Номер телефона или почта, или sso должны быть заполнены для пользователя"));
			exit(1);
		}

		if ($this->_mail != "" && $this->_password == "") {

			console(redText("Для почты необходимо также указать пароль для подтверждения"));
			exit(1);
		}

		if ($this->_mail == "" && $this->_password != "") {
			$this->_password = "";
		}

		// запускаем скрипт
		$this->run($this->_full_name, $this->_phone_number, $this->_mail, $this->_password, $this->_sso_login);
	}
}

try {
	(new Domino_CreateRootUser())->start();
} catch (Exception) {

	console(redText("Не смогли создать root пользователя"));
	exit(1);
}


