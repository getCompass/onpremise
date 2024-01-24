<?php

namespace Compass\Announcement;

// ---------------------------------------------
// РАБОЧИЕ КЛАССЫ
// ---------------------------------------------

/**
 * Команды каждую минуту
 */
class Cron_General_MinuteHandler {

	public static function work():void {

		console("-- [minuteHandler] start --");

		// очищаем таблицы
		self::_clearExpiredCompanyUser();
		self::_clearExpiredUserCompany();
		self::_clearExpiredToken();
	}

	// удаляем неактуальные связи компания-пользователь
	protected static function _clearExpiredCompanyUser():void {

		// получаем все таблицы с шардами
		$table_shard_name_list = Gateway_Db_AnnouncementCompany_CompanyUser::getTableShards();
		$limit                 = 1000;
		$expires_at            = time();
		foreach ($table_shard_name_list as $table_shard_name) {

			do {
				$deleted_count = Gateway_Db_AnnouncementCompany_CompanyUser::deleteExpired($table_shard_name, $expires_at, $limit);
			} while ($deleted_count == $limit);
		}
	}

	// удаляем неактуальные связи пользователь-компания
	protected static function _clearExpiredUserCompany():void {

		// получаем все таблицы с шардами
		$table_shard_name_list = Gateway_Db_AnnouncementUser_UserCompany::getTableShards();
		$limit                 = 1000;
		$expires_at            = time();
		foreach ($table_shard_name_list as $table_shard_name) {

			do {
				$deleted_count = Gateway_Db_AnnouncementUser_UserCompany::deleteExpired($table_shard_name, $expires_at, $limit);
			} while ($deleted_count == $limit);
		}
	}

	// удаляем истекшие токены
	protected static function _clearExpiredToken():void {

		// получаем все таблицы с шардами
		$table_shard_name_list = Gateway_Db_AnnouncementSecurity_TokenUser::getTableShards();
		$limit                 = 1000;
		$expires_at            = time();
		foreach ($table_shard_name_list as $table_shard_name) {

			do {
				$deleted_count = Gateway_Db_AnnouncementSecurity_TokenUser::deleteExpired($table_shard_name, $expires_at, $limit);
			} while ($deleted_count == $limit);
		}
	}
}

/**
 * Команды каждые 5 минут
 */
class Cron_General_Minute5Handler {

	public static function work():void {

		console("-- [minute5Handler] start --");
	}

}

/**
 * Команды каждые 15 минут
 */
class Cron_General_Minute15Handler {

	public static function work():void {

		console("-- [minute15Handler] start --");
	}
}

/**
 * Команды каждые 30 минут
 */
class Cron_General_Minute30Handler {

	public static function work():void {

		console("-- [minute30Handler] start --");
	}
}

/**
 * Команды каждый час
 */
class Cron_General_HourHandler {

	public static function work():void {

		console("-- [hourHandler] start --");
	}

}

/**
 * Команды каждый день
 */
class Cron_General_DayHandler {

	public static function work():void {

		console("-- [DayHandler] start --");
	}
}

/**
 * Команды каждый день в 3 часа ночи
 */
class Cron_General_DayAt3HoursHandler {

	/**
	 * run at 3 am in day worker
	 */
	public static function work():void {

		console("-- [3AmInDayHandler] start --");

		// оптимизируем таблицы пользователей и компаний
		// таблицы самих анонсов не оптимизируются таким образом
		Domain_System_Action_OptimizeTable::run();
	}
}

# ==============================================================================
# SYSTEM MODULE
# ==============================================================================

/**
 * Крон для выполнения команд через определенное время
 */
class Cron_General extends \Cron_Default {

	protected string $bot_name     = "general";
	protected int    $memory_limit = 50;

	function __construct() {

		global $argv;

		if (isset($argv[1]) && $argv[1] == "clear") {

			console("Begin datastore clearing");

			Type_System_Datastore::set($this->_getKey("1min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("5min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("15min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("30min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("hour"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("day"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("day_3hours"), ["need_work" => 0]);

			console("Datastore cleared");
			die();
		}

		parent::__construct();
	}

	public function work():void {

		console("BEGIN WORK ...");

		// каждую 1 минуту
		$this->_doMinute1Work();

		// каждые 5 минут
		$this->_doMinute5Work();

		// каждые 15 минут
		$this->_doMinute15Work();

		// каждые пол часа
		$this->_doMinute30Work();

		// каждый час
		$this->_doHour1Work();

		// каждую полночь
		$this->_doDay1Work();

		// каждый день в 3 часа
		$this->_doDay1At3HoursWork();

		$sleep = random_int(10, 30);
		console("END WORK ... sleep {$sleep} sec");
		$this->sleep($sleep);
	}

	// выполняем ежеминутные команды и обновляем need_work в базе
	protected function _doMinute1Work():void {

		$key  = $this->_getKey("1min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			Type_System_Datastore::set($key, ["need_work" => time() + 60]);
			Cron_General_MinuteHandler::work();
		}
	}

	// выполняем команды каждые 5 минут и обновляем need_work в базе
	protected function _doMinute5Work():void {

		$key  = $this->_getKey("5min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 5]);
			Cron_General_Minute5Handler::work();
		}
	}

	// выполняем команды каждые 15 минут и обновляем need_work в базе
	protected function _doMinute15Work():void {

		$key  = $this->_getKey("15min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 15]);
			Cron_General_Minute15Handler::work();
		}
	}

	// выполняем команды каждые 30 минут и обновляем need_work в базе
	protected function _doMinute30Work():void {

		$key  = $this->_getKey("30min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 30]);
			Cron_General_Minute30Handler::work();
		}
	}

	// выполняем команды каждый час и обновляем need_work в базе
	protected function _doHour1Work():void {

		$key  = $this->_getKey("hour");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			// выравнивание более мелкие работы если надо
			Type_System_Datastore::set("5min", ["need_work" => hourStart() + 60 * 5]);
			Type_System_Datastore::set("15min", ["need_work" => hourStart() + 60 * 15]);
			Type_System_Datastore::set("30min", ["need_work" => hourStart() + 60 * 30]);

			// в следующий час
			Type_System_Datastore::set($key, ["need_work" => hourStart() + 60 * 60]);

			// выполняем команды
			Cron_General_HourHandler::work();
		}
	}

	// выполняем команды каждый день и обновляем need_work в базе
	protected function _doDay1Work():void {

		$key  = $this->_getKey("day");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {
			Type_System_Datastore::set($key, ["need_work" => dayStart() + 60 * 60 * 24]);
			Cron_General_DayHandler::work();
		}
	}

	// выполняем команды каждый день в 3 часа и обновляем need_work в базе
	protected function _doDay1At3HoursWork():void {

		$key  = $this->_getKey("day_3hours");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			$need_work = (new \DateTime())->modify("next day 03:00")->getTimestamp();
			Type_System_Datastore::set($key, ["need_work" => $need_work]);
			Cron_General_DayAt3HoursHandler::work();
		}
	}

	// формируем первичный ключ для запроса в базу
	protected function _getKey(string $key):string {

		return CODE_UNIQ_VERSION . "_" . $key;
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "announcement_" . parent::_resolveBotName();
	}
}
