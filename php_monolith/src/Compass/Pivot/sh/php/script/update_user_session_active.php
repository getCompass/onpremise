<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Дата создания скрипта: 13/12/2024
 * Скрипт может быть переиспользован повторно
 * Скрипт может быть выполнен на saas&onpremise окружении
 *
 * Задача - обновить активные сессии пользователей, добавив данные last_online_at, login_type, server_version
 */
class Update_User_Session_Active {

	protected const _MAX_COUNT = 1000;

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

		do {

			$session_active_list = $this->_getAllSessionActive(self::_MAX_COUNT, $offset);
			$offset              += self::_MAX_COUNT;

			$user_id_list   = array_unique(array_column($session_active_list, "user_id"));
			$user_info_list = Gateway_Bus_PivotCache::getUserListInfo($user_id_list);

			foreach ($session_active_list as $session_active) {

				$user_info = $user_info_list[$session_active->user_id] ?? null;
				is_null($user_info) && $this->_logImportant("Не нашли информацию в pivot_cache по пользователю {$session_active->user_id}");

				$this->_processSession($session_active, $user_info);
			}

			!$this->_is_dry_run && console("скрипт успешно прошёлся по " . count($session_active_list) . " записям");
		} while (count($session_active_list) === self::_MAX_COUNT);

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

		$query = "SELECT * FROM `?p` WHERE `user_id` > ?i ORDER BY created_at DESC LIMIT ?i OFFSET ?i";
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
	 * работаем с активной сессией пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @long
	 */
	protected function _processSession(Struct_Db_PivotUser_SessionActive $session_active, Struct_Db_PivotUser_User|null $user_info):void {

		$extra = $session_active->extra;

		// проверяем, можем информация в активной сессии уже обновлена
		$login_type     = Domain_User_Entity_SessionExtra::getLoginType($extra);
		$last_online_at = $session_active->last_online_at;
		$server_version = Domain_User_Entity_SessionExtra::getServerVersion($extra);

		if ($login_type != 0 && $last_online_at != 0 && mb_strlen($server_version) > 0) {

			$this->_debug("dry-run: Данные активной сессии пользователя {$session_active->user_id} уже обновлены. Пропускаем");
			return;
		}

		// собираем данные для обновления
		$new_last_online_at = is_null($user_info) ? 0 : $user_info->last_active_day_start_at;
		$new_last_online_at = $last_online_at != 0 ? $last_online_at : $new_last_online_at;

		$new_server_version = ServerProvider::isSaas() ? SAAS_VERSION : ONPREMISE_VERSION;
		$updated_extra      = mb_strlen($server_version) > 0 ? $extra : Domain_User_Entity_SessionExtra::setServerVersion($extra, $new_server_version);

		// для saas версии имеется только один вариант типа авторизации
		if (ServerProvider::isSaas()) {

			$updated_extra = $login_type != 0 ? $updated_extra : Domain_User_Entity_SessionExtra::setLoginType(
				$updated_extra, Domain_User_Entity_SessionExtra::SAAS_SMS_LOGIN_TYPE
			);
		}

		if ($extra == $updated_extra) {

			$this->_debug("dry-run: Отсутствуют изменения для обновления для сессии {$session_active->session_uniq} пользователя {$session_active->user_id}");
			return;
		}

		if ($this->_is_dry_run) {

			$this->_debug("dry-run: Обновляю данные сессии {$session_active->session_uniq} пользователя {$session_active->user_id}");
			return;
		}

		$set = [
			"extra"          => $updated_extra,
			"last_online_at" => $new_last_online_at,
			"updated_at"     => time(),
		];

		Gateway_Db_PivotUser_SessionActiveList::set(
			$session_active->user_id, $session_active->session_uniq, $set
		);
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
	 * Это уже 100% должно быть отображено в любом случае
	 */
	protected function _logImportant(string $text):void {

		Type_System_Admin::log("update_user_session_active", $text);
		console(yellowText($text));
	}
}

// запускаем скрипт
(new Update_User_Session_Active())->run();

if (Type_Script_InputHelper::isDry()) {

	console(greenText("====================================="));
	console(greenText("Скрипт был выполнен в режиме dry-run!"));
	console(greenText("====================================="));
}