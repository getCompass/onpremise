<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Скрипт для бана номера телефона в Compass
 */
class Restrictions_Ban_Phone {

	/**
	 * стартовая функция скрипта
	 * @long
	 */
	public function run():void {

		if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

			console("Параметры:");
			console("--dry = запуск в тестовом/рабочем режиме");
			console("--phone-number-list = номера телефонов");
			console("--comment = комментарий к бану");
			exit(1);
		}

		// параметры
		$is_dry            = Type_Script_InputHelper::isDry();
		$phone_number_list = Type_Script_InputParser::getArgumentValue("--phone-number-list", Type_Script_InputParser::TYPE_ARRAY, []);
		$comment           = Type_Script_InputParser::getArgumentValue("--comment", Type_Script_InputParser::TYPE_STRING, "", false);

		if (count($phone_number_list) <1) {
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

		if (mb_strlen($comment) > 500) {

			console(redText("Передан слишком длинный комментарий"));
			exit(1);
		}

		$phones_count = count($phone_number_list);

		// dry-run
		if ($is_dry) {

			console("DRY-RUN отработал!!!");
			console("Забаним {$phones_count} номеров, комментарий: {$comment}");
			return;
		}

		if (!Type_Script_InputHelper::assertConfirm(blueText("Баним {$phones_count} номеров (y/n):"))) {

			console(yellowText("Бан {$phones_count} номеров отменен"));
			exit(0);
		}

		console(blueText("Бан подтвержден, ждем 5 секунд и баним..."));
		sleep(5);

		// баним номера
		$logout_count = 0;
		foreach ($phone_number_list as $phone_number) {

			$created_at        = time();
			$phone_number_hash = Type_Hash_PhoneNumber::makeHash((new \BaseFrame\System\PhoneNumber($phone_number))->number());
			$phone_banned      = new Struct_Db_PivotPhone_PhoneBanned($phone_number_hash, $comment, $created_at);
			Gateway_Db_PivotPhone_PhoneBanned::insert($phone_banned);

			try {

				// разлогиниваем если нашли зарегистрированного пользователя
				$phone_number_user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
				self::_doLogoutSessionList($phone_number_user_id);
				self::_deactivateDeviceList($phone_number_user_id);
				$logout_count++;
			} catch (cs_PhoneNumberNotFound) {
				// иначе ничего не делаем
			}
		}

		console("Успешное забанили {$phones_count} номеров, разлогинили из них: {$logout_count}, комментарий: {$comment}");
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
(new Restrictions_Ban_Phone())->run();