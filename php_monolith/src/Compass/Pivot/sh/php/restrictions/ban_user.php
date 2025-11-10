<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Скрипт для бана пользователя в Compass
 */
class Restrictions_Ban_User {

	/**
	 * стартовая функция скрипта
	 * @long
	 */
	public function run():void {

		if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

			console("Параметры:");
			console("--dry = запуск в тестовом/рабочем режиме");
			console("--user-id-list = id пользователей");
			console("--comment = комментарий к бану");
			exit(1);
		}

		// параметры
		$is_dry       = Type_Script_InputHelper::isDry();
		$user_id_list = Type_Script_InputParser::getArgumentValue("--user-id-list", Type_Script_InputParser::TYPE_ARRAY, []);
		$comment      = Type_Script_InputParser::getArgumentValue("--comment", Type_Script_InputParser::TYPE_STRING, "", false);

		if (count($user_id_list) < 1) {
			console(redText("Передан пустой --user-id-list"));
			exit(1);
		}

		// валидируем что нет левака
		if (in_array(0, $user_id_list)) {

			console(redText("Передан некорректный user_id в списке"));
			exit(1);
		}

		if (mb_strlen($comment) > 500) {

			console(redText("Передан слишком длинный комментарий"));
			exit(1);
		}

		$users_count = count($user_id_list);

		// dry-run
		if ($is_dry) {

			console("DRY-RUN отработал!!!");
			console("Забаним {$users_count} пользователей, комментарий: {$comment}");
			return;
		}

		if (!Type_Script_InputHelper::assertConfirm(blueText("Баним {$users_count} пользователей (y/n):"))) {

			console(yellowText("Бан {$users_count} пользователей отменен"));
			exit(0);
		}

		console(blueText("Бан подтвержден, ждем 5 секунд и баним..."));
		sleep(5);

		// баним пользователей
		foreach ($user_id_list as $user_id) {

			$created_at  = time();
			$user_banned = new Struct_Db_PivotUser_UserBanned($user_id, $comment, $created_at);
			Gateway_Db_PivotUser_UserBanned::insert($user_banned);

			// разлогиниваем
			self::_doLogoutSessionList($user_id);
			self::_deactivateDeviceList($user_id);
		}

		console("Успешное забанили и разлогинили {$users_count} пользователей, комментарий: {$comment}");
	}

	protected static function _doLogoutSessionList(int $user_id):void {

		// удаляем сессию из таблицы активных
		$user_active_session_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);
		foreach ($user_active_session_list as $item) {

			$pivot_session_uniq = $item->session_uniq;
			Gateway_Db_PivotHistoryLogs_SessionHistory::insert(
				$user_id,
				$pivot_session_uniq,
				3,
				$item->login_at,
				time(),
				$item->ua_hash,
				$item->ip_address,
				$item->extra
			);
			Gateway_Db_PivotUser_SessionActiveList::delete($user_id, $pivot_session_uniq);
			Type_User_ActionAnalytics::sessionEnd($user_id);

			Gateway_Bus_PivotCache::clearSessionCacheBySessionUniq($pivot_session_uniq);

			// отправляем задачу на разлогин
			Type_Phphooker_Main::onUserLogout($user_id, [$pivot_session_uniq]);
		}
	}

	protected static function _deactivateDeviceList(int $user_id):void {

		Type_User_Notifications::deleteDevicesForUser($user_id);
	}
}

// запускаем
(new Restrictions_Ban_User())->run();