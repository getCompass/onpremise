<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Дата создания скрипта: 24/01/2025
 * Скрипт может быть переиспользован повторно
 * Скрипт может быть выполнен на saas&onpremise окружении
 *
 * Задача - разлогинить сессии неопознанных устройств пользователей
 */
class Do_Logout_Unknown_Device_User_Session {

	protected const _MAX_COUNT = 1000;

	protected const _SESSION_STATUS_LOGGED_OUT = 3;

	protected bool $_is_dry_run = true;

	/**
	 * Запускаем работу скрипта
	 */
	public function run():void {

		$this->_is_dry_run = Type_Script_InputHelper::isDry();

		$this->_is_dry_run && $this->_debug(yellowText("Скрипт запущен в режиме dry-run"));

		$this->_work();
	}

	/**
	 * Работаем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	protected function _work():void {

		$offset = 0;

		$session_active_list_by_user_id = [];
		do {

			$session_active_list = $this->_getAllSessionActive(self::_MAX_COUNT, $offset);
			$offset              += self::_MAX_COUNT;

			foreach ($session_active_list as $session_active) {

				// пропускаем, если тип устройства не является неопознанным
				if ("unknown" != Domain_User_Entity_SessionExtra::getOutputDeviceType($session_active->extra)) {
					continue;
				}

				// пропускаем, если это вход через сайт онпрема
				if (Domain_User_Entity_SessionExtra::getLoginType($session_active->extra) == Domain_User_Entity_SessionExtra::ONPREMISE_WEB_LOGIN_TYPE) {
					continue;
				}

				$session_active_list_by_user_id[$session_active->user_id][] = $session_active;
			}

			$this->_debug("скрипт успешно прочитал " . count($session_active_list) . " записей");
		} while (count($session_active_list) === self::_MAX_COUNT);

		foreach ($session_active_list_by_user_id as $user_id => $session_list) {
			$this->_processUserSessions($user_id, $session_list);
		}

		if (!$this->_is_dry_run) {

			Gateway_Bus_PivotCache::resetSessionCache();
			console(greenText("Скрипт успешно завершил выполнение"));
		}
	}

	/**
	 * Получаем список активных сессий
	 *
	 * @return Struct_Db_PivotUser_SessionActive[]
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	protected function _getAllSessionActive(int $limit, int $offset):array {

		$shard_key  = "pivot_user_10m";
		$table_name = "session_active_list_1";

		$query = "SELECT * FROM `?p` WHERE `user_id` > ?i ORDER BY created_at ASC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, 0, $limit, $offset);

		$struct_list = [];
		foreach ($list as $row) {

			$struct_list[] = new Struct_Db_PivotUser_SessionActive(
				$row["session_uniq"],
				$row["user_id"],
				$row["created_at"],
				$row["updated_at"],
				$row["login_at"],
				$row["refreshed_at"],
				$row["last_online_at"],
				$row["ua_hash"],
				$row["ip_address"],
				fromJson($row["extra"])
			);
		}

		return $struct_list;
	}

	/**
	 * работаем со списком сессий пользователя
	 *
	 * @param int                                 $user_id
	 * @param Struct_Db_PivotUser_SessionActive[] $session_active_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 */
	protected function _processUserSessions(int $user_id, array $session_active_list):void {

		if ($this->_is_dry_run) {

			$this->_debug("dry-run: Удаляем " . count($session_active_list) . " сессии для пользователя {$user_id}");
			return;
		}

		$user_session_uniq_list_to_delete = self::_addSessionHistory(
			$user_id, $session_active_list, Domain_User_Entity_SessionExtra::LOGOUT_DEVICE_INVALIDATE_REASON
		);

		// удаляем сессии
		Gateway_Db_PivotUser_SessionActiveList::deleteArray($user_id, $user_session_uniq_list_to_delete);

		// оставляем в лог-файле какие uniq сессии были удалены
		$log_text = "Удалили сессии пользователя {$user_id}:\n";
		foreach ($user_session_uniq_list_to_delete as $uniq) {
			$log_text .= "- {$uniq}\n";
		}

		console("Удалили сессии пользователя {$user_id}");
		$this->_logImportant($log_text);
	}

	/**
	 * Добавить активную сессию в таблицу истории
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @long
	 */
	protected static function _addSessionHistory(int $user_id, array $session_list, int $invalidate_reason):array {

		// формируем массивы для удаления и истории
		$user_session_history_list        = [];
		$user_session_uniq_list_to_delete = [];

		foreach ($session_list as $session) {

			$logout_at  = time();
			$shard_year = Gateway_Db_PivotHistoryLogs_Main::getShardIdByTime($logout_at);
			if (!isset($user_session_history_list[$shard_year])) {
				$user_session_history_list[$shard_year] = [];
			}

			$session->extra = Domain_User_Entity_SessionExtra::setInvalidateReason($session->extra, $invalidate_reason);

			$user_session_history_list[$shard_year][] = [
				"session_uniq" => $session->session_uniq,
				"user_id"      => $user_id,
				"status"       => self::_SESSION_STATUS_LOGGED_OUT,
				"login_at"     => $session->login_at,
				"logout_at"    => $logout_at,
				"ua_hash"      => Type_Hash_UserAgent::makeHash(getUa()),
				"ip_address"   => getIp(),
				"extra"        => $session->extra,
			];

			$user_session_uniq_list_to_delete[] = $session->session_uniq;
		}

		// фиксируем событие в истории
		foreach ($user_session_history_list as $shard_id => $history_list) {
			Gateway_Db_PivotHistoryLogs_SessionHistory::insertArray($shard_id, $history_list);
		}

		return $user_session_uniq_list_to_delete;
	}

	/**
	 * Функция для дебага – только если запущен dry
	 * чтобы не захламлять вывод в момент реального запуска
	 */
	protected function _debug(string $text):void {

		// если не dry, то не дебажим
		if (!$this->_is_dry_run) {
			return;
		}

		console($text);
	}

	/**
	 * Сохраняем текст в лог
	 */
	protected function _logImportant(string $text):void {

		Type_System_Admin::log("do_logout_unknown_device_user_session", $text);
	}
}

// запускаем скрипт
(new Do_Logout_Unknown_Device_User_Session())->run();

if (Type_Script_InputHelper::isDry()) {

	console(greenText("====================================="));
	console(greenText("Скрипт был выполнен в режиме dry-run!"));
	console(greenText("====================================="));
}