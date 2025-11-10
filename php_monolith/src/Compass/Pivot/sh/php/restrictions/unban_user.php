<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Скрипт для разбана пользователя в Compass
 */
class Restrictions_Unban_User {

	/**
	 * стартовая функция скрипта
	 * @long
	 */
	public function run():void {

		if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

			console("Параметры:");
			console("--dry = запуск в тестовом/рабочем режиме");
			console("--user-id-list = id пользователей");
			exit(1);
		}

		// параметры
		$is_dry       = Type_Script_InputHelper::isDry();
		$user_id_list = Type_Script_InputParser::getArgumentValue("--user-id-list", Type_Script_InputParser::TYPE_ARRAY, []);

		if (count($user_id_list) < 1) {
			console(redText("Передан пустой --user-id-list"));
			exit(1);
		}

		// валидируем что нет левака
		if (in_array(0, $user_id_list)) {

			console(redText("Передан некорректный user_id в списке"));
			exit(1);
		}

		$users_count = count($user_id_list);

		// dry-run
		if ($is_dry) {

			console("DRY-RUN отработал!!!");
			console("Разбаним {$users_count} пользователей");
			return;
		}

		if (!Type_Script_InputHelper::assertConfirm(blueText("Разбаним {$users_count} пользователей (y/n):"))) {

			console(yellowText("Разбан {$users_count} пользователей отменен"));
			exit(0);
		}

		console(blueText("Разбан подтвержден, ждем 5 секунд и разбаниваем..."));
		sleep(5);

		// разбаниваем пользователей
		foreach ($user_id_list as $user_id) {
			Gateway_Db_PivotUser_UserBanned::delete($user_id);
		}

		console("Успешное разбанили {$users_count} пользователей");
	}
}

// запускаем
(new Restrictions_Unban_User())->run();