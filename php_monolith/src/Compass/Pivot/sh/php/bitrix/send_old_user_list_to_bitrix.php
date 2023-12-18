<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для отправки информации о пользователях, которые были зарегистрированы в приложении
 * до появления фичи с интеграцией Битрикс
 */
class Bitrix_SendOldUserListToBitrix {

	/**
	 * Запускаем работу скрипта
	 */
	public static function run(string $stage_id, int $users_registered_from_date, int|null $users_registered_until_date):void {

		// если здесь будет true, то в результате функции ничего не заафектится, самое то для dry-run режима
		$is_dry_run = isDryRun();

		// получаем список пользователей
		$user_list = self::_getUserListByRange($users_registered_from_date, $users_registered_until_date);

		$count = count($user_list);
		if ($count < 1) {

			console("Пользователи за переданный промежуток не найдены");
			exit(0);
		}

		console("Начинаем работу с пользователями. Всего пользователей – $count ");

		// нас интересуют только user_id
		$user_id_list = array_column($user_list, "user_id");

		// пробегаемся по каждому пользователю и заводим задачу на отправку информации о нем в Битрикс
		foreach ($user_id_list as $user_id) {

			// заводим задачу только если это НЕ режим --dry-run
			!$is_dry_run && Type_Phphooker_Main::sendBitrixOnUserRegistered($user_id, $stage_id);

			console("Отправили информацию об одном из пользователей");
		}

		console("Закончили");
	}

	/**
	 * Получаем список зарегистрированных пользователей за промежуток времени
	 *
	 * @return Struct_Db_PivotUser_User[]
	 */
	protected static function _getUserListByRange(int $users_registered_from_date, int|null $users_registered_until_date):array {

		// если нет верхней границы range, то воспользуемся текущим временем
		if (is_null($users_registered_until_date)) {
			$users_registered_until_date = time();
		}

		return Gateway_Db_PivotUser_UserList::getAllByInterval($users_registered_from_date, $users_registered_until_date);
	}
}

// если прислали аргумент --help
if (Type_Script_InputHelper::needShowUsage()) {

	console("Данный скрипт отправит информацию о пользователях, которые были зарегистрированы в приложении начиная с желаемой даты");
	console("Чтобы передать желаемую дату введите флаги --registered-from (параметр обязательный, формат: 31.05.2023) и --registered-until (параметр НЕ обязательный, формат: 31.05.2023)");
	console("Скрипт выберет всех пользователей, зарегистрированных в этом диапазоне и отправит информацию по ним в Битрикс");
	console("Запустите скрипт без флага --help, чтобы начать");
	console("Скрипт поддерживает флаг --dry-run – в таком случае работа скрипта не проделает никаких write-операций");

	exit(0);
}

$registered_from  = Type_Script_InputParser::getArgumentValue("registered-from", Type_Script_InputParser::TYPE_STRING, false, true);
$registered_until = Type_Script_InputParser::getArgumentValue("registered-until", Type_Script_InputParser::TYPE_STRING, false, false);

/**
 * Здесь сделано через readline, потому что Type_Script_InputParser::getArgumentValue обрезает значение в строке флага если оно содержит символ ":",
 * который характерен ID стадий
 */
$stage_id = readline("Введите ID стадии, в которой создадутся сделки: ");

// проверяем, что передали корректные параметры:
if (mb_strlen($stage_id) < 1) {

	console(redText("Передано пустое значение ID стадии"));
	exit(1);
}

$registered_from_at = strtotime($registered_from);
if (!$registered_from_at) {

	console(redText("Передано некорректное значение в параметре --registered-from, формат: --registered-from=31.05.2023"));
	exit(1);
}

// берем начало дня, хотя возможно оно изначально уже такое
$registered_from_at = dayStart($registered_from_at);

// по умолчанию null
$registered_until_at = null;

// если прислали флаг registered_until
if ($registered_until !== false) {

	$registered_until_at = strtotime($registered_until);
	if (!$registered_until_at) {

		console(redText("Передано некорректное значение в параметре --registered-until, формат: --registered-until=31.05.2023"));
		exit(1);
	}

	// преобразуем таким образом на случай если пришлют одинаковые registered-from & registered-until
	$registered_until_at = dayEnd($registered_until_at);

	// если until <= from
	if ($registered_until_at <= $registered_from_at) {

		console(redText("Значение параметра registered-until <= значения параметра registered-from"));
		exit(1);
	}
}

// запускаем основной скрипт
Bitrix_SendOldUserListToBitrix::run($stage_id, $registered_from_at, $registered_until_at);