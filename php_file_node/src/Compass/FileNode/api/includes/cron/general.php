<?php

namespace Compass\FileNode;

// ---------------------------------------------
// РАБОЧИЕ КЛАССЫ
// ---------------------------------------------
use BaseFrame\Server\ServerProvider;
use Generator;
use JetBrains\PhpStorm\ArrayShape;

/**
 * команды каждую минуту
 */
class Cron_General_MinuteHandler {

	public static function work():void {

		console("-- [minuteHandler] start --");
	}
}

/**
 * команды каждые 5 минут
 */
class Cron_General_Minute5Handler {

	public static function work():void {

		console("-- [minute5Handler] start --");
	}
}

/**
 * команды каждые 15 минут
 */
class Cron_General_Minute15Handler {

	public static function work():void {

		console("-- [minute15Handler] start --");
	}
}

/**
 * команды каждые 30 минут
 */
class Cron_General_Minute30Handler {

	public static function work():void {

		console("-- [minute30Handler] start --");
	}
}

/**
 * команды каждый час
 */
class Cron_General_HourHandler {

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

		// получаем файл для чтения построчно
		$file = self::_getFileLine(LOG_FILE_PATH);

		// читаем файл построчно и возвращаем массив для обновления
		$update_list = self::_readFileLine($file);

		// начинаем транзакцию в базе file_node
		\sharding::pdo("file_node")->beginTransaction();

		// обновляем записи
		self::_updateLastAccess($update_list);

		// закрываем транзакцию
		if (!\sharding::pdo("file_node")->commit()) {
			throw new \returnException("Unsuccessful commit on file_node, method: " . __METHOD__);
		}
	}

	/**
	 * возвращает генератор со строкой из файла для логов
	 */
	protected static function _getFileLine(string $file_log_path):?Generator {

		// если файл не существует
		if (!file_exists($file_log_path)) {
			return null;
		}

		// открываем файл на чтение
		$f = fopen($file_log_path, "r");

		// построчно читаем файл
		/** @noinspection PhpAssignmentInConditionInspection */
		while ($line = fgets($f)) {
			yield $line;
		}

		// закрываем файл
		fclose($f);
	}

	// читает файл построчно, и формирует массив для обновления записей в таблице file_node.file
	protected static function _readFileLine(Generator $file):array {

		$update_list = [];
		foreach ($file as $item) {

			$file_info = self::_getFileInfo($item);
			$part_path = $file_info["part_path"];

			// если запись для этого файла уже имеется в массиве, то сравниваем инкрементим access_count
			// и обновляем last_access_at при необходимости
			if (isset($update_list[$file_info["part_path"]])) {

				$update_list[$part_path]["access_count"]++;
				$update_list[$part_path]["last_access_at"] = $file_info["last_access_at"] > $update_list[$part_path]["last_access_at"]
					? $file_info["last_access_at"] : $update_list[$part_path]["last_access_at"];
				continue;
			}

			$update_list[$part_path] = [
				"access_count"   => 1,
				"last_access_at" => time(),
			];
		}

		return $update_list;
	}

	// получает информацию о файле
	#[ArrayShape(["part_path" => "string", "last_access_at" => "false|int"])]
	protected static function _getFileInfo(string $item):array {

		// разбиваем строку разделителем
		$line = preg_split("/\|#\|/", $item);

		// получаем время запроса
		$last_access_at = strtotime($line[0]);

		// получаем путь до файла, к которому производилось обращение
		// разделяем по пробелу, так как $request имеет следующую структуру:
		// GET /files/path/file.png HTTP/1.0
		$request           = explode(" ", trim($line[1]));
		$request_file_path = $request[1];

		// избавляемся от постфикса в part_path файла
		$part_path = preg_replace("/_w\w+/", "", $request_file_path);
		$part_path = trim($part_path, "/");

		return [
			"part_path"      => $part_path,
			"last_access_at" => $last_access_at,
		];
	}

	// обновляет несколько записей из массива $update_list
	protected static function _updateLastAccess(array $update_list):void {

		// проходимся по массиву
		foreach ($update_list as $key => $item) {

			$item["access_count"] = "access_count + " . $item["access_count"];

			// формируем запрос
			$query = "UPDATE `?p` SET ?u WHERE part_path = ?s LIMIT ?i";

			// осуществляем запрос
			\sharding::pdo("file_node")->update($query, "file", $item, $key, 1);
		}
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
		console("END WORK ... sleep $sleep sec");
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
