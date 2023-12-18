<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с таблицей company_call.call_tester_queue
 */
class Gateway_Db_CompanyCall_CallTesterQueue extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "call_tester_queue";

	// метод для добавления записи
	public static function insert(array $extra):int {

		$insert = [
			"status"      => 0,
			"need_work"   => time(),
			"stage"       => 1,
			"error_count" => 0,
			"created_at"  => time(),
			"updated_at"  => 0,
			"finished_at" => 0,
			"extra"       => $extra,
		];
		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// метод для обновления записи
	public static function set(int $test_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `test_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $test_id, 1);
	}

	// метод для получения записи
	public static function getOne(int $test_id):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query        = "SELECT * FROM `?p` WHERE `test_id` = ?i LIMIT ?i";
		$row          = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $test_id, 1);
		$row["extra"] = fromJson($row["extra"]);

		return $row;
	}

	// получаем ровно одну самую старую задачу
	public static function getOldTaskByStatus(int $status):array {

		// запрос проверен на EXPLAIN (INDEX=cron_call_tester_queue)
		$query = "SELECT * FROM `?p` WHERE `status` = ?i ORDER BY `test_id` ASC LIMIT ?i";

		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $status, 1);
	}

	// -------------------------------------------------------
	// поле extra
	// -------------------------------------------------------

	// текущая структура поля
	protected const _EXTRA_SCHEMA = [
		"initiator_user_id"         => 0,
		"opponent_user_id"          => 0,
		"call_map"                  => "",
		"is_initiator_enable_video" => 0,
		"is_opponent_enable_video"  => 0,
		"is_initiator_established"  => 0,
		"is_opponent_established"   => 0,
	];

	// функция для инициализации схемы extra
	public static function initExtra(int $initiator_user_id, int $opponent_user_id):array {

		$output                      = self::_EXTRA_SCHEMA;
		$output["initiator_user_id"] = $initiator_user_id;
		$output["opponent_user_id"]  = $opponent_user_id;

		return $output;
	}

	// получить инициатора звонка
	public static function getInitiatorUserIdFromExtra(array $extra):int {

		return $extra["initiator_user_id"];
	}

	// получить оппонента звонка
	public static function getOpponentUserIdFromExtra(array $extra):int {

		return $extra["opponent_user_id"];
	}

	// получить map звонка
	public static function getCallMapFromExtra(array $extra):string {

		return $extra["call_map"];
	}

	// устанавливаем map звонка
	public static function setExtraCallMap(array $extra, string $call_map):array {

		$extra["call_map"] = $call_map;
		return $extra;
	}

	// включил ли видео инициатор
	public static function isInitiatorEnableVideo(array $extra):bool {

		return $extra["is_initiator_enable_video"] == 1;
	}

	// включил ли видео оппонент
	public static function isOpponentEnableVideo(array $extra):bool {

		return $extra["is_opponent_enable_video"] == 1;
	}

	// помечаем, что инициатор включил видео
	public static function setInitiatorEnableVideo(array $extra, bool $is_enable):array {

		$extra["is_initiator_enable_video"] = $is_enable ? 1 : 0;
		return $extra;
	}

	// помечаем, что оппонент включил видео
	public static function setOpponentEnableVideo(array $extra, bool $is_enable):array {

		$extra["is_opponent_enable_video"] = $is_enable ? 1 : 0;
		return $extra;
	}

	// инициатор установил соединение
	public static function isInitiatorEstablished(array $extra):bool {

		return $extra["is_initiator_established"] == 1;
	}

	// оппонент установил соединение
	public static function isOpponentEstablished(array $extra):bool {

		return $extra["is_opponent_established"] == 1;
	}

	// помечаем, что инициатор установил соединение
	public static function setInitiatorEstablished(array $extra, bool $is_established):array {

		$extra["is_initiator_established"] = $is_established ? 1 : 0;
		return $extra;
	}

	// помечаем, что оппонент установил соединение
	public static function setOpponentEstablished(array $extra, bool $is_established):array {

		$extra["is_opponent_established"] = $is_established ? 1 : 0;
		return $extra;
	}
}
