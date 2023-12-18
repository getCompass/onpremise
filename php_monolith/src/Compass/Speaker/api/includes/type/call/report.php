<?php

namespace Compass\Speaker;

/**
 * класс, который работает с жалобами на связь
 */
class Type_Call_Report {

	protected const _ACTIVE_REPORT_STATUS  = 0; // активный статус
	protected const _IN_WORK_REPORT_STATUS = 1; // в работе
	protected const _DONE_REPORT_STATUS    = 2; // закрыт

	public const STATUS_LIST = [
		self::_ACTIVE_REPORT_STATUS,
		self::_IN_WORK_REPORT_STATUS,
		self::_DONE_REPORT_STATUS,
	];

	// заносит репорт на связь в таблицу
	public static function add(string $call_map, int $call_id, int $user_id, string $reason, string $network, array $call_connection_list):void {

		$insert = [
			"call_map"   => $call_map,
			"call_id"    => $call_id,
			"user_id"    => $user_id,
			"status"     => self::_ACTIVE_REPORT_STATUS,
			"created_at" => time(),
			"updated_at" => 0,
			"reason"     => $reason,
			"extra"      => self::_initExtra($network, $call_connection_list),
		];
		Gateway_Db_CompanyCall_ReportConnectionList::insert($insert);
	}

	// собираем дополнительную информацию для репорта
	protected static function _initExtra(string $network, array $call_connection_list):array {

		$extra["ip_address"]           = getIp();
		$extra["user_agent"]           = getUa();
		$extra["network"]              = $network;
		$extra["call_connection_list"] = $call_connection_list;

		return $extra;
	}

	// получаем запись с репортом по call_map
	public static function getOneByCallMap(string $call_map):array {

		return Gateway_Db_CompanyCall_ReportConnectionList::getOneByCallMap($call_map);
	}

	// получаем запись с репортом по call_id
	public static function getByCallId(int $report_call_id):array {

		return Gateway_Db_CompanyCall_ReportConnectionList::getOne($report_call_id);
	}

	// получаем список жалоб
	public static function getList(int $user_id, mixed $status, int $from_created_at, int $count, int $offset):array {

		return Gateway_Db_CompanyCall_ReportConnectionList::getList($user_id, $status, $from_created_at, $count, $offset);
	}

	// обновить запись с жалобой
	public static function set(int $report_call_id, array $set):void {

		Gateway_Db_CompanyCall_ReportConnectionList::set($report_call_id, $set);
	}
}