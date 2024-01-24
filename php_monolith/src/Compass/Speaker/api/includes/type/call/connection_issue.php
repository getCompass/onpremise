<?php

namespace Compass\Speaker;

/**
 * класс для работы с ошибками в соединении
 * если быть точнее, то класс фиксирует факты неудавшегося
 */
class Type_Call_ConnectionIssue {

	// то время, пока проблема с IP считается актуальной
	protected const _IP_ISSUE_LIFE_TIME = HOUR1 * 48;

	/**
	 * имеет ли IP адрес проблемы с подключением
	 *
	 * @return bool
	 * @throws \parseException
	 */
	public static function isIpHaveIssue(string $ip_address):bool {

		// пытаемся найти запись по ip-адресу
		try {
			$row = Gateway_Db_CompanyCall_CallIpLastConnectionIssue::getOne($ip_address);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		// если последняя проблема случилась недавно
		if ($row["last_happened_at"] + self::_IP_ISSUE_LIFE_TIME > time()) {
			return true;
		}

		return false;
	}

	/**
	 * сохраняем факт случившейся проблемы
	 *
	 * @throws \parseException
	 */
	public static function save(string $ip_address):void {

		Gateway_Db_CompanyCall_CallIpLastConnectionIssue::insertOrUpdate($ip_address, time());
	}
}