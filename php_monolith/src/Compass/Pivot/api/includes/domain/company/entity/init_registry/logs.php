<?php

namespace Compass\Pivot;

/**
 * Класс для работы с логами в init реестре компании
 */
class Domain_Company_Entity_InitRegistry_Logs {

	protected const COMPANY_CREATE_SUCCESS      = 1;
	protected const COMPANY_CREATE_INVALID      = 2;
	protected const COMPANY_START_OCCUPATION    = 10;
	protected const COMPANY_FINISHED_OCCUPATION = 11;
	protected const COMPANY_INVALID_OCCUPATION  = 12;

	/**
	 * Функция для добавления успешного лога создания
	 */
	public static function addCompanyCreateSuccessLog(array $logs):array {

		$type = self::COMPANY_CREATE_SUCCESS;
		return self::_addLog($logs, $type);
	}

	/**
	 * Функция для добавления лога старта занятия компании
	 */
	public static function addCompanyStartOccupationLog(array $logs):array {

		$type = self::COMPANY_START_OCCUPATION;
		return self::_addLog($logs, $type);
	}

	/**
	 * Функция для добавления лога конца занятия компании
	 */
	public static function addCompanyFinishedOccupationLog(array $logs):array {

		$type = self::COMPANY_FINISHED_OCCUPATION;
		return self::_addLog($logs, $type);
	}

	/**
	 * Функция для добавления лога ошибки занятия компании
	 */
	public static function addCompanyInvalidOccupationLog(array $logs):array {

		$type = self::COMPANY_INVALID_OCCUPATION;
		return self::_addLog($logs, $type);
	}

	/**
	 * Функция для добавления неуспешного лога
	 */
	public function addCompanyCreateInvalidLog(array $logs):array {

		$type = self::COMPANY_CREATE_INVALID;
		return self::_addLog($logs, $type);
	}

	/**
	 * Функция для добавления лога
	 *
	 * @param array $logs
	 * @param int   $type
	 *
	 * @return array
	 */
	protected static function _addLog(array $logs, int $type):array {

		$logs[time()] = $type;
		return $logs;
	}
}