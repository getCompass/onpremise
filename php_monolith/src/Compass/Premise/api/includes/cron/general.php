<?php

namespace Compass\Premise;

// ---------------------------------------------
// РАБОЧИЕ КЛАССЫ
// ---------------------------------------------

/**
 * команды каждую минуту
 */
class Cron_General_MinuteHandler {

	/**
	 * run 1 min worker
	 */
	public static function work():void {

		console("-- [minuteHandler] start --");
	}
}

/**
 * команды каждые 5 минут
 */
class Cron_General_Minute5Handler {

	/**
	 * run 5 min worker
	 */
	public static function work():void {

		console("-- [minute5Handler] start --");
	}
}

/**
 * команды каждые 15 минут
 */
class Cron_General_Minute15Handler {

	/**
	 * run 15 min worker
	 */
	public static function work():void {

		console("-- [minute15Handler] start --");
	}
}

/**
 * команды каждые 30 минут
 */
class Cron_General_Minute30Handler {

	/**
	 * run 30 min worker
	 */
	public static function work():void {

		console("-- [minute30Handler] start --");
	}
}

/**
 * команды каждый час
 */
class Cron_General_HourHandler {

	/**
	 * run hour worker
	 */
	public static function work():void {

		console("-- [hourHandler] start --");
	}
}

/**
 * команды каждый день
 */
class Cron_General_DayHandler {

	/**
	 * run day worker
	 */
	public static function work():void {

		console("-- [DayHandler] start --");
	}
}

# ==============================================================================
# SYSTEM MODULE
# ==============================================================================

/**
 * крон для выполнения команд через определенное время
 */
class Cron_General extends \Cron_Default {

	protected string $bot_name     = "general";
	protected int    $memory_limit = 50;

	/**
	 * Cron_General constructor.
	 */
	function __construct() {

		global $argv;
		if (isset($argv[1]) && $argv[1] == "clear") {

			console("Datastore clear");
			Type_System_Datastore::set($this->_getKey("1min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("5min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("15min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("30min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("hour"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("day"), ["need_work" => 0]);
			console("Datastore cleared");
			die();
		}

		parent::__construct();
	}

	/**
	 * run work
	 *
	 * @throws \Exception
	 */
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

	// выполяем команды каждый день и обновляем need_work в базе
	protected function _doDay1Work():void {

		$key  = $this->_getKey("day");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => dayStart() + 60 * 60 * 24]);
			Cron_General_DayHandler::work();
		}
	}

	// формируем первичный ключ для запроса в базу
	protected function _getKey(string $key):string {

		return CODE_UNIQ_VERSION . "_" . $key;
	}
}
