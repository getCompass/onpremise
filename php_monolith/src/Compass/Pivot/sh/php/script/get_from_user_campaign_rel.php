<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Дата создания скрипта: 24.11.2023
 * Скрипт может быть использован повторно
 *
 * Главная задача скрипта - получить данные из таблицы pivot_attribution.user_campaign_rel и записать их в файл
 */
class Get_From_User_Campaign_Rel {

	protected const _DB_KEY    = "pivot_attribution";
	protected const _TABLE_KEY = "user_campaign_rel";

	/**
	 * Запускаем работу скрипта
	 */
	public static function run(int $start_at, int $end_at):void {

		if (Type_Script_InputHelper::isDry()) {
			self::debug(yellowText("Скрипт запущен в режиме dry-run"));
		}

		self::_work($start_at, $end_at);
	}

	/**
	 * Работаем
	 */
	protected static function _work(int $start_at, int $end_at):void {

		// получаем список
		$user_campaign_rel_list = self::getListByInterval($start_at, $end_at);

		if (!Type_Script_InputHelper::isDry()) {

			// пишем в файл json
			$json = json_encode($user_campaign_rel_list, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

			$file_path = "data.json";
			if (file_put_contents($file_path, $json)) {
				echo "JSON file created successfully...";
			} else {
				echo "Oops! Error creating JSON file...";
			}

			self::logImportant("записали " . count($user_campaign_rel_list) . " в файл " . $file_path);
			return;
		}

		self::logImportant("получили " . count($user_campaign_rel_list) . " записей");
	}

	/**
	 * Получаем список записей времени
	 */
	public static function getListByInterval(int $start_at, int $end_at):array {

		$query = "SELECT * FROM `?p` WHERE `created_at` BETWEEN ?i AND ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $start_at, $end_at, 100000);

		return array_map(static fn(array $row) => Struct_Db_PivotAttribution_UserCampaignRel::rowToStruct($row), $list);
	}

	/**
	 * Функция для дебага – только если запущен dry
	 * чтобы не захламлять вывод в момент реального запуска
	 */
	protected static function debug(string $text):void {

		// если не dry, то не дебажим
		if (!Type_Script_InputHelper::isDry()) {
			return;
		}

		console($text);
	}

	/**
	 * Это уже 100% должно быть отображено в любом случае
	 */
	protected static function logImportant(string $text):void {

		console($text);
	}
}

$start_at = readline("Введите начало периода выборки в timestamp: ");
if (mb_strlen($start_at) < 1 || $start_at > time()) {

	console(redText("Передано некорректное время начала периода"));
	exit(1);
}

$end_at = readline("Введите конец периода выборки в timestamp: ");
if (mb_strlen($end_at) < 1 || $end_at > time()) {

	console(redText("Передано некорректное время конца периода"));
	exit(1);
}

// запускаем скрипт
Get_From_User_Campaign_Rel::run($start_at, $end_at);

if (Type_Script_InputHelper::isDry()) {

	console(greenText("====================================="));
	console(greenText("Скрипт был выполнен в режиме dry-run!"));
	console(greenText("====================================="));
}