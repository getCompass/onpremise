<?php

namespace Compass\Pivot;

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

	/**
	 * Запускаем работу скрипта
	 */
	public function run(string $full_name, string $phone_number):void {

		// если здесь будет true, то в результате функции ничего не выполнится, самое то для dry-run режима
		$is_dry_run = isDryRun();

		// если мы пытаемся создать root пользователя - проверяем, что такого не существует

		$root_user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

		if ($root_user_id !== -1) {

			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($root_user_id);
			console(yellowText("Root пользователь уже существует, id {$root_user_id}, телефон $user_security->phone_number"));
			exit(0);
		}

		try {

			// проверяем что такой пользователь уже не был зарегистрирован
			$found_user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
		} catch (cs_PhoneNumberNotFound) {

			if (!$is_dry_run) {
				console("Хотим зарегистрировать пользователя с именем {$full_name} и номером телефона {$phone_number}");
				exit(0);
			}

			// создаем нового пользователя
			$user = Domain_User_Action_Create_Human::do($phone_number, \BaseFrame\System\UserAgent::getUserAgent(), getIp(), $full_name, "", [], 0, default_partner_id: 0);
			Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

			$user_id = $user->user_id;

			Domain_User_Entity_OnpremiseRoot::setUserId($user_id);

			console("Успешно создали пользователя с именем {$full_name}, номером телефона {$phone_number}, user_id {$user_id}");
			exit(0);
		}

		// отдаем существующего пользователя
		$found_user_info = Gateway_Bus_PivotCache::getUserInfo($found_user_id);
		$found_full_name = $found_user_info->full_name;
		$found_user_id   = $found_user_info->user_id;

		console(redText("По номеру телефона {$phone_number} уже зарегистрирован пользователь с именем {$found_full_name}, user_id {$found_user_id}"));
		exit(0);
	}

	/**
	 * Принимаем параметры
	 *
	 * @return void
	 * @throws \paramException
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

				$phone_number = $input_phone_number !== ""
					? $input_phone_number
					: readline("Введите номер телефона (В международном формате, например: +7999999999): ");

				// проверяем номер телефона
				new \BaseFrame\System\PhoneNumber($phone_number);

				$this->_phone_number = $phone_number;
			}
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

			console(redText("Передан некорректный номер телефона"));
			$input_phone_number ? exit(1) : $this->start();
			$this->start();
			return;
		}

		// запускаем скрипт
		$this->run($this->_full_name, $this->_phone_number);
	}
}

try {
	(new Domino_CreateRootUser())->start();
} catch (Exception) {

	console(redText("Не смогли создать root пользователя"));
	exit(1);
}


