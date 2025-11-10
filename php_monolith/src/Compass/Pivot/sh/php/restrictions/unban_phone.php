<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Скрипт для разбана номера телефона в Compass
 */
class Restrictions_Unban_Phone {

	/**
	 * стартовая функция скрипта
	 * @long
	 */
	public function run():void {

		if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

			console("Параметры:");
			console("--dry = запуск в тестовом/рабочем режиме");
			console("--phone-number-list = номера телефонов");
			exit(1);
		}

		// параметры
		$is_dry            = Type_Script_InputHelper::isDry();
		$phone_number_list = Type_Script_InputParser::getArgumentValue("--phone-number-list", Type_Script_InputParser::TYPE_ARRAY, []);

		if (count($phone_number_list) < 1) {
			console(redText("Передан пустой --phone-number-list"));
			exit(1);
		}

		// валидируем что нет левака
		foreach ($phone_number_list as $phone_number) {

			try {
				new \BaseFrame\System\PhoneNumber($phone_number);
			} catch (InvalidPhoneNumber) {
				console(redText("Передан некорректный номер телефона {$phone_number} в списке"));
				exit(1);
			}
		}

		$phones_count = count($phone_number_list);

		// dry-run
		if ($is_dry) {

			console("DRY-RUN отработал!!!");
			console("Разбаним {$phones_count} номеров");
			return;
		}

		if (!Type_Script_InputHelper::assertConfirm(blueText("Разбаним {$phones_count} номеров (y/n):"))) {

			console(yellowText("Разбан {$phones_count} номеров отменен"));
			exit(0);
		}

		console(blueText("Разбан подтвержден, ждем 5 секунд и разбаниваем..."));
		sleep(5);

		// разбаниваем номера
		foreach ($phone_number_list as $phone_number) {

			$phone_number_hash = Type_Hash_PhoneNumber::makeHash((new \BaseFrame\System\PhoneNumber($phone_number))->number());
			Gateway_Db_PivotPhone_PhoneBanned::delete($phone_number_hash);
		}

		console("Успешное разбанили {$phones_count} номеров");
	}
}

// запускаем
(new Restrictions_Unban_Phone())->run();